<?
header('Content-type: text/plain');

// Get the data to encode into YAML

if (!isset($data)) $data = array();

// Now for our elegant YAML encoding
?>
<?= YAML::dump($data) ?>
