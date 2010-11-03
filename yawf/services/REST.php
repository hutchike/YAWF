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
    // Get a model object for an ID

    public function get_id($params)
    {
        $class = preg_replace('/_service$/', '', get_class($this));
        load_model($class);
        $model = new $class();
        return $model->load($params->id) ? array($class => $model->data()) : $this->error("id $params->id not found");
    }
}

// End of REST.php
