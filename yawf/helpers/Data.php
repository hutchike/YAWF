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
    // Return a data array by decoding a type

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

    // Decode the JSON type and return data

    public static function from_json($text)
    {
        $text = trim($text, "()\n ");
        return json_decode($text, TRUE);
    }

    // Decode the serialized type and return data

    public static function from_serialized($text)
    {
        return object_to_array(unserialize($text));
    }

    // Decode the XML type and return data

    public static function from_xml($text)
    {
        load_helper('XML');
        $data = object_to_array(XML::deserialize($text));
        return array_key($data, 'root', $data); // skip the root element
    }

    // Decode the YAML type and return data

    public static function from_yaml($text)
    {
        load_helper('YAML');
        return YAML::parse($text);
    }

    // Return some text by encoding a type

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

    // Encode the JSON type & return text

    public static function to_json($data)
    {
        return json_encode($data, TRUE);
    }

    // Encode the serialized type & return text

    public static function to_serialized($data)
    {
        return serialize($data);
    }

    // Encode the XML type & return text

    public static function to_xml($data)
    {
        load_helper('XML');
        return XML::serialize($data);
    }

    // Encode the YAML type & return text

    public static function to_yaml($data)
    {
        load_helper('YAML');
        return YAML::dump(object_to_array($data));
    }

    // Get the ID from some object data by looking for the class ID field

    public static function get_id($data, $class = NULL, $id_field = 'id')
    {
        if (is_object($data)) $data = object_to_array($data);
        if (!is_array($data)) return NULL; // data must be an array
        if ($id = array_key($data, $id_field)) return $id;
        if (!is_null($class) && $data = array_key($data, $class))
        {
            if (is_array($data) && $id = array_key($data, $id_field)) return $id;
        }
        return NULL;
    }
}

// End of Data.php
