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

require_once('Command.php');

class App_command extends Command
{
    public function run()
    {
        if ($this->opts->start)
            $this->start();
        elseif ($this->opts->stop)
            $this->stop();
        elseif ($this->opts->restart)
            $this->restart();
        elseif ($this->opts->status)
            $this->status();
        elseif ($this->opts->test)
            $this->test();
        else
            $this->usage();
    }

    protected function start()
    {
        // Override this method
    }

    protected function stop()
    {
        // Override this method
    }

    protected function restart()
    {
        $this->stop();
        $this->start();
    }

    protected function status()
    {
        // Override this method
    }

    protected function usage()
    {
        $this->quit("usage: $this->name [-start] [-stop] [-restart] [-status] [-test]");
    }

    // Return the default start directory

    protected function start_directory()
    {
        return dirname($this->path); // App commands start from their own path
    }

    // Run "yash" test files in the "app/tests" folder

    protected function test($test_dir = 'app/tests', $check_args = TRUE)
    {
        // Which directory holds the YASH test files?

        if (!is_dir($test_dir)) $this->quit("Test directory \"$test_dir\" does not exist");

        // Create an array of YASH test files to run

        $tests = array();
        if (file_exists("$test_dir/setup.yash")) $tests[] = 'setup.yash';
        if ($check_args && $this->args) // look at the args list
        {
            foreach ($this->args as $test)
            {
                if (is_dir("$test_dir/$test")) $this->test("$test_dir/$test", FALSE);
                elseif (file_exists("$test_dir/$test")) $tests[] = $test;
                elseif (file_exists("$test_dir/$test.yash")) $tests[] = "$test.yash";
                else $this->quit("Test file \"$test_dir/$test\" does not exist");
            }
        }
        else // find all test folders and files in the directory
        {
            $dir = opendir($test_dir);
            while ($test = readdir($dir))
            {
                if (substr($test, 0, 1) == '.') continue;
                if (is_dir("$test_dir/$test")) $this->test("$test_dir/$test");
                if (preg_match('/^(setup|teardown)\.yash$/', $test)) continue;
                if (!preg_match('/\.yash$/', $test)) continue; // must be yash
                $tests[] = $test;
            }
            closedir($dir);
        }
        if (file_exists("$test_dir/teardown.yash")) $tests[] = 'teardown.yash';

        // Run all the YASH test files in order

        foreach ($tests as $test)
        {
            print "Running test file \"$test_dir/$test\":\n";
            system("yash -quiet -test < $test_dir/$test");
            print "\n";
        }
        if (!$this->args && !$tests) print "No test files found in \"$test_dir\"\n\n";
    }
}

// End of App_command.php
