<?
header('Content-type: text/javascript');

// Get the callback and data to encode

if (!isset($callback)) $callback = '';
if (!isset($data)) $data = array();

// This is cross-domain loveliness using JSONP callbacks
?>
<?= $callback ?>(<?= json_encode($data) ?>)
