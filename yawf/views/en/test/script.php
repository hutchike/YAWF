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

// Display a test script with pretty color highlighting

$contents = highlight_string('<?' . file_get_contents($file_path), TRUE);
$php_errormsg = NULL; // to remove a strange error saying it found \'\\\'
$contents = str_replace('<span style="color: #0000BB">&lt;?</span>', '', $contents);
?>
<html>
    <body>
        <?= $contents ?>
    </body>
</html>
