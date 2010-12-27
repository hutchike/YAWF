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
 * The Validated interface ensures compatibility between the Model and Remote
 * classes, so that they support the same essential public methods.
 */
interface Validated
{
    /**
     * Return whether the model object is validated against validation methods
     *
     * @return Boolean whether the model validates against validation methods
     */
    public function is_validated();

    /**
     * Return an assoc array of validation messages, keyed by model data field
     *
     * @return Array an assoc array of validation messages, keyed by data field
     */
    public function validation_messages();
}

// End of Validated.php
