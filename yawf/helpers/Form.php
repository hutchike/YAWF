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
    public static $field_name = 'form_ip';
    public static $grace_secs = 86400; // A whole day
    public static $has_script = FALSE;

    public static function spam_attrs($attrs = array())
    {
        if (array_key($attrs, 'onsubmit')) Log::error('Form::spam_attrs "onsubmit" conflict!');
        return array_merge($attrs, array('onsubmit' => 'return form_script(this)'));
    }

    public static function spam_field()
    {
        $field = self::$field_name;
        $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
        return "<input type=\"hidden\" name=\"$field\" value=\"$addr\"/>\n";
    }

    public static function spam_script()
    {
        // Don't write the script twice

        if (self::$has_script) return '';
        self::$has_script = TRUE;

        // Write some 'onsubmit' script

        $field = self::$field_name;
        $html = <<<End_of_HTML
<script type="text/javascript">
/* <![CDATA[ */
function form_script(f){f.$field.value+=' '+(new Date()).getTime();f.$field.name+='_tm';return true}
/* ]]> */
</script>
End_of_HTML;
        return $html;
    }

    public static function is_spam($params)
    {
        // First check the field name has changed

        $field = self::$field_name;
        $field .= '_tm';
        if ($value = $params->$field)
        {
            // Second check the IP and clock time

            list ($ip, $msecs) = explode(' ', $value);
            $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
            $secs = abs(time() - intval($msecs / 1000));
            Log::info("Form::is_spam() measured $secs seconds of browser time difference at IP $addr");
            if ($ip == $addr && $secs < self::$grace_secs) return FALSE;
        }

        // If our checks fail, it's probably spam

        return TRUE;
    }
}

// End of Form.php
