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
 * The App_service class should be overriden by placing an
 * App_service class (a file called "App.php") in your own
 * "app/service" folder.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App_service extends Web_service
{
    /**
     * This is an example of a method to include in your "App_service" class
     */
    function basic_auth($username, $password)
    {
        // TODO: Check the username and password

        return TRUE; // or FALSE when no access
    }
}

// End of App.php
