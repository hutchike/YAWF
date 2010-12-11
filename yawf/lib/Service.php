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
 * The Service class responds to requests for web services, such
 * as REST web services. See the "REST_service" child class for
 * an example of a Service.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Service extends Request
{
    /**
     * Link this service to the web app
     *
     * @param App $app the app
     */
    public function setup_for_app($app)
    {
        $this->setup_request($app);
    }

    /**
     * Service errors should be arrays
     * for easy formatting as XML/JSON
     *
     * @param String $message the error message
     * @return Array the error message with details about the service class
     */
    protected function error($message)
    {
        $message .= ' in ' . get_class($this);
        return array('error' => $message);
    }
}

// End of Service.php
