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
  <link rel="stylesheet" href="/styles/test/browser.css" type="text/css" />
  <script type="text/javascript">
    function setup() {
      top.YAWF.browser = document.forms.browser;
      top.YAWF.prefix = '<?= VIEW_URL_PREFIX ?>';
      top.YAWF.open(document.forms.browser.url.value);
    }

    function change_script(form) {
      var url = form.url.value;
      var script = form.script.value;
      top.location.href = 'http://' + top.YAWF.domain + '/test/runner.part?url=' + escape(url) + '&script=' + escape(script);
    }
  </script>
</head>
<body onload="setup()">
  <form id="browser" onsubmit="top.YAWF.open(this.url.value); return false">
    <input type="button" name="home" value="Home" onclick="top.YAWF.open(this.form.url.value = '/')" />
    <label for="url">Address</label>
    <input type="text" id="url" name="url" value="<?= $url ?>" size="40" maxlength="100" />
    <input type="submit" name="go" value=" Go " />
    using test script
    <?= HTML::select('script', $scripts, $script, array('onchange' => 'change_script(this.form)')) ?>
    <a href="#" onclick="top.YAWF.open('test/script.part?script=' + document.forms.browser.script.value)">view</a>
    <span id="flash"><?= $flash ?></span>
  </form>
</body>
</html>
