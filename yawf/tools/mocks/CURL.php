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
 * This class provides a mock for the real "CURL" class.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class CURL extends YAWF
{
    private static $returned_content = array();

    /**
     * Set the content to be returned by CURL method calls
     *
     * @param Array $content the content to be returned by CURL method calls
     */
    public static function set_returned_content($content)
    {
        assert('is_string($content)');
        self::$returned_content = $content;
    }

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

        // Return data that has been set by "CURL::set_returned_content()"

        return self::$returned_content;
    }
}

// End of mocks/CURL.php
