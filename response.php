<?php
require_once(dirname(__FILE__) . "/../../../wp-config.php");
require_once(ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/functions.php");
header('Content-Type: text/html; charset='.get_option('blog_charset').'');

echo ealist_postentry($_POST);
?>