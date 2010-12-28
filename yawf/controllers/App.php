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
 * The App_controller class should be overriden by placing an
 * App_controller class (a file called "App.php") in your own
 * "app/controllers" folder.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App_controller extends Controller
{
    /**
     * This is an example of a method to include in your "App_controller" class
     */
    function before()
    {
        $this->render->title = 'Welcome to YAWF';
    }
}

// End of App.php
