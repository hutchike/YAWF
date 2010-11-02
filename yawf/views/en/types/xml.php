<?
header('Content-type: text/xml');

load_helper('XML');

// Get the data to encode into YAML

if (is_null($data)) $data = array();
?>
<?= XML::serialize($data, array('rootName' => 'api')) ?>
