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

/**
 * A simple "Object" class for field/value objects
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Object
{
    /**
     * Create a new object
     *
     * @param Object $data the initialization data (may be an Array)
     */
    public function __construct($data = NULL)
    {
        $data = (array)$data;
        foreach ($data as $field => $value) $this->$field = $value;
    }

    /**
     * Get the value of an object field
     *
     * @param String $field the object field
     * @return Object whatever the value is (can be any type)
     */
    public function __get($field)
    {
        return isset($this->$field) ? $this->$field : NULL;
    }

    /**
     * Set the value of an object field
     *
     * @param String $field the object field
     * @param Object $value the object value (can be any type)
     * @return Object whatever the value is (can be any type)
     */
    public function __set($field, $value)
    {
        return $this->$field = $value;
    }

    /**
     * Merge another object with this one
     *
     * @param Object $other the other object to merge with this one
     * @param Boolean $override whether to override this object's values (FALSE)
     */
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

/**
 * Turn an array into an object (or NULL when empty)
 *
 * @param Array $array the array to convert
 * @param Boolean $lists_too whether to convert lists to objects also (FALSE)
 * @return Object the resultant object
 */
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

/**
 * Turn an object into an array
 *
 * @param Object $object the object to convert
 * @return Array the resultant array
 */
function object_to_array($object)
{
    if(!is_object($object) && !is_array($object)) return $object;
    if(is_object($object)) $object = get_object_vars($object);
    return count($object) ? array_map('object_to_array', $object) : NULL;
}

/**
 * Return a key from an array or the default value (NULL by default)
 *
 * @param Array $array the array to lookup
 * @param String $key the key to find in the array
 * @return Object the value or the default (returned value can be any type)
 */
function array_key($array, $key, $default = NULL)
{
    assert('is_array($array)');
    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Return a random value from an array
 *
 * @param Array $array the array
 * @return Object a random value from the array (can be any type)
 */
function array_rand_value($array)
{
    return $array[array_rand($array)];
}

/**
 * Return the first non-null value in the argument list
 *
 * @param Array a list of arguments of any length
 * @return Object the first non-null argument or NULL (value can be any type)
 */
function first()
{
    $args = func_get_args();
    foreach ($args as $arg)
        if (!is_null($arg)) return $arg;
    return NULL;
}

/**
 * Return whether a file exists in either of the "app" or "yawf" folders.
 * If the file path begins with a "/" then the absolute path is checked.
 *
 * @param String $path the file path to find
 * @return Boolean whether the file exists in at least one of the folders
 */
function file_found($path)
{
    if (substr($path, 0, 1) === '/') return file_exists($path);
    return file_exists('app/' . $path) || file_exists('yawf/' . $path);
}

/**
*  Return the contents of  a file in the "app" or "yawf" folders
 *
 * @param String $path the path of the file to read
 * @return String the file contents
 */
function file_contents($path)
{
    if (substr($path, 0, 1) === '/')
        return file_exists($path) ? file_get_contents($path) : NULL;
    if (file_exists("app/$path")) return file_get_contents("app/$path");
    if (file_exists("yawf/$path")) return file_get_contents("yawf/$path");
    return NULL;
}

/**
 * Get a normalized URI, using the current app's prefix settings
 *
 * @param String $uri the URI to normalize (e.g. "user/sign-up")
 * @param String $prefix an optional URI prefix
 * @return String the normalized URI
 */
function uri($uri, $prefix = NULL)
{
    return AppView::uri($uri, $prefix);
}

/**
 * Get the contents at a URI
 *
 * @param String $uri the URI to read
 * @param Array $options an optional array of options (such as "prefix")
 * @return String the contents read from the URI
 */
function uri_get_contents($uri, $options = array())
{
    if (!preg_match('/^http/i', $uri))
    {
        $prefix = array_key($options, 'prefix');
        $uri = 'http://' . $_SERVER['HTTP_HOST'] . uri($uri, $prefix);
    }

    $contents = file_get_contents($uri);

    if (array_key($options, 'strip_xml_declaration'))
    {
        $contents = preg_replace('/<\?xml [^\?]+\?\>\n?/i', '', $contents);
    }
    return $contents;
}

/**
 * Encode an assoc array as a query string. Any values that
 * are arrays are JSON encoded to represent their structure.
 *
 * @param Array $array an assoc array of data to URL encode
 * @return String a query string containing URL encoded data
 */
function urlencode_array($array)
{
    $query = '';
    foreach($array as $key => $value)
    {
        if ($query) $query .= '&';
        $query .= urlencode($key) . '=';
        $query .= is_array($value) ? urlencode(json_encode($value)) : urlencode($value);
    }
    return $query;
}

/**
 * Split a comma-separated list
 *
 * @param String $text_list a comma-separated list (e.g. "this, that,other");
 * @return Array an array of list items
 */
function split_list($text_list)
{
    $list = preg_split('/,\s*/', $text_list);
    return count($list) == 1 && $list[0] == '' ? array() : $list;
}

/**
 * Load some PHP files with "require_once"
 *
 * @param String $dir the directory from which to load the files
 * @param Array $files a list of files to load from the directory
 */
function load_files($dir, $files)
{
    static $loaded = array();
    foreach ($files as $file)
    {
        $path = $dir . '/' . $file . '.php';
        if (array_key($loaded, $path)) continue;
        if (!file_found($path)) // to prevent fatal YAWF errors
        {
            throw new Exception("File \"$path\" not found");
        }
        require_once $path;
        $loaded[$path] = TRUE;
    }
}

/**
 * Load a controller
 *
 * @param String $controller the controller to load
 */
function load_controller($controller) { load_controllers($controller); }

/**
 * Load a list of controllers, passed as function arguments
 *
 * @param Array a list of controllers passed as function arguments
 */
function load_controllers() // list of controllers
{
    $controllers = func_get_args();
    load_files('controllers', $controllers);
}

/**
 * Load a helper
 *
 * @param String $controller the controller to load
 */
function load_helper($helper) { load_helpers($helper); }

/**
 * Load a list of helpers, passed as function arguments
 *
 * @param Array a list of helpers passed as function arguments
 */
function load_helpers() // list of helpers
{
    $helpers = func_get_args();
    load_files('helpers', $helpers);
}

/**
 * Load an interface
 *
 * @param String $controller the controller to load
 */
function load_interface($interface) { load_interfaces($interface); }

/**
 * Load a list of interfaces, passed as function arguments
 *
 * @param Array a list of interfaces passed as function arguments
 */
function load_interfaces() // list of interfaces
{
    $interfaces = func_get_args();
    load_files('interfaces', $interfaces);
}

/**
 * Load a model
 *
 * @param String $controller the controller to load
 */
function load_model($model) { load_models($model); }

/**
 * Load a list of models, passed as function arguments
 *
 * @param Array a list of models passed as function arguments
 */
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

/**
 * Load a plugin
 *
 * @param String $controller the controller to load
 */
function load_plugin($plugin) { load_plugins($plugin); }

/**
 * Load a list of plugins, passed as function arguments
 *
 * @param Array a list of plugins passed as function arguments
 */
function load_plugins() // list of plugins
{
    $plugins = func_get_args();
    load_files('plugins', $plugins);
}

/**
 * Load a service
 *
 * @param String $controller the controller to load
 */
function load_service($service) { load_services($service); }

/**
 * Load a list of services, passed as function arguments
 *
 * @param Array a list of services passed as function arguments
 */
function load_services() // list of services
{
    $services = func_get_args();
    load_files('services', $services);
}

/**
 * Load a tool
 *
 * @param String $controller the controller to load
 */
function load_tool($tool) { load_tools($tool); }

/**
 * Load a list of tools, passed as function arguments
 *
 * @param Array a list of tools passed as function arguments
 */
function load_tools() // list of tools
{
    $tools = func_get_args();
    load_files('tools', $tools);
}

/**
 * Autoload classes in the "lib"
 *
 * @param String $class_name the class to autoload from the "lib" directory
 */
function __autoload($class_name)
{
    require_once('lib/' . $class_name . '.php');
}

/**
 * Copy the Ruby on Rails "h()" HTML function to prepare text for HTML display
 *
 * @param String $text the text to clean by replacing HTML with HTML entities
 * @return String the text prepared for HTML display
 */
function h($text)
{
    return htmlentities($text);
}

load_tool('Translate');
/**
 * Copy the Ruby on Rails "t()" translate function to translate some text
 *
 * @param String $lookup the lookup string (see "configs/translate.yaml" file)
 * @param Array $replacements an optional array of text replacements to make
 * @return String the translated text after any replacements have been made
 */
function t($lookup, $replacements = array())
{
    $app = YAWF::prop(Symbol::APP);
    return $app ? Translate::into($app->get_lang(), $lookup, $replacements)
                : NULL;
}

/**
 * Copy the Ruby "p()" function (except this version doesn't return anything)
 *
 * @param Object $thing the thing to print out
 */
function p($thing)
{
    print_r($thing);

    // unlike Ruby, this doesn't return anything
    // - use dump() if you want to see the data.
}

/**
 * Copy the Ruby "puts()" function by writing a line of text
 *
 * @param String $text the text to print out with a newline
 */
function puts($text)
{
    print "$text\n";
}

/**
 * Indent some text with whitespace
 *
 * @param String $text the text to indent
 * @param Integer $chars the number of characters to indent (default is 2)
 * @return String the indented text
 */
function indent($text, $chars = 2)
{
    $spaces = '                                                '; // enough?
    $indent = substr($spaces, 0, $chars);
    return $indent . join("\n$indent", explode("\n", trim($text))) . "\n";
}

/**
 * Return a string representing all the data in object
 *
 * @param Object $object the object to dump
 * @return String the object's data, dumped out in a user-friendly format
 */
function dump($object)
{
    return print_r($object, TRUE);
}

// End of utils.php
