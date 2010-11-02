<?
// Copyright (c) 2009 Guanoo, Inc.
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

load_controller('REST');

class REST_test_controller extends Rest_controller
{
    // Create a test service with test methods to run checks

    public function render($view = null, $options = array())
    {
        $class = $this->app->get_class_name();
        if ($class != 'REST')
        {
            $service = $class . '_test';
            $this->service = $this->app->new_service($service);
        }
        return Controller::render(); // to show test results
    }
}

// End of REST_test.php
