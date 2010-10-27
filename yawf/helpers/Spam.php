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

class Spam extends YAWF
{
    public static $spam_score = 10;         // Measure out of 10
    public static $pass_score = 3;          // Pass at 3 or less
    public static $field_name = 'form_ip';  // Field name to use
    public static $grace_secs = 86400;      // A whole day grace
    public static $has_script = FALSE;      // Script was shown?

    public static function form_attrs($attrs = array())
    {
        if (array_key($attrs, 'onsubmit')) Log::error('Spam::form_attrs "onsubmit" conflict!');
        return array_merge($attrs, array('onsubmit' => 'return form_script(this)'));
    }

    public static function form_field()
    {
        $field = self::$field_name;
        $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
        return "<input type=\"hidden\" name=\"$field\" value=\"$addr\"/>\n";
    }

    public static function form_script()
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

    public static function score($params)
    {
        // First check the field name has changed

        $score = self::$spam_score;
        $field = self::$field_name;
        $field .= '_tm';
        if ($value = $params->$field)
        {
            $score -= 3;

            // Second check the IP and clock time

            list ($ip, $msecs) = explode(' ', $value);
            $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
            $secs = abs(time() - intval($msecs / 1000));
            if ($ip == $addr) $score -= 3;
            if ($secs < self::$grace_secs) $score -= 3;
            if ($secs >= self::$grace_secs / 2) Log::warn("Spam::score() measured $secs seconds of browser time difference at IP $addr");
        }

        // If the checks fail, it's probably spam

        if ($score < 0) $score = 0;
        Log::info("Spam::score() returned a score of $score");
        return $score;
    }

    public static function pass($params)
    {
        return self::score($params) <= self::$pass_score;
    }
}

// End of Spam.php
