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

class HTML extends YAWF // and depends on "AppView" and "Translate"
{
    public static function attrs($attrs)
    {
        $pairs = array();
        foreach ($attrs as $attr => $value)
        {
            $pairs[] = $attr . '="' . $value . '"';
        }
        return join(' ', $pairs);
    }

    public static function form_open($id, $action, $attrs = array())
    {
        $attrs['id'] = $id;
        $attrs['action'] = AppView::url($action, array_key($attrs, 'prefix'));
        $attrs['method'] = array_key($attrs, 'method', 'post'); // by default
        return '<form ' . self::attrs($attrs) . ">\n";
    }

    public static function frame($name, $src, $attrs = array())
    {
        $attrs['name'] = $name;
        $attrs['src'] = AppView::url($src, array_key($attrs, 'prefix'));
        return '<frame ' . self::attrs($attrs) . ' />' . "\n";
    }

    public static function img($url, $attrs = array())
    {
        $attrs['src'] = AppView::url($url, array_key($attrs, 'prefix', FILE_URL_PREFIX));
        return '<img ' . self::attrs($attrs) . ' />';
    }

    public static function input($name, $attrs = array())
    {
        $object_name = null;
        if (strstr($name, '->')) // special case for "object->field"
        {
            list($object_name, $field) = preg_split('/\->/', $name);
            $object = AppView::get($object_name);
            $attrs['value'] = array_key($attrs, 'value', $object->$field);
        }
        $attrs['id'] = array_key($attrs, 'id', str_replace('->', '-', $name));
        $attrs['name'] = h($name);
        $attrs['type'] = array_key($attrs, 'type', 'text');
        return '<input ' . self::attrs($attrs) . ' />' . "\n";
    }

    public static function link($url, $html, $attrs = array())
    {
        $attrs['href'] = AppView::url($url, array_key($attrs, 'prefix'));
        return '<a ' . self::attrs($attrs) . '>' . $html . '</a>';
    }

    public static function link_tag($url, $attrs = array())
    {
        $attrs['href'] = AppView::url($url, array_key($attrs, 'prefix', FILE_URL_PREFIX));
        $attrs['type'] = array_key($attrs, 'type', 'text/css');
        $attrs['rel'] = array_key($attrs, 'rel', 'stylesheet');
        return '<link ' . self::attrs($attrs) . ' />' . "\n";
    }

    public static function bullet_list($items)
    {
        return "<ul>\n$items\n</ul>\n";
    }

    public static function number_list($items)
    {
        return "<ol>\n$items\n</ol>\n";
    }

    public static function list_item($text)
    {
        return "\t<li>$text</li>\n";
    }

    public static function script($url, $attrs = array())
    {
        $attrs['src'] = AppView::url($url, array_key($attrs, 'prefix', FILE_URL_PREFIX));
        $attrs['type'] = array_key($attrs, 'type', 'text/javascript'); // by default
        return '<script ' . self::attrs($attrs) . '></script>' . "\n";
    }

    public static function select($id, $options, $selected = NULL, $attrs = array())
    {
        $attrs['name'] = $attrs['id'] = $id;
        $html = '<select ' . self::attrs($attrs) . ">\n";
        foreach ($options as $value => $text)
        {
            $choose = $value === $selected || $text === $selected ? ' selected="selected"' : NULL;
            $value = is_int($value) ? NULL : ' value="' . $value . '"';
            $html .= "<option$value$choose>$text</option>\n";
        }
        return $html . '</select>';
    }

    public static function textarea($name, $attrs = array())
    {
        $object_name = null;
        if (strstr($name, '->')) // special case for "object->field"
        {
            list($object_name, $field) = preg_split('/\->/', $name);
            $object = AppView::get($object_name);
            $text = $object->$field;
        }
        $attrs['id'] = array_key($attrs, 'id', str_replace('->', '-', $name));
        $attrs['name'] = h($name);
        return '<textarea ' . self::attrs($attrs) . ">$text</textarea>\n";
    }

    public static function translate($message)
    {
        return Translate::into(AppView::get('lang'), $message);
    }

    public static function validation_message($object_name_and_field)
    {
        list($object_name, $field) = preg_split('/\->/', $object_name_and_field);
        $object = AppView::get($object_name);
        $message = $object->validation_message_for($field);
        return $message ? '<span class="validation_message">' . HTML::translate($message) . '</span>' : null;
    }
}

// End of HTML.php
