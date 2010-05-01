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

class App extends YAWF
{
    protected $content_type;// derived from the file extension
    protected $controller;  // a controller to render the view
    protected $folder;      // views folder name e.g. "default"
    protected $file;        // the view file name e.g. "index"
    protected $is_silent;   // "TRUE" after we've redirected
    protected $is_testing;
    protected $error_messages;

    // Construct a new App object

    public function __construct()
    {
        // Setup the config, HTML, log file & errors

        AppConfig::configure();
        load_helpers('HTML', 'Log');
        $this->error_messages = array();

        // Get the content type, URI folder and file

        $uri_no_fluff = preg_replace('/[\?#].*/', '', $_SERVER['REQUEST_URI']);
        $content_type = preg_match('/\.([^\/]+)$/', $uri_no_fluff, $matches) ? $matches[1] : DEFAULT_CONTENT_TYPE;
        if ($content_type === 'test' && !TESTING_ENABLED) $content_type = DEFAULT_CONTENT_TYPE;
        if (substr($uri_no_fluff, 0, strlen(VIEW_URL_PREFIX)) === VIEW_URL_PREFIX) $uri_no_fluff = substr($uri_no_fluff, strlen(VIEW_URL_PREFIX));
        list($folder, $file) = preg_split('/\//', strtolower($uri_no_fluff) . '//');
        $folder = preg_replace('/\.\w+$/', '', $folder);
        $file = preg_replace('/\.\w+$/', '', $file);

        // Setup the application request environment

        $this->content_type = $content_type; // can be "html", "test", etc.
        $this->folder = ($folder ? $folder : DEFAULT_FOLDER);
        $this->file = ($file ? $file : DEFAULT_FILE);

        // Use the content type to select a database

        require_once 'lib/Model.php';
        Model::set_database($content_type === 'test' ? DB_DATABASE_TEST : DB_DATABASE_LIVE);
        $this->is_testing = $content_type === 'test' ? TRUE : FALSE;
    }

    // Say whether we're testing

    public function is_testing()
    {
        return $this->is_testing;
    }

    // Return the request content type

    public function get_content_type()
    {
        return $this->content_type;
    }

    // Create a new controller and return it

    public function new_controller($class = NULL, $render = NULL)
    {
        // Allow render data to be passed in

        $render = new Object($render);

        // Require the Controller base class
        // ...and the Application controller

        require_once 'lib/Controller.php';
        require_once 'controllers/App.php';

        // Require the controller's subclass

        if (!$class) $class = ucfirst($this->folder);
        if ($this->is_testing && !strpos($class, '_test')) $class .= '_test';
        $path = 'controllers/' . $class . '.php';
        if (!file_found($path))
        {
            $class = ucfirst(DEFAULT_CONTROLLER);
            $path = 'controllers/' . $class . '.php';
        }
        require_once $path;

        // Create and return a new Controller object

        $class .= '_controller'; // mandatory suffix
        $this->controller = new $class();
        $this->controller->setup_for_app($this, $render);
        return $this->controller;
    }

    // Get the folder in the URL (e.g. "default")

    public function get_folder()
    {
        return $this->folder;
    }

    // Get the file in the URL (e.g. "index")

    public function get_file()
    {
        return $this->file;
    }

    // Get the path to a view file by looking in many places

    protected function get_view_path($file, $options = array())
    {
        // Read any options that were passed, e.g. extension

        $ext = array_key($options, 'ext', DEFAULT_EXTENSION);
        $must_find = array_key($options, 'must_find', FALSE);
        $lang = first(array_key($options, 'lang'), $this->controller->get_lang());
        $folder = first(array_key($options, 'folder'), $this->folder);

        // Create an array of paths to look for a view file

        $lang_folder = 'views/' . $lang . '/';
        $paths = array();
        if ($this->content_type !== DEFAULT_CONTENT_TYPE) $paths[] = $lang_folder . $folder . '/' . $file . '.' . $this->content_type . $ext;
        $paths[] = $lang_folder . $folder . '/' . $file . $ext;
        $paths[] = $lang_folder . DEFAULT_FOLDER . '/' . $file . $ext;
        if (!$must_find) $paths[] = $lang_folder . DEFAULT_FOLDER . '/' . FILE_NOT_FOUND . DEFAULT_EXTENSION;

        // Return the first path we found in the path array

        foreach ($paths as $path)
        {
            if (file_found($path)) return $path;
        }
        return NULL; // path not found
    }

    // Render a view file

    public function render_view($view, $render = NULL, $options = array())
    {
        if ($this->is_silent) return ''; // if redirect
        $render = new Object($render);

        // Setup the render data and the view file path

        $this->controller->setup_render_data($render);
        $path = $this->get_view_path($view, $options);
        if (is_null($path)) return NULL; // no view?

        // Use class "AppView" to limit view capability

        return AppView::render($path, $render);
    }

    // Render a content-type file in the "types" folder

    public function render_type($type, $render, $options = array())
    {
        $options['must_find'] = TRUE;

        // Optionally render the content in some layout

        $layout = $render->layout;
        if ($layout)
        {
            $options['folder'] = 'layouts';
            $content = $this->render_view("$layout.$type", $render, $options);
            if (isset($content)) $render->content = $content;
        }

        // Render the content in a content-type wrapper

        $options['folder'] = 'types';
        $content = $this->render_view($type, $render, $options);
        if (is_null($content)) $content = url_get_contents('/not/found');
        return $content;
    }

    // Redirect to another URL, and possibly exit

    public function redirect($url, $exit = FALSE)
    {
        if ($this->content_type !== DEFAULT_CONTENT_TYPE) $url .= '.' . $this->content_type;
        header('Location: ' . AppView::url($url));
        $this->is_silent = TRUE;
        if ($exit) exit; // it stops our logging!
    }

    // Send some mail (this depends on the Mail helper)

    public function send_mail($file, $render = NULL)
    {
        $render = new Object($render);
        load_helper('Mail');
        $text = $this->render_view($file, $render, array('ext' => '.mail.txt', 'must_find' => TRUE));
        $html = $this->render_view($file, $render, array('ext' => '.mail.html', 'must_find' => TRUE));
        $render->text = $text;
        $render->html = $html;
        return Mail::send($render, $this->is_testing);
    }

    // Add a new error message to the list of messages

    public function add_error_message($error_message)
    {
        $this->error_messages[] = $error_message;
        Log::error($error_message); // log it too
    }

    // Get the list of error messages

    public function get_error_messages()
    {
        return $this->error_messages;
    }
}

class AppView extends YAWF
{
    private static $render;

    // Render the view path by extracting the render array

    public static function render($__path_to_the_view_file, &$render)
    {
        self::$render = $render;
        ob_start();
        extract((array)$render);
        include $__path_to_the_view_file;
        if (isset($php_errormsg) && isset($app) && $app instanceof App)
            $app->add_error_message($php_errormsg);
        return ob_get_clean();
    }

    // Modify a view URL by adding an optional prefix

    public static function url($url, $prefix = NULL)
    {
        if (preg_match('/^\w+:/', $url)) return $url; // coz it's absolute
        return first($prefix, VIEW_URL_PREFIX) . $url; // or it's local
    }

    // Get data from the render object

    public static function get($field)
    {
        return self::$render->$field;
    }
}

class AppConfig extends YAWF
{
    // Configure application constants

    public static function configure()
    {
        require_once "lib/Config.php";
        $config = Config::load('app');
        foreach ($config['ini'] as $field => $value) ini_set($field, $value);
        date_default_timezone_set(ini_get('date.timezone'));

        Config::define_constants($config['settings']);
        Config::define_constants($config['testing']);
        Config::define_constants($config['database'], array('prefix' => 'db_'));
        Config::define_constants($config['content']);
    }
}

// End of App.php
