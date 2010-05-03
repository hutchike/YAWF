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

class Command
{
    private $app;
    private $name;
    public  $args;

    public function __construct()
    {
        $file = basename($_SERVER['SCRIPT_FILENAME']);
        $path = $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME'];
        chdir(dirname($path) . '/../..');
        error_reporting(E_ALL | E_STRICT);
        ini_set('include_path', 'app:yawf:.');
        require_once('lib/utils.php');
        $this->parse_command_line();
        $this->app = $this->args->test ? new App_test($file) : new App($file);
        Log::type('command');
        return $this;
    }

    public function __destruct()
    {
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
        if (!array_key($_SERVER, 'argv')) return;

        $args_list = $_SERVER['argv'];
        $this->name = basename(array_shift($args_list));
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
        $this->args = new Object($args);
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
