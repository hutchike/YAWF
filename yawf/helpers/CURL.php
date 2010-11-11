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

class CURL extends YAWF
{
    public static function delete($url, $headers = array())
    {
        // TODO
    }

    public static function get($url, $headers = array())
    {
        $c = curl_init($url);
        if ($headers) curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($c);
        if ($data === FALSE) throw new Exception(curl_error($c));
        return $data;
    }

    public static function post($url, $data, $headers = array())
    {
        // TODO
    }

    public static function put($url, $data, $headers = array())
    {
        // TODO
    }
}

// End of CURL.php
