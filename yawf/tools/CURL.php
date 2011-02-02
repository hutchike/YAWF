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

if (!function_exists('curl_init')) {
    throw new Exception('The YAWF CURL helper needs the cURL PHP extension');
}

/**
 * Wrap the PHP cURL extension with handy static function calls
 * for the HTTP methods "get", "delete", "post" and "put". Note
 * that the URL in function calls may be prefixed using a basic
 * authentication login like "https://user:pass@site.com/a/12".
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class CURL extends YAWF
{
    /**
     * Get a resource at a URL
     *
     * @param String $url the The URL to request
     * @param Array $headers optional headers to send
     * @return String the web server response content
     */
    public static function get($url, $headers = array())
    {
        return self::method('get', $url, $headers);
    }

    /**
     * Delete a resource at a URL
     *
     * @param String $url the The URL to request
     * @param Array $headers optional headers to send
     * @return String the web server response content
     */
    public static function delete($url, $headers = array())
    {
        return self::method('delete', $url, $headers);
    }

    /**
     * Post some data to a URL, and return the response content
     *
     * @param String $url the The URL to request
     * @param Array $headers optional headers to send
     * @return String the web server response content
     */
    public static function post($url, $data, $headers = array())
    {
        return self::method('post', $url, $headers, $data);
    }

    /**
     * Put some data to a URL, and return the response content
     *
     * @param String $url the The URL to request
     * @param Array $headers optional headers to send
     * @return String the web server response content
     */
    public static function put($url, $data, $headers = array())
    {
        return self::method('put', $url, $headers, $data);
    }

    /**
     * Perform an HTTP method at a URL with optional headers and data to send
     *
     * @param String $method the The HTTP method to use for the request
     * @param String $url the The URL to request
     * @param Array $headers optional headers to send
     * @param String $data optional data to put or post (may also be an Array)
     * @return String the web server response content
     */
    public static function method($method, $url, $headers = array(), $data = NULL)
    {
        // Parse a "user:password@" URL prefix for basic auth by
        // removing it, like taking the meat out of the sandwich
        // and being sure to remember the protocol we're using.

        $protocol = '';
        $auth = '';
        if (preg_match('/^(https?:\/\/)?([^:]+:[^@]+)@(.+)$/', $url, $matches))
        {
            list($all, $protocol, $auth, $url) = $matches;
        }
        $url = $protocol . $url;
        
        // Get a Curl session

        $c = curl_init($url);
        if ($auth) curl_setopt($c, CURLOPT_USERPWD, $auth);

        // Include a payload

        if (!is_null($data))
        {
            curl_setopt($c, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Length: ' . strlen($data);
        }
        if ($headers) curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

        // Set our default CURL web browser settings

        $cookies = tempnam('/tmp', 'curl_cookies_');
        curl_setopt($c, CURLOPT_COOKIEJAR, $cookies);
        curl_setopt($c, CURLOPT_MAXREDIRS, 10);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);

        // Set the method type

        if ($method == 'get') {} // nothing to do
        elseif ($method == 'delete') curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
        elseif ($method == 'post') curl_setopt($c, CURLOPT_POST, TRUE);
        elseif ($method == 'put') curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');

        // Send the request and receive the response to return as a string

        $received_data = curl_exec($c);

        // Remove the cookies, throw errors as excptions, then return data

        curl_close($c);
        unlink($cookies);
        if ($received_data === FALSE) throw new Exception(curl_error($c));
        return $received_data;
    }
}

// End of CURL.php
