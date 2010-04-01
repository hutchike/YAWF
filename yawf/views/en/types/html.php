<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
    <title><?= $title ?></title>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <meta name="description" content="YAWF - Yet Another Web Framework" />
    <meta name="keywords" content="YAWF, PHP, SQLite, MySQL, MVC, Web, Framework" />
    <meta name="robots" content="index, follow" />
    <?= HTML::link_tag('/images/y.jpg', array('rel' => 'shortcut icon', 'type' => 'image/jpg')) ?>
    <?= HTML::link_tag('/styles/base.css' ) ?>
    <script type="text/javascript" src="/scripts/jquery.js"></script>
</head>
<body>
<?= $content ?>
</body>
</html>
