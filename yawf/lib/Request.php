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

load_interface('Mailer');

/**
 * The Request class provides Controller and Service objects with
 * access to form and query parameters, cookies, the web session
 * and the web server environment. It also makes the app language
 * available, and the requested content type and HTTP method.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Request extends YAWF implements Mailer
{
    /**
     * A mapping of request content types to file types
     */
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

    /**
     * Setup some web request data objects
     *
     * @param App $app the web application object
     */
    protected function setup_request($app)
    {
        $this->app = $app;
        $this->set_params();
        $this->cookie = $this->cookie_object();
        $this->server = $this->server_object();
        $this->session = $this->session_object();
    }

    /**
     * Allow method overriding using the "_method" parameter
     * (or the "X_HTTP_METHOD_OVERRIDE" custom HTTP header).
     *
     * @return String the request method (as a lowercase string)
     */
    public function request_method()
    {
        return strtolower(first($this->params->_method,
                                $this->server->x_http_method_override,
                                $this->server->request_method));
    }

    /**
     * Return the requested content type set in HTTP headers
     * Don't return the "x-www-form-urlencoded" content type
     *
     * @return String the requested content type (as a lowercase string)
     */
    public function request_type()
    {
        $type = strtolower($this->server->content_type);
        $type = preg_replace('/;charset=.*$/', '', $type); // strip the encoding
        if ($type == 'application/x-www-form-urlencoded') $type = NULL;
        return ($type ? array_key(self::$request_types, $type)
                      : $this->app->get_content_type());
    }

    /**
     * Return part of the path
     *
     * @param Integer $position the position in the path, starting at zero
     * @param Boolean $remove_extn whether to remove the file extension or not
     * @return String the requested part of the path
     */
    public function part($position, $remove_extn = FALSE)
    {
        return $this->app->get_part($position, $remove_extn);
    }

    /**
     * Return the language
     *
     * @return String the language setting (as a two-letter language code)
     */
    public function lang()
    {
        return $this->app->get_lang();
    }

    /**
     * Get or set a params value
     *
     * @param String $key the parameter key to get or set
     * @param String $vaue the parameter value to set (optional)
     * @return String the value of the parameter key
     */
    public function params($key, $value = NULL) 
    {
        return (is_null($value) ? $this->params->$key
                                : $this->params->$key = $value);
    }

    /**
     * Get or set a cookie value
     *
     * @param String $name the name of the cookie to get or set
     * @param String $vaue the cookie value to set (optional)
     * @param String $expires the cookie expiry setting (optional)
     * @param String $path the cookie path setting (default is "/")
     * @param String $domain the cookie domain (default is set by COOKIE_DOMAIN)
     * @param String $is_secure whether the cookie is secure (default is FALSE)
     * @return String the value of the cookie
     */
    public function cookie($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $is_secure = FALSE)
    {
        if (!is_null($value)) $this->cookie->set($name, $value, $expires, $path, $domain, $is_secure);
        return $this->cookie->$name;
    }

    /**
     * Get or set a server value
     *
     * @param String $key the server key to get or set
     * @param String $vaue the server value to set (optional)
     * @return String the value of the server key
     */
    public function server($key, $value = NULL) 
    {
        return (is_null($value) ? $this->server->$key
                                : $this->server->$key = $value);
    }

    /**
     * Get or set a session value
     *
     * @param String $key the session key to get or set
     * @param String $vaue the session value to set (optional)
     * @return String the value of the session key
     */
    public function session($key, $value = NULL) 
    {
        return (is_null($value) ? $this->session->$key
                                : $this->session->$key = $value);
    }

    /**
     * Redirect to another URI
     *
     * @param String $uri the URI to redirect to
     * @param Array $options options such as "finish" to force an early finish
     */
    public function redirect($uri, $options = array())
    {
        $this->app->redirect($uri, $options);
    }

    /**
     * Mail errors to the webmaster
     *
     * @return String the mail message that was sent (if any)
     */
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

    /**
     * Send some mail as text & HTML multipart (depends on the Mail helper)
     *
     * @param String $file the file name of the mail view to render
     * @param Object $render render data to include in the mai view
     * @return String the mail message that was sent
     */
    public function send_mail($file, $render = NULL)
    {
        // Allow the $mailer object to be defined as a YAWF prop

        $mailer = first(YAWF::prop(Symbol::MAILER), $this->app);
        if ($mailer instanceof Mailer) return $mailer->send_mail($file, $render);
        else throw new Exception('The mailer should implement the "Mailer" interface');
    }

    /**
     * Set the request parameters that we use to create the params object
     *
     * @param Array $request an array of request params (get, post, put, etc.)
     * @param Array $options an array of options to use when parsing the params
     * @return Request this object
     */
    public function set_params($request = array(), $options = array())
    {
        $this->params = $this->params_object($request, $options);
        return $this;
    }

    /**
     * Set some parameters by reading parts of the URI
     *
     * @param Array a list of parameters to read from the parts of the URI
     * @return Request this object
     */
    public function set_params_from_parts()
    {
        $params = func_get_args();
        if (is_array($params[0])) $params = $params[0];
        $position = 0;
        foreach ($params as $param)
        {
            if ($param && is_null($this->params->$param)) // we don't overwrite
            {
                $value = $this->part($position, TRUE);
                if ($param == 'id' || preg_match('/_id$/', $param))
                {
                    if (!is_numeric($value)) $value = NULL; // ID's are numbers
                }
                if (!is_null($value)) $this->params->$param = $value;
            }
            $position++;
        }
        return $this;
    }

    /**
     * Return a request params object, using the $_REQUEST array by default
     *
     * @param Array $request an array of request params (get, post, put, etc.)
     * @param Array $options an array of options to use when parsing the params
     * @return Object an object holding all the parameter values after parsing
     */
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

    /**
     * Return a request cookie object
     *
     * @return Request_cookie a request cookie object holding cookie values
     */
    protected function cookie_object()
    {
        if ($cookie = YAWF::prop(Symbol::COOKIE)) return $cookie;
        else return YAWF::prop(Symbol::COOKIE, new Request_cookie());
    }

    /**
     * Return a request server object
     *
     * @return Request_server a request server object holding server values
     */
    protected function server_object()
    {
        if ($server = YAWF::prop(Symbol::SERVER)) return $server;
        else return YAWF::prop(Symbol::SERVER, new Request_server());
    }

    /**
     * Return a request session object
     *
     * @return Request_session a request session object holding session values
     */
    protected function session_object()
    {
        if ($session = YAWF::prop(Symbol::SESSION)) return $session;
        else return YAWF::prop(Symbol::SESSION, new Request_session());
    }

    /**
     * Test that something that "should" be true, indeed is true
     *
     * @param String $desc A description of the test case
     * @param Boolean $passed Whether the test case passed
     * @param Object $test_data Test data to display when the test case fails
     */
    protected function should($desc, $passed = FALSE, $test_data = NULL)
    {
        $this->app->test_case($desc, $passed, $test_data);
    }

    /**
     * Test that something "should not" be true, indeed is not true
     *
     * @param String $desc A description of the test case
     * @param Boolean $failed Whether the test case failed
     * @param Object $test_data Test data to display when the test case passes
     */
    protected function should_not($desc, $failed = TRUE, $test_data = NULL)
    {
        $this->app->test_case('not ' . $desc, !$failed, $test_data);
    }
}

/**
 * The Request_cookie class enables the app to get and set HTTP cookies
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Request_cookie extends YAWF
{
    private $cookie;

    /**
     * Create a Request_cookie object
     *
     * @param Array $array an optional array of data to use instead of $_COOKIE
     */
    public function __construct($array = NULL)
    {
        if (is_null($array)) $this->cookie =& $_COOKIE;
        elseif (is_array($array)) $this->cookie = $array; // to enable mocks
        else throw new Exception('Cannot create cookie object for request');
    }

    /**
     * Get a cookie value for a name (e.g. "username")
     *
     * @param String $name the name to find in the cookie object
     * @return String the value corresponding to the cookie name
     */
    public function __get($name)
    {
        return array_key($this->cookie, $name);
    }

    /**
     * Set a cookie value for a name (e.g. "username")
     *
     * @param String $name the name to set in the cookie object
     * @param String $value the value to set in the cookie object
     * @return String the value corresponding to the cookie name
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Set a cookie value for a name, with optional extra cookie parameters
     *
     * @param String $name the name to set in the cookie object
     * @param String $value the value to set in the cookie object
     * @param String $expires the expiry epoch time of the cookie (default is 0)
     * @param String $path the cookie path (default is "/")
     * @param String $domain the cookie domain (default is COOKIE_DOMAIN config)
     * @param Boolean $secure whether the cookie is secure (default is FALSE)
     * @return String the value corresponding to the cookie name
     */
    public function set($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        setcookie($name, $value, $expires, $path, $domain, $secure);
        return $value;
    }
}

/**
 * The Request_server class enables the app to get (and set!) server settings
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Request_server extends YAWF
{
    private $server;

    /**
     * Create a Request_cookie object
     *
     * @param Array $array an optional array of data to use instead of $_SERVER
     */
    public function __construct($array = NULL)
    {
        if (is_null($array)) $this->server =& $_SERVER;
        elseif (is_array($array)) $this->server = $array; // to enable mocks
        else throw new Exception('Cannot create server object for request');
    }

    /**
     * Get a server value for a key (e.g. "remote_addr")
     *
     * @param String $key the key to find in the server object
     * @return String the value corresponding to the server key
     */
    public function __get($key)
    {
        return array_key($this->server, strtoupper($key));
    }

    /**
     * Set a server value for a key (e.g. "remote_addr")
     *
     * @param String $key the key to set in the server object
     * @param String $value the value to set in the server object
     * @return String the value corresponding to the server key
     */
    public function __set($key, $value)
    {
        $this->server[strtoupper($key)] = $value;
    }
}

/**
 * The Request_session class enables the app to get and set session variables
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Request_session extends YAWF
{
    private $session;

    /**
     * Create a Request_cookie object
     *
     * @param Array $array an optional array of data to use instead of $_SESSION
     */
    public function __construct($array = NULL)
    {
        @session_start();
        if (is_null($array)) $this->session =& $_SESSION;
        elseif (is_array($array)) $this->session = $array; // to enable mocks
        else throw new Exception('Cannot create session object for request');
    }

    /**
     * Get a session value for a key
     *
     * @param String $key the key to find in the session object
     * @return Object the value corresponding to the session key
     */
    public function __get($key)
    {
        return array_key($this->session, $key);
    }

    /**
     * Set a session value for a key
     *
     * @param String $key the key to set in the session object
     * @param Object $value the value to set in the session object
     * @return Object the value corresponding to the session key
     */
    public function __set($key, $value)
    {
        return $this->session[$key] = $value;
    }

    /**
     * Destroy the session
     */
    public function destroy()
    {
        @session_destroy();
        $this->session = array();
    }
}

// End of Request.php
