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
 * It provides services to define constants, get defined constants
 * and cache any parsed YAML files that have been loaded previously.
 * The defined constants are also available as regular PHP constants.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Config extends YAWF
{
    protected static $configs = array();
    protected static $constants = array();

    /**
     * Load a config file, and optionally force a file reload
     *
     * @param String $config_file the name of the config to load (excl ".yaml")
     * @param Boolean $reload whether to reload a file that's already loaded
     * @return Array the config data as an assoc array
     */
    public static function load($config_file = 'app', $reload = FALSE)
    {
        // Return a matching loaded config (unless reloading)

        if ($reload) self::$configs[$config_file] = NULL;
        if ($config = array_key(self::$configs, $config_file)) return $config;

        // Load a config file by looking in "app" and "yawf"

        $file = '/configs/' . $config_file . '.yaml';
        if (file_exists(Symbol::APP . $file))
            self::$configs[$config_file] = YAML::parse_file(Symbol::APP . $file);
        elseif (file_exists(Symbol::YAWF . $file))
            self::$configs[$config_file] = YAML::parse_file(Symbol::YAWF . $file);
        else
            throw new Exception("Config file \"$config_file.yaml\" not found");

        // Return the loaded config file as a PHP data array

        return self::$configs[$config_file];
    }

    /**
     * Define some constants by reading a PHP array of keys and values.
     * Note that the constant names will always be uppercase.
     * By setting a "prefix" option, constant names can be "DB_*" for example.
     * If you've defined your own constants, you should call this function
     * without any parameters to update the cache of user-defined constants.
     *
     * @param Array $array optional array of constant names and values
     * @param Array $options optional settings such as "prefix" and "suffix"
     */
    public static function define_constants($array = array(), $options = array())
    {
        // Define some new constants

        foreach ($array as $key => $value)
        {
            $prefix = array_key($options, 'prefix', '');
            $suffix = array_key($options, 'suffix', '');
            if (is_array($value)) $value = join(', ', $value);
            $name = strtoupper($prefix . $key . $suffix);
            if (!defined($name)) define($name, $value);
        }

        // Update the cache of user-defined constants, and return it

        self::$constants = array_key(get_defined_constants(TRUE), 'user');
        return self::get_constants();
    }

    /**
     * Get an assoc array of all the user-defined constants
     *
     * @return Array user-defined constants as an assoc array
     */
    public static function get_constants()
    {
        return self::$constants;
    }

    /**
     * Get a config setting by looking for the name of a user-defined constant
     *
     * @param String $name the name of the user-defined constant to get
     * @param Boolean $is_required whether the constant is required or not
     * @return Integer/String the value of the user-defined constant
     */
    public static function get($name, $is_required = FALSE)
    {
        $value = array_key(self::$constants, strtoupper($name));
        if ($is_required && is_null($value))
        {
            throw new Exception("Constant \"$name\" is not defined");
        }
        return $value;
    }

    /**
     * Set a config setting and its constant, but only if not already set
     *
     * @param String $name the name of the user-defined constant to set
     * @param String $value the value of the user-defined constant to set
     * @return Boolean whether we successfully set the user-defined constant
     */
    public static function set($name, $value)
    {
        assert('is_string($name)');
        if (defined($name)) return FALSE;
        self::$constants[$name] = $value;
        define($name, $value);
        return TRUE;
    }
}

//End of Config.php
