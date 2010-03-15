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

// Use the path info to view code

if (isset($_SERVER['PATH_INFO']))
{
    $file = '.' . $_SERVER['PATH_INFO'];
    if (file_exists($file))
    {
        highlight_file($file);
    }
    else // file not found
    {
        echo "File <code><b>$file</b></code> not found";
    }
}
else // no file provided
{
    echo "Usage: <code>/yawf/code.php<b>/lib/App.php</b></code> or other YAWF file";
}
?>
