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

class Auth extends YAWF
{
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

    // Show a basic authentication login dialog in the user's web browser

    public static function login($realm = 'Login', $message = 'Wrong username or password')
    {
        header("WWW-Authenticate: Basic realm=\"$realm\"");
        header('HTTP/1.0 401 Unauthorized');
        print $message;
        exit;
    }

    // Set the test username (returned for testing)

    public static function test_username($username)
    {
        self::$test_username = $username;
    }

    // Set the test password (returned for testing)

    public static function test_password($password)
    {
        self::$test_password = $password;
    }

    // Set whether or not we're in testing mode right now

    public static function is_testing($is_testing = NULL)
    {
        if (!is_null($is_testing)) self::$is_testing = $is_testing;
        return self::$is_testing;
    }
}

// End of Auth.php
