<?
// Copyright (c) 2009 Guanoo, Inc.
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

class XML extends YAWF // depends on the SimpleXML PHP extension
{
    private static $defaults = array(
        'addDecl' => TRUE,
        'encoding' => 'UTF8',
        'indent' => '  ',
        'rootName' => 'root',
        'mode' => 'simplexml',
    );

    public static function serialize($data, $options = array())
    {
        load_plugin('XML/Serializer');
        $options = array_merge(self::$defaults, $options);
        $serializer = new XML_Serializer($options);
        $status = $serializer->serialize($data);
        if (PEAR::isError($status)) throw new Exception($status->getMessage());
        return $serializer->getSerializedData();
    }

    public static function deserialize($data, $options = array())
    {
        return new SimpleXMLElement($data);
    }
}

// End of XML.php
