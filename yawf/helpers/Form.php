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

class Form extends YAWF // and depends on "AppView" and "Translate"
{
    private static $field_name = 'form_sec';

    public static function spam_attrs($attrs = array())
    {
        if (array_key($attrs, 'onsubmit')) Log::error('Form::spam_attrs "onsubmit" conflict!');
        return array_merge($attrs, array('onsubmit' => 'return form_script(this)'));
    }

    public static function spam_field($field = NULL)
    {
        if (is_null($field)) $field = self::$field_name;
        return "<input type=\"hidden\" name=\"$field\" value=\"\"/>\n";
    }

    public static function spam_script($field = NULL)
    {
        if (is_null($field)) $field = self::$field_name;
        $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
        $html = <<<End_of_HTML
<script type="text/javascript">
/* <![CDATA[ */
function form_script(f){f.$field.value='$addr';f.$field.name+='_ip';return true}
/* ]]> */
</script>
End_of_HTML;
        return $html;
    }

    public static function is_spam($params, $field = NULL)
    {
        if (is_null($field)) $field = self::$field_name . '_ip';
        $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
        return $params->$field == $addr ? FALSE : TRUE;
    }
}

// End of Form.php
