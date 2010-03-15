#!/usr/bin/env php
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

class YAWF_admin
{
    protected $home;    // home directory from $YAWF_ROOT environment variable
    protected $prog;    // the name of this program (e.g. "./admin/yawf")
    protected $cmd;     // the command to run (e.g. "app")
    protected $args;    // arguments to the command (e.g. "my_new_app")

    function __construct()
    {
        $this->home = $_ENV['YAWF_ROOT'];
        if (!$this->home) $this->error("Please set the YAWF_ROOT environment variable\nto the folder name where YAWF may be found on\nyour computer, e.g. ~/Sites/YAWF");

        global $argv;
        $this->prog = array_shift($argv);
        $this->cmd = array_shift($argv);
        $this->args = $argv;
    }

    // Display a usage message, defaulting to the standard help message

    function usage($cmd = '', $message = '')
    {
        if ($cmd && $message) die("\nusage: $cmd\n\n$message\n\n");
        $usage = <<<End_of_usage
usage: yawf CMD [arg1 arg2 ...]

where CMD is one of:
* app   : Create a new YAWF application
* help  : Display this help message
End_of_usage;
        die("\n$usage\n\n");
    }

    // Display an error message

    function error($message)
    {
        die("\n$message\n\n");
    }

    // Create a new YAWF app with a name

    function app($name)
    {
        if (!$name) $this->usage('yawf app myapp.com', 'You need to choose a name for your new app, e.g. "myapp.com"');
        chdir($this->home . '/apps');
        if (file_exists($name)) $this->error("Sorry but the app \"$name\" already exists in this folder:\n$this->home/apps/$name");
    }

    // Run the chosen command

    function run()
    {
        switch (strtolower($this->cmd))
        {
            case 'app':
                $this->app($this->args[0]);
                break;

            default:
                $this->usage();
        }
    }
}

$admin = new YAWF_admin();
$admin->run();

// End of "bin/yawf"
