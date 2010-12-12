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

/**
 * The Controller class executes application logic and renders views.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Controller extends Request
{
    protected $view;    // a string naming the view file for display
    protected $path;    // a string holding the folder and view path
    protected $type;    // a string with the content type, e.g. html
    protected $flash;   // an object to send data into the next view
    protected $render;  // array of data to be rendered inside views

    /**
     * Set up this new Controller object for an app with render data
     *
     * @param App $app the application
     * @param Object $render the render data
     */
    public function setup_for_app($app, $render)
    {
        $this->view = $app->get_file(); // "faq" from "www.yawf.org/project/faq"
        $this->path = $app->get_path(); // e.g. "project/faq"
        $this->render = $render;        // data to be rendered in views
        $this->setup_request($app);     // inherited from Request class
        $this->flash = $this->flash_object(); // uses a request session
    }

    /**
     * Get or set a flash message (used by App)
     *
     * @param String $key the flash key (e.g. "notice" or "now")
     * @param String $value the flash value (optional)
     * @return String the flash value
     */
    public function flash($key, $value = NULL) 
    {
        return (is_null($value) ? $this->flash->$key
                                : $this->flash->$key = $value);
    }

    /**
     * Render the requested view by calling methods on the controller subclass
     * including "before", the view method, "after", then rendering view data.
     *
     * @param String $view the name of the view to render
     * @param Array $options rendering options (e.g. "type" to set content type)
     * @return String the content to display in the response to the client
     */
    public function render($view = null, $options = array())
    {
        // Get the view (e.g. "index") and the type (e.g. "html")

        $this->view = first($view, $this->view);
        $this->type = first(array_key($options, 'type'),
                            $this->type,
                            $this->app->get_content_type());

        // Call the controller's view method

        $this->before();
        $method = strtr($this->view, '-', '_');
        if (method_exists($this, $method)) $this->$method();
        $this->after();

        // Render the view with a data array

        $this->render->content = $this->app->render_view($this->view, $this->render, $options);
        return $this->app->render_type($this->type, $this->render);
    }

    /**
     * Set up render data defaults (called by $this->app)
     *
     * @param Object $render the render data object to setup
     */
    public function setup_render_data($render)
    {
        $this->render_path_data($render);
        $render->app = $this->app;
        $render->view = $this->view;
        $render->path = $this->path;
        $render->flash = $this->flash;
        $render->params = $this->params;
        $render->cookie = $this->cookie;
        $render->server = $this->server;
        $render->session = $this->session;
    }

    /**
     * Setup path-dependent data to be rendered. For example by setting the
     * "$render->title" to be "path_config", a YAML file called "titles.yaml"
     * will be used to find the title for this view in the list for the lang.
     *
     * @param Object $render the render data object to check for path data
     */
    protected function render_path_data($render)
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

    /**
     * Before there's nothing to do (override this in your controller subclass)
     */
    protected function before()
    {
        // Override in controllers
    }

    /**
     * After there's nothing to do (override this in your controller subclass)
     */
    protected function after()
    {
        // Override in controllers
    }

    /**
     * Set the view to be rendered
     *
     * @param String $view the name of the view to be rendered
     * @return Controller this controller object for method chaining
     */
    public function set_view($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Set the display content type
     *
     * @param String $type the content type to be rendered (e.g. "txt")
     * @return Controller this controller object for method chaining
     */
    public function set_type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Return a value corresponding to this path & lang from a path config file
     *
     * @param String $config_file the name of the config file to read
     * @return String the value after looking up this path in the config file
     */
    protected function get_path_config_from($config_file)
    {
        $langs = Config::load($config_file);
        if (is_array($langs) && $lang = array_key($langs, $this->lang()))
        {
            if ($value = array_key($lang, $this->path)) return $value;
            if ($value = array_key($lang, Symbol::DEFAULT_WORD)) return $value;
        }
        return '';
    }

    /**
     * Get the controller flash object
     *
     * @return Controller_flash the controller flash object
     */
    protected function flash_object()
    {
        if ($flash = YAWF::prop(Symbol::FLASH)) return $flash;
        else return new Controller_flash();
    }
}

/**
 * The Controller_flash class manages flash messages in this request and
 * the next one. It does this by storing future messages in the session.
 * Although flash objects typically store "notice", "warning" and "error"
 * messages, they can also be used to store any kind of data object at all.
 * This makes flash objects a powerful and flexible way to add useful logic.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Controller_flash extends YAWF
{
    const SESSION_VAR = '__flash__';
    private $session;       // A session object that we can "get" and "set"
    private $flash_now;     // Flash info to display in the current view
    private $flash_next;    // Flash info to display in the next view

    /**
     * Create a new controller flash object
     *
     * @param Array $array an optional array of flash messages to display now
     */
    public function __construct($array = NULL)
    {
        $this->session = YAWF::prop(Symbol::SESSION);
        $var = self::SESSION_VAR;
        if (!is_object($this->session->$var)) $this->session->$var = new Object();
        $this->flash_now = is_null($array) ? $this->session->$var // a reference
                                           : new Object($array);
        $this->session->$var = $this->flash_next = new Object();
    }

    /**
     * Get a flash message corresponding to a message key (e.g. "notice")
     *
     * @param String $key the flash message key (e.g. "notice" or "warning")
     */
    public function __get($key)
    {
        return $this->flash_now->$key;
    }

    /**
     * Get a flash message corresponding to a message key (e.g. "notice").
     * If the key is "now" or ends in "_now" then the flash message is shown
     * in the current request, otherwise it's shown in the next one by default.
     *
     * @param String $key the flash message key (e.g. "notice" or "warning")
     * @param String $value the flash message value (e.g. "check your email")
     */
    public function __set($key, $value)
    {
        if ($key == 'now') $key = 'notice_now';
        if (preg_match('/^(\w+)_now$/', $key, $matches))
        {
            return $this->now($matches[1], $value);
        }
        else
        {
            return $this->flash_next->$key = $value;
        }
    }

    /**
     * Get/set a flash message (or messages) to display in the current request.
     * If an array is passed then all its key/value pairs will setup the flash.
     *
     * @param String $key the flash key to get or set (may be an Array instead)
     * @param Object $value the flash value to set (optional)
     * @return String the flash value corresponding to the key
     */
    public function now($key, $value = NULL)
    {
        if (is_array($key)) // allow arrays to set key/value pairs
        {
            foreach ($key as $k => $v) $this->flash_now->$k = $v;
            return ''; // it doesn't make sense to return a value
        }
        else // either set a new value or return the current value
        {
            return is_null($value) ? $this->flash_now->$key
                                   : $this->flash_now->$key = $value;
        }
    }
}

// End of Controller.php
