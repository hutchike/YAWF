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
    protected $service;

    public function render($view = null, $options = array())
    {
        $this->service = $this->app->new_service();
        $method = $this->method();
        $options['folder'] = 'REST';
        return parent::render($method, $options);
    }

    public function method()
    {
        return first($this->params->_method,
                     strtolower(array_key($_SERVER, 'REQUEST_METHOD')));
    }

    public function delete()
    {
        $this->call_method('delete');
    }

    public function get()
    {
        $this->call_method('get');
    }

    public function move() // WEBDAV method
    {
        $this->call_method('move');
    }

    public function options() // WEBDAV method
    {
        $this->call_method('options');
    }

    public function post()
    {
        $this->call_method('post');
    }

    public function put()
    {
        $this->call_method('put');
    }

    protected function call_method($method)
    {
        if ($this->service && method_exists($this->service, $method))
        {
            $this->render->callback = $this->params->callback;
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
