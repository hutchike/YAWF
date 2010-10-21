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

class Log extends YAWF
{
    const DEBUG = 0;
    const INFO = 1;
    const WARN = 2;
    const ERROR = 3;
    const ALERT = 4;
    const TEST = 5;
    private static $level_names = array('DEBUG', 'INFO', 'WARN', 'ERROR', 'ALERT', 'TEST');
    private static $level;
    private static $type;
    private static $is_testing;

    public static function level($level = NULL)
    {
        if (!is_null($level)) self::$level = $level;
        return self::$level;
    }

    public static function type($type = NULL)
    {
        if (!is_null($type)) self::$type = $type;
        return self::$type;
    }

    public static function is_testing($is_testing = NULL)
    {
        if (!is_null($is_testing)) self::$is_testing = $is_testing;
        return self::$is_testing;
    }

    public static function level_name($level)
    {
        return self::$level_names[$level];
    }

    public static function line($line, $level = self::INFO)
    {
        if (!LOGGING_ENABLED) return;
        if (is_array($line)) $line = join(' ', $line);
        if (!self::$level) self::$level = array_search(strtoupper(DEFAULT_LOG_LEVEL), self::$level_names);
        if ($level < self::$level) return;
        $path = file_exists('app/logs') ? 'app/logs' : 'yawf/logs';
        $date = date('Ymd');
        $type = self::$type ? '.' . self::$type : '';
        $log = self::$is_testing ? 'test.log' : 'log';
        $fh = fopen("$path/$date$type.$log", 'a'); // append
        $time = date('H:i:s');
        $level = self::level_name($level);
        fwrite($fh, "$time $level $line\n");
        fclose($fh);
    }

    public static function assert($pass, $line, $var = NULL)
    {
        if ($pass)
        {
            self::info("Assertion passed: $line");
        }
        else
        {
            $debug = $var ? "\n" . print_r($var, TRUE) : '';
            self::error("Assertion failed: $line$debug");
        }
    }

    public static function debug($line)
    {
        self::line($line, self::DEBUG);
    }

    public static function info($line)
    {
        self::line($line, self::INFO);
    }

    public static function warn($line)
    {
        self::line($line, self::WARN);
    }

    public static function error($line)
    {
        self::line($line, self::ERROR);
    }

    public static function alert($line)
    {
        self::line($line, self::ALERT);
    }

    public static function test($line)
    {
        self::line($line, self::TEST);
    }
}

// End of Log.php
