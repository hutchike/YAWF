<?
// Copyright (c) 2010 Guanoo, Inc.
// 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

load_helpers('CURL', 'Data');

class Remote extends YAWF
{
    const DEFAULT_TYPE = Symbol::JSON; // (it's built into PHP)

    private static $defaults = array();
    private $username;      // Do we need a username too?
    private $password;      // Do we need a password too?
    private $class;         // What class are we remoting?
    private $object;        // The object that gets remoted
    private $type;          // What type are we marshalling?
    private $url;           // At what URL is the data found?
    private $has_changed;   // Did we change our remoted data?
    private $response;      // Data we received from the server

    // Create a remote object behaving *like* a regular model

    public function __construct($class_or_object, $url = NULL)
    {
        if (is_string($class_or_object))
        {
            $this->class = $class_or_object;
            $this->object = new $class_or_object();
            $this->has_changed = FALSE;
        }
        elseif (is_object($class_or_object))
        {
            $this->class = get_class($class_or_object);
            $this->object = $class_or_object;
            $this->has_changed = TRUE;
        }
        $this->type = array_key(self::$defaults, 'type', self::DEFAULT_TYPE);
        $this->url = $url ? $url : $this->default_url($this->class);
        $this->response = NULL;
    }

    // Set a default (e.g. "server", "username" & "password")

    public static function set_default($field, $value = NULL)
    {
        if (is_array($field) && is_null($value))
        {
            foreach ($field as $key => $value)
            {
                self::$defaults[$key] = $value;
            }
        }
        elseif (is_string($field))
        {
            self::$defaults[$field] = $value;
        }
        else
        {
            throw new Exception("Bad default field: " . dump($field));
        }
    }

    // Get a remote default setting (see above)

    public static function get_default($field)
    {
        return array_key(self::$defaults, $field);
    }

    // Set the username and password for auth

    public function auth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    // Set the URL to request

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    // Set the data content type

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    // Perform a remote load request

    public function load($id = 0)
    {
        $class = $this->class;
        $type = $this->type;
        $url = $this->secure_url($this->url . '/' . $id);
        $text = CURL::get($url, $this->headers_for($type));
        $data = Data::from($type, $text);
        if (!array_key($data, $class)) return 0;
        $this->object = new $class($data[$class]);
        return $this->object->get_id();
    }

    // Perform a remote save request

    public function save()
    {
        if (!$this->has_changed) return FALSE;
        return $this->object->get_id() ? $this->update() : $this->insert();
    }

    // Perform a remote insert request using the REST "post" method

    public function insert()
    {
        if (!is_object($this->object)) return 0;
        if ($this->object->get_id()) return 0;
        if (!$this->is_validated()) return 0;
        $type = $this->type;
        $url = $this->secure_url($this->url);
        $data = Data::to($type, array($this->class => $this->object->data()));
        $text = CURL::post($url, $data, $this->headers_for($type));
        $this->response = Data::from($type, $text);
        $id = Data::get_id($this->response, $this->class, $this->object->get_id_field());
        if ($id) $this->object->set_id($id);
        $this->check_response();
        return $id;
    }

    // Perform a remote update request using the REST "put" method

    public function update()
    {
        if (!is_object($this->object) || !$this->has_changed) return NULL;
        if (!$this->object->get_id()) return NULL;
        if (!$this->is_validated()) return NULL;
        $type = $this->type;
        $url = $this->secure_url($this->url . '/' . $this->object->get_id());
        $data = Data::to($type, array($this->class => $this->object->data()));
        $text = CURL::put($url, $data, $this->headers_for($type));
        $this->response = Data::from($type, $text);
        $this->check_response();
        return $this;
    }

    // Perform a remote delete request using the REST "delete" method

    public function delete()
    {
        if (!is_object($this->object)) return NULL;
        if (!$this->object->get_id()) return NULL;
        $type = $this->type;
        $url = $this->secure_url($this->url . '/' . $this->object->get_id());
        $text = CURL::delete($url, $this->headers_for($type));
        $this->response = Data::from($type, $text);
        $this->check_response();
        return $this;
    }

    // Get a field of object data

    public function __get($field)
    {
        return ($this->object ? $this->object->$field : NULL);
    }

    // Set a field of remoted object data

    public function __set($field, $value)
    {
        if ($this->object) $this->object->$field = $value;
        $this->has_changed = TRUE;
        return $value;
    }

    // Get the object data

    public function data()
    {
        return is_object($this->object) ? $this->object->data() : NULL;
    }

    // Get whether it has changed

    public function has_changed()
    {
        return $this->has_changed;
    }

    // Return the object ID

    public function get_id()
    {
        return is_object($this->object) ? $this->object->get_id() : 0;
    }

    // Set the object ID number

    public function set_id($id)
    {
        if (is_object($this->object)) $this->object->set_id($id);
        return $this; // just like normal models do
    }

    // Return response object

    public function response()
    {
        return array_to_object($this->response);
    }

    // Validate our object locally

    public function is_validated()
    {
        if (!is_object($this->object)) return FALSE;
        if ($this->object->is_validated()) return TRUE;
        $this->response = array(Symbol::VALIDATION_MESSAGES => $this->object->validation_messages());
        return FALSE;
    }

    // Return any validation messages

    public function validation_messages()
    {
        return is_array($this->response) ?
                        array_key($this->response, Symbol::VALIDATION_MESSAGES) : NULL;
    }

    // Return the default URL for an object class

    protected function default_url($class = NULL)
    {
        $default_url = array_key(self::$defaults, 'server', '');
        if ($default_url && is_string($class)) $default_url .= "/$class";
        return $default_url;
    }

    // Prefix a username and password to a URL

    protected function secure_url($url = NULL)
    {
        if (is_null($url)) $url = $this->url;
        $protocol = '';
        if (preg_match('/^(https?:\/\/)(.*)$/', $url, $matches))
        {
            $protocol = $matches[1];
            $url = $matches[2];
        }
        $username = first($this->username, self::get_default('username'));
        $password = first($this->password, self::get_default('password'));
        if ($username && $password) $url = "$username:$password" . '@' . $url;
        return $protocol . $url;
    }

    // Check data returned in the response is identical

    protected function check_response($response = NULL)
    {
        if (is_null($response)) $response = $this->response;
        $response = array_key($response, $this->class, $response);
        foreach ($this->object->data() as $key => $value)
        {
            if (($found = array_key($response, $key)) !== $value)
            {
                $class = $this->class;
                $message = "Check response \"$key\" for class \"$class\" - expected \"$value\" but received \"$found\"";
                Log::warn($message);
            }
        }
    }

    // Return an array of client headers for the content type

    protected function headers_for($type, $charset = 'UTF-8')
    {
        $headers = array();
        $headers[] = "User-Agent: YAWF (http://yawf.org/)";
        $headers[] = "Content-Type: application/$type;charset=$charset";
        return $headers;
    }
}

// End of Remote.php
