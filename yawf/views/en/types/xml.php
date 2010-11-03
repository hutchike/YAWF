<?
header('Content-type: text/xml');

load_helper('XML');

// Get the data to encode into YAML

if (!isset($data)) $data = array();
?>
<?= XML::serialize($data, array('rootName' => 'api')) ?>
