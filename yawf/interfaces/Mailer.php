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
 * The Mailer interface defines a single method to send email messages.
 * The App and Request classes show how the Mailer interface is used.
 */
interface Mailer
{
    /**
     * Send a mail message
     *
     * @param String $file the file to send (e.g. "welcome")
     * @param Object $render optional data to render (can be an Array)
     * @return String the raw content of the message that was sent
     */
    public function send_mail($file, $render = NULL);
}

// End of Mailer.php
