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
 * This helper provides a simple way to send email via PHP using
 * mime/multipart to include both a text and HTML message version.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Mail extends YAWF
{
    /**
     * Send a mail message by specifying all the message detials in am
     * array of render data, including "from", "to", "subject", "text"
     * and optionally "html" (if you wish to send multipart messages).
     * If we're testing then the mail is not sent, it's only returned.
     *
     * @param Object $render an object containing the mail details
     * @param Boolean $is_testing is it a mail test? (FALSE by default)
     * @return String the sent message, to be checked when testing
     */
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
            $message = $text;
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

    /**
     * Return a mail field with return/newline characters replaced with spaces
     *
     * @param String $value the field value to clean
     * @return String the field value after its cleaned
     */
    private static function field($value)
    {
        return strtr($value, "\r\n", '  ');
    }
}

// End of Mail.php
