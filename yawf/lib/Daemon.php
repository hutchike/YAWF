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

class Daemon extends Command
{
    private $pid, $pid_file;

    public function __construct($dir = NULL)
    {
        parent::__construct($dir);
        if ($pid = $this->is_running()) $this->quit("Daemon \"$this->name\" is already running with PID $pid");
        $this->write_pid_to_file();
    }

    public function __destruct()
    {
        $this->delete_pid_file();
        parent::__destruct();
    }

    public function pid()
    {
        if ($this->pid) return $this->pid;
        return ($this->pid = getmypid());
    }

    public function pid_file()
    {
        if (!$this->pid_file)
        {
            $pid_dir = 'app/tmp/pids';
            if (!is_dir($pid_dir)) $this->quit("No PID directory here: $pid_dir");
            $this->pid_file = "$pid_dir/$this->name.pid";
        }
        return $this->pid_file;
    }

    protected function is_running()
    {
        if ($pid = $this->read_pid_from_file())
        {
            $fh = popen("ps -p $pid", 'r');
            $ps = fread($fh, 2048);
            pclose($fh);
            return strpos($ps, $pid) > 0 ? $pid : FALSE;
        }
        else
        {
            return FALSE;
        }
    }

    protected function read_pid_from_file()
    {

        return file_exists($this->pid_file()) ?
               trim(file_get_contents($this->pid_file())) :
               null;
    }

    protected function write_pid_to_file()
    {
        file_put_contents($this->pid_file(), $this->pid());
    }

    protected function delete_pid_file()
    {
        if ($pid = $this->read_pid_from_file());
        {
            if ($pid == $this->pid()) unlink($this->pid_file());
        }
    }
}

// End of Daemon.php