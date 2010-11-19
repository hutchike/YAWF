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

class Controller extends Request
{
    protected $view;    // a string naming the view file for display
    protected $path;    // a string holding the folder and view path
    protected $type;    // a string with the content type, e.g. html
    protected $lang;    // the two character language code e.g. "en"
    protected $flash;   // an object to send data into the next view
    protected $render;  // array of data to be rendered inside views

    // Set up this new Controller object for an app with render data

    public function setup_for_app($app, &$render)
    {
        $this->view = $app->get_file(); // "faq" from "www.yawf.org/project/faq"
        $this->path = $app->get_folder().'/'.$this->view;  // e.g. "project/faq"
        $this->render = $render;        // data to be rendered in views
        $this->set_lang();              // the browser language setting
        $this->setup_request($app);     // inherited from Request class
        $this->flash = $this->flash_object(); // uses a request session
    }

    // Get or set a flash message (used by App)

    public function flash($key, $value = NULL) 
    {
        return (is_null($value) ? $this->flash->$key
                                : $this->flash->$key = $value);
    }

    // Render the requested view

    public function render($view = null, $options = array())
    {
        // Get the view (e.g. "index") and the type (e.g. "html")

        $this->view = is_null($view) ? $this->view : $view;
        if ($type = array_key($options, 'type')) $this->type = $type;
        else $this->type = first($this->type, $this->app->get_content_type());

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
        $this->render_path_data($render);
        $render->app = $this->app;
        $render->view = $this->view;
        $render->path = $this->path;
        $render->lang = $this->lang;
        $render->flash = $this->flash;
        $render->params = $this->params;
        $render->cookie = $this->cookie;
        $render->server = $this->server;
        $render->session = $this->session;
    }

    // Setup path-dependent data to be rendered.
    // This is useful for titles & descriptions.

    protected function render_path_data(&$render)
    {
        foreach (get_object_vars($render) as $field => $value)
        {
            if ($value === Symbol::PATH_CONFIG)
            {
                $config_file = Text::pluralize($field);
                $render->$field = $this->get_path_config_from($config_file);
            }
            elseif ($value === Symbol::PATH_METHOD)
            {
                $method = 'get_' . $field; // e.g. "get_title"
                $render->$field = $this->$method($this->path);
            }
        }
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

    // Set the view to be rendered

    public function set_view($view)
    {
        $this->view = $view;
        return $this;
    }

    // Set the display content type

    public function set_type($type)
    {
        $this->type = $type;
        return $this;
    }

    // Set the requested language by checking supported languages

    public function set_lang($lang = NULL, $supported_languages = SUPPORTED_LANGUAGES)
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
        return $this;
    }

    // Get the language setting

    public function get_lang()
    {
        return $this->lang;
    }

    // Return a value for a lang from a path config file

    protected function get_path_config_from($config_file)
    {
        $langs = Config::load($config_file);
        if (is_array($langs) && $lang = array_key($langs, $this->lang))
        {
            if ($value = array_key($lang, $this->path)) return $value;
            if ($value = array_key($lang, Symbol::DEFAULT_WORD)) return $value;
        }
        return '';
    }

    // Get the controller flash object

    protected function flash_object()
    {
        if ($flash = YAWF::prop(Symbol::FLASH)) return $flash;
        else return new Controller_flash();
    }
}

class Controller_flash extends YAWF
{
    const SESSION_VAR = '__flash__';
    private $session;       // A session object that we can "get" and "set"
    private $flash_now;     // Flash info to display in the current view
    private $flash_next;    // Flash info to display in the next view

    public function __construct($array = NULL)
    {
        $this->session = YAWF::prop(Symbol::SESSION);
        $var = self::SESSION_VAR;
        if (!is_object($this->session->$var)) $this->session->$var = new Object();
        $this->flash_now = is_null($array) ? $this->session->$var // a reference
                                           : new Object($array);
        $this->session->$var = $this->flash_next = new Object();
    }

    public function __get($key)
    {
        return $this->flash_now->$key;
    }

    public function __set($key, $value)
    {
        if ($key == 'now') return $this->now('notice', $value);
        return $this->flash_next->$key = $value;
    }

    public function now($key, $value = NULL)
    {
        if (is_array($key)) // allow arrays to set key/value pairs
        {
            foreach ($key as $k => $v) $this->flash_now->$k = $v;
        }
        else // either set a new value or return the current value
        {
            return is_null($value) ? $this->flash_now->$key
                                   : $this->flash_now->$key = $value;
        }
    }
}

// End of Controller.php
