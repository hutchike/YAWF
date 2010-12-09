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
?>
<?= '<?xml version="1.0" encoding="utf-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Test Runner</title>
    <?= HTML::script_tag('scripts/test/test.js') ?>
    <?= HTML::script_tag('scripts/test/' . $script) ?>
  </head>
  <frameset cols="*" rows="36,*,0">
    <?= HTML::frame('browser', 'test/browser.part?url=' . urlencode($url) . '&amp;script=' . urlencode($script)) ?>
    <frameset cols="250,*" rows="*">
      <?= HTML::frame('console', 'test/console.part') ?>
      <?= HTML::frame('view') ?>
    </frameset>
    <?= HTML::frame('logger', 'test/logger.part') ?>
  </frameset>
</html>
