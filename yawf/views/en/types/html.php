<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
    <title><?= $title ? "YAWF - $title" : '' ?></title>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <meta name="description" content="YAWF is Yet Another Web Framework (PHP) to do things simpler" />
    <meta name="keywords" content="YAWF, PHP, SQLite, MySQL, MVC, Web, Framework, REST, simpler" />
    <meta name="robots" content="index, follow" />
    <?= HTML::link_tag('images/y.png', array('rel' => 'shortcut icon', 'type' => 'image/png')) ?>
    <?= HTML::link_tag('styles/base.css') ?>
    <?= HTML::script('scripts/jquery.js') ?>
</head>
<body>
<?= $content ?>
</body>
</html>
