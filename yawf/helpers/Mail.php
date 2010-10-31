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

class Mail extends YAWF
{
    public static function send($render, $is_testing = FALSE)
    {
        $render_defaults = array('from' => CONTACT_EMAIL, 'to' => NULL,
                                 'subject' => NULL,
                                 'text' => NULL, 'html' => NULL);
        $render->merge($render_defaults);

        // Get the message parameters

        $from = self::field($render->from);
        $to = self::field($render->to); if (!$to) throw new Exception('no "to" email');
        $subject = self::field($render->subject); if (is_null($subject)) throw new Exception('no email "subject"');
        $text = $render->text; if (is_null($text)) throw new Exception('no email message text');
        $html = $render->html;

        // Create the message headers and email simple text

        $headers = "From: $from\r\n" .
                   "Reply-To: $from\r\n" .
                   "X-Mailer: PHP/" . phpversion() . "\r\n";
        if (is_null($html))
        {
            $message = wordwrap($text, 80);
        }
        else // it's HTML
        {
            $random_hash = md5(date('r', time()));
            $headers .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-$random_hash\"\r\n";
            $message = <<<End_of_message
--PHP-alt-$random_hash
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

$text

--PHP-alt-$random_hash
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

$html

--PHP-alt-$random_hash--
End_of_message;
        }

        // Finally, send the email message

        $message = wordwrap($message, 80);
        if (!$is_testing) mail($to, $subject, $message, $headers);
        return $message;
    }

    // Mail fields cannot include returns

    private static function field($value)
    {
        return strtr($value, "\r\n", '  ');
    }
}

// End of Mail.php
