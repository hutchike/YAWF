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

    public function get()
    {
        return 'method "get" not supported by ' . get_class($this);
    }

    public function post()
    {
        return 'method "post" not supported by ' . get_class($this);
    }

    public function put()
    {
        return 'method "put" not supported by ' . get_class($this);
    }

    public function delete()
    {
        return 'method "delete" not supported by ' . get_class($this);
    }
}

// End of Service.php
