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

// Replace this class by writing your own class in "myapp/app/services/REST.php"

class REST_service extends Service
{
    // Get object data for an ID param

    public function get_for_id($params)
    {
    }

    // ------------------------
    // HTTP METHODS TO OVERRIDE
    // ------------------------

    // Default "delete" method behavior is "delete"

    public function delete($params)
    {
        $class = $this->class_name();
        $object = new $class();
        if ($object->load($params->id)) $object->delete();
        return $params->data;
    }

    // Default "get" method behavior is "load"

    public function get($params)
    {
        $class = $this->class_name();
        $model = new $class();
        return $model->load($params->id) ? array($class => $model->data()) : $this->error("id $params->id not found");
    }

    // Override "move" in your service if you wish to support it

    public function move($params)
    {
        return $this->error('method "move" not supported');
    }

    // Override "options" in your service if you wish to support it

    public function options($params)
    {
        return $this->error('method "options" not supported');
    }

    // Default "post" method behavior is "insert"

    public function post($params)
    {
        $class = $this->class_name();
        $object = new $class($params->data[$class]);
        $params->data[$class][$object->get_id_field()] = $object->insert();
        return $params->data;
    }

    // Default "put" method behavior is "update"

    public function put($params)
    {
        $class = $this->class_name();
        $object = new $class($params->data[$class]);
        if ($params->id) $object->set_id($params->id);
        $object->update_all_fields();
        return $params->data;
    }

    // Return the likely class name

    protected function class_name()
    {
        $class = preg_replace('/_service$/', '', get_class($this));
        load_model($class); // in case we haven't loaded it already
        return $class;
    }
}

// End of REST.php
