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

load_tool('Data');

/**
 * The REST_controller class connects REST service classes to the
 * web application via view methosd that correspond to HTTP methods
 * such as "get", "post", "put", etc.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class REST_controller extends App_controller
{
    /**
     * The REST service to provide
     */
    protected $service;

    /**
     * Call a REST method, then render the REST service view
     *
     * @param String $view the view to render (ignored)
     * @param Array $options an array of rendering options (ignored)
     * @return String the contents to render (REST data in a content type)
     */
    public function render($view = NULL, $options = array())
    {
        $this->service = $this->app->new_service();
        if (!$this->service->auth()) return NULL;
        $this->setup_REST_params();
        $method = $this->request_method();
        $options['type'] = $this->request_type();
        $options['folder'] = 'REST'; // to use the "yawf/views/en/REST" folder
        return parent::render($method, $options);
    }

    /**
     * Setup the request parameters by looking for ID fields
     */
    protected function setup_REST_params()
    {
        $file = $this->app->get_file();
        if (is_numeric($file)) $this->params->id = $file;
    }

    /**
     * Call the REST service "delete" method
     */
    public function delete()
    {
        $this->call_method('delete');
    }

    /**
     * Call the REST service "get" method
     */
    public function get()
    {
        $this->call_method('get');
    }

    /**
     * Call the REST service "move" method
     */
    public function move() // WEBDAV method
    {
        $this->call_method('move');
    }

    /**
     * Call the REST service "options" method
     */
    public function options() // WEBDAV method
    {
        $this->call_method('options');
    }

    /**
     * Call the REST service "post" method
     */
    public function post()
    {
        $this->parse_input();
        $this->call_method('post');
    }

    /**
     * Call the REST service "put" method
     */
    public function put()
    {
        $this->parse_input();
        $this->call_method('put');
    }

    /**
     * Get any input for "put" and "post" methods
     *
     * @return String the input data sent to "put" and "post" HTTP methods
     */
    protected function get_input()
    {
        return file_get_contents('php://input');
    }

    /**
     * Parse any input data according to type
     */
    protected function parse_input()
    {
        if ($input = $this->get_input())
        {
            $this->params->input = $input;
            $this->params->data = Data::from($this->request_type(), $input);
        }
    }

    /**
     * Call a REST method on the REST service. Note that "before" and "after"
     * methods will also be called if they're provided by the REST service.
     *
     * @param String $method the REST method to call on the REST service
     */
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
