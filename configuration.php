<?php
function ealist_get_option($option) {
	switch ($option) {
		case "ealist_day":
			return get_option('ealist_day');
			break;
		case "ealist_date":
			return get_option('ealist_date');
			break;
		case "ealist_font_color":
			return get_option("ealist_font_color");
			break;
		case "ealist_facebook_app":
			return get_option('ealist_facebook_app');
			break;
		case "ealist_edit":
			return get_option("ealist_edit");
			break;
		case "ealist_players":
			return get_option('ealist_players');
			break;
		case "ealist_design":
			return get_option("ealist_design");
			break;
		case "ealist_inputtext":
			return get_option("ealist_inputtext");
			break;
		case "ealist_attendant":
			return get_option("ealist_attendant");
			break;
		case "ealist_stats_width":
			return get_option("ealist_stats_width");
			break;
		case "ealist_list_width":
			return get_option("ealist_list_width");
			break;
		case "ealist_date_format":
			return get_option("ealist_date_format");
			break;
		case "ealist_jquery":
			return get_option("ealist_jquery");
			break;
		case "ealist_hidedate":
			return get_option("ealist_hidedate");
			break;
	}
}

// Additional Configurations
$ealist_plugin_url = site_url() . "/wp-content/plugins/ajax-easy-attendance-list/";
?>