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

// A simple "Object" class for field/value objects

class Object
{
    public function __construct($data = NULL)
    {
        $data = (array)$data;
        foreach ($data as $field => $value) $this->$field = $value;
    }

    public function __get($field)
    {
        return isset($this->$field) ? $this->$field : NULL;
    }

    public function __set($field, $value)
    {
        return $this->$field = $value;
    }

    public function merge($other, $override = FALSE)
    {
        $other = (array)$other;
        foreach ($other as $field => $value)
        {
            if (!$override && isset($this->$field)) continue;
            $this->$field = $value;
        }
    }
}

// Turn an array into an object (or NULL when empty)

function array_to_object($array, $lists_too = FALSE)
{
    if (!is_array($array)) return NULL;
    if (count($array) == 0) return NULL;
    $object = new Object();
    foreach ($array as $key => $value)
    {
        $object->$key = (is_array($value) ?
                            (array_key_exists(0, $value) && !$lists_too ?
                                $value : array_to_object($value, $lists_too))
                            : $value);
    }
    return $object;
}

// Turn an object into an array

function object_to_array($object)
{
    if(!is_object($object) && !is_array($object)) return $object;
    if(is_object($object)) $object = get_object_vars($object);
    return count($object) ? array_map('object_to_array', $object) : NULL;
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

// Return the first non-null value in the argument list

function first()
{
    $args = func_get_args();
    foreach ($args as $arg)
        if (!is_null($arg)) return $arg;
    return NULL;
}

// Find a file in the "app" or "yawf" folders

function file_found($path)
{
    if (substr($path, 0, 1) === '/') return file_exists($path);
    return file_exists('app/' . $path) || file_exists('yawf/' . $path);
}

// Find a file in the "app" or "yawf" folders, and return its contents

function file_contents($path)
{
    if (substr($path, 0, 1) === '/')
        return file_exists($path) ? file_get_contents($path) : NULL;
    if (file_exists("app/$path")) return file_get_contents("app/$path");
    if (file_exists("yawf/$path")) return file_get_contents("yawf/$path");
    return NULL;
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

// Encode array as query string

function urlencode_array($array)
{
    $query = '';
    foreach($array as $key => $value)
    {
        if ($query) $query .= '&';
        $query .= urlencode($key) . '=';
        $query .= is_array($value) ? urlencode(serialize($value)) : urlencode($value);
    }
    return $query;
}

// Split a comma-separated list

function split_list($list)
{
    $list = preg_split('/,\s*/', $list);
    return count($list) == 1 && $list[0] == '' ? array() : $list;
}

// Load some PHP files with "require_once"

function load_files($dir, $files)
{
    foreach ($files as $file)
    {
        $path = $dir . '/' . $file . '.php';
        if (!file_found($path))
        {
            throw new Exception("File \"$path\" not found");
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
        $object->setup();
    }
}

// Load some plugins

function load_plugin($plugin) { load_plugins($plugin); }
function load_plugins() // list of plugins
{
    $plugins = func_get_args();
    load_files('plugins', $plugins);
}

// Load some services 

function load_service($service) { load_services($service); }
function load_services() // list of services
{
    $services = func_get_args();
    load_files('services', $services);
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

// Indent some text with whitespace

function indent($text, $chars = 2)
{
    $spaces = '                                                '; // enough?
    $indent = substr($spaces, 0, $chars);
    return $indent . join("\n$indent", explode("\n", trim($text))) . "\n";
}

// Dump data in object

function dump($object)
{
    return print_r($object, TRUE);
}

// End of utils.php
