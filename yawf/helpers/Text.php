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

load_plugin('Text/AkInflector'); // from the Akelos PHP Framework

/**
 * Provide useful text manipulation methods via the AkInflector
 * class kindly provided by the Akelos PHP framework - for info
 * see http://www.akelos.org/ for more information.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Text extends YAWF
{
    /**
     * Use the excellent AkInflector
     */
    private static $inflector = NULL;

    /**
     * Return the singleton inflector object
     *
     * @return AkInflector the singleton inflector object
     */
    private static function singleton()
    {
        if (!self::$inflector) self::$inflector = new AkInflector();
        return self::$inflector;
    }

    /**
     * Convert a link into an "http" or "mailto" link
     *
     * @param String $link the link to convert
     * @return String the converted link
     */
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

    /**
     * Convert a link into an English plural, e.g. "person" ==> "people"
     *
     * @param String $word the word to pluralize
     * @return String the pluralized word
     */
    public static function pluralize($word)
    {
        return self::singleton()->pluralize($word);
    }

    /**
     * Convert a link into an English singular, e.g. "people" ==> "person"
     *
     * @param String $word the word to singularize
     * @return String the singularized word
     */
    public static function singularize($word)
    {
        return self::singleton()->singularize($word);
    }

    /**
     * Convert a word or sentence into a capitalized English title
     * e.g. "send_mail", "SendMail" or "send mail" ==> "Send Mail"
     *
     * @param String $word the word to titleize
     * @param String $uppercase choose "first" or "all" ("all" by default)
     * @return String the titleized word
     */
    public static function titleize($word, $uppercase = '')
    {
        return self::singleton()->titleize($word, $uppercase); // 'first' or 'all' (default)
    }

    /**
     * Convert a word into its camelized equivalent
     * e.g. "send_mail" ==> "SendMail"
     *
     * @param String $word the word to camelize
     * @return String the camelized word
     */
    public static function camelize($word)
    {
        return self::singleton()->camelize($word);
    }

    /**
     * Convert a word into its underscored equivalent
     * e.g. "Kevin's code" ==> "Kevin_s_code"
     *
     * @param String $word the word to underscore
     * @return String the underscored word
     */
    public static function underscore($word)
    {
        return self::singleton()->underscore($word);
    }

    /**
     * Translate a method name into some humanized friendly text
     * e.g. "first_place" ==> "First place"
     *
     * @param String $method the method name to humanize
     * @return String the humanized method name
     */
    public static function humanize($method, $capitalize = '')
    {
        return self::singleton()->humanize($method, $capitalize); // 'all' or 'first' (default)
    }

    /**
     * Convert some text into a variable (lowercase first letter)
     * e.g. "Some text" ==> "someText"
     *
     * @param String $word the word to variablize
     * @return String the variablized word
     */
    public static function variablize($word)
    {
        return self::singleton()->variablize($word);
    }

    /**
     * Convert a class name into a database table name
     * e.g. "Person" ==> "people"
     *
     * @param String $class_name the class name to tableize
     * @return String the tableized class name
     */
    public static function tableize($class_name)
    {
        return self::singleton()->tableize($class_name);
    }

    /**
     * Convert a table name into a class name
     * e.g. "people" ==> "Person"
     *
     * @param String $table_name the table name to classify
     * @return String the classified table name
     */
    public static function classify($table_name)
    {
        return self::singleton()->classify($table_name);
    }

    /**
     * Convert a number into its ordinal English form
     * e.g. "12" ==> "12th"
     *
     * @param String $number the number to ordinalize
     * @return String the ordinalized number
     */
    public static function ordinalize($number)
    {
        return self::singleton()->ordinalize($number);
    }

    /**
     * Convert a word into a URL-friendly equivalent without any accents
     * e.g. "súper mercado" ==> "super_mercado"
     *
     * @param String $word the word to urlize
     * @return String the urlized word
     */
    public static function urlize($word)
    {
        return self::singleton()->urlize($word);
    }
}

// End of Text.php
