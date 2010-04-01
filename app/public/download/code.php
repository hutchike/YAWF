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

if (isset($_SERVER['QUERY_STRING']))
{
    $yawf_file = '../../../yawf/' . $_SERVER['QUERY_STRING'];
    $app_file = '../../../app/' . $_SERVER['QUERY_STRING'];
    if (file_exists($yawf_file)) highlight_file($yawf_file);
    else if (file_exists($app_file)) highlight_file($app_file);
    else echo "Files <code><b>$yawf_file, $app_file</b></code> not found";
}
else // no file provided
{
    echo "Usage: <code>code.php<b>/lib/App.php</b></code> or other YAWF file";
}
?>
