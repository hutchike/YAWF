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
 * The Persisted interface is a contract for persisted objects that
 * can be loaded, saved, inserted, updated and deleted.
 */
interface Persisted
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
}

// End of Persisted.php
