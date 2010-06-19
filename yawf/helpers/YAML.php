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

load_plugin('YAML/spyc');

class YAML extends YAWF
{
    public static function parse($yaml)
    {
        return Spyc::YAMLLoadString($yaml);
    }

    public static function load_file($yaml_file)
    {
        return Spyc::YAMLLoad($yaml_file);
    }

    public static function dump($array, $indent = false, $wordwrap = false)
    {
        return Spyc::YAMLDump($array, $indent, $wordwrap);
    }
}

// End of YAML.php
