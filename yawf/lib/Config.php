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

load_tool('YAML');

/**
 * The Config class reads and parses config files written in YAML.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Config extends YAWF
{
    protected static $configs = array();

    /**
     * Load a config file, and optionally force a file reload
     *
     * @param String $config_name the name of the config to load (excl ".yaml")
     * @param Boolean $reload whether to reload a file that's already loaded
     * @return Array the config data as an assoc array
     */
    public static function load($config_name, $reload = FALSE)
    {
        // Return a matching loaded config (unless reloading)

        if ($reload) self::$configs[$config_name] = NULL;
        if ($config = array_key(self::$configs, $config_name)) return $config;

        // Load a config file by looking in "app" and "yawf"

        $file = '/configs/' . $config_name . '.yaml';
        if (file_exists(Symbol::APP . $file))
            self::$configs[$config_name] = YAML::parse_file(Symbol::APP . $file);
        elseif (file_exists(Symbol::YAWF . $file))
            self::$configs[$config_name] = YAML::parse_file(Symbol::YAWF . $file);
        else
            throw new Exception("Config file \"$config_name.yaml\" not found");

        // Return the loaded config file as a PHP data array

        return self::$configs[$config_name];
    }

    /**
     * Define some constants by reading a PHP array of keys and values.
     * Note that the constant names will always be uppercase.
     * By setting a "prefix" option, constant names can be "DB_*" for example.
     *
     * @param Array $array the array of constant names and values
     * @param Array $options optional settings such as "prefix" and "suffix"
     */
    public static function define_constants($array, $options = array())
    {
        foreach ($array as $key => $value)
        {
            $prefix = array_key($options, 'prefix', '');
            $suffix = array_key($options, 'suffix', '');
            if (is_array($value)) $value = join(', ', $value);
            $name = strtoupper($prefix . $key . $suffix);
            if (!defined($name)) define($name, $value);
        }
    }
}

//End of Config.php
