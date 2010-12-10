<?
header('Content-Type: text/xml');

load_tool('XML');

// Get the data to encode into XML

if (!isset($data)) $data = array();

// Now for our elegant XML encoding
?>
<?= XML::serialize($data) ?>
