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
    public static function get($url, $headers = array())
    {
        return self::method('get', $url, $headers);
    }

    public static function delete($url, $headers = array())
    {
        return self::method('delete', $url, $headers);
    }

    public static function post($url, $data, $headers = array())
    {
        return self::method('post', $url, $headers, $data);
    }

    public static function put($url, $data, $headers = array())
    {
        return self::method('put', $url, $headers, $data);
    }

    public static function method($method, $url, $headers = array(), $data = NULL)
    {
        // Get a Curl session

        $c = curl_init($url);

        // Include a payload

        if (!is_null($data))
        {
            //if (is_string($data)) $data = urlencode($data);
            curl_setopt($c, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Length: ' . strlen($data);
        }
        if ($headers) curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

        // Set the method type

        if ($method == 'get') {} // nothing to do
        elseif ($method == 'delete') curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
        elseif ($method == 'post') curl_setopt($c, CURLOPT_POST, TRUE);
        elseif ($method == 'put') curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');

        // Send the request and receive a response

        $received_data = curl_exec($c);
        if ($received_data === FALSE) throw new Exception(curl_error($c));
        return $received_data;
    }
}

// End of CURL.php
