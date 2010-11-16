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
    protected $type;    // a string with the content type, e.g. html
    protected $lang;    // the two character language code e.g. "en"
    protected $render;  // array of data to be rendered inside views
    protected $params;  // a copy of all the request parameters sent

    // Set up this new Controller object for an app with render data

    public function setup_for_app($app, &$render)
    {
        $this->view = $app->get_file(); // "faq" from "www.yawf.org/project/faq"
        $this->path = $app->get_folder().'/'.$this->view;  // e.g. "project/faq"
        $this->render = $render;        // data to be rendered in views
        $this->set_params();            // request parameters passed in
        $this->set_lang();              // the browser language setting
        $this->setup_request_for($app); // inherited from Request class
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
        $this->setup_path_configs($render);
        $render->app = $this->app;
        $render->view = $this->view;
        $render->path = $this->path;
        $render->lang = $this->lang;
        $render->flash = $this->flash;
        $render->params = $this->params;
    }

    // Setup path configs from render data

    protected function setup_path_configs(&$render)
    {
        foreach (get_object_vars($render) as $field => $value)
        {
            if ($value === Symbol::PATH_CONFIG)
            {
                $config_file = Text::pluralize($field);
                $render->$field = $this->get_path_config_from($config_file);
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

    // Change the view to be rendered

    protected function set_view($view)
    {
        $this->view = $view;
    }

    // Change the display content type

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
}

// End of Controller.php
