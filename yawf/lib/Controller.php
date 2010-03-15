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
    protected $lang;    // the two character language code e.g. "en"
    protected $render;  // array of data to be rendered inside views
    protected $params;  // a copy of all the request parameters sent

    // Set up this new Controller object (with optional render data)

    public function set_up_for_app($app, &$render = array())
    {
        $this->app = $app;
        $this->view = $app->get_file(); // "faq" from "www.yawf.org/project/faq"
        $this->path = $app->get_folder().'/'.$this->view;  // e.g. "project/faq"
        $this->render = $render;        // data to be rendered in views
        $this->set_params();            // request parameters passed in
        $this->set_lang();              // the browser language setting
        @session_start();               // start a session for the user
    }

    // Render the requested view

    public function render()
    {
        // Call the controller's view method

        $this->before();
        $view = $this->view;
        if (method_exists($this, $view)) $this->$view();
        $this->after();

        // Render the view with a data array

        $this->render['content'] = $this->app->render_view($this->view, $this->render);
        return $this->app->render_type($this->app->get_content_type(), $this->render);
    }

    // Set up render data defaults (called by $this->app)

    public function set_up_render_data(&$render = array())
    {
        $render['app'] = $this->app;
        $render['view'] = $this->view;
        $render['lang'] = $this->lang;
        $render['params'] = $this->params;
    }

    // Before just start a session

    protected function before()
    {
        // Override in controllers
    }

    // After there's nothing to do

    protected function after()
    {
        // Override in controllers
    }

    // Get/set a cookie

    protected function cookie($name, $value = NULL, $expires = 0, $path = '/', $domain = COOKIE_DOMAIN, $secure = FALSE)
    {
        if (!is_null($value)) setcookie($name, $value, $expires, $path, $domain, $secure);
        return array_key($_COOKIE, $name);
    }

    // Get/set a session variable

    protected function session($name, $value = NULL)
    {
        if (!is_null($value)) $_SESSION[$name] = $value;
        return array_key($_SESSION, $name);
    }

    // Change the view to be rendered

    protected function set_view($view)
    {
        $this->view = $view;
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

    // Redirect to another URL

    protected function redirect($url, $exit = FALSE)
    {
        $this->app->redirect($url, $exit);
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

    protected function send_mail($file, $render = array())
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

// End of Controller.php
