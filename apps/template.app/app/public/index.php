<?php

// Run YAWF!

chdir('../..');
ini_set('include_path', 'app:yawf:.');
require_once('lib/utils.php');
YAWF::respond_to_web_request();

// End of index.php
