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
    if (!is_object($object) && !is_array($object)) return $object;
    if ($object instanceof Modelled) $object = $object->data();
    elseif (is_object($object)) $object = get_object_vars($object);
    return count($object) ? array_map('object_to_array', $object) : NULL;
}

/**
 * Return an array of data from an array of objects, e.g. models
 *
 * @param Array $array the array of objects whose data is to be extracted
 * @return Array the array of arrays of object data
 */
function object_data($objects)
{
    assert('is_array($objects)');
    $data = array();
    foreach ($objects as $object) $data[] = $object->data();
    return $data;
}

/**
 * Return a list of field values from a list of objects.
 * If a single field is passed, then a list is returned.
 * If two fields are passed, an assoc array is returned,
 * with field1 as the array key and field2 as the value.
 *
 * @param Array $objects the list of objects
 * @param String $field1 the field whose value is returned
 * @param String $field2 the field whose value is returned (for assoc arrays)
 * @return Array a list of field values from the list of objects
 */
function object_fields($objects, $field1, $field2 = NULL)
{
    assert('is_array($objects)');
    assert('is_string($field1)');
    $values = array();
    foreach ($objects as $object)
    {
        if (is_null($field2)) $values[] = $object->$field1;
        else $values[$object->$field1] = $object->$field2;
    }
    return $values;
}

/**
 * Sort an array of objects on a field
 *
 * @param String $field the field to compare
 * @param Array $array the array of objects to sort
 * @return Array the sorted array
 */
$object_sort_field = 'undefined';
function objects_sort_by($field, $array)
{
    global $object_sort_field;
    assert('is_array($array)');
    if (count($array) == 0) return $array;
    $object_sort_field = $field;
    usort($array, 'object_cmp');
    return $array;
}

/**
 * Compare two objects by comparing the same field in each object
 *
 * @param Object $left the left object to compare
 * @param Object $right the right object to compare
 * @param String $field the field to compare (defaults to $object_sort_field)
 * @return Integer 0 if the same, -1 if $left is less, 1 if $left is greater
 */
function object_cmp($left, $right, $field = NULL)
{
    global $object_sort_field;
    $field = first($field, $object_sort_field);
    $value1 = $left->$field;
    $value2 = $right->$field;
    if ($value1 == $value2) return 0;
    return (is_numeric($value1) ?
            ($value1 < $value2 ? -1 : 1) :
            strcmp($value1, $value2));
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
 * Truncate the keys of an array so they are no longer than a maximum length
 *
 * @param Array $array the array with keys to truncate
 * @param Integer $max_length the maximum length of an array key
 * @param String $append any text to append, e.g. "..."
 * @return Array an copy of the array with its keys truncated
 */
function array_keys_truncate($array, $max_length, $append = '')
{
    $truncated = array();
    foreach ($array as $key => $value)
    {
        $new_key = substr($key, 0, $max_length);
        if ($new_key != $key) $new_key .= $append;
        $truncated[$new_key] = $value;
    }
    return $truncated;
}

/**
 * Prune an object or array by removing empty fields (i.e. string length zero)
 *
 * @param Object/Array $obj_or_array the object or array to prune
 * @return Array a pruned array which is a copy of the original object or array
 */
function array_prune($obj_or_array)
{
    if (!$obj_or_array) $obj_or_array = array();
    $arry = is_array($obj_or_array) ? $obj_or_array
                                    : object_to_array($obj_or_array);
    $pruned = array();
    foreach ($arry as $field => $value)
    {
        if (strlen($value)) $pruned[$field] = $value;
    }
    return $pruned;
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
 * Return whether an assoc array is passed to this function as an argument
 *
 * @param Array $array the array to test (or any variable at all really)
 * @return Boolean whether an assoc array was passed as an argument or not
 */
function is_assoc_array($array)
{
    return is_array($array) &&
           array_keys($array) !== range(0, count($array) - 1); 
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
 * Split a (usually) comma-separated list into an array list using a regex 
 *
 * @param String $text_list a comma-separated list (e.g. "this, that,other")
 * @param String $regex the regex to use (it's ",\s*" by default)
 * @return Array an array of list items
 */
function split_list($text_list, $regex = ',\s*')
{
    $list = preg_split('/' . $regex . '/', $text_list);
    return count($list) == 1 && $list[0] == '' ? array() : $list;
}

/**
 * Load some PHP files with "require_once", only if they are not already loaded
 *
 * @param String $dir the directory from which to load the files
 * @param Array $files a list of files to load from the directory
 * @param Boolean $is_mock whether to load a mock version of the file
 * @return Array a list of the files that were loaded with "require_once"
 */
function load_files($dir, $files, $is_mock = FALSE)
{
    static $loaded = array();   // to skip loading files that were loaded before
    $required_files = array();  // to return a list of files that are now loaded
    foreach ($files as $file)
    {
        $path = $dir . '/' . $file . '.php';
        if (array_key($loaded, $path)) continue;
        $real_path = $is_mock ? $dir . '/mocks/' . $file . '.php' : $path;
        if (!file_found($real_path)) // to prevent fatal YAWF errors
        {
            throw new Exception("File \"$real_path\" not found");
        }
        require_once $real_path;
        $loaded[$path] = TRUE;
        $required_files[] = $file;
    }
    return $required_files;
}

/**
 * Load a mock file
 *
 * @param String $dir the directory to find the mock (e.g. "tools")
 * @param String/Array $file the mock to load (e.g. "CURL")
 */
function load_mock($dir, $file)
{
    if (is_string($file)) $file = array($file);
    load_files($dir, $file, TRUE);
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
 * @param String $helper the helper to load
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
 * @param String $interface the interface to load
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
 * @param String $model the model to load
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
    $loaded = load_files('models', $models);
    foreach ($loaded as $model)
    {
        $object = new $model();
        $object->setup();
    }
}

/**
 * Load a plugin
 *
 * @param String $plugin the plugin to load
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
 * @param String $service the service to load
 */
function load_service($service, $version = 1) { load_services($service, $version); }

/**
 * Load a list of services, passed as function arguments
 *
 * @param Array a list of services passed as function arguments
 */
function load_services() // list of services
{
    $services = func_get_args();
    $version = array_pop($services);
    load_files("services/$version", $services);
}

/**
 * Load a tool
 *
 * @param String $tool the tool to load
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

/**
 * Copy the Ruby on Rails "t()" translate function to translate some text
 *
 * @param String $lookup the lookup string (see "configs/translate.yaml" file)
 * @param Array $replacements an optional array of text replacements to make
 * @return String the translated text after any replacements have been made
 */
function t($lookup, $replacements = array())
{
    static $is_loaded = FALSE; // for speed
    if (!$is_loaded) load_tool('Translate');
    $is_loaded = TRUE;
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
 * Return a trimmed line of text with a single newline at the end
 *
 * @param String $line a line of text with possible whitespace at each end
 * @return String a trimmed line of text with a single newline at the end
 */
function nl($line = '')
{
    return trim($line) . "\n";
}

/**
 * Postfix some text with some other text, but only if it's not empty
 *
 * @param String $text the text to modify if it's not empty
 * @param String $postfix something to add to the end of the text
 * @return String the modified text
 */
function postfix($text, $postfix)
{
    return $text ? $text . $postfix : '';
}

/**
 * Prefix some text with some other text, but only if it's not empty
 *
 * @param String $text the text to modify if it's not empty
 * @param String $prefix something to add to the start of the text
 * @return String the modified text
 */
function prefix($text, $prefix)
{
    return $text ? $prefix . $text : '';
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
 * @param Integer $chars the number of characters to indent (default is 2)
 * @param String $text the text to indent
 * @return String the indented text
 */
function indent($chars, $text)
{
    assert('is_int($chars)');
    assert('is_string($text)');
    $spaces = '                                                '; // enough?
    $indent = substr($spaces, 0, $chars);
    $text = $indent . join("\n$indent", explode("\n", trim($text))) . "\n";
    $text = preg_replace('/(<textarea[^>]*>)(.+)(<\/textarea>)/ise', "stripslashes('\\1'.unindent('\\2').'\\3')", $text);
    return $text;
}

/**
 * Remove indentation whitespace added by "indent"
 *
 * @param String $text the indented text to unindent
 * @return String the text with indentations removed
 */
function unindent($text)
{
    return preg_replace('/\n +/s', "\n", trim($text));
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

/**
 * Get information from a stack trace about the caller of the current function
 *
 * @param $levels_up how many levels up to look
 * @param $info info to return - php.net/manual/en/function.debug-backtrace.php
 * @return String the requested info from the requested level up the stack trace
 */
function caller($levels_up = 1, $info = 'object')
{
    $trace = debug_backtrace();
    return array_key($trace[$levels_up + 1], $info);
}

/**
 * Return this computer's hostname by reading the "/etc/hostname" file or
 * other file specified as a parameter. Return "localhost" if file not found.
 *
 * @param $file the file containing the hostname ("/etc/hostname" by default)
 * @return String this computer's hostname
 */
function hostname($file = '/etc/hostname')
{
    return file_exists($file) ? trim(file_get_contents($file)) : 'localhost';
}

/**
 * Return the host IP address of the client by checking "HTTP_X_FORWARDED_FOR"
 * and "REMOTE_ADDR" on the $_SERVER global array, or returning a default addr.
 *
 * @param String $default the default address to return if none is found
 * @return String the client's host IP address
 */
function hostaddr($default = '')
{
    return array_key($_SERVER, 'HTTP_X_FORWARDED_FOR',
                                array_key($_SERVER, 'REMOTE_ADDR', $default));
}

/**
 * Return some highlighted code
 *
 * @param String $code the code to highlight
 * @return String the highlighted code
 */
function highlight($code)
{
    $pretty = highlight_string('<'.'?' . $code . '?'.'>', TRUE);
    $pretty = preg_replace('/&lt;\?/', '', $pretty);      // remove PHP open
    $pretty = preg_replace('/\?&gt;/', '', $pretty);      // remove PHP close
    $pretty = preg_replace('/"><br \/>/', '">', $pretty); // remove first <br/>
    return '<div class="highlight">' . $pretty . '</div>';// wrap up in a <div>
}

/**
 * This is a convenience function that calls the PHP "putenv" function.
 * It seems to be a far better complement to the PHP "getenv" function?
 *
 * @param String $key the environment variable key to set
 * @param String $value the environment variable value to set
 */
function setenv($key, $value)
{
    putenv("$key=$value");
}

/**
 * Return the current user, for example "www-data"
 *
 * @return String the current user
 */
function user()
{
    return first(getenv('USER'), getenv('APACHE_RUN_USER'));
}

// End of utils.php
