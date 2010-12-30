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

require_once('Command.php');

/**
 * The YAWF App_command class provides a standard interface to
 * web app admin commands. Each web app has an admin command with
 * a name that corresponds to the web app. It may be run by using
 * options such as "webapp -start", "webapp -stop", "webapp -test"
 * and suchlike. Run "webapp -usage" for a list of all the options.
 *
 * @author Kevin Hutchinson <kevin@guanoo.com>
 */
class App_command extends Command
{
    /**
     * Run an app command such as "start", "stop", "restart", "status"
     * or "test" by checking the options passed on the command line.
     */
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

    /**
     * Return a list of all the daemons run by this web app
     */
    protected function daemons()
    {
        return array(); // this should be overriden by your web app
    }

    /**
     * Start the web app daemon(s)
     */
    protected function start()
    {
        foreach ($this->daemons() as $daemon)
        {
            // Check to see if the daemon is already running

            $pipe = popen("yawf_daemon status $daemon", 'r');
            $status = fgets($pipe);
            pclose($pipe);

            // Start the daemon if it's not already running

            if (strpos($status, 'is not running') > 0)
            {
                system("yawf_daemon start $daemon");
            }
        }
    }

    /**
     * Stop the web app daemon(s)
     */
    protected function stop()
    {
        $daemons = join(' ', $this->daemons());
        system("yawf_daemon stop $daemons");
    }

    /**
     * Restart the web app daemon(s)
     */
    protected function restart()
    {
        $this->stop();
        $this->start();
    }

    /**
     * Check the status of the web app daemon(s)
     */
    protected function status()
    {
        $daemons = join(' ', $this->daemons());
        system("yawf_daemon status $daemons");
    }

    /**
     * Display a usage message
     */
    protected function usage()
    {
        $this->quit("usage: $this->name [-start] [-stop] [-restart] [-status] [-test]");
    }

    /**
     * Return the default start directory (the app commands folder)
     */
    protected function start_directory()
    {
        return dirname($this->path); // App commands start from their own path
    }

    /**
     * Run "yash" test files in the "app/tests" folder
     *
     * @param String $test_dir the test directory (default is "app/tests")
     * @param Boolean $check_args whether to run tests listed in command args
     */
    protected function test($test_dir = 'app/tests', $check_args = TRUE)
    {
        // Which directory holds the YASH test files?

        if (!is_dir($test_dir)) $this->quit("Test directory \"$test_dir\" does not exist");

        // Create an array of YASH test files to run

        $tests = array();
        if ($check_args && $this->args) // look at the args list
        {
            foreach ($this->args as $test)
            {
                if (file_exists("$test_dir/$test")) $tests[] = $test;
                elseif (file_exists("$test_dir/$test.yash")) $tests[] = "$test.yash";
                else $this->quit("Test file \"$test_dir/$test\" does not exist");
            }
        }
        else // find all test folders and files in the directory
        {
            // Get a sorted list of files and folders

            $dir = opendir($test_dir);
            while ($test = readdir($dir))
            {
                if (substr($test, 0, 1) == '.') continue;
                $tests[] = $test;
            }
            closedir($dir);
            usort($tests, 'App_command::sort_for_setup_and_teardown');
        }

        // Run the test files and folders in order

        foreach ($tests as $test)
        {
            if (is_dir("$test_dir/$test")) $this->test("$test_dir/$test", FALSE);
            if (!preg_match('/\.yash$/', $test)) continue; // must be yash
            print "Running test file \"$test_dir/$test\":\n";
            system("yash -quiet -test < $test_dir/$test");
            print "\n";
        }

        if (!$this->args && !$tests) print "No test files found in \"$test_dir\"\n\n";
    }

    /**
     * Compare one file/folder name with another, putting "setup" first and
     * "teardown" last. This is a static user-defined comparison function.
     *
     * @param String $left the left test file or folder name to compare
     * @param String $right the right test file or folder name to compare
     * @return Integer -1 if $a is less than $b, 1 if $a is greater than $b
     */
    private static function sort_for_setup_and_teardown($left, $right)
    {
        if (substr($left, 0, 5) == 'setup') return -1;
        if (substr($right, 0, 5) == 'setup') return 1;
        if (substr($left, 0, 8) == 'teardown') return 1;
        if (substr($right, 0, 8) == 'teardown') return -1;
        if ($left == $right) return 0;
        return $left < $right ? -1 : 1;
    }
}

// End of App_command.php
