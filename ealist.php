<?php
/*  Copyright 2010  dasweb.net  (email : info@dasweb.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Plugin Name: AJAX Easy Attendance List
Plugin URI: http://wordpress.dasweb.net/
Description: For the local hockey club a small plugin was required, which provides an overview of the attendance of players. The reason was on the irregular attendance. To avoid an empty training with just a few players this plugin was established. Players can easily sign in by just entering their name. Later the attendance can be changed, but never deleted. This plugin provides no checking on the entries on purpose. Everyone can load the page and mark people as not attending.
Version: 0.5.25
Author: dasweb.net
Author URI: http://dasweb.net
License: GPL2
*/






// Wordpress specific
	if (!function_exists('add_action')) {
		if (file_exists(ABSPATH.'/wp-load.php')) {
			require_once(ABSPATH.'/wp-load.php');
		} else {
			require_once(ABSPATH.'/wp-config.php');
		}
	}
	add_shortcode('easyattendancelist', 'ealist_void');
	add_shortcode('easyattendancelist_stat', 'ealist_stat');
	add_action("wp_head", "ealist_addhead");
	if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	}
	
// Start Configuration
	$ealist_server_url = ABSPATH . "wp-content/plugins/ajax-easy-attendance-list/";
	require_once ($ealist_server_url . "configuration.php");
	require_once ($ealist_server_url . "functions.php");
	
// If table not exisits
	$create_table = "CREATE TABLE IF NOT EXISTS `".$table_prefix."ealist` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`date` int(10) unsigned NOT NULL,
		`name` VARCHAR(20) NOT NULL,
		`vote` int(10) unsigned NOT NULL,
		`facebook_id` VARCHAR(100) NULL,
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	if ($table_prefix != "") {
		if (!dbDelta($create_table)) {
			echo "Easy Attedance List error: db query failed";
		}
	}


// Void function
function ealist_void($atts) {
	global $current_user, $ealist_plugin_url;
	echo '<script src="' . $ealist_plugin_url . 'js/script.js" type="text/javascript"></script>';
	
	// Get configuration. Check if attributes are set over input code
	$td_width = $atts['width'];
	$att_date = $atts['date'];
	if ($td_width > 0) {
	} else {
		$td_width = ealist_get_td_width();
	}

	
	// Get date
	$next_day = ealist_get_option('ealist_day');
	$today = mktime(0,0,0, date('m'), date('d'), date('Y'));
	$next_special_date = ealist_get_option('ealist_date');
	if ($att_date != "") {
		if ($att_date == "monday" OR $att_date == "tuesday" OR $att_date == "wednesday" OR $att_date == "thursday" OR $att_date == "friday" OR $att_date == "saturday" OR $att_date == "sunday") {
			$next_special_date = "";
			switch ($att_date) {
				case "monday":
					$next_day = "tuesday";
					break;
				case "tuesday":
					$next_day = "wednesday";
					break;
				case "wednesday":
					$next_day = "thursday";
					break;
				case "thursday":
					$next_day = "friday";
					break;
				case "friday":
					$next_day = "saturday";
					break;
				case "saturday":
					$next_day = "sunday";
					break;
				case "sunday":
					$next_day = "monday";
					break;
			}
		} else {
			$next_special_date = $att_date;
		}
	}

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
	

	// Get font color;
	if (ealist_get_option("ealist_font_color")) {
		$next_color = ealist_get_option("ealist_font_color");
	} else {
		$next_color = "#AAA";
	}
		
	
	// Facebook Code	
	$ealist_facebook_app = ealist_get_option('ealist_facebook_app');
	$return .= "<div id='fb-root'></div>";
	$return .= "<script type='text/javascript'>var ealist_facebook_app = '$ealist_facebook_app'; var ealist_current_user = '" . $current_user->ID . "';";
	$forbid_edit = ealist_get_option("ealist_edit");
	if ($current_user->ID > 0) {
		$return .= "var ealist_forbid_edit = 'no';";
	} else if ($forbid_edit == "yes") {
		$return .= "var ealist_forbid_edit = 'yes';";
	} else {
		$return .= "var ealist_forbid_edit ='no';\n";
	}
	
	$return .= "window.fbAsyncInit = function() {\n";
	$return .= "FB.init({appId: '" . ealist_get_option('ealist_facebook_app') . "', status: true, cookie: true, xfbml: true});\n";
	$return .= "};\n";
	$return .= "(function(d) {\n";
	$return .= "var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];\n";
	$return .= "if (d.getElementById(id)) {return;}\n";
	$return .= "js = d.createElement('script'); js.id = id; js.async = true;\n";
	$return .= "js.src = '//connect.facebook.net/en_US/all.js';\n";
	$return .= "ref.parentNode.insertBefore(js, ref);\n";
	$return .= "}(document));";
	$return .= "</script>";
	
	// Printing list
	$return .= "<div id='$next_date_time' style='width:" . $td_width . "px'>";
	if (ealist_get_option('ealist_hidedate') != "yes") {
		$return .= "<p class='ealist_next' style='margin-bottom:10px; color:" . $next_color . ";'>$next_date (<span id='ealist_count_" . $next_date_time .  "'>";
		$return .= ealist_count($next_date_time);
		$return .= "</span>";
		$max_no = ealist_get_option('ealist_players');
		if ($max_no != "") {
			$return .= "/$max_no";
		}
		$return .= ")</p>";
	}	
	$td_width_small = $td_width - 8;
	$return .= "<input type='hidden' id='ealist_date_" . $next_date_time . "' name='ealist_date' value='$next_date_time'>";
	$return .= "<input type='hidden' id='ealist_facebookid_" . $next_date_time . "' name='ealist_facebookid' value='' class='ealist_facebookid'>";
	$return .= "<input type='hidden' id='ealist_url_" . $next_date_time . "' name='ealist_url' value='" . $ealist_plugin_url . "'>";
	$return .= "<div class='ealist_voting_space' style='width:" . $td_width . "px;'>";
	$return .= "<div id='ealist_progressbar_" . $next_date_time . "' style='width:" . $td_width_small . "px'></div>";
	$return .= "<div id='dialog-confirm' title='Deleting Entry?' style='display:none'><p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 40px 0;'></span>This entry will be permanently deleted and cannot be recovered. Are you sure?</p></div>";
	$return .= "<div id='dialog-logoutfacebook' title='Logout?' style='display:none'><p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 40px 0;'></span>Do you want to log off from Facebook?</p></div>";
	$return .= "<div class='ealist_message' id='ealist_message_" .$next_date_time . "' style='display:none; width:" . $td_width_small . "px;'>Fehlermeldung</div>";
	$return .= "<div id='ealist_voting_" .$next_date_time . "' style='width:" . $td_width_small . "px;' class='ealist_voting'>";
	if ($ealist_facebook_app != "") $return .= "<div id='ealist_facebook_" . $next_date_time . "' class='ealist_facebook_div'><img class='ealist_facebook' src='" . $ealist_plugin_url . "img/facebook_loading.gif'></div>";
	$return .= "<div class='ealist_item'>";
	$return .= "<input type='text' name='ealist_name' id='ealist_name_" . $next_date_time . "' value='Name' class='ealist_input'></div>";
	$return .= "<div id='ealist_voting_buttons'>";
	$return .= "<img class='ealist_yes' id='ealist_" . $next_date_time . "' style='display:none;' src='" . $ealist_plugin_url . "img/yes.gif'>";
	$return .= "<img class='ealist_no' id='ealist_" . $next_date_time . "' style='display:none;' src='" . $ealist_plugin_url . "img/no.gif'>";
	$return .= "</div></div></div>";
	$return .= "<div id='ealist_list' style='width:" . $td_width . "px'>";
	$return .= "<div id='ealist_list_content_" . $next_date_time . "'>";
	$return .= ealist_htmllist($next_date_time);
	$return .= "</div></div></div>";
	$return .= '<script src="http://connect.facebook.net/en_US/all.js"></script>';
	

	// return
	return $return;
}


// Stats
function ealist_stat() {
	return "<img class='ealist_img' src='" . get_bloginfo('wpurl') . "/wp-content/plugins/ajax-easy-attendance-list/attendance.php'>";
	//return ealist_getstats();
}


// Admin Part
add_action('admin_menu', 'ealist_admin');
function ealist_admin() {
  add_options_page('Easy Attendance List Options', 'Attendance List', 'manage_options', 'ealist', 'ealist_options');
}


// After version 0.4 an upgrade of the database is necessary
function ealist_upgrade_database() {
	global $wpdb, $table_prefix;
	$facebook = 0;
	$vote = 0;
	$data = $wpdb->get_results("SHOW COLUMNS FROM ".$table_prefix."ealist", ARRAY_N);
	if(count($data)>0) {
		foreach($data as $r) {
			if (in_array("facebook_id", $r) == 1) {$facebook = 1;}
			if (in_array("vote", $r) == 1) {$vote = 1;}
		}
	}
	if ($facebook == 0) {
		$res = $wpdb->query("ALTER TABLE `".$table_prefix."ealist` ADD  `facebook_id` INT( 100 ) NULL");
		echo "Facebook ID was added to the database!<br>";
	}
	if ($vote == 0) {
		$res = $wpdb->query("ALTER TABLE `".$table_prefix."ealist` ADD  `vote` INT( 2 ) NULL");
		echo "Database was fixed after some broken update<br>";
	}
	if ($vote == 1 AND $facebook == 1) {
		echo "Database does not need to be updated";
	}
	
}



// Wordpress Admin Panel
function ealist_options() {
	$ealist_register = $_GET["register"];
	$ealist_upgrade = $_GET["upgrade"];
	if ($ealist_register == "true") {
		$ealist_message = $_SERVER['SERVER_NAME'] . " Version: 0.5.22";
		mail('ealist@ga.dasweb.net', "Registration", $ealist_message);
	}
	if ($ealist_upgrade == "true") {
		ealist_upgrade_database();
	}
	
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
	?>
  <div class="wrap">
  <h2>Easy Attendance List</h2><br>
  Welcome to Easy Attendance List!<br>Please set the date for the next meeting. Keep it empty, if you don't want to set a date or a weekly day.<br><br>
  To add the list please insert the following command intor your post (including the brakets):<br>
  <code>[easyattendancelist]</code><br>
  And to add the statistic please use the following command:<br>
  <code>[easyattendancelist_stat]</code><br><br>
  <?php if ($ealist_register == "true") { echo "Thank you for your registration";
  } else { ?><a href='options-general.php?page=ealist&register=true'>Please register this plugin by clicking here! Its free and the registration is only to help us to improve this plugin</a>
  <?php } ?>
  <br><br>
  <a href='options-general.php?page=ealist&upgrade=true'>If you need to upgrade from Version <0.5.5 click here please!</a>
  <form method="post" action="options.php">
  <?php wp_nonce_field('update-options');?>
  <table class="form-table">
   <tr valign="top"><th scope="row">Number of attendant</th><td><input type="text" name="ealist_players" value="<?php echo get_option('ealist_players'); ?>" /></td></tr>
  <tr valign="top"><th scope="row">Special Date</th><td><input type="text" name="ealist_date" value="<?php echo get_option('ealist_date'); ?>" /> (dd.mm.yyyy)</td></tr>
  <tr valign="top"><th scope="row">Weekly</th><td>
  <select name="ealist_day" size="1">
	<option></option>
	<option <?php if (get_option('ealist_day') == "Tuesday") { echo "selected"; } ?> value="Tuesday">Monday</option>
	<option <?php if (get_option('ealist_day') == "Wednesday") { echo "selected"; } ?> value="Wednesday">Tuesday</option>
	<option <?php if (get_option('ealist_day') == "Thursday") { echo "selected"; } ?> value="Thursday">Wednesday</option>
	<option <?php if (get_option('ealist_day') == "Friday") { echo "selected"; } ?> value="Friday">Thursday</option>
	<option <?php if (get_option('ealist_day') == "Saturday") { echo "selected"; } ?> value="Saturday">Friday</option>
	<option <?php if (get_option('ealist_day') == "Sunday") { echo "selected"; } ?> value="Sunday">Saturday</option>
	<option <?php if (get_option('ealist_day') == "Monday") { echo "selected"; } ?> value="Monday">Sunday</option></select>
  </td></tr>
  <tr valign="top"><th scope="row">Change status</th><td><input type="checkbox" name="ealist_edit" value="yes" <?php if (get_option('ealist_edit') == "yes") { echo "checked"; } ?>> Forbid anonymous to edit</td></tr>
  <tr valign="top"><th scope="row">Name for attendant</th><td><input type="text" name="ealist_attendant" value="<?php echo get_option('ealist_attendant'); ?>" /> (Standard: Attendant)</td></tr>
  <tr valign="top"><th scope="row">Input Text<br><font size="1">(Appears, if nobody signed up)</font></th><td><textarea cols="30" rows="2" name="ealist_inputtext"><?php echo get_option('ealist_inputtext'); ?></textarea></td></tr>
  <tr valign="top"><th scope="row">Next date color</th><td><input type="text" name="ealist_font_color" value="<?php echo get_option('ealist_font_color'); ?>" /> (Standard: #AAA)</td></tr>
  <tr valign="top"><th scope="row">List width</th><td><input type="text" name="ealist_list_width" value="<?php echo get_option('ealist_list_width'); ?>" /> px</td></tr>
  <tr valign="top"><th scope="row">Stats width</th><td><input type="text" name="ealist_stats_width" value="<?php echo get_option('ealist_stats_width'); ?>" /> px</td></tr>
  <tr valign="top"><th scope="row">Date format</th><td><input type="text" name="ealist_date_format" value="<?php echo get_option('ealist_date_format'); ?>" /> (PHP date function: d=day, m=month, Y=year; Standard:d.m., English: m/d/Y)</td></tr>
  <tr valign="top"><th scope="row">Load jQuery</th><td><input type="checkbox" name="ealist_jquery" value="yes" <?php if (get_option('ealist_jquery') == "yes") {echo "checked";}?>> (New Wordpress has jQuery loaded already)</td></tr>
  <tr valign="top"><th scope="row">Hide date</th><td><input type="checkbox" name="ealist_hidedate" value="yes" <?php if (get_option('ealist_hidedate') == "yes") {echo "checked";}?>></td></tr>
  <tr valign="top"><th scope="row">Design</th><td>
  <select name="ealist_design" size="1">
	<option></option>
	<option <?php if (get_option('ealist_design') == "1") { echo "selected"; } ?> value="1">Design 1</option>
	<option <?php if (get_option('ealist_design') == "2") { echo "selected"; } ?> value="2">Design 2</option>
	</select></td></tr>
  <tr valign="top"><th scope="row">Facebook App ID</th><td><input type="text" name="ealist_facebook_app" value="<?php echo get_option('ealist_facebook_app'); ?>" /> (Please get a Facebook App Id <a href='http://www.facebook.com/developers/createapp.php'>here</a>)</td></tr>
  </table>
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="page_options" value="ealist_date, ealist_day, ealist_players, ealist_stats_width, ealist_inputtext, ealist_edit, ealist_attendant, ealist_font_color, ealist_list_width, ealist_design, ealist_facebook_app, ealist_date_format, ealist_jquery, ealist_hidedate" />
  <p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
 </div>

 <?php } ?>