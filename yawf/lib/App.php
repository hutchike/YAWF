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
 * The YAWF App object creates controller and service objects to
 * handle web requests. It also manages the request file, folder
 * content type, language and any URI prefix.
 *
 * An App object has the side-effect of creating some YAWF::prop
 * objects such as "app", "service" and "controller" props. This
 * enables independent parts of the code to access these objects
 * and makes it easier to test with mock objects.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App extends YAWF implements Mailer
{
    protected $content_type;// derived from the file extension
    protected $controller;  // a controller to render the view
    protected $service;     // a service to enable web services
    protected $folder;      // views folder name e.g. "default"
    protected $file;        // the view file name e.g. "index"
    protected $lang;        // the language code (two letters)
    protected $is_silent;   // "TRUE" after we've redirected
    protected $is_testing;
    protected $error_messages;

    /**
     * Construct a new App object
     *
     * @param String $uri an optional relative URI (e.g. "/folder/file")
     */
    public function __construct($uri = NULL)
    {
        if (is_null($uri)) $uri = $_SERVER['REQUEST_URI'];

        // Setup the config, prefix and assert checks

        $config = AppConfig::configure();
        load_helper('HTML'); // for views
        load_tools('Log', 'Translate');
        $this->error_messages = array();
        $this->assert_checking($config);

        // Set the content type, folder and file

        $uri = preg_replace('/[\?#].*/', '', $uri);
        $content_type = preg_match('/\.(\w+)$/', $uri, $matches) ? $matches[1] : DEFAULT_CONTENT_TYPE;
        if ($content_type === Symbol::TEST && !TESTING_ENABLED) $content_type = DEFAULT_CONTENT_TYPE;
        $uri = $this->set_lang_and_remove_prefix($uri);
        list($folder, $file) = explode('/', $uri . '//');
        $folder = preg_replace('/\.\w+$/', '', $folder);
        $file = preg_replace('/\.\w+$/', '', $file);

        // Setup the application request environment

        $this->content_type = strtolower($content_type); // e.g. "html", "test"
        $this->folder = ($folder ? $folder : DEFAULT_FOLDER);
        $this->file = ($file ? $file : DEFAULT_FILE);
        $this->is_testing = array_key($_REQUEST, Symbol::TEST) || $content_type === Symbol::TEST;

        // If we're testing then use a test database

        require_once 'lib/Model.php';
        $model = new Model();
        $database = $this->is_testing ? DB_DATABASE_TEST : DB_DATABASE_LIVE;
        $model->set_connector(DB_CONNECTOR,
                              array(Symbol::DATABASE => $database));

        // Setup the language translations

        Translate::add_translations(Config::load('validate'));
        Translate::add_translations(Config::load('translate'));

        // Register this app as a prop

        YAWF::prop(Symbol::APP, $this);
    }

    /**
     * Say whether we're testing
     *
     * @return Boolean whether we're testing
     */
    public function is_testing()
    {
        return $this->is_testing;
    }

    /**
     * Return the request content type
     *
     * @return String the content type
     */
    public function get_content_type()
    {
        return $this->content_type;
    }

    /**
     * Create a new controller and return it. If the controller is in the
     * REST service list, then a REST controller will be created and this
     * REST controller will ask this $app object to create a REST service.
     *
     * @param String $class an optional class name
     * @param Object $render optional render data (can also be an array)
     * @return App_controller the new controller
     */
    public function new_controller($class = NULL, $render = NULL)
    {
        // Require the Controller base class
        // ...and the Application controller

        require_once 'lib/Controller.php';
        require_once 'controllers/App.php';

        // Require the controller's subclass

        if (!$class) $class = $this->folder;
        if (defined('REST_SERVICE_LIST') && in_array($class, split_list(REST_SERVICE_LIST))) $class = 'REST';
        if ($this->is_testing && FALSE === strpos($class, '_test')) $class .= '_test';
        $class = ucfirst($class);
        $path = "controllers/$class.php";
        if (!file_found($path))
        {
            $class = ucfirst(DEFAULT_CONTROLLER);
            $path = "controllers/$class.php";
        }
        require_once $path;

        // Create and return a new Controller object

        $class .= '_controller'; // mandatory suffix
        $this->controller = new $class();
        $this->controller->setup_for_app($this, new Object($render));
        return YAWF::prop(Symbol::CONTROLLER, $this->controller);
    }

    /**
     * Create a new service and return it
     *
     * @param String $class an optional class name
     * @return Service the new service
     */
    public function new_service($class = NULL)
    {
        // Require the Web_service base class
        // ...and the Application web service

        require_once 'lib/Web_service.php';
        require_once 'services/App.php';

        // Require the service's subclass

        if (!$class) $class = $this->folder;
        load_service($class);

        // Create and return a new Service object

        $class .= '_service'; // mandatory suffix
        $this->service = new $class();
        $this->service->setup_for_app($this);
        return YAWF::prop(Symbol::SERVICE, $this->service);
    }

    /**
     * Get the folder in the URI (e.g. "default")
     *
     * @return String the folder in the URI
     */
    public function get_folder()
    {
        return $this->folder;
    }

    /**
     * Get the file in the URI (e.g. "index")
     *
     * @return String the file in the URI
     */
    public function get_file()
    {
        return $this->file;
    }

    /**
     * Get the path (i.e. "folder/file")
     *
     * @return String the path in the URI
     */
    public function get_path()
    {
        return $this->get_folder() . '/' . $this->get_file();
    }

    /**
     * Get the language setting (e.g. "en")
     *
     * @return String the language setting
     */
    public function get_lang()
    {
        return $this->lang;
    }

    /**
     * Set the requested language by checking supported languages
     *
     * @param String $lang the two-letter language setting (optional)
     * @param String $supported_languages an optional list of language settings
     * @return String the new language setting (must be a supported language)
     */
    public function set_lang($lang = NULL, $supported_languages = SUPPORTED_LANGUAGES)
    {
        // Only web browsers send the HTTP_ACCEPT_LANGUAGE header

        if (!$lang) $lang = array_key($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '');
        $lang = substr($lang, 0, 2); // take the primary language

        // Check that the language is supported by our app config

        $lang = strtolower($lang);
        if (!$lang || !stristr($supported_languages, $lang))
        {
            $lang = DEFAULT_LANGUAGE;
        }
        return $this->lang = $lang;
    }

    /**
     * Set the language and remove the URI prefix
     *
     * @param String $uri the URI of the request (e.g. "/en/folder/file.txt")
     * @return String $uri the URI with the URI prefix removed
     */
    protected function set_lang_and_remove_prefix($uri)
    {
        $lang = NULL;
        $prefix = VIEW_URI_PREFIX;

        // Set the language by checking the URI against supported languages

        if (preg_match('/^\/(\w{2})($|\/)/', $uri, $matches))
        {
            if (stristr(SUPPORTED_LANGUAGES, $matches[1]))
            {
                $lang = $matches[1];
                $prefix = "/$lang$prefix"; // include the lang in the URI prefix
                if (!$matches[2]) $uri .= '/';
            }
        }
        $this->set_lang($lang); // this will default to the web browser language

        // Remove the URI prefix from the URI

        AppView::prefix($prefix); // remember the prefix in "AppView::uri" calls
        if (substr($uri, 0, strlen($prefix)) === $prefix)
        {
            $uri = substr($uri, strlen($prefix));
        }
        return $uri;
    }

    /**
     * Get the path to a view file by looking in many places
     *
     * @param String $file the view file to find by searching the views folders
     * @param Array $options an optional array of options (e.g. "must_find")
     * @return String the path to the view file
     */
    protected function get_view_path($file, $options = array())
    {
        // Read any options that were passed, e.g. extension

        $must_find = array_key($options, 'must_find', FALSE);
        $lang = array_key($options, 'lang', $this->lang);
        $folder = array_key($options, Symbol::FOLDER, $this->folder);
        $type = array_key($options, 'type', $this->content_type);
        $ext = array_key($options, 'ext', DEFAULT_EXTENSION);

        // Create an array of paths to look for a view file

        $lang_folder = 'views/' . $lang . '/';
        $paths = array();
        if ($type !== DEFAULT_CONTENT_TYPE) $paths[] = "$lang_folder$folder/$file.$type$ext";
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

    /**
     * Render a view file
     *
     * @param String $view the view file to render
     * @param Object $render an optional Object containing render data
     * @param Array $options an optional array of options (e.g. "must_find")
     * @return String the rendered output to be returned to the client
     */
    public function render_view($view, $render = NULL, $options = array())
    {
        if ($this->is_silent) return ''; // if redirect
        $render = new Object($render); // Object needed

        // Setup the render data and the view file path

        $this->controller->setup_render_data($render);
        $path = $this->get_view_path($view, $options);
        if (is_null($path)) return NULL; // no view?

        // Use class "AppView" to limit view capability

        return AppView::render($path, $render);
    }

    /**
     * Render a content-type file in the "types" folder
     *
     * @param String $type the type file to render
     * @param Object $render an optional Object containing render data
     * @param Array $options an optional array of options (e.g. "must_find")
     * @return String the rendered output to be returned to the client
     */
    public function render_type($type, $render, $options = array())
    {
        $options['must_find'] = TRUE;

        // Optionally render the content in some layout

        $layout = $render->layout;
        if ($layout)
        {
            $options[Symbol::FOLDER] = 'layouts';
            $content = $this->render_view("$layout.$type", $render, $options);
            if (isset($content)) $render->content = $content;
        }

        // Render the content in a content-type wrapper

        $options[Symbol::FOLDER] = 'types';
        $content = $this->render_view($type, $render, $options);
        if (is_null($content)) $content = uri_get_contents('not/found');
        return $content;
    }

    /**
     * Redirect to another URI, and possibly exit
     *
     * @param String $uri the URI to redirect at
     * @param Array $options an optional array of options (e.g. "exit")
     */
    public function redirect($uri, $options = array())
    {
        // Set flash messages to be shown on the next view page

        foreach (array('notice', 'warning', 'error') as $level)
        {
            if ($message = array_key($options, $level)) $this->controller->flash($level, $message);
        }

        // Remember, type for interface testing is "test"

        if ($this->content_type !== DEFAULT_CONTENT_TYPE)
        {
            $uri .= '.' . $this->content_type;
        }

        // Set a location header and optional status

        $view_uri = AppView::uri($uri);
        $header = "Location: $view_uri";
        if ($status = array_key($options, 'status'))
        {
            header($header, TRUE, $status); // set user-defined HTTP status code
        }
        else
        {
            header($header);
        }

        // Remain silent, and optionally exit or finish (preferred for logging)

        $this->is_silent = TRUE;
        if (array_key($options, 'exit')) exit; // careful! it stops our logging!
        if (array_key($options, 'finish')) YAWF::finish("Redirected to $view_uri");
    }

    /**
     * Send a mail message (this depends on the Mail tool)
     *
     * @param String $file the file to send (e.g. "welcome")
     * @param Object $render optional data to render (can be an Array)
     * @return String the raw content of the message that was sent
     */
    public function send_mail($file, $render = NULL)
    {
        $render = new Object($render);
        load_tool('Mail');
        $text = $this->render_view($file, $render, array('ext' => '.text.php', 'must_find' => TRUE));
        $html = $this->render_view($file, $render, array('ext' => '.html.php', 'must_find' => TRUE));
        $render->text = $text;
        $render->html = $html;
        return Mail::send($render, $this->is_testing);
    }

    /**
     * Add a new error message to the list of messages
     *
     * @param String $error_message the error message to add to the list
     */
    public function add_error_message($error_message)
    {
        $this->error_messages[] = $error_message;
        Log::error($error_message); // log it too
    }

    /**
     * Get the list of error messages
     *
     * @return Array the list of error messages
     */
    public function get_error_messages()
    {
        return $this->error_messages;
    }
}

/**
 * The AppView class renders views and normalizes URIs by applying a prefix.
 */
class AppView extends YAWF
{
    private static $prefix = '';    // string value
    private static $render = NULL;  // object value

    /**
     * Get/set the prefix to apply to URIs
     *
     * @param String $prefix an optional prefix to apply to URIs
     * @return String the current prefix being applied to URIs
     */
    public static function prefix($prefix = NULL)
    {
        return is_null($prefix) ? self::$prefix
                                : self::$prefix = $prefix;
    }


    /**
     * Render the view path by extracting the render array
     *
     * @param String $__path_to_the_view_file the path to the view file
     * @param Object $render the data to render in the view
     * @return String the rendered output to be returned to the client
     */
    public static function render($__path_to_the_view_file, $render)
    {
        self::$render = $render;
        ob_start();
        extract((array)$render);
        include $__path_to_the_view_file;
        if (isset($php_errormsg) && isset($app) && $app instanceof App)
            $app->add_error_message($php_errormsg);
        return ob_get_clean();
    }

    /**
     * Modify a view URI by adding an optional prefix
     *
     * @param String $uri the URI to modify by applying the prefix
     * @param String $prefix an optional prefix to apply to the URI
     * @return String the URI with the prefix applied
     */
    public static function uri($uri, $prefix = NULL)
    {
        if (preg_match('/^\w+:/', $uri)) return $uri; // coz it's absolute
        return first($prefix, self::$prefix) . $uri; // or it's local
    }

    /**
     * Get data from the render object
     *
     * @param String $field the render object field to read
     * @return String the value of the render object field
     */
    public static function get($field)
    {
        return self::$render->$field;
    }
}

/**
 * The AppConfig class reads the "app.yaml" config file to configure the app.
 */
class AppConfig extends YAWF
{
    /**
     * Configure application constants
     *
     * @return Array an assoc array containing all the user-defined constants
     */
    public static function configure()
    {
        require_once 'lib/Config.php';
        $config = Config::load(Symbol::APP);
        foreach ($config['ini'] as $field => $value) ini_set($field, $value);
        date_default_timezone_set(ini_get('date.timezone'));

        Config::define_constants($config['settings']);
        Config::define_constants($config['testing']);
        Config::define_constants($config['database'], array('prefix' => 'db_'));
        Config::define_constants($config['content']);

        // Return an array with all the user-defined constants

        return array_key(get_defined_constants(TRUE), 'user');
    }
}

// End of App.php
