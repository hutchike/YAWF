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

class Service extends YAWF
{
    protected $app;

    // Setup the service for the app

    public function setup_for_app($app)
    {
        $this->app = $app;
    }

    // Service errors should be arrays

    protected function error($message)
    {
        $message .= ' in ' . get_class($this);
        return array('error' => $message);
    }
}

// End of Service.php
