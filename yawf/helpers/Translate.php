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

load_tool('YAML');

/**
 * Provide useful text translation methods after loading the YAML
 * "validate" and "translate" config files from the app "configs"
 * folder. The methods provided are "add_translations" to add new
 * translations overriding current ones, "into" to translate some
 * text and "validate" to check that translations aren't missing.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Translate extends YAWF
{
    private static $translations = array();

    /**
     * Add to the translations table as an array of XX language codes
     * where each array is an array of phrase codes with translations.
     *
     * @param Array $translations an array of translations keyed by language
     */
    public static function add_translations($translations)
    {
        foreach ($translations as $lang => $map)
        {
            // Over-ride earlier default translations with later ones

            if (!array_key(self::$translations, $lang)) self::$translations[$lang] = array();
            self::$translations[$lang] = array_merge(self::$translations[$lang], $map);
        }
    }

    /**
     * Translate a word into a language, optionally with replacements
     * for example "array('NAME', $user->name)" would insert the name.
     *
     * @param String $lang the language to translate into
     * @param String $lookup the string to lookup in the translations
     * @param Array $replacements an optional array of replacements to make
     * @return String the translated string in the chosen language
     */
    public static function into($lang, $lookup, $replacements = array())
    {
        $text = array_key(self::$translations[$lang], strtolower($lookup));
        if (is_null($text)) throw new Exception("Translation missing for $lookup in '$lang'");
        foreach ($replacements as $find => $replace)
        {
            $text = str_replace($find, $replace, $text);
        }
        return $text;
    }

    /**
     * Compare the translation lists to find any missing translations
     *
     * @throws Exception thrown if a translation is missing from a language
     */
    public static function validate()
    {
        $langs = array_keys(self::$translations);
        if (count($langs) < 2) return;

        for ($i = 1; $i < count($langs); $i++)
        {
            $list1 = array_keys(self::$translations[$langs[$i-1]]);
            $list2 = array_keys(self::$translations[$langs[$i]]);
            $diff = array_diff($list1, $list2);
            if ($diff) throw new Exception('Translation only in "' . $langs[$i-1] . '" ' . join(', ', $diff));
            $diff = array_diff($list2, $list1);
            if ($diff) throw new Exception('Translation only in "' . $langs[$i] . '" ' . join(', ', $diff));
        }
    }
}

/**
 * Application translations are kept in "app/configs"
 */
Translate::add_translations(Config::load('validate'));
Translate::add_translations(Config::load('translate'));

// End of Translate.php
