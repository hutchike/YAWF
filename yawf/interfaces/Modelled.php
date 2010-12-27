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
     */
    public function copy_to($other);

    /**
     * Get an assoc array of data stored in the model object
     *
     * @return Array the assoc array of data stored in the model object
     */
    public function data();

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
}

// End of Modelled.php
