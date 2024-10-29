<?php
require_once("../../../wp-config.php");
//require_once(ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/functions.php");

$ealist_server_url = ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/";
	require_once ($ealist_server_url . "configuration.php");
	require_once ($ealist_server_url . "functions.php");

header('Content-Type: text/html; charset='.get_option('blog_charset').'');


$next_day = ealist_get_option('ealist_day');
$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
$next_special_date = ealist_get_option('ealist_date');

if ($next_special_date != "") {
	$next_special_date = mktime(0, 0, 0, substr($next_special_date, 3, 2), substr($next_special_date, 0, 2), substr($next_special_date, 6, 4));
}
if ($next_day != "") {
	$next_date_time = strtotime("next $next_day") - (24*3600) + ($i * 7*24*3600);
		// Estimating next meeting into time-stamp. In strtotime() the day after tomorrow 
		// has to be included, since the next day is also today. This is also the time,
		//  which will be saved to the database.
} else {
	$next_date_time = $today;
}

if (($next_special_date < $next_date_time OR $next_date_time == $today) AND $next_special_date > 0 AND $next_special_date >= $today) {
	$next_date_time = $next_special_date;
}											
$date_format = ealist_get_option('ealist_date_format');

if ($date_format == "") {
	$date_format = "d.m.";
}
$next_date = date($date_format, $next_date_time); 				// Calculating the time out of the time stamp

$list = ealist_getlist( $next_date_time );

if (!empty($list)) {
	foreach ($list as $item=>$vote) {
		if ($vote[0] == "1") {
			echo ucwords(strtolower($item));
			echo "\n";
		}	
	}
}
?>