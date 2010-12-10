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
 * Provide XML methods to "serialize" and "deserialize" XML data.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class XML extends YAWF // depends on the SimpleXML PHP extension
{
    /**
     * By default, XML serialization and deserialization will
     * add an XML declaration, use the UTF8 encoding, use a
     * 2-space indent, use a root symbol "root" and parse in
     * "simplexml" mode (i.e. it won't use XML attributes).
     */
    private static $defaults = array(
        'addDecl' => TRUE,
        'encoding' => Symbol::UTF8,
        'indent' => '  ',
        'rootName' => Symbol::ROOT,
        'mode' => 'simplexml',
    );

    /**
     * Serialize a data array as a text string of XML data
     *
     * @param Array $data the array of data to be serialized
     * @param Array $options an array of options overrides (optional)
     * @return String the data serialized as a text string of XML data
     */
    public static function serialize($data, $options = array())
    {
        load_plugin('XML/Serializer');
        $options = array_merge(self::$defaults, $options);
        $serializer = new XML_Serializer($options);
        $status = $serializer->serialize($data);
        if (PEAR::isError($status)) throw new Exception($status->getMessage());
        return $serializer->getSerializedData();
    }

    /**
     * Deserialize a text string of XML data as a SimpleXMLElement object
     *
     * @param String $text the text string of XML data to be deserialized
     * @return Object a SimpleXMLElement object holding all the XML data
     */
    public static function deserialize($text)
    {
        return new SimpleXMLElement($text);
    }
}

// End of XML.php
