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

// Use the path info to view code

if (isset($_SERVER['QUERY_STRING']))
{
    $query = $_SERVER['QUERY_STRING'];
    $absolute_file = "../../../$query";
    $yawf_file = "../../../yawf/$query";
    $app_file = "../../../app/$query";
    if (file_exists($absolute_file)) highlight_file($absolute_file);
    elseif (file_exists($yawf_file)) highlight_file($yawf_file);
    elseif (file_exists($app_file)) highlight_file($app_file);
    else echo "Filess <code><b>$absolute_file, $yawf_file, $app_file</b></code> not found";
}
else // no file provided
{
    echo "Usage: <code>code.php<b>/lib/App.php</b></code> or other YAWF file";
}
?>
