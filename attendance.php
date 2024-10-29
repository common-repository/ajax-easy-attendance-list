<?php
header( "Content-type: image/png" );

require_once("../../../wp-config.php");
require_once(ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/functions.php");

ealist_getstats();


?>