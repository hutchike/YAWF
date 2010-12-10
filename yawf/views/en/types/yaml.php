<?
header('Content-Type: text/plain');

load_tool('YAML');

// Get the data to encode into YAML

if (!isset($data)) $data = array();

// Now for our elegant YAML encoding
?>
<?= YAML::dump($data) ?>
