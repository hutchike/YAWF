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
 * Provide spam protection for HTML forms (and their controllers),
 * by using JavaScript to modify a hidden form element to indicate
 * whether the form was submitted by a real user who took a while.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Spam extends YAWF // and depends on "HTML"
{
    public static $spam_score = 10;         // Measure out of 10
    public static $pass_score = 3;          // Pass at 3 or less
    public static $field_name = 'form_ip';  // Field name to use
    public static $grace_secs = 86400;      // A whole day grace
    public static $quick_secs = 3;          // Filled in quickly
    public static $has_script = FALSE;      // Script was shown?
    public static $is_testing = FALSE;      // True when testing

    /**
     * Return HTML attrs for a form open tag (e.g in "HTML::form_open()")
     * These attrs will cause a JavaScript anti-spam script to be called.
     *
     * @param Array $attrs an optional array of attrs
     * @return Array the attrs, including anti-spam measures
     */
    public static function attrs($attrs = array())
    {
        if (array_key($attrs, 'onsubmit')) Log::error('Spam::attrs "onsubmit" conflict!');
        return array_merge($attrs, array('onsubmit' => 'return(window.form_script?form_script(this):true)'));
    }

    /**
     * Return HTML for a hidden field to hold spam detection values.
     * This method should be called from within a view with a form.
     *
     * @return String the HTML for a hidden field
     */
    public static function field()
    {
        $field = self::$field_name;
        $addr = hostaddr();
        return "<input type=\"hidden\" name=\"$field\" value=\"$addr\"/>\n";
    }

    /**
     * Return HTML for some JavaScript to perform spam detection.
     * This method should be called from within a view with a form.
     *
     * @return String the HTML for the JavaScript
     */
    public static function script()
    {
        // Don't write the script twice

        if (self::$has_script) return '';
        self::$has_script = TRUE;

        // Write some 'onsubmit' script

        $field = self::$field_name;
        $html = <<<End_of_HTML
<script type="text/javascript">
/* <![CDATA[ */
window.form_render=new Date();
window.form_script=function(f){f.$field.value+='|'+window.form_render.getTime()+'|'+(new Date()).getTime();f.$field.name+='_tm';return true};
/* ]]> */
</script>
End_of_HTML;
        return $html;
    }

    /**
     * Get a spam score by checking the form parameters against
     * a number of tests, including whether we can rename the
     * form field using JavaScript, whether the browser's clock
     * is wrongly set, and whether the form was submitted quickly.
     *
     * @param Object $params the request parameters submitted
     * @param Integer $test_score an optional test score to return when testing
     * @return Integer the spam score between 1 and 10
     */
    public static function score($params, $test_score = 1)
    {
        if (self::$is_testing) return $test_score;

        // First check the field name has changed

        $score = self::$spam_score;
        $field = self::$field_name;
        $field .= '_tm';
        if ($value = $params->$field)
        {
            $score -= 3;

            // Second check the IP and clock time

            list ($ip, $msecs1, $msecs2) = explode('|', $value);
            $addr = array_key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']);
            $used = intval(($msecs2 - $msecs1) / 1000);
            $diff = abs(time() - intval($msecs2 / 1000));
            if ($ip == $addr) $score -= 2;
            if ($used >= self::$quick_secs) $score -= 2;
            if ($diff <= self::$grace_secs) $score -= 2;
            if ($diff >= self::$grace_secs / 2) Log::warn("Spam::score() measured $diff seconds of browser time difference at IP $addr");
            $info = " (form submitted after $used secs)";
        }
        else
        {
            $info = ' (field was not renamed)';
        }

        // If the checks fail, it's probably spam

        if ($score < 0) $score = 0;
        Log::info("Spam::score() returned $score$info");
        return $score;
    }

    /**
     * Check whether the form params indicate it looks like spam.
     * This should be called from within the form controller.
     *
     * @param Object $params the request parameters submitted
     * @param Integer $score an optional spam score threshold
     * @return Boolean whether it is probably spam
     */
    public static function is_spam($params, $score = NULL)
    {
        if (is_null($score)) $score = self::$pass_score;
        return self::score($params) > $score;
    }

    /**
     * Check whether the form params indicate it's not spam.
     * This should be called from within the form controller.
     *
     * @param Object $params the request parameters submitted
     * @param Integer $score an optional spam score threshold
     * @return Boolean whether it's probably not spam
     */
    public static function is_not_spam($params, $score = NULL)
    {
        return !self::is_spam($params, $score);
    }

    /**
     * Get/set whether we're testing. This should be called by
     * controller test suites to prevent unwanted spam detection.
     *
     * @param Boolean $is_testing whether we're testing (optional)
     * @return Boolean whether we're testing
     */
    public static function is_testing($is_testing = NULL)
    {
        if (!is_null($is_testing)) self::$is_testing = $is_testing;
        return self::$is_testing;
    }
}

// End of Spam.php
