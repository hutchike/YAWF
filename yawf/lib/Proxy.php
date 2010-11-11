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

// Replace this class by writing your own class in "myapp/app/services/REST.php"

load_helper('CURL');

class Proxy
{
    const DEFAULT_FORMAT = 'json';

    private static $defaults = array();
    private $username;
    private $password;
    private $class;
    private $object;
    private $type;
    private $url;
    private $has_changed;

    public function __construct($class, $url = NULL)
    {
        $this->class = $class;
        $this->object = new $class();
        $this->type = array_key(self::$defaults, 'format', self::DEFAULT_FORMAT);
        $this->url = $url ? $url : $this->default_url($class);
        $this->has_changed = FALSE;
    }

    public static function login($username, $password)
    {
        self::$defaults['username'] = $username;
        self::$defaults['password'] = $password;
    }

    public static function server($server)
    {
        self::$defaults['server'] = rtrim($server, '/');
    }

    public static function format($format)
    {
        self::$defaults['format'] = strtolower($format);
    }

    public function from($url)
    {
        $this->url = $url;
    }

    public function load($id = 0)
    {
        $class = $this->class;
        $type = $this->type;
        $url = $this->url . '/' . $id;
        CURL::get($url, array("Content-type: text/$type"));

        // TODO: Make it work with other formats too

        $data = trim($data, "()\n ");
        $data = json_decode($data, TRUE);
        if (!array_key($data, $class)) return 0;
        $this->object = new $class($data[$class]);
        return $this->object->get_id();
    }

    public function save()
    {
        if (!$this->has_changed) return;
        return $this->object->get_id() ? $this->update() : $this->insert();
    }

    public function insert()
    {
        // TODO - post the object
    }

    public function update()
    {
        if (!$this->has_changed) return;
        // TODO - put the object
    }

    public function delete()
    {
        // TODO - delete the object
    }

    public function __get($field)
    {
        return ($this->object ? $this->object->$field : NULL);
    }

    public function __set($field, $value)
    {
        if ($this->object) $this->object->$field = $value;
        $this->has_changed = TRUE;
        return $value;
    }

    protected function default_url($class = NULL)
    {
        $default_url = array_key(self::$defaults, 'server', '');
        if ($default_url && $class) $default_url .= "/$class";
        return $default_url;
    }

    protected function secure_url()
    {
        $url = $this->url;
        if ($this->username && $this->password)
        {
            $url = $this->username . ':' . $this->password . '@' . $url;
        }
        elseif (self::$username && self::$password)
        {
            $url = self::$username . ':' . self::$password . '@' . $url;
        }
        return $url;
    }
}

// End of Proxy.php
