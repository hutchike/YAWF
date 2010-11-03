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

// Replace this class by writing your own class in "myapp/app/services/REST.php"

class REST_service extends Service
{
    // Get object data for an ID param

    public function get_for_id($params)
    {
        $class = preg_replace('/_service$/', '', get_class($this));
        load_model($class);
        $model = new $class();
        return $model->load($params->id) ? array($class => $model->data()) : $this->error("id $params->id not found");
    }

    // ------------------------
    // HTTP METHODS TO OVERRIDE
    // ------------------------

    // Override "delete" in your service

    public function delete($params)
    {
        return $this->error('method "delete" not supported');
    }

    // Override "get" in your service

    public function get($params)
    {
        return $this->error('method "get" not supported');
    }

    // Override "move" in your service

    public function move($params)
    {
        return $this->error('method "move" not supported');
    }

    // Override "options" in your service

    public function options($params)
    {
        return $this->error('method "options" not supported');
    }

    // Override "post" in your service

    public function post($params)
    {
        return $this->error('method "post" not supported');
    }

    // Override "put" in your service

    public function put($params)
    {
        return $this->error('method "put" not supported');
    }
}

// End of REST.php
