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

class REST extends YAWF
{
    private static $type = Symbol::XML;
    private static $charset = Symbol::UTF8;

    public static function type($type = NULL)
    {
        return is_null($type) ? self::$type
                              : self::$type = $type;
    }

    public static function charset($charset = NULL)
    {
        return is_null($charset) ? self::$charset
                                 : self::$charset = $charset;
    }

    public static function delete($url, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $text = CURL::delete($url, self::headers_for($type, $charset));
        return Data::from($type, $text);
    }

    public static function get($url, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $text = CURL::get($url, self::headers_for($type));
        return Data::from($type, $text);
    }

    public static function post($url, $data, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $data = Data::to($type, $data);
        $text = CURL::post($url, $data, self::headers_for($type));
        return Data::from($type, $text);
    }

    public static function put($url, $data, $type = NULL, $charset = NULL)
    {
        $type = first($type, self::$type);
        $charset = first($charset, self::$charset);
        $data = Data::to($type, $data);
        $text = CURL::put($url, $data, self::headers_for($type));
        return Data::from($type, $text);
    }

    // Return an array of request headers for the content type

    private static function headers_for($type, $charset = NULL)
    {
        $charset = first($charset, self::$charset);
        $headers = array();
        $headers[] = "User-Agent: YAWF (http://yawf.org/)";
        $headers[] = "Content-Type: application/$type;charset=$charset";
        return $headers;
    }
}

// End of REST.php