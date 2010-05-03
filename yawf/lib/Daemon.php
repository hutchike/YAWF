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

class Daemon
{
    private $app;
    private $args;

    public function setup()
    {
        $file = basename($_SERVER['SCRIPT_FILENAME']);
        $path = $_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME'];
        chdir(dirname($path) . '/../..');
        error_reporting(E_ALL | E_STRICT);
        ini_set('include_path', 'app:yawf:.');
        require_once('lib/utils.php');
        $this->parse_command_line_args();
        $this->app = $this->arg('test') ? new App_test($file) : new App($file);
    }

    public function arg($name)
    {
        return array_key($this->args, $name);
    }

    protected function parse_command_line_args()
    {
        $this->args = array();
        if (!array_key($_SERVER, 'argv')) return;

        $args = $_SERVER['argv'];
        array_shift($args); // remove the script name
        foreach ($args as $arg)
        {
            $arg = ltrim($arg, '-'); // remove dashes
            if (preg_match('/^(\w+)=(.+)$/', $arg, $matches))
                $this->args[$matches[1]] = $matches[2];
            else
                $this->args[$arg] = TRUE;
        }
    }

    protected function quit($message)
    {
        print "\n$message\n\n";
        exit;
    }
}

class YAWF
{
}

// End of Daemon.php
