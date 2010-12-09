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

load_helpers('CURL', 'Data');

/**
 * Provide REST methods to "get", "put", "post" and "delete" data,
 * and parse any received data so that it's available as an array.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class REST extends YAWF
{
    /**
     * By default, REST uses the JSON type for parsing speed
     */
    private static $type = Symbol::JSON;

    /**
     * By default, REST uses the UTF8 encoding
     */
    private static $charset = Symbol::UTF8;

    /**
     * Get/set the REST content type we're using
     *
     * @param String $type the content type to use (optional)
     * @return String the content type being used (e.g. "JSON")
     */
    public static function type($type = NULL)
    {
        return is_null($type) ? self::$type
                              : self::$type = $type;
    }

    /**
     * Get/set the REST character set encoding we're using
     *
     * @param String $charset the character set encoding to use (optional)
     * @return String the character set encoding being used (e.g. "UTF-8")
     */
    public static function charset($charset = NULL)
    {
        return is_null($charset) ? self::$charset
                                 : self::$charset = $charset;
    }

    /**
     * Perform a REST "delete" method at a URL
     *
     * @param String $url the URL to "delete"
     * @param String $type the content type to use (optional)
     * @param String $charset the charset encoding to use (optional)
     * @return Array any decoded data returned from the method
     */
    public static function delete($url, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $text = CURL::delete($url, self::headers_for($type, $charset));
        return self::decode($type, $text, 'delete');
    }

    /**
     * Perform a REST "get" method at a URL
     *
     * @param String $url the URL to "get"
     * @param String $type the content type to use (optional)
     * @param String $charset the charset encoding to use (optional)
     * @return Array any decoded data returned from the method
     */
    public static function get($url, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $text = CURL::get($url, self::headers_for($type));
        return self::decode($type, $text, 'get');
    }

    /**
     * Perform a REST "post" method at a URL
     *
     * @param String $url the URL to "post"
     * @param Array $data the data to "post"
     * @param String $type the content type to use (optional)
     * @param String $charset the charset encoding to use (optional)
     * @return Array any decoded data returned from the method
     */
    public static function post($url, $data, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $data = Data::to($type, $data);
        $text = CURL::post($url, $data, self::headers_for($type));
        return self::decode($type, $text, 'post');
    }

    /**
     * Perform a REST "put" method at a URL
     *
     * @param String $url the URL to "put"
     * @param Array $data the data to "post"
     * @param String $type the content type to use (optional)
     * @param String $charset the charset encoding to use (optional)
     * @return Array any decoded data returned from the method
     */
    public static function put($url, $data, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $data = Data::to($type, $data);
        $text = CURL::put($url, $data, self::headers_for($type));
        return self::decode($type, $text, 'put');
    }

    /**
     * Return an array of headers for a content type
     *
     * @param String $type the content type
     * @param String $charset the charset encoding to use (optional)
     * @return Array an array of headers for the content type
     */
    private static function headers_for($type, $charset = NULL)
    {
        $charset = first($charset, self::$charset);
        $headers = array();
        $headers[] = "User-Agent: YAWF (http://yawf.org/)";
        $headers[] = "Content-Type: application/$type;charset=$charset";
        return $headers;
    }

    /**
     * Return an assoc array of decoded data
     * NOTE: This method does not throw exceptions, but logs them as warnings
     *
     * @param String $type the content type
     * @param String $text the text to decode
     * @param String $method an optional REST method name to debug warnings
     * @return Array data that was decoded from the text
     */
    private static function decode($type, $text, $method = '')
    {
        $data = array();
        try
        {
            $data = Data::from($type, $text);
        }
        catch (Exception $e)
        {
            if (strlen($method) > 0) $method = " $method method";
            Log::warn("REST$method warning " . $e->getMessage());
            Log::info("Check that REST permissions are correct");
        }
        return $data;
    }
}

// End of REST.php
