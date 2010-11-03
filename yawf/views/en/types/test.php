<?
// What's the location of this view we're testing?

$folder = $app->get_folder();
$file = $app->get_file();
$url = $folder . '/' . $file . '.test';

// First, render the requested view as normal HTML
// with a "setupView()" method call when it loads.

$html = $app->render_type('html', $render);
$code = '<script type="text/javascript">
if (top.YAWF)
  top.YAWF.attachEvent(this, \'load\', function() {
    top.YAWF.setupView(\'' . $url . '\'); });
</script>';
$html = preg_replace('/<\/body>/i', "$code</body>", $html);
echo $html;

// Then, show sent email contents
// so we can include in the tests

echo isset($sent) ? $sent : $flash->sent;
?>
