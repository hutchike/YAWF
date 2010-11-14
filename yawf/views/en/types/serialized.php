<?
header('Content-Type: text/serialized');

// Get the data to encode as serialized

if (!isset($data)) $data = array();

// Now for our elegant YAML encoding
?>
<?= serialize($data) ?>
