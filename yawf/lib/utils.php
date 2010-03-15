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

// A simple "Object" class for field/value objects

class Object
{
    function __construct($data = array())
    {
        foreach ($data as $field => $value)
        {
            $this->$field = $value;
        }
    }

    function __get($var)
    {
        return isset($this->$var) ? $this->$var : NULL;
    }
}

// Return a key from an array or the default value

function array_key($array, $key, $default = NULL)
{
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

// Return a random value from an array

function array_rand_value($array)
{
    return $array[array_rand($array)];
}

// Use the argument list to return a default value

function default_value()
{
    $args = func_get_args();
    foreach ($args as $arg)
        if ($arg) return $arg;
    return NULL;
}

// Find a file in the "app" or "yawf" folders

function file_found($path)
{
    if (substr($path, 0, 1) === '/') return file_exists($path);
    return file_exists('app/' . $path) || file_exists('yawf/' . $path);
}

// Get the contents at a URL

function url_get_contents($url, $options = array())
{
    if (!preg_match('/^http/', $url))
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    }

    $contents = file_get_contents($url);

    if (array_key($options, 'strip_xml_declaration'))
    {
        $contents = preg_replace('/<\?xml [^\?]+\?>\n?/i', '', $contents);
    }
    return $contents;
}

// Split a comma/space separated list

function split_list($list)
{
    return preg_split('/[,\s]\s*/', $list);
}

// Load some PHP files with "require_once"

function load_files($dir, $files)
{
    foreach ($files as $file)
    {
        $path = $dir . '/' . $file . '.php';
        if (!file_found($path))
        {
            throw new Exception("File $path not found");
        }
        require_once $path;
    }
}

// Load some controllers 

function load_controller($controller) { load_controllers($controller); }
function load_controllers() // list of controllers
{
    $controllers = func_get_args();
    load_files('controllers', $controllers);
}

// Load some helpers

function load_helper($helper) { load_helpers($helper); }
function load_helpers() // list of helpers
{
    $helpers = func_get_args();
    load_files('helpers', $helpers);
}

// Load some models

function load_model($model) { load_models($model); }
function load_models() // list of models
{
    $models = func_get_args();
    load_files('models', $models);
    foreach ($models as $model)
    {
        $object = new $model();
        $object->set_up();
    }
}

// Load some plugins

function load_plugin($plugin) { load_plugins($plugin); }
function load_plugins() // list of plugins
{
    $plugins = func_get_args();
    load_files('plugins', $plugins);
}

// Autoload classes in the "lib"

function __autoload($class_name)
{
    require_once('lib/' . $class_name . '.php');
}

// Copy the Ruby on Rails "h()"

function h($text)
{
    return htmlentities($text);
}

// End of utils.php
