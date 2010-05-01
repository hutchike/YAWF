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

load_plugin('AkInflector'); // from the Akelos PHP Application Framework

class Text extends YAWF
{
    // Use the excellent AkInflector

    private static $inflector = NULL;
    private static function singleton()
    {
        if (!self::$inflector) self::$inflector = new AkInflector();
        return self::$inflector;
    }

    // Convert a link into an "http" or "mailto" link

    public static function link($link)
    {
        $link = html_entity_decode($link);
        $link = str_replace('"', urlencode('"'), $link);

        // Intelligently(?) add "mailto:" or "http://"

        if (FALSE === strpos($link, '/') && strpos($link, '@') > 0)
            $link = 'mailto:' . $link;
        elseif (!preg_match('/^http/', $link))
            $link = 'http://' . $link;

        return $link;
    }

    // Convert a word into English plural
    // e.g. "person" ==> "people"

    public static function pluralize($word)
    {
        return self::singleton()->pluralize($word);
    }

    // Convert a word into English singular
    // e.g. "people" ==> "person"

    public static function singularize($word)
    {
        return self::singleton()->singularize($word);
    }

    // Convert a word or sentence into a capitalized English title
    // e.g. "send_mail", "SendMail" or "send mail" ==> "Send Mail"

    public static function titleize($word, $uppercase = '')
    {
        return self::singleton()->titleize($word, $uppercase); // 'first' or 'all' (default)
    }

    // Convert a word into its camelized equivalent
    // e.g. "send_mail" ==> "SendMail"

    public static function camelize($word)
    {
        return self::singleton()->camelize($word);
    }

    // Convert a word into its underscored equivalent
    // e.g. "Kevin's code" ==> "Kevin_s_code"

    public static function underscore($word)
    {
        return self::singleton()->underscore($word);
    }

    // Translate a method name into some humanized friendly text
    // e.g. "first_place" ==> "First place"

    public static function humanize($method, $capitalize = '')
    {
        return self::singleton()->humanize($method, $capitalize); // 'all' or 'first' (default)
    }

    // Convert some text into a variable (lowercase first letter)
    // e.g. "Some text" ==> "someText"

    public static function variablize($word)
    {
        return self::singleton()->variablize($word);
    }

    // Convert a class name into a database table name
    // e.g. "Person" ==> "people"

    public static function tableize($class_name)
    {
        return self::singleton()->tableize($class_name);
    }

    // Convert a table name into a class name
    // e.g. "people" ==> "Person"

    public static function classify($table_name)
    {
        return self::singleton()->classify($table_name);
    }

    // Convert a number into its ordinal English form
    // e.g. "12" ==> "12th"

    public static function ordinalize($number)
    {
        return self::singleton()->ordinalize($number);
    }

    // Convert a word into a URL-friendly equivalent without any accents
    // e.g. "súper mercado" ==> "super_mercado"

    public static function urlize($word)
    {
        return self::singleton()->urlize($word);
    }
}

// End of Text.php
