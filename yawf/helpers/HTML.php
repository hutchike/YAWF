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
 * This helper makes it easy to insert HTML into views in a clean
 * consistent manner. The form methods support object properties:
 *
 * <code>
 * $html = HTML::form_open('form-id');
 * $html .= HTML::input('user->name');
 * $html .= HTML::submit('OK');
 * $html .= HTML::form_close();
 * </code>
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class HTML extends YAWF // and depends on "AppView" class in "App.php"
{
    /**
     * Set HTML::$id_format to Symbol::DASH if you prefer CSS ids like "the-id"
     */
    public static $id_format = Symbol::UNDERSCORE; // e.g. id="new_user_email"

    /**
     * Set HTML::$class_for_type['text'] = 'css-class-name' to set CSS classes
     */
    public static $class_for_type = array();

    /**
     * Return HTML text for an array of attribute values.
     * Note that emtpy attribute values are just skipped.
     *
     * @param Array $attrs the array of attribute values
     * @return String some HTML text of the form: attr1="this" attr2="that"...
     */
    public static function attrs($attrs)
    {
        $pairs = array();
        foreach ($attrs as $attr => $value)
        {
            if (strlen($value) == 0) continue;
            $pairs[] = $attr . '="' . $value . '"';
            if ($attr == 'type' && $class = array_key(self::$class_for_type, $value)) $pairs[] = 'class="' . $class . '"';
        }
        return join(' ', $pairs);
    }

    /**
     * Start an HTML form by returning an opening form tag
     *
     * @param String $id the form's id (required)
     * @param String $action the form's action (optional)
     * @param Array $attrs the form tag's attributes (optional)
     * @return String the opening HTML form tag
     */
    public static function form_open($id, $action = NULL, $attrs = array())
    {
        if (is_null($action) && $app = YAWF::prop(Symbol::APP)) $action = $app->get_path();
        $attrs['id'] = $id;
        $attrs['action'] = AppView::uri($action, array_key($attrs, 'prefix'));
        $attrs['method'] = array_key($attrs, 'method', 'post'); // by default
        return '<form ' . self::attrs($attrs) . ">\n";
    }

    /**
     * Finish an HTML form by returning a closing form tag
     *
     * @return String the closing HTML form tag
     */
    public static function form_close()
    {
        return "</form>\n";
    }

    /**
     * Return an HTML frame tag
     *
     * @param String $name the frame's name (required)
     * @param String $src the frame's source URI (optional)
     * @param Array $attrs the frame tag's attributes (optional)
     * @return String the HTML frame tag
     */
    public static function frame($name, $src = '', $attrs = array())
    {
        $attrs['name'] = $name;
        $attrs['src'] = AppView::uri($src, array_key($attrs, 'prefix'));
        $tag = array_key($attrs, Symbol::TAG, Symbol::FRAME);
        return "<$tag " . self::attrs($attrs) . ' />' . "\n";
    }

    /**
     * Return an HTML iframe tag
     *
     * @param String $name the iframe's name (required)
     * @param String $src the iframe's source URI (optional)
     * @param Array $attrs the iframe tag's attributes (optional)
     * @return String the HTML iframe tag
     */
    public static function iframe($name, $src = '', $attrs = array())
    {
        $attrs[Symbol::TAG] = Symbol::IFRAME;
        return self::frame($name, $src, $attrs);
    }

    /**
     * Return an HTML img tag
     *
     * @param String $src the image's source URI (required)
     * @param Array $attrs the img tag's attributes (optional)
     * @return String the HTML img tag
     */
    public static function img($src, $attrs = array())
    {
        $attrs['src'] = AppView::uri($src, array_key($attrs, 'prefix', FILE_URI_PREFIX));
        return '<img ' . self::attrs($attrs) . ' />';
    }

    /**
     * Return an HTML input tag
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag
     */
    public static function input($name, $attrs = array())
    {
        $object_name = null;
        if (is_null(array_key($attrs, 'value'))) $attrs['value'] = self::object_value_for($name);
        $attrs['id'] = self::css_id_for($name, $attrs);
        $attrs['name'] = h($name); // turn "->" into "-&gt;" (ugly but it works)
        $attrs['type'] = array_key($attrs, 'type', 'text');
        return '<input ' . self::attrs($attrs) . ' />' . "\n";
    }

    /**
     * Return an HTML input tag of a particular type (e.g. "button")
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $type the field type (required)
     * @param String $name the field name (required)
     * @param String $value the field value (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag
     */
    public static function input_type($type, $name, $value, $attrs = array())
    {
        $attrs['type'] = $type;
        $attrs['value'] = $value;
        return self::input($name, $attrs);
    }

    /**
     * Return an HTML input tag for a button field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param String $value the field value (optional - defaults to the name)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a button field
     */
    public static function button($name, $value = NULL, $attrs = array())
    {
        return self::input_type('button', $name, first($value, $name), $attrs);
    }

    /**
     * Return an HTML input tag for a checkbox field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param String $value the field value (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a checkbox field
     */
    public static function checkbox($name, $value, $attrs = array())
    {
        if (array_key($attrs, Symbol::CHECKED) ||
            $value == self::object_value_for($name))
        {
            $attrs[Symbol::CHECKED] = Symbol::CHECKED;
        }
        return self::input_type('checkbox', $name, $value, $attrs);
    }

    /**
     * Return an HTML input tag for a hidden field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param String $value the field value (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a hidden field
     */
    public static function hidden($name, $value, $attrs = array())
    {
        return self::input_type('hidden', $name, $value, $attrs);
    }

    /**
     * Return an HTML input tag for a password field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a password field
     */
    public static function password($name, $attrs = array())
    {
        return self::input_type('password', $name, '', $attrs); // blank value
    }

    /**
     * Return an HTML input tag for a radio field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param String $value the field value (required)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a radio field
     */
    public static function radio($name, $value, $attrs = array())
    {
        if (array_key($attrs, Symbol::CHECKED) ||
            $value == self::object_value_for($name))
        {
            $attrs[Symbol::CHECKED] = Symbol::CHECKED;
        }
        return self::input_type('radio', $name, $value, $attrs);
    }

    /**
     * Return an HTML input tag for a submit field
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param String $value the field value (optional - defaults to the name)
     * @param Array $attrs the input tag's attributes (optional)
     * @return String the HTML input tag for a submit field
     */
    public static function submit($name, $value = NULL, $attrs = array())
    {
        return self::input_type('submit', $name, first($value, $name), $attrs);
    }

    /**
     * Return an HTML "a" link tag
     *
     * @param String $uri the URI to link at (required)
     * @param String $html the HTML to display as a link (required)
     * @param Array $attrs the "a" link tag's attributes (optional)
     * @return String the HTML "a" link tag
     */
    public static function link($uri, $html, $attrs = array())
    {
        $attrs['href'] = AppView::uri($uri, array_key($attrs, 'prefix'));
        return '<a ' . self::attrs($attrs) . '>' . $html . '</a>';
    }

    /**
     * Return an HTML unordered list containing some items
     *
     * @param Array $items a list of items
     * @return String the HTML for the unordered list
     */
    public static function unordered_list($items)
    {
        if (is_array($items)) $items = self::list_items($items);
        return "<ul>\n$items\n</ul>\n";
    }

    /**
     * Return an HTML bullet (i.e. unordered) list containing some items
     *
     * @param Array $items a list of items
     * @return String the HTML for the bullet (i.e. unordered) list
     */
    public static function bullet_list($items)
    {
        return self::unordered_list($items);
    }

    /**
     * Return an HTML ordered list containing some items
     *
     * @param Array $items a list of items
     * @return String the HTML for the ordered list
     */
    public static function ordered_list($items)
    {
        if (is_array($items)) $items = self::list_items($items);
        return "<ol>\n$items\n</ol>\n";
    }

    /**
     * Return an HTML numbered (i.e. ordered) list containing some items
     *
     * @param Array $items a list of items
     * @return String the HTML for the numbered (i.e. ordered) list
     */
    public static function number_list($items)
    {
        return self::ordered_list($items);
    }

    /**
     * Return an HTML list of items to include in a bullet or number list
     *
     * @param Array $items a list of items
     * @return String the HTML containing a list of items
     */
    public static function list_items($items)
    {
        $html = '';
        foreach ($items as $item) $html .= self::list_item($item);
        return $html;
    }

    /**
     * Return an HTML list item
     *
     * @param String $item an item
     * @return String the HTML for the list item
     */
    public static function list_item($item)
    {
        return "\t<li>$item</li>\n";
    }

    /**
     * Return an HTML link tag (e.g. to include a CSS file)
     *
     * @param String $href the location of the file to link (required)
     * @param Array $attrs the meta tag's attributes (optional)
     * @return String the HTML link tag
     */
    public static function link_tag($href, $attrs = array())
    {
        $attrs['href'] = AppView::uri($href, array_key($attrs, 'prefix', FILE_URI_PREFIX));
        $attrs['type'] = array_key($attrs, 'type', 'text/css');
        $attrs['rel'] = array_key($attrs, 'rel', 'stylesheet');
        return '<link ' . self::attrs($attrs) . ' />' . "\n";
    }

    /**
     * Return an HTML meta tag
     *
     * @param Array $attrs the meta tag's attributes (required)
     * @return String the HTML meta tag
     */
    public static function meta_tag($attrs)
    {
        return '<meta ' . self::attrs($attrs) . ' />' . "\n";
    }

    /**
     * Return an HTML script tag
     *
     * @param String $src the location of the script file (required)
     * @param Array $attrs the script tag's attributes (optional)
     * @return String the HTML script tag
     */
    public static function script_tag($src, $attrs = array())
    {
        $attrs['src'] = AppView::uri($src, array_key($attrs, 'prefix', FILE_URI_PREFIX));
        $attrs['type'] = array_key($attrs, 'type', 'text/javascript'); // by default
        return '<script ' . self::attrs($attrs) . '></script>' . "\n";
    }

    /**
     * Return an HTML select tag containing a list of options
     * NOTE: The name can be an object property like "user->name"
     *
     * @param String $name the field name (required)
     * @param Array $options the options as value/text pairs (required)
     * @param String $selected the selected option value or text (optional)
     * @param Array $attrs the select tag's attributes (optional)
     * @return String the HTML select tag containing a list of options
     */
    public static function select($name, $options, $selected = NULL, $attrs = array())
    {
        if (is_null($selected)) $selected = self::object_value_for($name);
        $attrs['id'] = self::css_id_for($name, $attrs);
        $attrs['name'] = h($name);
        $html = '<select ' . self::attrs($attrs) . ">\n";
        foreach ($options as $value => $text)
        {
            $choose = ($value === $selected || $text === $selected) ? ' selected="selected"' : NULL;
            $value = is_int($value) ? NULL : ' value="' . $value . '"';
            $html .= "<option$value$choose>$text</option>\n";
        }
        return $html . "</select>\n";
    }

    /**
     * Return an HTML textarea tag
     *
     * @param String $name the field name (required)
     * @param Array $attrs the textarea tag's attributes (optional)
     * @return String the HTML textarea tag
     */
    public static function textarea($name, $attrs = array())
    {
        $object_name = null;
        $text = array_key($attrs, 'text');
        if (is_null($text)) $text = self::object_value_for($name);
        $attrs['id'] = self::css_id_for($name, $attrs);
        $attrs['name'] = h($name);
        return '<textarea ' . self::attrs($attrs) . ">$text</textarea>\n";
    }

    /**
     * Return a translated validation message for an object property field
     *
     * @param String $object_name_and_field the object field (e.g. "user->name")
     * @return String the translated validation message (e.g. "too short")
     */
    public static function validation_message($object_name_and_field)
    {
        list($object_name, $field) = explode(Symbol::ARROW, $object_name_and_field);
        $object = AppView::get($object_name);
        $message = $object->validation_message_for($field);
        return $message ? '<span class="validation_message">' . t($message) . '</span>' : null;
    }

    /**
     * Return the object value for an object name (e.g. "user->name")
     *
     * @param String $name the object name and property (e.g. "user->name")
     * @return String the object value or NULL if it was not found
     */
    public static function object_value_for($name)
    {
        $value = NULL;
        if (strstr($name, Symbol::ARROW)) // special case for "object->field"
        {
            list($object_name, $field) = explode(Symbol::ARROW, $name);
            $object = AppView::get($object_name);
            $value = h($object->$field);
        }
        return $value;
    }

    /**
     * Return the CSS id for an object name (e.g. "user->name")
     *
     * @param String $name the object name and property (e.g. "user->name")
     * @param Array $attrs an array of HTML attributes (optional)
     * @return String the CSS id for the object name (e.g. "user_name")
     */
    public static function css_id_for($name, $attrs = array())
    {
        if ($id = array_key($attrs, Symbol::ID)) return $id;
        else return str_replace(Symbol::ARROW, self::$id_format, $name);
    }   
}

// End of HTML.php
