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
 * The Rest_service class may be subclassed by service classes in your app's
 * "services" directory. It provides standard REST HTTP methods as methods to
 * override in your subclass - e.g. "get", "post", "put", "delete" and others.
 * It also provides a hook for a "basic_auth" method to check a username and
 * password before providing access to the service.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class REST_service extends Web_service
{
    // ----------------------------------------
    // AUTH METHODS TO OVERRIDE IN YOUR SERVICE
    // ----------------------------------------

    /**
     * Return whether a web user is authorized to access this service.
     * This method calls the "basic_auth" method - override to modify.
     *
     * @return Boolean TRUE if authorized or FALSE otherwise
     */
    public function auth()
    {
        return $this->basic_auth($this->server->php_auth_user,
                                 $this->server->php_auth_pw);
    }

    /**
     * Return whether a web user is authorized to access this service.
     * This method should be overriden in your subclass to check the
     * username and password and return whether they're authorized.
     *
     * @param String $username the username to authorize
     * @param string $password the password to authorize
     * @return Boolean TRUE if authorized or FALSE otherwise
     */
    protected function basic_auth($username, $password)
    {
        // Override this method in your service

        return TRUE;
    }

    // ----------------------------------------
    // HTTP METHODS TO OVERRIDE IN YOUR SERVICE
    // ----------------------------------------

    /**
     * The default "delete" method behavior is to delete the model object
     * if the "id" param is positive, and delete all model objects if the
     * "id" param is negative. See the "Remote" class "delete_all" method.
     *
     * @param Object $params the HTTP params
     * @return Array the "data" HTTP param containing all the REST data
     */
    public function delete($params)
    {
        $model_class = $this->get_model_class();
        $object = $this->make($model_class);
        if ($params->id < 0) $object->delete_all();
        elseif ($object->load($params->id)) $object->delete();
        return $params->data;
    }


    /**
     * The default "get" method behavior is to load the model object
     *
     * @param Object $params the HTTP params
     * @return Array the "data" HTTP param containing REST data, or an error
     */
    public function get($params)
    {
        $model_class = $this->get_model_class();
        $object = $this->make($model_class);
        if ($where = $this->params->where)
        {
            // Run the "find_where" query using the params

            $where = preg_replace('/^where /', '', $where); // no prefix
            if ($order = $this->params->order) $object->set_order($order);
            if ($limit = $this->params->limit) $object->set_limit($limit);
            if ($offset = $this->params->offset) $object->set_offset($offset);
            $objects = $object->find_where($where, array('join' => $this->params->join));

            // Return a list of model objects matching a "where" clause

            $data = array();
            foreach ($objects as $object) $data[] = $object->data();
            return array($model_class => $data);
        }
        else
        {
            // Return a single model object matching an ID, or an error message

            return $object->load($params->id) ?
                    array($model_class => $object->data()) :
                    $this->error($params->id ? "id $params->id not found"
                                             : "id missing");
        }
    }

    /**
     * Override "move" in your service if you wish to support it
     *
     * @param Object $params the HTTP params
     * @return Array an error saying this HTTP REST method isn't yet supported
     */
    public function move($params)
    {
        return $this->error('method "move" not supported');
    }

    /**
     * Override "options" in your service if you wish to support it
     *
     * @param Object $params the HTTP params
     * @return Array an error saying this HTTP REST method isn't yet supported
     */
    public function options($params)
    {
        return $this->error('method "options" not supported');
    }

    /**
     * The default "post" method behavior is to insert the model object
     *
     * @param Object $params the HTTP params
     * @return Array the "data" HTTP param containing all the REST data
     */
    public function post($params)
    {
        assert('is_array($params->data)');
        $model_class = $this->get_model_class();
        $object = $this->make($model_class, $params->data);
        if ($object->is_validated())
            $params->data[$model_class][$object->get_id_field()] = $object->insert();
        else
            $params->data[Symbol::VALIDATION_MESSAGES] = $object->validation_messages();

        return $params->data;
    }

    /**
     * The default "put" method behavior is to update the model object
     *
     * @param Object $params the HTTP params
     * @return Array the "data" HTTP param containing all the REST data
     */
    public function put($params)
    {
        assert('is_array($params->data)');
        $model_class = $this->get_model_class();
        $object = $this->make($model_class, $params->data);
        if ($params->id) $object->id = $params->id;
        if ($object->is_validated())
            $object->update_all_fields();
        else
            $params->data[Symbol::VALIDATION_MESSAGES] = $object->validation_messages();
            
        return $params->data;
    }

    /**
     * Return the data model class name (and ensure it has been loaded)
     *
     * @return String the name of the model class (e.g. "User")
     */
    protected function get_model_class()
    {
        $model_class = preg_replace('/(_test)?_service$/', '', get_class($this));
        load_model($model_class); // in case we haven't loaded it already
        return $model_class;
    }

    /**
     * Make a new model object and set its database by looking for any
     * database name in the query string parameters or the object data.
     *
     * @param String $model_class the name of the model class
     * @param Array $data data to use to construct the model object
     * @return Model a newly made model with its database set
     */
    protected function make($model_class, $data = array())
    {
        $object = new $model_class(array_key($data, $model_class));
        $param = Remote::DB; // what's the database parameter called?
        $db = first($this->params->$param, array_key($data, $param));
        if (is_string($db)) $object->set_database($db);
        return $object;
    }
}

// End of REST.php
