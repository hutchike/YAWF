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

class Auth extends YAWF
{
    private static $realm = 'Login';
    private static $message = 'Wrong username or password';
    private static $is_testing = FALSE;
    private static $test_username = '';
    private static $test_password = '';

    // Get the authenticated username

    public static function username()
    {
        if (self::$is_testing) return self::$test_username;
        return array_key($_SERVER, 'PHP_AUTH_USER');
    }

    // Get the authenticated password

    public static function password()
    {
        if (self::$is_testing) return self::$test_password;
        return array_key($_SERVER, 'PHP_AUTH_PW');
    }

    // Show a basic authentication login dialog pop-up

    public static function login($username, $password)
    {
        if (self::username() == $username && self::password() == $password)
        {
            return TRUE;
        }
        else // wrong username and/or password
        {
            header('WWW-Authenticate: Basic realm="' . self::$realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            print self::$message;
            return FALSE;
        }
    }

    // Setup the test login details (returned when testing)

    public static function test_login($username, $password)
    {
        self::is_testing(TRUE);
        self::test_username($username);
        self::test_password($password);
    }

    // Set the realm - i.e. user message dialog

    public static function realm($realm = NULL)
    {
        if (!is_null($realm)) self::$realm = $realm;
        return self::$realm;
    }

    // Set the message displayed on a login failure

    public static function message($message = NULL)
    {
        if (!is_null($message)) self::$message = $message;
        return self::$message;
    }

    // Set whether or not we're in testing mode right now

    public static function is_testing($is_testing = NULL)
    {
        if (!is_null($is_testing)) self::$is_testing = $is_testing;
        return self::$is_testing;
    }

    // Set the test username (returned for testing)

    public static function test_username($username = NULL)
    {
        if (!is_null($username)) self::$test_username = $username;
        return self::$test_username;
    }

    // Set the test password (returned for testing)

    public static function test_password($password = NULL)
    {
        if (!is_null($password)) self::$test_password = $password;
        return self::$test_password;
    }
}

// End of Auth.php
