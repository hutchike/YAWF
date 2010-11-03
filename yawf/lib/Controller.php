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

class Controller extends YAWF
{
    protected $app;     // either the "App" or the "App_test" object
    protected $view;    // a string naming the view file for display
    protected $type;    // a string with the content type, e.g. html
    protected $lang;    // the two character language code e.g. "en"
    protected $render;  // array of data to be rendered inside views
    protected $params;  // a copy of all the request parameters sent
    protected $flash;   // an object to send data into the next view
    protected $cookie;  // an object to get & set $_COOKIE variable,
    protected $server;  // an object to get & set $_SERVER variable,
    protected $session; // an object to get & set $_SESSION variable

    // Set up this new Controller object for an app with render data

    public function setup_for_app($app, &$render)
    {
        $this->app = $app;
        $this->view = $app->get_file(); // "faq" from "www.yawf.org/project/faq"
        $this->path = $app->get_folder().'/'.$this->view;  // e.g. "project/faq"
        $this->render = $render;        // data to be rendered in views
        $this->set_params();            // request parameters passed in
        $this->set_lang();              // the browser language setting
        @session_start();               // start a session for the user
        $this->flash = new Controller_flash();
        $this->cookie = new Controller_cookie();
        $this->server = new Controller_server();
        $this->session = new Controller_session();
    }

    // Render the requested view

    public function render($view = null, $options = array())
    {
        // Get the view (e.g. "index") and the type (e.g. "html")

        $this->view = is_null($view) ? $this->view : $view;
        $this->type = array_key($options, 'type', $this->app->get_content_type());

        // Call the controller's view method

        $this->before();
        $method = strtr($this->view, '-', '_');
        if (method_exists($this, $method)) $this->$method();
        $this->after();

        // Render the view with a data array

        $this->render->content = $this->app->render_view($this->view, $this->render, $options);
        return $this->app->render_type($this->type, $this->render);
    }

    // Set up render data defaults (called by $this->app)

    public function setup_render_data(&$render)
    {
        $render->app = $this->app;
        $render->view = $this->view;
        $render->path = $this->path;
        $render->lang = $this->lang;
        $render->flash = $this->flash;
        $render->params = $this->params;
    }

    // Before there's nothing to do

    protected function before()
    {
        // Override in controllers
    }

    // After there's nothing to do

    protected function after()
    {
        // Override in controllers
    }

    // Change the view to be rendered

    protected function set_view($view)
    {
        $this->view = $view;
    }

    // Change the content type to show

    protected function set_type($type)
    {
        $this->type = $type;
    }

    // Parse request params (from the $_REQUEST array by default)

    protected function set_params($request = array(), $options = array())
    {
        $trim_whitespace = array_key($options, 'trim_whitespace', PARAMS_TRIM_WHITESPACE);
        $format_as_html = array_key($options, 'format_as_html', PARAMS_FORMAT_AS_HTML);
        $strip_slashes = array_key($options, 'strip_slashes', PARAMS_STRIP_SLASHES);
        $this->params = new Object();
        if (!count($request)) $request = $_REQUEST;
        foreach ($request as $field => $value)
        {
            if ($trim_whitespace) $value = trim($value);
            if ($strip_slashes) $value = stripslashes($value);
            if ($format_as_html) $value = htmlentities($value);
            if (strstr($field, '->'))
            {
                list($object, $field) = preg_split('/\->/', $field);
                if (!$this->params->$object) $this->params->$object = new Object();
                $this->params->$object->$field = $value;
            }
            else
            {
                $this->params->$field = $value;
            }
        }
    }

    // Set the requested language by checking supported languages

    protected function set_lang($lang = NULL, $supported_languages = SUPPORTED_LANGUAGES)
    {
        // Only web browsers send the HTTP_ACCEPT_LANGUAGE header

        if (!$lang) $lang = array_key($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '');
        $lang = substr($lang, 0, 2); // take the primary language

        // Check that the language is supported by our app config

        $lang = strtolower($lang);
        if (!$lang || !preg_match("/$lang,/i", $supported_languages . ','))
        {
            $lang = DEFAULT_LANGUAGE;
        }
        $this->lang = $lang;
    }

    // Get the language setting

    public function get_lang()
    {
        return $this->lang;
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

    // Send any errors to the webmaster

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

    // Send some mail as text & HTML multipart (depends on the MailHelper)

    protected function send_mail($file, $render)
    {
        return $this->app->send_mail($file, $render);
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

class Controller_flash
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

class Controller_cookie
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

class Controller_server
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

class Controller_session
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

// End of Controller.php
