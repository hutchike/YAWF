<?php
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

// Classes should extend YAWF for hooks

class YAWF // Yet Another Web Framework
{
    private static $start = 0; // msecs
    private static $hooks = array();
    private static $props = array();

    // Note the start time of YAWF

    public static function start()
    {
        if (self::$start) throw new Exception('YAWF has already started');
        self::$start = microtime(TRUE);         // Keep note of our start time
        self::hook('default', 'self::unknown'); // Set the default hook method
    }

    // YAWF respond to a web request

    public static function respond_to_web_request()
    {
        self::start();
        error_reporting(E_ALL | E_STRICT);
        $uri = array_key($_SERVER, 'REQUEST_URI');

        $app_class = preg_match('/_test($|[^\w])/', $uri) ? 'App_test' : 'App';
        require_once "lib/$app_class.php";
        $app = new $app_class();
        $controller = $app->new_controller();

        try { echo $controller->render(); }
        catch (Exception $e) { self::handle_exception($app, $e); }
        if (isset($php_errormsg)) $app->add_error_message($php_errormsg);
        $controller->report_errors();
        self::finish('Rendered ' . $uri);
    }

    // Write benchmarking performance in the log file

    public static function finish($log_info = 'Finished')
    {
        if (defined('BENCHMARKING_ON'))
        {
            load_helper('Log');
            $msecs = (int)( 1000 * ( microtime(TRUE) - self::$start ) );
            Log::info($log_info . " after $msecs ms");
        }
        exit;
    }

    // Handle an exception by displaying or redirecting

    protected static function handle_exception($app, $e)
    {
        $error_message = nl2br($e);
        if (ini_get('display_errors')) echo $error_message;
        elseif (defined('EXCEPTION_REDIRECT')) $app->redirect(EXCEPTION_REDIRECT);
        $app->add_error_message($error_message);
    }

    // Throw an "Unknown method" exception

    public static function unknown($name, $args)
    {
        $info = ($args ? " with args:\n" . dump($args) : '');
        throw new Exception('Unknown method ' . $name . '() called' . $info);
    }

    // Hook a method name to some other static method.
    // This is useful to add dynamic calls at runtime.

    public static function hook($name, $method = NULL)
    {
        return (is_null($method) ? array_key(self::$hooks, $method)
                                 : self::$hooks[$name] = $method);
    }

    // Get or set a YAWF prop, for example an object.
    // This is useful for registering static methods.

    public static function prop($prop, $value = NULL)
    {
        return (is_null($value) ? array_key(self::$props, $prop)
                                : self::$props[$prop] = $value);
    }

    // Catch all undefined methods calls

    public function __call($name, $args)
    {
        // Look for a hooked method to call

        $method = array_key(self::$hooks, $name);
        if (!$method) $method = array_key(self::$hooks, 'default');
        if ($method === 'return') return; // optimization
        elseif ($method) eval("$method(\$name, \$args);");
    }

    // Configure the assert checking by using a config

    public function assert_checking($config = array())
    {
        $is_on = (array_key($config, 'ASSERT_CHECK_ON') === TRUE);
        assert_options(ASSERT_ACTIVE, $is_on);
        if ($is_on) assert_options(ASSERT_CALLBACK, 'YAWF::assert_failed');
    }

    // Throw exceptions when assertions fail (if we're checking)

    public static function assert_failed($file, $line, $message)
    {
        throw new Exception("Assert failed at line $line in $file: $message");
    }
}

// End of YAWF.php
