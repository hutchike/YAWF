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

class App_controller extends Controller
{
    private static $greetings = array(
        'Welcome!',
        'Hi there!',
        'Bienvenidos!',
        'Hola!',
    );

    // Before the controller's view method runs...

    public function before()
    {
        // Put code here that should be performed by every controller

        $this->render->title = 'Yet Another Web Framework';
        $this->render->layout = 'purple';
        $this->render->greeting = array_rand_value(self::$greetings);
        $this->render->active_tab = array($this->path => ' class="active"');
    }

    // After the controller's view method runs...

    public function after()
    {
        // Put code here that should be performed by every controller
    }
}

// End of App.php
