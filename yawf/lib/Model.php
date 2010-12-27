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

load_interfaces('Modelled', 'Persisted', 'Validated');

/**
 * The Model class is just a simple alias for the SQL_model class.
 * If you'd like a different kind of model, then just add your own
 * "Model" class in your own "app" directory et voila - easy huh?
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */

class Model extends SQL_model implements Modelled, Persisted, Validated
{
    // Don't add anything here!
}

// End of Model.php
