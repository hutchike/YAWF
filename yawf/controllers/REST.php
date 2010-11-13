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

load_helper('Data');

class REST_controller extends App_controller
{
    // A mapping of request content types to file types

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
    // (or the "X_HTTP_METHOD_OVERRIDE" custom HTTP header).

    protected function request_method()
    {
        return strtolower(first($this->params->_method,
                                $this->server->x_http_method_override,
                                $this->server->request_method));
    }

    // Return the requested content type set in HTTP headers

    protected function request_type()
    {
        $type = strtolower($this->server->content_type);
        $type = preg_replace('/;charset=.*$/', '', $type); // strip the encoding
        if ($type == 'application/x-www-form-urlencoded') $type = NULL;
        return ($type ? array_key(self::$request_types, $type)
                      : $this->app->get_content_type());
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

    // Get any input for PUT and POST methods

    protected function get_input()
    {
        return file_get_contents('php://input');
    }

    // Parse any input data according to type

    protected function parse_input()
    {
        if ($input = $this->get_input())
        {
            $this->params->input = $input;
            $this->params->data = Data::from($this->request_type(), $input);
        }
    }

    // Call a REST method on the REST service

    protected function call_method($method)
    {
        if ($this->service && method_exists($this->service, $method))
        {
            // Wrap the method call in "before" and "after" calls

            if (method_exists($this->service, 'before')) $this->service->before($this->params);
            $this->render->callback = $this->params->callback; // for JSON
            $this->render->data = $this->service->$method($this->params);
            if (method_exists($this->service, 'after')) $this->service->after($this->params);
        }
        else
        {
            $service = $this->service ? get_class($this->service) : 'We';
            throw new Exception("$service cannot handle $method method");
        }
    }
}

// End of REST.php
