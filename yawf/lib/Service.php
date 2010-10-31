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
    protected $token;

    // Get/set the service access token

    public function token($token = NULL)
    {
        if (!is_null($token)) $this->token = $token;
        return $this->token;
    }

    // Service errors should be arrays

    protected function error($message)
    {
        $message .= ' in ' . get_class($this);
        return array('error' => $message);
    }

    // Override "get" in your service

    public function get()
    {
        return $this->error('method "get" not supported');
    }

    // Override "post" in your service

    public function post()
    {
        return $this->error('method "post" not supported');
    }

    // Override "put" in your service

    public function put()
    {
        return $this->error('method "put" not supported');
    }

    // Override "delete" in your service

    public function delete()
    {
        return $this->error('method "delete" not supported');
    }
}

// End of Service.php
