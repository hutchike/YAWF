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

    public static function from($type, $data)
    {
        switch (strtolower($type))
        {
            case 'json': return self::from_json($data);
            case 'xml': return self::from_xml($data);
            case 'yaml': return self::from_yaml($data);
            default: throw new Exception("Type $type is not supported");
        }
    }

    // Decode the JSON type and return data

    public static function from_json($data)
    {
        $data = trim($data, "()\n ");
        return json_decode($data, TRUE);
    }

    // Decode the XML type and return data

    public static function from_xml($data)
    {
        load_helper('XML');
        $data = object_to_array(XML::deserialize($data));
        return array_key($data, 'api', $data);
    }

    // Decode the YAML type and return data

    public static function from_yaml($data)
    {
        load_helper('YAML');
        return YAML::parse($data);
    }
}

// End of Data.php
