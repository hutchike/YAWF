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
 * This helper provides logging methods available to all classes.
 * All methods are static to make logging as simple as possible.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Log extends YAWF
{
    /**
     * The logging levels 0 thru 5 all have class constant names
     */
    const DEBUG = 0;
    const INFO = 1;
    const WARN = 2;
    const ERROR = 3;
    const ALERT = 4;
    const TEST = 5;
    const QUIET = 6;

    /**
     * The logging level names are all symbols from the Symbol library class
     */
    private static $level_names = array(Symbol::DEBUG, Symbol::INFO, Symbol::WARN, Symbol::ERROR, Symbol::ALERT, Symbol::TEST, Symbol::QUIET);

    /**
     * Logging happens at or above a logging level e.g. Log::DEBUG or Log::INFO
     */
    private static $level;

    /**
     * Logging has a type e.g. "test" if we're performing test logging
     */
    private static $type;

    /**
     * Get/set the logging level to a number, e.g. Log::DEBUG or Log::INFO
     *
     * @param Integer $level the logging level (optional)
     * @return Integer the current logging level
     */
    public static function level($level = NULL)
    {
        if (!is_null($level)) self::$level = $level;
        return self::$level;
    }

    /**
     * Get/set the logging type (e.g. "test" when we're logging test results)
     *
     * @param String $type the logging type (optional)
     * @return String the current logging type (e.g. "test")
     */
    public static function type($type = NULL)
    {
        if (!is_null($type)) self::$type = $type;
        return self::$type;
    }

    /**
     * Get the name of a logging level number
     *
     * @param Integer $level the logging level (e.g. Log::INFO)
     * @return String the name of the logging level (e.g. "INFO")
     */
    public static function level_name($level)
    {
        return strtoupper(self::$level_names[$level]);
    }

    /**
     * Write a line to the log file
     *
     * @param String $line the line to write
     * @param Integer $level the logging level number (defaults to Log::INFO)
     */
    public static function line($line, $level = self::INFO)
    {
        if (!LOGGING_ENABLED) return;
        if (is_array($line)) $line = join(' ', $line);
        if (!self::$level) self::$level = array_search(strtolower(DEFAULT_LOG_LEVEL), self::$level_names);
        if ($level < self::$level) return;
        $path = file_exists('app/logs') ? 'app/logs' : 'yawf/logs';
        $date = date('Ymd');
        $type = self::$type ? '.' . self::$type : '';
        $fh = fopen("$path/$date$type.log", 'a'); // append
        $time = date('H:i:s');
        $level = self::level_name($level);
        fwrite($fh, "$time $level $line\n");
        fclose($fh);
    }

    /**
     * Write a debug line to the log file
     *
     * @param String $line the line to write
     */
    public static function debug($line)
    {
        self::line($line, self::DEBUG);
    }

    /**
     * Write an info line to the log file
     *
     * @param String $line the line to write
     */
    public static function info($line)
    {
        self::line($line, self::INFO);
    }

    /**
     * Write a warn line to the log file
     *
     * @param String $line the line to write
     */
    public static function warn($line)
    {
        self::line($line, self::WARN);
    }

    /**
     * Write an error line to the log file
     *
     * @param String $line the line to write
     */
    public static function error($line)
    {
        self::line($line, self::ERROR);
    }

    /**
     * Write an alert line to the log file
     *
     * @param String $line the line to write
     */
    public static function alert($line)
    {
        self::line($line, self::ALERT);
    }

    /**
     * Write a test line to the log file
     *
     * @param String $line the line to write
     */
    public static function test($line)
    {
        self::line($line, self::TEST);
    }
}

// End of Log.php
