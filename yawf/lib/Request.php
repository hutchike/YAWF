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

class Request extends YAWF
{
    protected $app;     // either the "App" or the "App_test" object
    protected $flash;   // an object to send data into the next view
    protected $cookie;  // an object to get & set $_COOKIE variable,
    protected $server;  // an object to get & set $_SERVER variable,
    protected $session; // an object to get & set $_SESSION variable

    protected function setup_request_for($app)
    {
        @session_start();
        $this->app = $app;
        $this->flash = $this->new_flash_object();
        $this->cookie = $this->new_cookie_object();
        $this->server = $this->new_server_object();
        $this->session = $this->new_session_object();
    }

    // Get or set a cookie

    protected function cookie($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        if (!is_null($value)) $this->cookie->set($name, $value, $expires, $path, $domain, $secure);
        return $this->cookie->$name;
    }

    // Get or set a flash message (used by App)

    public function flash($name, $value = NULL) 
    {
        return (is_null($value) ? $this->flash->$name
                                : $this->flash->$name = $value);
    }

    // Redirect to another URL

    protected function redirect($url, $options = array())
    {
        $this->app->redirect($url, $options);
    }

    // Mail errors to the webmaster

    public function report_errors()
    {
        // Get error details

        $errors = $this->app->get_error_messages();
        if (!count($errors)) return;

        // Send errors email

        $folder = $this->app->get_folder();
        $file = $this->app->get_file();
        $render = array(
                    'to'        => WEBMASTER_EMAIL,
                    'subject'   => APP_NAME . " errors in $folder/$file",
                    'errors'    => $errors,
                    );
        return $this->send_mail('errors', $render);
    }

    // Send some mail as text & HTML multipart (depends on the Mail helper)

    protected function send_mail($file, $render)
    {
        return $this->app->send_mail($file, $render);
    }

    // Return new controller flash object

    protected function new_flash_object()
    {
        return new Request_flash();
    }

    // Return new controller cookie object

    protected function new_cookie_object()
    {
        return new Request_cookie();
    }

    // Return new controller server object

    protected function new_server_object()
    {
        return new Request_server();
    }

    // Return new controller session object

    protected function new_session_object()
    {
        return new Request_session();
    }

    // Assert that something "should" be true

    protected function should($desc, $passed = FALSE, $test_data = NULL)
    {
        $this->app->test_case($desc, $passed, $test_data);
    }

    // Assert that something "should not" be true

    protected function should_not($desc, $failed = TRUE, $test_data = NULL)
    {
        $this->app->test_case('not ' . $desc, !$failed, $test_data);
    }
}

class Request_flash
{
    const SESSION_KEY = '__flash__';
    private $flash;

    public function __construct()
    {
        $this->flash = array_key($_SESSION, self::SESSION_KEY, array());
        $_SESSION[self::SESSION_KEY] = array();
    }

    public function __get($name)
    {
        return array_key($this->flash, $name);
    }

    public function __set($name, $value)
    {
        return $name == 'now' ? $this->now('notice', $value)
                              : $_SESSION[self::SESSION_KEY][$name] = $value;
    }

    public function now($name, $value = NULL)
    {
        if (is_array($name))
        {
            foreach ($name as $key => $val) $this->flash[$key] = $val;
        }
        else
        {
            return is_null($value) ? array_key($this->flash, $name)
                                   : $this->flash[$name] = $value;
        }
    }
}

class Request_cookie
{
    public function __get($name)
    {
        return array_key($_COOKIE, $name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function set($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        setcookie($name, $value, $expires, $path, $domain, $secure);
    }
}

class Request_server
{
    public function __get($key)
    {
        return array_key($_SERVER, strtoupper($key));
    }

    public function __set($key, $value)
    {
        $_SERVER[strtoupper($key)] = $value;
    }
}

class Request_session
{
    public function __get($key)
    {
        return array_key($_SESSION, $key);
    }

    public function __set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}

// End of Request.php
