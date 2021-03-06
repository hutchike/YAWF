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

error_reporting(E_ALL | E_STRICT);

/**
 * The Command class provides a base class to write commands to
 * run from the command line. Commands have a path, a name, some
 * options (optionally) and some arguments (optionally). Options
 * take the form "-opt1=a -opt2=b" on the command line, whereas
 * arguments are simply listed at the end like "arg1 arg2 arg3".
 *
 * Command logging is written to a ".command" log, or to the
 * ".command.test" log when we're running command tests, for
 * example when the "-test" option is passed to the command.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class Command // cannot extend YAWF coz "utils" not yet loaded
{
    // Command properties are all public for convenience

    public $app;
    public $path, $name;
    public $opts, $args;

    /**
     * Create a new Command object
     *
     * @param String $dir an optional starting directory to change from
     */
    public function __construct($dir = NULL)
    {
        // Parse options and arguments, and run in the project's root directory

        $this->parse_command_line();
        $this->change_directory($dir);

        // Include from "app" before "yawf"

        if (!is_dir('yawf')) $this->quit('No "yawf" directory in ' . getcwd());
        ini_set('include_path', 'app:yawf:.');
        require_once('lib/utils.php');
        YAWF::start(); // for benchmarks

        // Convert the opts to an Object

        $this->opts = new Object($this->opts);

        // Create an App object, optionally for testing

        $this->app = $this->opts->test ? new App_test($this->path)
                                       : new App($this->path);
        $this->app->new_controller('Controller'); // for email

        // Log output to a log file named "YYYYMMDD.command[.test].log"

        $type = $this->opts->test ? 'command.test' : 'command';
        Log::type($type);

        // Allow commands to run quietly by not logging

        if ($this->opts->quiet) Log::level(Log::QUIET);

        return $this;
    }

    /**
     * Log the time it took to run the command, given the options and arguments
     */
    public function __destruct()
    {
        $name = $this->name;
        $opts = json_encode((array)$this->opts);
        $args = json_encode((array)$this->args);
        YAWF::finish("\"$name\" command completed with opts $opts and args $args");
    }

    /**
     * Catch all undefined methods calls by calling YAWF::unknown
     * to throw an exception.
     *
     * @param String $name the name of the unknown method call
     * @param Array $args the arguments passed to the unknown method
     */
    public function __call($name, $args)
    {
        YAWF::unknown($name, $args);
    }

    /**
     * Parse options and arguments on the command line
     */
    protected function parse_command_line()
    {
        if (!array_key_exists('argv', $_SERVER))
            $this->quit("Cannot parse the command line");

        $args_list = $_SERVER['argv'];
        $this->path = array_shift($args_list);
        $this->name = basename($this->path);
        $opts = array();
        $args = array();
        foreach ($args_list as $arg)
        {
            if (preg_match('/^\-+(\w+)$/', $arg, $matches))
                $opts[$matches[1]] = TRUE;
            elseif (preg_match('/^\-+(\w+)=(.+)$/', $arg, $matches))
                $opts[$matches[1]] = $matches[2];
            else
                array_push($args, $arg);
        }
        $this->opts = $opts;
        $this->args = $args;
    }

    // Return the default start directory

    protected function start_directory()
    {
        return getcwd(); // Regular commands start from the current directory
    }

    /**
     * Change to a project's root directory containing "app" and "yawf" dirs.
     * This method calls the "start_directory" method which may be overriden.
     *
     * @param String $dir an optional directory to begin searching from
     */
    protected function change_directory($dir = NULL)
    {
        if (is_null($dir)) $dir = $this->start_directory();

        $config = 'app/configs/app.yaml'; // search for the app's config file
        $last_dir = NULL;
        do {
            if ($last_dir == $dir) break;
            if (file_exists("$dir/$config"))
            {
                chdir($dir);
                $last_dir = NULL;
                break;
            }
            $last_dir = $dir;
        } while ($dir = dirname($dir));
        if ($last_dir) chdir(dirname($this->path) . '/../..');
    }

    /**
     * Quit the command by logging an optional message then calling "exit"
     *
     * @param String $message an optional message to write to the log
     */
    protected function quit($message = NULL)
    {
        if (!is_null($message)) print(trim($message) . "\n");
        exit;
    }
}

// End of Command.php
