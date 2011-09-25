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

load_interfaces('Modelled', 'Persisted', 'Validated');
load_tool('REST');

/**
 * The Remote class provides remote data manipulation capabilities via REST
 * by providing a facade to a data model object via the Modelled, Persisted
 * and Validated interfaces.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Remote extends Relating_model implements Modelled, Persisted, Validated
{
    const DEFAULT_TYPE = Symbol::JSON; // (it's built into PHP)
    const DB = '__db';

    private static $defaults = array();
    private $username;      // Do we need a username too?
    private $password;      // Do we need a password too?
    private $object;        // The object that gets remoted
    private $type;          // What type are we marshalling?
    private $url;           // At what URL is the data found?
    private $response;      // Data we received from the server

    /**
     * Create a remote object behaving *like* a regular model
     *
     * @param String/Object $class_or_object the model class or an actual object
     * @param String $url the URL of the model REST service at the remote server
     */
    public function __construct($class_or_object, $url = NULL)
    {
        if (is_string($class_or_object))
        {
            $this->object = new $class_or_object();
            assert('$this->object instanceof SQL_model');
        }
        elseif (is_object($class_or_object) && $class_or_object instanceof SQL_model)
        {
            $this->object = $class_or_object;
        }
        else
        {
            throw new Exception('Cannot create Remote object for class ' .
                                get_class($class_or_object));
        }
        $this->data =& $this->object->data;
        $this->changed =& $this->object->changed;
        $this->type = array_key(self::$defaults, 'type', self::DEFAULT_TYPE);
        $this->url = $url ? $url : $this->default_url($this->get_class());
        $this->response = NULL;
    }

    /**
     * Set a default (e.g. "server", "username" & "password")
     *
     * @param String $field the default field to set (e.g. "server")
     * @param String $value the default value to set (e.g. "localhost")
     */
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

    /**
     * Get a default setting (see above)
     *
     * @param String $field the default field to get (e.g. "server")
     */
    public static function get_default($field)
    {
        return array_key(self::$defaults, $field);
    }

    /**
     * Set the username and password for auth
     *
     * @param String $username the auth username
     * @param String $password the auth password
     * @return Remote this object for method chaining
     */
    public function set_auth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the URL to request
     *
     * @param String $url the URL of the model REST service at the remote server
     * @return Remote this object for method chaining
     */
    public function set_url($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set the data content type
     *
     * @param String $type the content type to marshall the model object data
     * @return Remote this object for method chaining
     */
    public function set_type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Perform a remote insert request using the REST "post" method
     *
     * @return Integer the ID of the inserted model object, or zero on failure
     */
    public function insert()
    {
        if (!is_object($this->object)) return 0;
        if ($this->object->id) return 0;
        if (!$this->is_validated()) return 0;
        $url = $this->secure_url();
        $data = array($this->get_class() => $this->object->data());
        $data[self::DB] = $this->get_database();
        $this->response = REST::post($url, $data, $this->type);
        if (is_null($this->response)) return 0;
        $id = $this->get_id_from_response();
        if (is_numeric($id)) $this->object->id = $id;
        $this->check_response();
        $this->object->changed = array();
        return $id;
    }

    /**
     * Perform a remote update request using the REST "put" method
     *
     * @return Remote this object for method chaining, or NULL on failure
     */
    public function update()
    {
        if (!$this->has_changed()) return $this;
        return $this->update_all_fields(array_keys($this->changed));
    }

    /**
     * Perform a remote update request using the REST "put" method.
     * Note that this method will update *all* this model object's fields
     * that are included in this model object's data array - see fields().
     * You can override this by providing an arg list of fields to update.
     *
     * @return Remote this object for method chaining, or NULL on failure
     */
    public function update_all_fields()
    {
        if (!is_object($this->object)) return NULL;
        if (!$this->object->id) return NULL;
        if (!$this->is_validated()) return NULL;

        $fields = func_get_args();
        $count = count($fields);
        if ($count == 0) $fields = $this->fields(); // the most usual case
        elseif ($count == 1 && is_array($fields[0])) $fields = $fields[0];
        $fields[] = $this->object->get_id_field(); // can't update without

        $url = $this->secure_url() . '/' . $this->object->id;
        $data = array($this->get_class() => $this->object->data($fields));
        $data[self::DB] = $this->get_database();
        if ($this->response = REST::put($url, $data, $this->type)) $this->check_response();
        $this->object->changed = array();
        return $this;
    }

    /**
     * Perform a remote delete request using the REST "delete" method
     *
     * @return Remote this object for method chaining, or NULL on failure
     */
    public function delete()
    {
        if (!is_object($this->object)) return NULL;
        if (!$this->object->id) return NULL;
        $url = $this->secure_url() . '/' . $this->object->id;
        $url .= $this->db_in_url();
        if ($this->response = REST::delete($url, $this->type)) $this->check_response();
        return $this;
    }

    /**
     * Perform a remote "delete all" request using the REST "delete" method
     *
     * @return Remote this object for method chaining, or NULL on failure
     */
    public function delete_all()
    {
        $url = $this->secure_url() . '/-1';
        $url .= $this->db_in_url();
        if ($this->response = REST::delete($url, $this->type)) $this->check_response();
        return $this;
    }

    /**
     * Find all remote model objects that match some conditions
     *
     * @param Array $conditions an array of conditions, e.g. a "join" clause
     * @return Array a list of model objects that match the SQL "where" clause
     */
    public function find_all($conditions = array())
    {
        // Use a REST service to get matching "find_where" data

        $params = array();
        if ($where = array_key($conditions, 'where')) $params['where'] = $where;
        else $params['where'] = $this->where_clause($conditions, FALSE);
        if (!$params['where']) $params['where'] = 'true'; // coz can't be blank
        $params['join'] = array_key($conditions, 'join');
        $params['order'] = $this->get_order();
        $params['limit'] = $this->get_limit();
        $params['offset'] = $this->get_offset();
        $url = $this->secure_url();
        $url .= $this->db_in_url();
        $url .= '&' . http_build_query($params);
        $data = REST::get($url, $this->type);
        if (!is_array($data)) return array();

        // Return a list of new model objects

        $class = $this->get_class();
        $objects = array();
        if ($found = array_key($data, $class))
        {
            foreach ($found as $data)
            {
                $object = $this->new_model_object_for($class);
                $object->data = $data;
                $objects[] = $object;
            }
        }
        return $objects;
    }

    /**
     * Execute a database query and return the result set data fetcher object.
     *
     * Note that Remote objects CANNOT run remote queries because the returned
     * "fetcher" object is persistent so that multiple objects may be returned.
     *
     * @param String $sql the SQL to execute in the database query
     * @return Object a result set data fetcher object
     */
    public function query($sql)
    {
        // Log a warning then call the "query" method to return a local fetcher

        $class = $this->get_class();
        Log::warn("Remote \"$class\" object running SQL query locally: $sql");
        return parent::query($sql);
    }

    /**
     * Return a new model object, given a model name like "user_config"
     *
     * @param String $model the model name, normally with underscores
     * @return SQL_model a new model object
     */
    protected function new_model_object_for($model)
    {
        return new Remote(parent::new_model_object_for($model));
    }

    /**
     * Return the REST response as an object
     *
     * @return Object the REST response as an object
     */
    public function response()
    {
        return array_to_object($this->response);
    }

    /**
     * Return whether the remoted object's field values are locally validated
     *
     * @return Boolean whether the field values are locally validated
     */
    public function is_validated()
    {
        if (!is_object($this->object)) return FALSE;
        if ($this->object->is_validated()) return TRUE;
        $this->response = array(Symbol::VALIDATION_MESSAGES => $this->object->validation_messages());
        return FALSE;
    }

    /**
     * Return any validation messages
     *
     * @return Array an array of validation messages
     */
    public function validation_messages()
    {
        return is_array($this->response) ?
                        array_key($this->response, Symbol::VALIDATION_MESSAGES) : array();
    }

    /**
     * Return the default URL for the REST service of a remoted object class
     *
     * @param String $class the class name for the remoted model
     * @return String the default URL for the remoted model's REST service
     */
    protected function default_url($class = NULL)
    {
        $version = defined('API_VERSION') ? API_VERSION : 1;
        $default_url = array_key(self::$defaults, 'server', '');
        if ($default_url && is_string($class)) $default_url .= "/$version/$class";
        return $default_url;
    }

    /**
     * Prefix a username and password to a URL
     *
     * @param String $url an optional URL (defaults to the object's URL)
     * @return String the secure URL for the remoted model's REST service
     */
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

    /**
     * Return a database to be included in a URL, e.g "?db=mydata_live"
     *
     * @return String a database to be included in a URL
     */
    protected function db_in_url()
    {
        return '?' . self::DB . '=' . urlencode($this->get_database());
    }

    /**
     * Check data returned in the response is identical by writing log warnings
     */
    protected function check_response()
    {
        $class = $this->get_class();
        $response = array_key($this->response, $class, $this->response);
        foreach ($this->object->data() as $key => $value)
        {
            $found = first(array_key($response, $key), ''); // no NULL values
            if ((string)$found !== (string)$value) // XML data is all strings
            {
                $message = "Check response \"$key\" for class \"$class\" - expected \"$value\" but received \"$found\"";
                Log::warn($message);
            }
        }
    }

    /**
     * Get the ID from the data array response received from the REST service
     *
     * @return Integer the ID from the data array response from the REST service
     */
    private function get_id_from_response()
    {
        assert('is_array($this->response)');
        if ($data = array_key($this->response, $this->get_class()))
        {
            assert('is_object($this->object)');
            $id_field = $this->object->get_id_field();
            if (is_array($data) && $id = array_key($data, $id_field)) return $id;
        }
        return 0;
    }

    /**
     * Cast this remote model object into another model class
     *
     * @param String $new_class a model class into which to cast this object
     * @param Boolean $has_changed whether the new object has changed or not
     * @return Remote_model this remote model object
     */
    public function cast_into($new_class, $has_changed = NULL)
    {
        if (!is_object($this->object)) return $this;
        $this->object = $this->object->cast_into($new_class, $has_changed);
        $this->data =& $this->object->data;
        $this->changed =& $this->object->changed;
        $this->url = $this->default_url($this->get_class());
        return $this;
    }

    /**
     * Return this remote model object's class name
     *
     * @return String the remote model object's class name
     */
    public function get_class()
    {
        if (!is_object($this->object)) throw new Exception('No remote object');
        return get_class($this->object);
    }

    /**
     * Forward all other method calls to the model object that is being remoted
     *
     * @param String $method the method name that was called
     * @param Array $args an array of arguments passed to the called method
     * @return Object whatever the model object returns from its method call
     */
    public function __call($method, $args)
    {
        if (is_object($this->object))
        {
            switch (count($args))
            {
                case 1: return $this->object->$method($args[0]);
                case 2: return $this->object->$method($args[0], $args[1]);
                case 3: return $this->object->$method($args[0], $args[1], $args[2]);
                case 4: return $this->object->$method($args[0], $args[1], $args[2], $args[3]);
                case 5: return $this->object->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                default: return $this->object->$method();
            }
        }
    }
}

// End of Remote.php
