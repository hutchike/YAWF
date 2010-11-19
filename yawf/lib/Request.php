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
    // A mapping of request content types to file types

    private static $request_types = array(
        'text/xml' => Symbol::XML,
        'text/html' => Symbol::HTML,
        'text/plain' => Symbol::TXT,
        'text/yaml' => Symbol::YAML,
        'text/json' => Symbol::JSON,
        'text/jsonp' => Symbol::JSON,
        'text/javascript' => Symbol::JSON,
        'text/serialized' => Symbol::SERIALIZED,
        'application/json' => Symbol::JSON,
        'application/jsonp' => Symbol::JSON,
        'application/javascript' => Symbol::JSON,
        'application/serialized' => Symbol::SERIALIZED,
        'application/x-javascript' => Symbol::JSON,
        'application/xml' => Symbol::XML,
        'application/yaml' => Symbol::YAML,
    );

    protected $app;     // either the "App" or the "App_test" object
    protected $params;  // a copy of all the request parameters sent
    protected $cookie;  // an object to get & set $_COOKIE variable,
    protected $server;  // an object to get & set $_SERVER variable,
    protected $session; // an object to get & set $_SESSION variable

    // Setup some web request data objects

    protected function setup_request($app)
    {
        $this->app = $app;
        $this->params = $this->new_params_object();
        $this->cookie = $this->new_cookie_object();
        $this->server = $this->new_server_object();
        $this->session = $this->new_session_object();
    }

    // Allow method overriding using the "_method" parameter
    // (or the "X_HTTP_METHOD_OVERRIDE" custom HTTP header).

    protected function request_method()
    {
        return strtolower(first($this->params->_method,
                                $this->server->x_http_method_override,
                                $this->server->request_method));
    }

    // Return the requested content type set in HTTP headers
    // Don't return the "x-www-form-urlencoded" content type

    protected function request_type()
    {
        $type = strtolower($this->server->content_type);
        $type = preg_replace('/;charset=.*$/', '', $type); // strip the encoding
        if ($type == 'application/x-www-form-urlencoded') $type = NULL;
        return ($type ? array_key(self::$request_types, $type)
                      : $this->app->get_content_type());
    }

    // Get or set a cookie

    protected function cookie($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        if (!is_null($value)) $this->cookie->set($name, $value, $expires, $path, $domain, $secure);
        return $this->cookie->$name;
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
        // Allow the $mailer object to be defined as a YAWF prop

        $mailer = $this->get_prop(Symbol::MAILER, $this->app);
        return $mailer->send_mail($file, $render);
    }

    // Return a new request params object, using the $_REQUEST array by default

    protected function new_params_object($request = array(), $options = array())
    {
        if ($params = YAWF::prop(Symbol::PARAMS)) return $params;
        else $params = new Object(); // uses regular Object class

        $trim_whitespace = array_key($options, 'trim_whitespace', PARAMS_TRIM_WHITESPACE);
        $format_as_html = array_key($options, 'format_as_html', PARAMS_FORMAT_AS_HTML);
        $strip_slashes = array_key($options, 'strip_slashes', PARAMS_STRIP_SLASHES);
        if (!count($request)) $request =& $_REQUEST;
        foreach ($request as $field => $value)
        {
            if ($trim_whitespace) $value = trim($value);
            if ($strip_slashes) $value = stripslashes($value);
            if ($format_as_html) $value = htmlentities($value);
            if (strstr($field, '->'))
            {
                list($object, $field) = preg_split('/\->/', $field);
                if (!$params->$object) $params->$object = new Object();
                $params->$object->$field = $value;
            }
            else
            {
                $params->$field = $value;
            }
        }
        return YAWF::prop(Symbol::PARAMS, $params);
    }

    // Return a new request cookie object

    protected function new_cookie_object()
    {
        return $this->get_prop(Symbol::COOKIE, new Request_cookie());
    }

    // Return a new request server object

    protected function new_server_object()
    {
        return $this->get_prop(Symbol::SERVER, new Request_server());
    }

    // Return a new request session object

    protected function new_session_object()
    {
        return $this->get_prop(Symbol::SESSION, new Request_session());
    }

    // Return an existing or new YAWF prop object
    // This enables mocks to be set up beforehand

    protected function get_prop($symbol, $object)
    {
        if ($prop = YAWF::prop($symbol)) return $prop;
        else return YAWF::prop($symbol, $object);
    }

    // Test that something that "should" be true, indeed is true

    protected function should($desc, $passed = FALSE, $test_data = NULL)
    {
        $this->app->test_case($desc, $passed, $test_data);
    }

    // Test that something "should not" be true, indeed is not true

    protected function should_not($desc, $failed = TRUE, $test_data = NULL)
    {
        $this->app->test_case('not ' . $desc, !$failed, $test_data);
    }
}

class Request_cookie extends YAWF
{
    private $cookie;

    public function __construct($array = NULL)
    {
        if (is_null($array)) $this->cookie =& $_COOKIE;
        elseif (is_array($array)) $this->cookie = $array; // to enable mocks
        else throw new Exception('Cannot create cookie object for request');
    }

    public function __get($name)
    {
        return array_key($this->cookie, $name);
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

class Request_server extends YAWF
{
    private $server;

    public function __construct($array = NULL)
    {
        if (is_null($array)) $this->server =& $_SERVER;
        elseif (is_array($array)) $this->server = $array; // to enable mocks
        else throw new Exception('Cannot create server object for request');
    }

    public function __get($key)
    {
        return array_key($this->server, strtoupper($key));
    }

    public function __set($key, $value)
    {
        $this->server[strtoupper($key)] = $value;
    }
}

class Request_session extends YAWF
{
    private $session;

    public function __construct($array = NULL)
    {
        @session_start();
        if (is_null($array)) $this->session =& $_SESSION;
        elseif (is_array($array)) $this->session = $array; // to enable mocks
        else throw new Exception('Cannot create session object for request');
    }

    public function __get($key)
    {
        return array_key($this->session, $key);
    }

    public function __set($key, $value)
    {
        return $this->session[$key] = $value;
    }

    public function destroy()
    {
        @session_destroy();
    }
}

// End of Request.php
