<?php

function ealist_addhead(){
	global $ealist_plugin_url;
	$ealist_design = ealist_get_option("ealist_design");
	$ealist_jquery = ealist_get_option("ealist_jquery");
	if ($ealist_design == "") $ealist_design = 1;
	echo '<link rel="stylesheet" href="' . $ealist_plugin_url . 'css/style' . $ealist_design . '.css" type="text/css" media="screen"  />'; 
	echo '<link rel="stylesheet" href="' . $ealist_plugin_url . 'css/overcast/jquery-ui-1.8.9.custom.css" type="text/css" media="screen"  />'; 
	echo '<link rel="stylesheet" href="' . $ealist_plugin_url . 'css/jquery.ajaxLoader.css" type="text/css" media="screen"  />'; 
	if ($ealist_jquery == "yes") {
		echo '<script src="http://code.jquery.com/jquery-1.5.1.min.js" type="text/javascript"></script>';
	}
	echo '<script src="' . $ealist_plugin_url . 'js/jquery-ui-1.8.9.custom.min.js" type="text/javascript"></script>';
	echo '<script src="' . $ealist_plugin_url . 'js/md5.js" type="text/javascript"></script>';
	echo '<script src="' . $ealist_plugin_url . 'js/jquery.ajaxLoader.js" type="text/javascript"></script>';
	
}

function ealist_postentry($vars) {
	global $wpdb, $current_user, $table_prefix;
	$date = intval($vars["ealist_date"]);
	$facebook_id = $vars["ealist_facebook_id"];
	$del = substr($vars["ealist_name"], 0, 3);
	if ($current_user->ID > 0) {
		$name = $vars["ealist_name"];
		$name = strtolower($name);
	} else {
		$name = $vars["ealist_name"];
		$name = strtolower($name);
		$was = array("ä", "ö", "ü", "ß"); 
		$wie = array("ae", "oe", "ue", "ss"); 
		$name = str_replace($was, $wie, $name);
		$allowed = "/[^a-z0-9\\040\\.\\-\\_\\\\]/i";
		$name = preg_replace($allowed,"",$name);
		$facebook_id = preg_replace($allowed,"",$facebook_id);
	}
	$vote = intval($vars["ealist_vote"]);
	$list = ealist_getlist($date);
	$data = $wpdb->get_results("SELECT id, name, vote FROM ".$table_prefix."ealist WHERE date = " . intval($date) . " AND name = '" . $name . "'");
	if (count($data) > 0) {
		$ealist_message = "Double entry!";
	} else {
		if ($name != "") {
			if ($del == "del") {
				$name = substr($name, 3);
				$res=$wpdb->query(sprintf("DELETE FROM `".$table_prefix."ealist` WHERE date=%d AND id='%d'",
					$date,
					$name
				));
			} else if ($name != "name") {
				if ((int)$name > 0) {
					$res=$wpdb->query(sprintf("UPDATE `".$table_prefix."ealist` SET `vote`=%d WHERE `id`='%s' AND `date`='%d' LIMIT 1",
						$vote,
						$name,
						$date
					));
				} else {
					$res=$wpdb->query(sprintf("INSERT INTO `".$table_prefix."ealist` (`date`, `name`, `vote`, `facebook_id`) VALUES (%d, '%s', %d, '%s')",
						$date,
						$name,
						$vote,
						$facebook_id
					));
	
				}
			} else {
				$ealist_message = "Do not use Name!";
			}

		}
	}

	
	
	//ealist_getstats();  // Creating and loading new Stats
	ealist_display_message($ealist_message, $date); // Display error message
	return ealist_htmllist($date);
}

function ealist_getlist($ealist_date) {
	global $wpdb, $table_prefix;
	$list=array();
	$data = $wpdb->get_results("SELECT id, name, vote, facebook_id FROM ".$table_prefix."ealist WHERE date = " . intval($ealist_date) . " ORDER BY vote DESC, name ASC");
	if(count($data)>0 && is_array($data)) {
		foreach($data as $r) {
			$list[$r->name] = array($r->vote, $r->id, $r->facebook_id);
		}
		return $list;
	} else {
		$data = $wpdb->get_results("SELECT id, name, vote FROM ".$table_prefix."ealist WHERE date = " . intval($ealist_date) . " ORDER BY vote DESC, name ASC");
		
		if(count($data)>0 && is_array($data)) {		// Update Database
			$res = $wpdb->query("ALTER TABLE `".$table_prefix."ealist` ADD  `facebook_id` INT( 100 ) NULL");
			$data3 = $wpdb->get_results("SELECT id, name, vote, facebook_id FROM ".$table_prefix."ealist WHERE date = " . intval($ealist_date) . " ORDER BY vote DESC, name ASC");
			foreach($data3 as $r) {
				$list[$r->name] = array($r->vote, $r->id, $r->facebook_id);
			}
			return $list;
		}
	}

	return false;
}

function ealist_htmllist($ealist_date) {
	global $current_user, $ealist_plugin_url;;
	$list = ealist_getlist($ealist_date);
	$ealist_names = ealist_names();
	array_walk($ealist_names, ealist_lower_u);
	$ealist_names = array_unique($ealist_names);
	
	echo "<script type='text/javascript'>";			// Is user allowed to edit the list + Autocomple list
	echo "jQuery(function() {";
	echo "var availableTags = [";
	if (count($ealist_names)>0) {
		foreach ($ealist_names as $key) {
			echo "'" . $key . "',";
		}
	}
	echo "];";
	echo "jQuery( '#ealist_name_" . $ealist_date . "' ).autocomplete({";
	echo "		source: availableTags";
	echo "});";
	echo "});";
	echo "</script>";
	
	
	$j = 1;
	$k = 1;
	if (!empty($list)) {
		// $return .= "<table class='ealist_table'>";
		$return = "<ul class='ealist_ul' id='ealist_ul_" . $ealist_date . "'>";
		foreach ($list as $item=>$vote) {
			$facebook_id_2 = md5($vote[2]);
			if ($vote[0] == '1') {$m = $j; $j++;}
			if ($vote[0] == '0') {$m = $k; $k++;}
			if ($k > 1) $k = 0;
			if ($j > 1) $j = 0;
			$return .= "<li data-id='$vote[1]' facebook='" . $facebook_id_2 . "' class='ealist_div_" . $m . "_$vote[0]' id='ealist_" . $ealist_date . "_$vote[1]'><div class='ealist_item'>";
			if ($vote[2] > 0) {$return .= "<img class='fb_profile_pic2' src='https://graph.facebook.com/" . $vote[2] . "/picture'>&nbsp;";}
			$return .= ucwords(strtolower($item));
			$return .= "</div><div class='ealist_result'>";
			$return .= ($vote[0] == "1") ?  "<img src='" . $ealist_plugin_url . "img/yes.gif' alt='$vote[1]'>" : "<img src='" . $ealist_plugin_url . "img/no.gif' alt='$vote[1]'>";
			$return .= "</div><div class='ealist_vote' style='display:none;'><img id='ealist_" . $ealist_date . "_$vote[1]' class='ealist_yes' src='" . $ealist_plugin_url . "img/yes.gif'><img id='ealist_" . $ealist_date . "_$vote[1]' class='ealist_no' src='" . $ealist_plugin_url . "img/no.gif'>";
			if ($current_user->ID > 0) {
				$return .= "<a href='javascript:ealist_delete(" . $vote[1] . ", ". $ealist_date . ");'>delete</a>";
			}
			$return .= "</div></li>";
		}
		$return .= "</ul>";
	} else {
		$return = "<p style='padding-left:5px;'><font color=grey>" . ealist_get_option("ealist_inputtext") . "</font></p>";
	}	
	
	return $return;
}

function ealist_count($ealist_date) {
	$list = ealist_getlist($ealist_date);
	$j = 0;
	if (!empty($list)) {
	foreach ($list as $item=>$vote) {
		if ($vote[0] == 1) {
			$j++;
		}
	}
	}
	return $j;
	
}

function ealist_getstats() {
	$list = ealist_getstats_list();
	if (!empty($list)) {
		if (ealist_get_option('ealist_attendant')) {
			$attendant = ealist_get_option('ealist_attendant');
		} else {
			$attendant = "Attendant";
		}
		$i = 0;
		foreach($list as $a=>$b) {
			$week = (int)ealist_get_week_number($a);		
			$total[$week] = $b;
		}
		
		foreach($total as $a=>$b) {
			$i++;
			$yaxis[$i] = $b;
			$xaxis[$i] = $a;
			//echo $b;
		}
		if ($i > 1) {
			$max = max($yaxis);
			if ($max % 5 > 0) {
				$max = (floor($max / 5) + 1) * 5;
			}
			include("pchart/pData.class");     
			include("pchart/pChart.class"); 	
			$stats_width = ealist_get_option('ealist_stats_width'); //620 default
			if ($stats_width > 0) {
			} else {
				$stats_width = 620;
			}
			
			$DataSet = new pData;  
			 
			$DataSet->AddPoint($yaxis,"Attendance");
			$DataSet->AddPoint($xaxis,"Week");
			$DataSet->AddSerie("Attendance");  
			$DataSet->SetAbsciseLabelSerie("Week");
			$DataSet->SetYAxisName($attendant);
			if ($i > 1) {$DataSet->SetXAxisName("Week");}
			$Test = new pChart($stats_width,280);   
			
			$Test->setFixedScale(0,$max); 
			$Test->setFontProperties(dirname(__FILE__). "/Fonts/tahoma.ttf",8);     
			$Test->setGraphArea(70,30,$stats_width-20,230);     
			$Test->drawFilledRoundedRectangle(7,7,$stats_width-7,273,5,210,210,210);     
			$Test->drawRoundedRectangle(5,5,$stats_width-5,275,5,230,230,230);     
			$Test->drawGraphArea(255,255,255,TRUE);  
			
			$Test->drawGraphAreaGradient(200,200,200,-20);  
			$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);     
			$Test->drawGrid(4,TRUE,220,220,220,50);  
		   
			// Draw the 0 line     
			$Test->setFontProperties(dirname(__FILE__). "/Fonts/tahoma.ttf",6);     
			$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
			$player = get_option('ealist_players');
			if ($player > 0) {
				$Test->drawTreshold(get_option('ealist_players'),143,55,72,TRUE,TRUE);        
			}
		   
			// Draw the line graph  
			$Test->setColorPalette(0,153,0,0); 
			$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());     
			$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);     
		   
			// Finish the graph     
			$Test->setFontProperties(dirname(__FILE__). "/Fonts/tahoma.ttf",8);     
			//$Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);     
			$Test->setFontProperties(dirname(__FILE__). "/Fonts/tahoma.ttf",10);     
			$Test->drawTitle(60,22,"Attendance",50,50,50,$stats_width-35);     
			//$Test->Render(ABSPATH . "attendance.png");        
			$Test->Stroke();

			

			//return "<img class='ealist_img' src='" . get_bloginfo('wpurl') . "/attendance.png'>";
			//return $return;
		}
	}
}

function ealist_getstats_list() {
	global $wpdb, $table_prefix;
	$date_low = time() - 365*24*60*60;
	$list=array();
	$result = $wpdb->get_results("SELECT date, vote FROM ".$table_prefix."ealist WHERE vote = 1 AND date > $date_low ORDER BY date ASC");
	if(count($result)>0 && is_array($result)) {
		foreach($result as $r) {
			$j++;
			$list[$r->date]++;
		}
		return $list;
	}


return false;
}

function ealist_get_week_number($timestamp) { 
 
        $d = GETDATE($timestamp); 
 
        $days = ealist_iso_week_days($d[ "yday"], $d[ "wday"]); 
 
        IF ($days < 0) { 
                $d[ "yday"] += 365 + ealist_is_leap_year(--$d[ "year"]); 
                $days = ealist_iso_week_days($d[ "yday"], $d[ "wday"]); 
        } ELSE { 
                $d[ "yday"] -= 365 + ealist_is_leap_year($d[ "year"]); 
                $d2 = ealist_iso_week_days($d[ "yday"], $d[ "wday"]); 
                IF (0 <= $d2) { 
                        /* $d["year"]++; */ 
                        $days = $d2; 
                } 
        } 
 
        RETURN (int)($days / 7) + 1; 
} 

FUNCTION ealist_iso_week_days($yday, $wday) { 
        RETURN $yday - (($yday - $wday + 382) % 7) + 3; 
} 

FUNCTION ealist_is_leap_year($year) { 
        IF ((($year % 4) == 0 and ($year % 100)!=0) or ($year % 400)==0) { 
                RETURN 1; 
        } ELSE { 
                RETURN 0; 
        } 
} 

function ealist_get_td_width() {
$list_width = ealist_get_option("ealist_list_width");
	if ($list_width == "") {
		$list_width = 238;
	}
	//$td_width = $list_width - 64 - 16;
	$td_width = $list_width;
	return $td_width;
}

function ealist_display_message($data, $voting_id) {

	if ($data > "") {
		$code = '<script type="text/javascript">';
		$code .= "ealist_message('" . $data . "' , '" . $voting_id . "')";
    	$code .= '</script>';
	}
	echo $code;
}

function ealist_names() {
	global $wpdb, $table_prefix;
	$results=$wpdb->get_col("SELECT name FROM ".$table_prefix."ealist");
	return $results;
}

function ealist_lower_u(&$string) {
	$string = ucwords(strtolower($string));
}
?>