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
?>
<html>
<head>
  <link rel="stylesheet" href="/styles/test/console.css" type="text/css" />
  <link rel="stylesheet" href="/styles/test/runner.css" type="text/css" />
</head>
<body onload="top.YAWF.console = document.getElementById('console')">
  <p><?= HTML::link('test/console.part', 'Clear') ?></p>
  <div id="console"></div>
</body>
</html>
