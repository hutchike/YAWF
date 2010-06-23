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
    public function __construct($dir = NULL)
    {
        // TODO: Check for a pid file

        parent::__construct($dir);
    }

    public function __destruct()
    {
        // TODO: Remove the pid file

        parent::__destruct();
    }
}

// End of Daemon.php
