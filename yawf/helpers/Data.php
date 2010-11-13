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

class Data extends YAWF
{
    // Return a data array by decoding a type

    public static function from($type, $text)
    {
        switch (strtolower($type))
        {
            case 'js':
            case 'json':
            case 'jsonp':
                return self::from_json($text);

            case 'xml':
                return self::from_xml($text);

            case 'yaml':
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
            case 'js':
            case 'json':
            case 'jsonp':
                return self::to_json($data);

            case 'xml':
                return self::to_xml($data);

            case 'yaml':
                return self::to_yaml($data);

            default: throw new Exception("Type $type is not supported");
        }
    }

    // Encode the JSON type & return text

    public static function to_json($data)
    {
        return json_encode($data, TRUE);
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

    // Get the ID from some object data (using a class)

    public static function get_id($data, $class = NULL)
    {
        if (is_object($data)) $data = object_to_array($data);
        if (!is_array($data)) return NULL;
        if ($id = array_key($data, 'id')) return $id;
        if ($class = array_key($data, $class))
        {
            if (is_array($class) && $id = array_key($class, 'id')) return $id;
        }
        return NULL;
    }
}

// End of Data.php
