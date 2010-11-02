<?
header('Content-type: text/javascript');

// Get the callback and data to encode

if (is_null($callback)) $callback = '';
if (is_null($data)) $data = array();

// This is cross-domain loveliness using JSONP callbacks
?>
<?= $callback ?>(<?= json_encode($data) ?>)
