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

/**
 * The YAWF object responds to web requests by creating an "App"
 * object, a "Controller" object, then rendering a web response.
 *
 * The YAWF class should be inherited by all classes so that it
 * may handle exceptions and unknown method calls. It provides
 * useful static methods such as "hook" to hook unknown method
 * calls, and "prop" to register props for mock tests or other
 * kinds of dependency injection.
 *
 * The safe way to finish responding to a web request is to
 * call the YAWF::finish() method because this will perform
 * logging and benchmarking, unlike a simple "exit" call.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class YAWF // Yet Another Web Framework
{
    private static $start = 0; // msecs
    private static $hooks = array();
    private static $props = array();

    /**
     * Note the start time of YAWF
     */
    public static function start()
    {
        if (self::$start) throw new Exception('YAWF has already started');
        self::$start = microtime(TRUE);         // Keep note of our start time
        self::hook('default', 'self::unknown'); // Set the default hook method
    }

    /**
     * Respond to a web request
     */
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

    /**
     * Write benchmarking performance in the log file, then exit
     *
     * @param String $log_info info written to the log (default is "Finished")
     */
    public static function finish($log_info = 'Finished')
    {
        if (defined('BENCHMARKING_ON'))
        {
            load_tool('Log');
            $msecs = (int)( 1000 * ( microtime(TRUE) - self::$start ) );
            Log::info($log_info . " after $msecs ms");
        }
        exit;
    }

    /**
     * Handle an exception by displaying (via the app) or redirecting
     *
     * @param App $app the app
     * @param Exception $e the exception to handle
     */
    protected static function handle_exception($app, $e)
    {
        $error_message = nl2br($e);
        if (ini_get('display_errors')) echo $error_message;
        elseif (defined('EXCEPTION_REDIRECT')) $app->redirect(EXCEPTION_REDIRECT);
        $app->add_error_message($error_message);
    }

    /**
     * Throw an "Unknown method" exception
     *
     * @param String $name the name of the unknown method
     * @param Array $args the arguments passed to the unknown method
     */
    public static function unknown($name, $args)
    {
        $info = ($args ? " with args:\n" . dump($args) : '');
        throw new Exception('Unknown method ' . $name . '() called' . $info);
    }

    /**
     * Get or set a YAWF hook name to route unknown method calls.
     * This is useful to add dynamic calls at runtime.
     *
     * @param String $name the name of the hooked method (e.g. "my_alias")
     * @param String $method the method to call (e.g. "Class::my_method")
     * @return String the method hooked to the name
     */
    public static function hook($name, $method = NULL)
    {
        return (is_null($method) ? array_key(self::$hooks, $name)
                                 : self::$hooks[$name] = $method);
    }

    /**
     * Get or set a YAWF prop, which is typically an object.
     * This is useful for applying dependency injection.
     *
     * @param String $prop the name of the prop (e.g. "Symbol::APP")
     * @param String $value the value of the prop (e.g. "new App()")
     * @return String the value of the prop
     */
    public static function prop($prop, $value = NULL)
    {
        return (is_null($value) ? array_key(self::$props, $prop)
                                : self::$props[$prop] = $value);
    }

    /**
     * Catch all undefined methods calls by calling a hooked method
     *
     * @param String $name the name of the unknown method
     * @param Array $args the arguments passed to the unknown method
     */
    public function __call($name, $args)
    {
        // Look for a hooked method to call

        $method = array_key(self::$hooks, $name);
        if (!$method) $method = array_key(self::$hooks, 'default');
        if ($method === 'return') return; // optimization
        elseif ($method) eval("$method(\$name, \$args);");
    }

    /**
     * Configure the assert checking by using a config
     *
     * @param Array $config an array of user-defined constants
     */
    public function assert_checking($config = array())
    {
        $is_on = (array_key($config, 'ASSERT_CHECK_ON') === TRUE);
        assert_options(ASSERT_ACTIVE, $is_on);
        if ($is_on) assert_options(ASSERT_CALLBACK, 'YAWF::assert_failed');
    }

    /**
     * Throw exceptions when assertions fail (if "ASSERT_CHECK_ON" is true)
     *
     * @param String $file the file where the assertion failed
     * @param String $line the line number where the assertion failed
     * @param String $message the assertion failure message to display
     */
    public static function assert_failed($file, $line, $message)
    {
        throw new Exception("Assert failed at line $line in $file: $message");
    }
}

// End of YAWF.php
