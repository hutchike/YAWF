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
    /**
     * The $method, $url, $headers and $data are passed into the CURL mock
     * by the CURL mock user when it makes calls into the regular CURL API.
     */
    private static $method;
    private static $url;
    private static $headers;
    private static $data;
    private static $auth;

    /**
     * The $returned_content is content returned to the user of the CURL mock.
     */
    private static $returned_content = array();

    /**
     * Reset the data to get after the next call to a CURL method, e.g. "get"
     *
     * @param Boolean $do_reset_returned_content reset returned content too?
     */
    public static function reset($do_reset_returned_content = FALSE)
    {
        self::$method = NULL;
        self::$url = NULL;
        self::$headers = NULL;
        self::$data = NULL;
        self::$auth = NULL;
        if ($do_reset_returned_content) self::$returned_content = NULL;
    }

    /**
     * Get the method called by the CURL mock user
     *
     * @return String the method called by the CURL mock user
     */
    public static function get_mock_method()
    {
        return self::$method;
    }

    /**
     * Get the URL passed by the CURL mock user
     *
     * @return String the URL passed by the CURL mock user
     */
    public static function get_mock_url()
    {
        return self::$url;
    }

    /**
     * Get the headers passed by the CURL mock user
     *
     * @return Array the headers passed by the CURL mock user
     */
    public static function get_mock_headers()
    {
        return self::$headers;
    }

    /**
     * Get the data passed by the CURL mock user
     *
     * @return String/Array the data passed by the CURL mock user
     */
    public static function get_mock_data()
    {
        return self::$data;
    }

    /**
     * Get any auth passed by the CURL mock user via the URL (e.g. "user:pass")
     *
     * @return String any auth passed by the CURL mock user via the URL
     */
    public static function get_mock_auth()
    {
        return self::$auth;
    }

    /**
     * Set the content to be returned by CURL method calls
     *
     * @param Array $content the content to be returned by CURL method calls
     */
    public static function set_mock_returned_content($content)
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

        // Set the static properties to return test data to the mock client

        self::$method = $method;
        self::$url = $url;
        self::$headers = $headers;
        self::$data = $data;
        self::$auth = $auth;

        // Return data that has been set by "CURL::set_returned_content()"

        return self::$returned_content;
    }
}

// End of mocks/CURL.php
