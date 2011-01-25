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
 * The Modelled interface is a contract for model objects that store
 * data in fields.
 */
interface Modelled
{
    /**
     * Setup a model, for example by setting timestamp and validation fields.
     * This method is called by <code>load_model()</code> when loading models.
     */
    public function setup();

    /**
     * Get a data field value from the model object
     *
     * @param String $field the data field to read
     * @return String the value of the data field
     */
    public function __get($field);

    /**
     * Set a data field value in the model object
     *
     * @param String $field the data field to write
     * @param String $value the data value to write
     * @return String the value of the newly updated data field
     */
    public function __set($field, $value);

    /**
     * Copy the model object's data to another object
     *
     * @param Object $other the other object receiving the model object's data
     * @param Array $fields_to_copy a list of fields to copy (optional)
     * @return Modelled this object for method chaining
     */
    public function copy_to($other, $fields_to_copy = array());

    /**
     * Get an assoc array of data stored in this model object.
     * This method may be overriden in subclasses, e.g. Remote.
     *
     * @param Array an optional list of fields to return
     * @return Array the assoc array of data stored in this model object
     */
    public function data($fields = array());

    /**
     * Get a list array of data fields for the model object
     *
     * @return Array the list array of data fields for the model object
     */
    public function fields();

    /**
     * Return whether the model object has changed
     *
     * @return Boolean whether the model object has changed
     */
    public function has_changed();

    /**
     * Return an assoc array of changed data
     *
     * @return Array an assoc array of changed data
     */
    public function get_changed();

    /**
     * Cast this model object into another model class
     *
     * @param String $new_class a model class into which to cast this object
     * @param Boolean $has_changed whether the new object has changed or not
     * @return Simple_model a new model object of the new class
     */
    public function cast_into($class, $has_changed = NULL);

    /**
     * Return this model object's class name
     *
     * @return String the model object's class name
     */
    public function get_class();
}

// End of Modelled.php
