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
 * The Basic_model class provides a foundation class from which
 * to build your own model classes using alternative data stores.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Basic_model extends YAWF
{
    protected $data;
    protected $to_update;

    /**
     * Create a new basic model object
     *
     * @param Array $data the data to initialize the object (may be an object)
     */
    public function __construct($data = array())
    {
        $this->data = (array)$data;
        $this->to_update = array();
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
        $this->to_update[$field] = TRUE;
        $this->data[$field] = $value;
        return $value;
    }

    /**
     * Copy data from this model object to another model object
     *
     * @param Basic_model $other the other model object
     */
    public function copy_to($other)
    {
        foreach ($this->data() as $field => $value)
        {
            $other->$field = $value;
        }
    }

    /**
     * Get an assoc array of data stored in this model object
     *
     * @return Array the assoc array of data stored in this model object
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Get a list array of data fields for this model object
     *
     * @return Array the list array of data fields for this model object
     */
    public function fields()
    {
        return array_keys($this->data());
    }

    /**
     * Return whether this model object has changed
     *
     * @return Boolean whether this model object has changed
     */
    public function has_changed()
    {
        return count($this->to_update) ? TRUE : FALSE;
    }

    /**
     * Return an encrypted password ready to store in the database
     *
     * @param String $text the unencrypted password text to be encrypted
     * @return String an encrypted password ready to store in the database
     */
    protected function password($text)
    {
        return sha1(md5($text));
    }
}

// End of Basic_model.php
