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
 * Provide basic auth functionality for web apps by providing the
 * simple API methods "username", "password", "challenge", "login",
 * "message" and "realm".
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class BasicAuth extends YAWF
{
    private static $realm = 'Login';
    private static $message = 'Wrong username or password';

    /**
     * Get the basic auth username
     *
     * @return String the basic auth username
     */
    public static function username()
    {
        $server = YAWF::prop(Symbol::SERVER);
        return $server->php_auth_user;
    }

    /**
     * Get the basic auth password
     *
     * @return String the basic auth password
     */
    public static function password()
    {
        $server = YAWF::prop(Symbol::SERVER);
        return $server->php_auth_pw;
    }

    /**
     * Challenge the web user to provide a username and password
     *
     * @param String $message the message to display (optional)
     * @param String $realm the realm to display (optional)
     * @return NULL nothing is returned
     */
    public static function challenge($message = NULL, $realm = NULL)
    {
        if (is_null($message)) $message = self::$message;
        if (is_null($realm)) $realm = self::$realm;
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('HTTP/1.0 401 Unauthorized');
        print $message;
    }

    /**
     * Require the web user to login using basic auth.
     *
     * Either pass the required username and password,
     * or pass an object in place of the username, and
     * have the object provide a "basic_auth()" method
     * with username and password parameters, returning
     * TRUE if the username and password are authorized.
     *
     * Note that this method will call YAWF::finish()
     * by default, to cause the web request to finish.
     *
     * @param String/Object $username the username or object with "basic_auth()"
     * @param String $password the required password (when username is a string)
     * @param Boolean $do_return whether to always return (default is FALSE)
     * @return Boolean whether the login succeeded
     */
    public static function login($username, $password = '', $do_return = FALSE)
    {
        if (is_object($username))
        {
            if ($username->basic_auth(self::username(), self::password())) return TRUE;
        }
        elseif (is_string($username))
        {
            if (self::username() == $username && self::password() == $password) return TRUE;
        }

        // Wrong username and/or password

        self::challenge();
        if ($do_return) return FALSE;
        YAWF::finish(); // this exits
    }

    /**
     * Set the realm for basic auth challenges
     *
     * @param String $realm the realm to display (optional)
     * @return String the currently defined realm
     */
    public static function realm($realm = NULL)
    {
        if (!is_null($realm)) self::$realm = $realm;
        return self::$realm;
    }

    /**
     * Set the message for basic auth challenges
     *
     * @param String $message the message to display (optional)
     * @return String the currently defined message
     */
    public static function message($message = NULL)
    {
        if (!is_null($message)) self::$message = $message;
        return self::$message;
    }
}

// End of BasicAuth.php
