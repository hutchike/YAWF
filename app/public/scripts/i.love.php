<pre>
<?php
foreach($x=str_split(base64_decode('IMYDhCOELbBIhEiMQwkQiRGAYcPxwhgYIEIgQMwECEQIBgCBCB='))as$y)$x.=sprintf("%08b",ord($y));
echo preg_replace(array('/0/','/1/'),array(' ','#'),chunk_split(substr($x,5).'1',43,"\n"));
?>
</pre>
