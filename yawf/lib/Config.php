<?
// Copyright (c) 2009 Guanoo, Inc.
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

load_helper('YAML');

class Config extends YAWF
{
    protected static $configs = array();

    // Return a config file, optionally a config file section

    public static function get($config_name, $section = NULL)
    {
        $config = array_key(self::$configs, $config_name);
        if (!$config) return NULL;
        return $section ? array_key($config, $section) : $config;
    }

    // Load a config file, and optionally force a file reload

    public static function load($config_name, $reload = FALSE)
    {
        // Return a matching loaded config (unless reloading)

        if ($reload) self::$configs[$config_name] = NULL;
        if ($config = array_key(self::$configs, $config_name)) return $config;

        // Load a config file by looking in "app" and "yawf"

        $file = '/configs/' . $config_name . '.yaml';
        if (file_exists('app' . $file))
            self::$configs[$config_name] = YAML::load_file('app' . $file);
        elseif (file_exists('yawf' . $file))
            self::$configs[$config_name] = YAML::load_file('yawf' . $file);

        // Return the loaded config file as a PHP data array

        return self::$configs[$config_name];
    }

    // Define some constants by reading a PHP array of keys and values

    public static function define_constants($array, $options = array())
    {
        foreach ($array as $key => $value)
        {
            $prefix = array_key($options, 'prefix');
            $suffix = array_key($options, 'suffix');
            if (is_array($value)) $value = join(', ', $value);
            define(strtoupper($prefix . $key . $suffix), $value);
        }
    }
}

//End of Config.php
