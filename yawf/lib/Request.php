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
        $this->set_params();
        $this->cookie = $this->cookie_object();
        $this->server = $this->server_object();
        $this->session = $this->session_object();
    }

    // Allow method overriding using the "_method" parameter
    // (or the "X_HTTP_METHOD_OVERRIDE" custom HTTP header).

    public function request_method()
    {
        return strtolower(first($this->params->_method,
                                $this->server->x_http_method_override,
                                $this->server->request_method));
    }

    // Return the requested content type set in HTTP headers
    // Don't return the "x-www-form-urlencoded" content type

    public function request_type()
    {
        $type = strtolower($this->server->content_type);
        $type = preg_replace('/;charset=.*$/', '', $type); // strip the encoding
        if ($type == 'application/x-www-form-urlencoded') $type = NULL;
        return ($type ? array_key(self::$request_types, $type)
                      : $this->app->get_content_type());
    }

    // Return the language

    public function lang()
    {
        return $this->app->get_lang();
    }

    // Get or set a params value (used by yash)

    public function params($key, $value = NULL) 
    {
        return (is_null($value) ? $this->params->$key
                                : $this->params->$key = $value);
    }

    // Get or set a cookie value (used by yash)

    public function cookie($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        if (!is_null($value)) $this->cookie->set($name, $value, $expires, $path, $domain, $secure);
        return $this->cookie->$name;
    }

    // Get or set a server value (used by yash)

    public function server($key, $value = NULL) 
    {
        return (is_null($value) ? $this->server->$key
                                : $this->server->$key = $value);
    }

    // Get or set a session value (used by yash)

    public function session($key, $value = NULL) 
    {
        return (is_null($value) ? $this->session->$key
                                : $this->session->$key = $value);
    }

    // Redirect to another URL

    public function redirect($uri, $options = array())
    {
        $this->app->redirect($uri, $options);
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

    public function send_mail($file, $render)
    {
        // Allow the $mailer object to be defined as a YAWF prop

        $mailer = first(YAWF::prop(Symbol::MAILER), $this->app);
        if ($mailer instanceof Mailer) return $mailer->send_mail($file, $render);
        else throw new Exception('The mailer should implement the "Mailer" interface');
    }

    // Set the request parameters that we use to create the params object

    public function set_params($request = array(), $options = array())
    {
        $this->params = $this->params_object($request, $options);
        return $this;
    }

    // Return a request params object, using the $_REQUEST array by default

    protected function params_object($request = array(), $options = array())
    {
        if (!count($request) && $params = YAWF::prop(Symbol::PARAMS)) return $params;
        else $params = new Object();

        $trim_whitespace = array_key($options, 'trim_whitespace', PARAMS_TRIM_WHITESPACE);
        $format_as_html = array_key($options, 'format_as_html', PARAMS_FORMAT_AS_HTML);
        $strip_slashes = array_key($options, 'strip_slashes', PARAMS_STRIP_SLASHES);
        if (!count($request)) $request =& $_REQUEST;
        foreach ($request as $field => $value)
        {
            if ($trim_whitespace) $value = trim($value);
            if ($strip_slashes) $value = stripslashes($value);
            if ($format_as_html) $value = htmlentities($value);
            if (preg_match('/^[\[\{]/', $value)) $value = json_decode($value);
            if (strstr($field, Symbol::ARROW))
            {
                list($object, $field) = explode(Symbol::ARROW, $field);
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

    // Return a request cookie object

    protected function cookie_object()
    {
        if ($cookie = YAWF::prop(Symbol::COOKIE)) return $cookie;
        else return YAWF::prop(Symbol::COOKIE, new Request_cookie());
    }

    // Return a request server object

    protected function server_object()
    {
        if ($server = YAWF::prop(Symbol::SERVER)) return $server;
        else return YAWF::prop(Symbol::SERVER, new Request_server());
    }

    // Return a request session object

    protected function session_object()
    {
        if ($session = YAWF::prop(Symbol::SESSION)) return $session;
        else return YAWF::prop(Symbol::SESSION, new Request_session());
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
