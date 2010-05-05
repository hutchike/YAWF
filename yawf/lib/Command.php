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

error_reporting(E_ALL | E_STRICT);

class Command
{
    private $app;
    private $name;
    private $path;
    public  $args;

    public function __construct()
    {
        $this->parse_command_line();
        chdir(dirname($this->path) . '/../..');

        // Include from "app" before "yawf"

        ini_set('include_path', 'app:yawf:.');
        require_once('lib/utils.php');

        // Convert the args to an Object

        $this->args = new Object($this->args);

        // Create an App object, optionally for testing

        $this->app = $this->args->test ? new App_test($this->name)
                                       : new App($this->name);

        // Log output to a log file named "YYYYMMDD.command.log"

        Log::type('command');

        return $this;
    }

    public function __destruct()
    {
        // Log the time it took to run the command given the arguments

        $name = $this->name;
        $args = json_encode((array)$this->args);
        YAWF::benchmark("\"$name\" command completed with args $args");
    }

    public function name()
    {
        return $this->name;
    }

    protected function parse_command_line()
    {
        if (!array_key_exists('argv', $_SERVER))
            $this->quit("Cannot parse the command line");

        $args_list = $_SERVER['argv'];
        $this->path = array_shift($args_list);
        $this->name = basename($this->path);
        $args = array();
        foreach ($args_list as $arg)
        {
            $arg = strtolower($arg);
            $arg = ltrim($arg, '-'); // remove dashes
            if (preg_match('/^(\w+)=(.+)$/', $arg, $matches))
                $args[$matches[1]] = $matches[2];
            else
                $args[$arg] = TRUE;
        }
    }

    protected function quit($message)
    {
        print "\n$message\n\n";
        exit;
    }
}

$__YAWF_start_time__ = microtime(TRUE);

class YAWF
{
    // Write benchmark info in the log file

    public static function benchmark($info)
    {
        if (!BENCHMARKING_ON) return;
        global $__YAWF_start_time__; // Compute benchmark times in milliseconds
        $msecs = (int)( 1000 * ( microtime(TRUE) - $__YAWF_start_time__ ) );
        Log::alert($info . " after $msecs ms"); // "Log" helper loaded by run()
    }
}

// End of Command.php
