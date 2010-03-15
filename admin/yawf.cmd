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
    protected $root;    // root directory from $YAWF_ROOT environment variable
    protected $prog;    // the name of this program (e.g. "./admin/yawf")
    protected $cmd;     // the command to run (e.g. "app")
    protected $args;    // arguments to the command (e.g. "my_new_app")

    function __construct()
    {
        $this->root = $_SERVER['YAWF_ROOT'];
        if (!$this->root) $this->error("Please set the YAWF_ROOT environment variable\nto the folder name where YAWF may be found on\nyour computer, e.g. ~/Sites/YAWF");

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
usage: yawf.cmd ACTION [arg1 arg2 ...]

where ACTION is one of:
* create : Create a new YAWF application
* help   : Display this help message
End_of_usage;
        die("\n$usage\n\n");
    }

    // Display an error message

    function error($message)
    {
        die("\n$message\n\n");
    }

    // Create a new YAWF app with a name

    function create($name)
    {
        if (!$name) $this->usage('yawf.cmd create foo.org', 'You need to choose a name for your new app, e.g. "foo.org"');

        $template = "$this->root/apps/template.app";
        $new_app = "$this->root/apps/$name";
        if (file_exists("$new_app")) $this->error("Sorry but the app \"$name\" already exists in this folder:\n$new_app");

        mkdir("$new_app");
        copy("$template/index.php", "$new_app/index.php");
        $this->recurse_copy("$template/app", "$new_app/app");
        symlink("$this->root/yawf", "$new_app/yawf");
    }

    // Run the chosen command

    function run()
    {
        switch (strtolower($this->cmd))
        {
            case 'create':
                $this->create($this->args[0]);
                break;

            default:
                $this->usage();
        }
    }

    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        mkdir($dst);
        while( FALSE !== ($file = readdir($dir)) )
        {
            if (($file != '.') && ($file != '..'))
            {
                is_dir("$src/$file") ?
                    $this->recurse_copy("$src/$file", "$dst/$file") :
                    copy("$src/$file", "$dst/$file");
            }
        }
        closedir($dir);
    } 
}

$admin = new YAWF_admin();
$admin->run();

// End of "bin/yawf"
