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

load_plugin('YAML/spyc');

/**
 * Provide YAML methods to "parse" and "dump" YAML data.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class YAML extends YAWF
{
    /**
     * Parse a text string of YAML data into a data array
     *
     * @param String $yaml the string of YAML data to parse
     * @return Array a data array containing the YAML data
     */
    public static function parse($yaml)
    {
        return Spyc::YAMLLoadString($yaml);
    }

    /**
     * Parse a text file of YAML data into a data array
     *
     * @param String $yaml_file the filename containing YAML data to parse
     * @return Array a data array containing the YAML data
     */
    public static function parse_file($yaml_file)
    {
        return Spyc::YAMLLoad($yaml_file);
    }

    /**
     * Dump an array of data as a YAML text string
     *
     * @param Array $array the array of data to dump as YAML text
     * @return String a text string of YAML data
     */
    public static function dump($array, $indent = FALSE, $wordwrap = FALSE)
    {
        return Spyc::YAMLDump($array, $indent, $wordwrap);
    }
}

// End of YAML.php
