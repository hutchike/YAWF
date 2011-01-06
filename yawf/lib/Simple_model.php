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

load_interface('Modelled');

/**
 * The Simple_model class provides a foundation class from which
 * to build your own model classes using alternative data stores.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Simple_model extends YAWF implements Modelled
{
    /**
     * The $data Array holds data fields and values for the simple model object
     */
    protected $data;

    /**
     * The $changed Array holds data fields whose values have been changed
     */
    protected $changed;

    /**
     * Create a new simple model object
     *
     * @param Array $data the data to initialize the object (may be an object)
     * @param Boolean $has_changed whether the new object has changed (optional)
     */
    public function __construct($data = array(), $has_changed = TRUE)
    {
        $this->data = (array)$data;
        $this->changed = array();
        if ($has_changed)
        {
            foreach ($this->data as $field => $value)
            {
                $this->changed[$field] = TRUE;
            }
        }
    }

    /**
     * Setup a model by calling methods such as "set_id_field", "set_virtual"
     * and "set_timestamp". This method should be overriden in your subclass:
     *
     * <code>
     * // Subclass in your models like this (depending on model capabilities):
     *
     * public function setup()
     * {
     *     $this->set_id_field('table_id_field');
     *     $this->set_virtual('transient_field');
     *     $this->set_timestamp('created_at', 'updated_at');
     *     $this->validates('email', 'is_valid_email');
     * }
     * </code>
     */
    public function setup()
    {
        // Override this method in your subclasses
    }

    /**
     * Get a data field value from this model object
     *
     * @param String $field the data field to read
     * @return String the value of the data field
     */
    public function __get($field)
    {
        return array_key($this->data, $field);
    }

    /**
     * Set a data field value in this model object
     *
     * @param String $field the data field to write
     * @param String $value the data value to write
     * @return String the value of the newly updated data field
     */
    public function __set($field, $value)
    {
        $this->changed[$field] = TRUE;
        $this->data[$field] = $value;
        return $value;
    }

    /**
     * Copy data from this model object to another model object
     *
     * @param Simple_model $other the other model object
     */
    public function copy_to($other)
    {
        foreach ($this->data() as $field => $value) // important to call data()
        {
            $other->$field = $value;
        }
    }

    /**
     * Get an assoc array of data stored in this model object.
     * This method may be overriden in subclasses, e.g. Remote.
     *
     * @param Array an optional list of fields to return
     * @return Array the assoc array of data stored in this model object
     */
    public function data($fields = array())
    {
        $keys = array();
        foreach ($fields as $field) $keys[$field] = TRUE;
        return $keys ? array_intersect_key($this->data, $keys) : $this->data;
    }

    /**
     * Get a list array of data fields for this model object
     *
     * @return Array the list array of data fields for this model object
     */
    public function fields()
    {
        return array_keys($this->data()); // important to call data()
    }

    /**
     * Return whether this model object has changed
     *
     * @return Boolean whether this model object has changed
     */
    public function has_changed()
    {
        return count($this->changed) ? TRUE : FALSE;
    }

    /**
     * Return an assoc array of changed data
     *
     * @return Array an assoc array of changed data
     */
    public function get_changed()
    {
        $data = array();
        foreach ($this->changed as $field => $has_changed)
        {
            if ($has_changed) $data[$field] = $this->$field;
        }
        return $data;
    }

    /**
     * Cast this model object into another model class
     *
     * @param String $new_class a model class into which to cast this object
     * @param Boolean $has_changed whether the new object has changed or not
     * @return Simple_model a new model object of the new class
     */
    public function cast_into($new_class, $has_changed = NULL)
    {
        if (is_null($has_changed)) $has_changed = $this->has_changed();
        return new $new_class($this->data(), $has_changed);
    }

    /**
     * Return this model object's class name
     *
     * @return String the model object's class name
     */
    public function get_class()
    {
        return get_class($this);
    }
}

// End of Simple_model.php
