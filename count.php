<?php
require_once("../../../wp-config.php");
require_once(ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/functions.php");
header('Content-Type: text/html; charset='.get_option('blog_charset').'');
$ealist_date = $_POST["date"];

echo ealist_count($ealist_date);
?>