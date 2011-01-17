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

if (!function_exists('json_decode')) {
    throw new Exception('The YAML Data helper needs the JSON PHP extension');
}

/**
 * Encode and decode data into various formats, including JSON,
 * PHP Serialized, XML and YAML. Note that decoded data is always
 * returned as an associative array for consistency when working
 * with multiple data formats. Also note that XML data has a "root"
 * element automatically added (encode) and removed (decode).
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Data extends YAWF
{
    /**
     * Return a data array by decoding a type
     *
     * @param String $type the data type
     * @param String $text the text to decode
     * @return Array the decoded data as an assoc array
     */
    public static function from($type, $text)
    {
        switch (strtolower($type))
        {
            case Symbol::JS:
            case Symbol::JSON:
            case Symbol::JSONP:
                return self::from_json($text);

            case Symbol::SERIALIZED:
                return self::from_serialized($text);

            case Symbol::XML:
                return self::from_xml($text);

            case Symbol::YAML:
                return self::from_yaml($text);

            default: throw new Exception("Type $type is not supported");
        }
    }

    /**
     * Decode the JSON type and return data
     *
     * @param String $text the text to decode
     * @return Array the decoded data as an assoc array
     */
    public static function from_json($text)
    {
        $text = trim($text, "()\n ");
        return json_decode($text, TRUE);
    }

    /**
     * Decode the serialized type and return data (an array by default)
     *
     * @param String $text the text to decode
     * @param Boolean $as_object whether to return an object instead
     * @return Array the decoded data as an assoc array
     */
    public static function from_serialized($text, $as_object = FALSE)
    {
        $object = unserialize($text);
        return $as_object ? $object : object_to_array($object);
    }

    /**
     * Decode the XML type and return data
     *
     * @param String $text the text to decode
     * @param String $root the root element name (default is "root")
     * @return Array the decoded data as an assoc array
     */
    public static function from_xml($text, $root = Symbol::ROOT)
    {
        load_tool('XML');
        $data = object_to_array(XML::deserialize($text));
        return array_key($data, $root, $data); // skip the root element
    }

    /**
     * Decode the YAML type and return data
     *
     * @param String $text the text to decode
     * @return Array the decoded data as an assoc array
     */
    public static function from_yaml($text)
    {
        load_tool('YAML');
        return YAML::parse($text);
    }

    /**
     * Return some text by encoding data into a type
     *
     * @param String $type the data type
     * @param Array/Object $data the data to encode
     * @return String the encoded data as text
     */
    public static function to($type, $data)
    {
        switch (strtolower($type))
        {
            case Symbol::JS:
            case Symbol::JSON:
            case Symbol::JSONP:
                return self::to_json($data);

            case Symbol::SERIALIZED:
                return self::to_serialized($data);

            case Symbol::XML:
                return self::to_xml($data);

            case Symbol::YAML:
                return self::to_yaml($data);

            default: throw new Exception("Type $type is not supported");
        }
    }

    /**
     * Encode data as JSON and return JSON text
     *
     * @param Array/Object $data the data to encode
     * @return String the encoded data as JSON text
     */
    public static function to_json($data)
    {
        return json_encode($data);
    }

    /**
     * Encode data as PHP serialized and return PHP serialized text
     *
     * @param Array/Object $data the data to encode
     * @return String the encoded data as PHP serialized text
     */
    public static function to_serialized($data)
    {
        return serialize($data);
    }

    /**
     * Encode data as XML and return XML text
     *
     * @param Array/Object $data the data to encode
     * @param String $root the root element name (default is "root")
     * @return String the encoded data as XML text
     */
    public static function to_xml($data, $root = Symbol::ROOT)
    {
        load_tool('XML');
        return XML::serialize($data, array('rootName' => $root));
    }

    /**
     * Encode data as YAML and return YAML text
     *
     * @param Array/Object $data the data to encode
     * @return String the encoded data as YAML text
     */
    public static function to_yaml($data)
    {
        load_tool('YAML');
        return YAML::dump(object_to_array($data));
    }
}

// End of Data.php
