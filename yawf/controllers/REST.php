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

class REST_controller extends App_controller
{
    // A mapping of request content types to file types

    private $request_type;
    private static $request_types = array(
        'text/xml' => 'xml',
        'text/html' => 'html',
        'text/plain' => 'txt',
        'text/yaml' => 'yaml',
        'text/json' => 'json',
        'text/jsonp' => 'json',
        'text/javascript' => 'json',
        'application/json' => 'json',
        'application/jsonp' => 'json',
        'application/javascript' => 'json',
        'application/x-javascript' => 'json',
        'application/yaml' => 'yaml',
        'application/xml' => 'xml',
    );

    // The REST service

    protected $service;

    // Call a REST method, then render the REST service view

    public function render($view = null, $options = array())
    {
        $this->service = $this->app->new_service();
        $this->setup_params();
        $method = $this->request_method();
        $options['type'] = $this->request_type();
        $options['folder'] = 'REST';
        return parent::render($method, $options);
    }

    // Setup the request parameters by looking for ID fields

    protected function setup_params()
    {
        $file = $this->app->get_file();
        if (is_numeric($file)) $this->params->id = $file;
    }

    // Allow method overriding using the "_method" parameter

    protected function request_method()
    {
        return first($this->params->_method,
                     strtolower(array_key($_SERVER, 'REQUEST_METHOD')));
    }

    // Return the requested content type set in HTTP headers

    protected function request_type()
    {
        if ($this->request_type) return $this->request_type;
        $type = strtolower(array_key($_SERVER, 'CONTENT_TYPE'));
        if ($type == 'application/x-www-form-urlencoded') $type = NULL;
        $this->request_type = ($type ? array_key(self::$request_types, $type)
                                     : $this->app->get_content_type());
        return $this->request_type;
    }

    // Call the REST service "delete" method

    public function delete()
    {
        $this->call_method('delete');
    }

    // Call the REST service "get" method

    public function get()
    {
        $this->call_method('get');
    }

    // Call the REST service "move" method

    public function move() // WEBDAV method
    {
        $this->call_method('move');
    }

    // Call the REST service "options" method

    public function options() // WEBDAV method
    {
        $this->call_method('options');
    }

    // Call the REST service "post" method

    public function post()
    {
        $this->parse_input();
        $this->call_method('post');
    }

    // Call the REST service "put" method

    public function put()
    {
        $this->parse_input();
        $this->call_method('put');
    }

    // Parse any input data according to type

    protected function parse_input()
    {
        if ($input = file_get_contents('php://input'))
        {
            $this->params->input = $input;
            switch ($this->request_type())
            {
                case 'js':
                case 'json':
                case 'jsonp':
                    $this->params->data = json_decode($input);
                    break;

                case 'xml':
                    load_helper('XML');
                    $this->params->data = XML::deserialize($input);
                    break;

                case 'yaml':
                    load_helper('YAML');
                    $this->params->data = YAML::parse($input);
                    break;
            }
        }
    }

    // Call a REST method on the REST service

    protected function call_method($method)
    {
        if ($this->service && method_exists($this->service, $method))
        {
            $this->render->callback = $this->params->callback; // for JSON
            $this->render->data = $this->service->$method($this->params);
        }
        else
        {
            $service = $this->service ? get_class($this->service) : 'We';
            throw new Exception("$service cannot handle $method method");
        }
    }
}

// End of REST.php
