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
 * The Modelled interface ensures compatibility between the Model and Remote
 * classes, so that they support the same essential public methods.
 */
interface Modelled
{
    /**
     * Load a model object (optionally specify the model object's ID number)
     *
     * @return Integer object ID
     */
    public function load($id = 0);

    /**
     * Save a model object
     *
     * @return Integer/Object object ID if inserted, or the object if updated
     */
    public function save();

    /**
     * Insert a model object
     *
     * @return Integer object ID
     */
    public function insert();

    /**
     * Update a model object
     *
     * @return Object the object
     */
    public function update();

    /**
     * Update all the model object's fields
     *
     * @return Object the object
     */
    public function update_all_fields();

    /**
     * Delete a model object in the database
     *
     * @return Object the object
     */
    public function delete();

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
     * Get an assoc array of data stored in the model object
     *
     * @return Array the assoc array of data stored in the model object
     */
    public function data();

    /**
     * Return whether the model object has changed
     *
     * @return Boolean whether the model object has changed
     */
    public function has_changed();

    /**
     * Get a list array of data fields for the model object
     *
     * @return Array the list array of data fields for the model object
     */
    public function fields();

    /**
     * Get the ID number for the model object
     *
     * @return Integer the ID number for the model object
     */
    public function get_id();

    /**
     * Set the ID number for the model object
     *
     * @param Integer the ID number for the model object
     * @return Object the model object for method chaining
     */
    public function set_id($id);
}

// End of Modelled.php
