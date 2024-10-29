var ealist_last_id = 0;
var ealist_count = 0;
var ie6 = false;
var ealist_facebook = false;
jQuery.expr[":"].econtains = function(obj, index, meta, stack){
return (obj.textContent || obj.innerText || jQuery(obj).text() || "").toLowerCase() == meta[3].toLowerCase();
}
 
 
 jQuery(document).ready(function(){ 
 var browser = "msie 6";
 var agent = navigator.userAgent.toLowerCase();
 ie6 = agent.indexOf(browser.toLowerCase())>-1;

	jQuery(".ealist_input").focus(function() {						// Empty the name field after click
		
		voting_id = jQuery(this).parent().parent().parent().parent().attr("id");
		if(this.value=='Name')
		{
			this.value='';
			jQuery('#ealist_voting_' + voting_id + ' .ealist_yes').show();						// Shows the voting buttons
			jQuery('#ealist_voting_' + voting_id + ' .ealist_no').show();
		}
	});
	
	jQuery(".ealist_input").blur(function() {
		if(this.value=='')
		{
			this.value='Name';										// Writes back 'Name' in the name field, if field was not filled
			jQuery('#ealist_voting_' + voting_id + ' .ealist_yes').hide();						// Hides the voting buttons, if name field was not fillded
			jQuery('#ealist_voting_' + voting_id + ' .ealist_no').hide();
		}
	});
	
	ealist_ready();
	jQuery('#ealist_list li').bind("click", ealist_click);

	// Facebook
	if (ealist_facebook_app != "")
	{
	
		jQuery('.ealist_facebook_div').live('click', function()
		{
			var id = jQuery(this).attr('id');
			var voting_id = id.substr(16,10);
			//alert("Facebook support is not finished yet! The following approach is just an attemp to do so ...");
			var fbuid;
			try {
				FB.getLoginStatus(function(response) {(response.status != "unknown") ? fbuid = response.authResponse.userID : fbuid="";});
			} catch(err) {
				ealist_message("An error occured (" + err + ")", voting_id);
			}
			
			if (fbuid != "") {
				jQuery( "#dialog-logoutfacebook" ).dialog({
						resizable: false,
						height:180,
						modal: true,
						buttons: {
							"Yes": function() {
								jQuery( this ).dialog( "close" );
								FB.logout(function(response) {
									ealist_deactivate_facebook(voting_id);
								});
							},
							Cancel: function() {
								jQuery( this ).dialog( "close" );
							}
						}
					});
				
			} else {

				FB.login(function(response) {
					if (response) {
						var user_id = response.authResponse.userID;
						var query = FB.Data.query('select name, uid from user where uid={0}', user_id);
						query.wait(function(rows) {
							ealist_activate_facebook(rows[0].name, user_id);
						});
					} else {
						ealist_deactivate_facebook();;// user cancelled login
					}
				});
			}	
		});
		ealist_fbEnsureInit();

	}
	
	
});

function ealist_ready() {
	jQuery('.ealist_yes').unbind();
	jQuery('.ealist_no').unbind();
	
	jQuery('.ealist_yes').bind('click', function() {				// By click on yes
		ealist_count = 0;											// count sets the progressbar
		var id = jQuery(this).attr('id');
		var voting_id = id.substr(7,10);
		jQuery("#ealist_progressbar_" + voting_id).fadeIn();
		ealist_progressbar(voting_id);
		id = id.substr(18);
		jQuery('#ealist_' + id).addClass( "ealist_div_1_1");
		jQuery('#ealist_' + id).removeClass( "ealist_div_1_0");
		jQuery('#ealist_' + id).removeClass( "ealist_div_0_0");
		ealist_change(1, id, voting_id);
	});
	jQuery('.ealist_no').bind('click', function() {					// By click on no
		ealist_count = 0;											// count sets the progressbar
		var id = jQuery(this).attr('id');
		var voting_id = id.substr(7,10);
		jQuery("#ealist_progressbar_" + voting_id).fadeIn();
		ealist_progressbar(voting_id);
		id = id.substr(18);
		jQuery('#ealist_' + id).addClass( "ealist_div_1_0");
		jQuery('#ealist_' + id).removeClass( "ealist_div_1_1");
		jQuery('#ealist_' + id).removeClass( "ealist_div_0_1");
		ealist_change(0, id, voting_id);
	});	
}

var ealist_fb_n = 0;
function ealist_fbEnsureInit() {
	ealist_fb_n = ealist_fb_n + 1;
	setTimeout("ealist_delete_facebook();" , 5000);
	//	console.log("Checking Facebook (" + ealist_fb_n + ")");
        if (FB.getAuthResponse()) {
			FB.getLoginStatus(function(response) {
				ealist_facebook_init(response);
			});
		} else {
			if (!FB.getLoginStatus(function(response) {
				ealist_facebook_init(response);
			})) {
				if (ealist_fb_n < 2) {
					setTimeout("ealist_fbEnsureInit();" , 1000);
				}
			}
			
			
			// if (ealist_fb_n > 20) {
				// ealist_delete_facebook();
			// } else {
				// setTimeout("ealist_fbEnsureInit();", 100);
			// }
		}

}

function ealist_activate_facebook(ealist_name, user_id) {
	jQuery('.ealist_facebook').show();
	var url = jQuery('input[name="ealist_url"]').val();
	jQuery(".ealist_facebook").attr("src", url + "img/facebook_icon.gif");
	jQuery(".ealist_input").attr("disabled", true);
	jQuery(".ealist_input").attr("value", ealist_name);
	jQuery(".ealist_facebookid").attr("value", user_id);
	jQuery(".ealist_facebook").unbind('mouseenter').unbind('mouseleave');
	jQuery('.ealist_yes').show();						// Shows the voting buttons
	jQuery('.ealist_no').show();
	
}


function ealist_deactivate_facebook(voting_id) {
	jQuery('.ealist_facebook').show();
	var url = jQuery('input[name="ealist_url"]').val();
	jQuery('.ealist_facebook').attr("src", url + "img/facebook_icon_grey2.gif");
	jQuery('.ealist_facebook').hover(function() {
		jQuery(this).attr("src", url + "img/facebook_icon.gif");
	}, function() {
		jQuery(this).attr("src", url + "img/facebook_icon_grey2.gif");
	});
	jQuery(".ealist_input").attr("disabled", false);
	//jQuery(".ealist_input").attr("value", "Name");
	jQuery(".ealist_facebookid").attr("value", "");
	jQuery('#ealist_votting_buttons .ealist_yes').hide();
	jQuery('#ealist_votting_buttons .ealist_no').hide();
	if (voting_id > 0) {
		ealist_message("Facebook logout successful!", voting_id);
	}
}

function ealist_delete_facebook() {
	if (!ealist_facebook) {
		ealist_deactivate_facebook();
		jQuery('.ealist_facebook').hide();
	}
}


function ealist_facebook_init(response) {
	ealist_facebook = true;
	if (response.status == 'connected') {
		
		var user_id = response.authResponse.userID;
		if (user_id)
		{
			var query = FB.Data.query('select name, uid from user where uid={0}', user_id);
			query.wait(function(rows) {
				ealist_activate_facebook(rows[0].name, user_id);
			});
		} else {
			ealist_deactivate_facebook();
//			console.log("No UserID for Facebook");
		}
	} else if (response.status == 'notConnected') {
		ealist_deactivate_facebook();
//		console.log("Not connected to Facebook");				
	} else if (response.status == 'unknown') {
//		console.log("Facebook Status unknown");
		ealist_deactivate_facebook();
	} else {
		FB.Event.subscribe('auth.sessionChange', function() {
			ealist_fbEnsureInit();
		});
		if (ealist_fb_n > 10) {
			ealist_deactivate_facebook();
//			console.log("Facebook Error");
		} else {
			setTimeout("ealist_fbEnsureInit();", 100);
		}
	}
}

function ealist_progressbar(voting_id) {
	jQuery("#ealist_message_" + voting_id).fadeOut();
	jQuery("#ealist_progressbar_" + voting_id).progressbar({
		value: ealist_count
	});
	if(ealist_count < 95) {
		ealist_count = ealist_count+0.5;
		setTimeout("ealist_progressbar(" + voting_id + ");", 100);
	}
}


function ealist_change(vote, id, voting_id) {
	
	if (ealist_count < 10) {
		ealist_count = 10;
	}

	if (id == "") {
		var ealist_name = document.getElementById("ealist_name_" + voting_id).value;
		document.getElementById("ealist_name_" + voting_id).value = "Name";
	} else {
		var ealist_name = id;
	}
	
	var ealist_vote = vote;
	var ealist_date = document.getElementById("ealist_date_" + voting_id).value;
	var ealist_url = document.getElementById("ealist_url_" + voting_id).value;
	var facebook_id = document.getElementById("ealist_facebookid_" + voting_id).value;
	if (ealist_count < 15) {
		ealist_count = 15;
	}
	
	ealist_entry(ealist_date, ealist_name, ealist_vote, ealist_url, id, voting_id, facebook_id);
	}

function ealist_entry(date, name, vote, url, id, voting_id, facebook_id) {
	
	if (ealist_count < 16) {
		ealist_count = 16;
	}
	
	ealist_progressbar(voting_id);
	if (id > 0) {
		//jQuery('#ealist_' + id).hide();
		if (ie6 == false) jQuery('#ealist_list_content_' + voting_id).ajaxLoader();
	} else {
		jQuery('#ealist_voting_' + voting_id).fadeOut();
		if (ealist_count < 18) {
			ealist_count = 18;
		}
		if (vote != "") { 
			jQuery('#ealist_ul_' + voting_id).prepend("<li class='ealist_div_1_" + vote + "'>" + name + "</li>");
			if (ie6 == false) jQuery('#ealist_list_content_' + voting_id).delay(100).ajaxLoader();
		}
	}
	

	if (ealist_count < 20) {
		ealist_count = 20;
	}
	var file = url + "response.php";
	var newdata1;
	var newdata2;
	jQuery.post(file, { ealist_date: date, ealist_name: name, ealist_vote: vote, ealist_facebook_id: facebook_id },
		function(data) {
			jQuery('#ealist_list_content_' + voting_id).html(data);

			if (ie6 == false) jQuery('#ealist_list_content_' + voting_id).ajaxLoaderRemove();
			jQuery('#ealist_list li').bind("click", ealist_click);
			if (ealist_count < 50) {
				ealist_count = 50;
			}
			//jQuery('#ealist_list_content').fadeOut(function () {
				if (ealist_count < 60) {
					ealist_count = 100;
				}
	
			//	jQuery('.ealist_voting_space').show();
			//}).fadeIn();

			
			var file2 = url + "count.php";
			jQuery('#ealist_voting_' + voting_id).fadeIn(function() {
						jQuery('.ealist_voting_space_' + voting_id).show();
						jQuery("#ealist_progressbar_" + voting_id).fadeOut();						
					});
			jQuery.post(file2, { date: date},
				function(data) {
			
					if (ealist_count < 100) {
						ealist_count = 100;
					}
					newdata2 = data;
					d = new Date();
					ealist_ready();  					// bind yes and no again
					jQuery(".ealist_img").attr("src", url + "attendance.php?"+d.getTime());
					FB.getLoginStatus(function(response) {
						if (response.status === 'connected') {
							var user_id = response.authResponse.userID;
							var query = FB.Data.query('select name, uid from user where uid={0}', user_id);
								query.wait(function(rows) {
								ealist_activate_facebook(rows[0].name, user_id);
							});
						//
						} else {
							document.getElementById("ealist_name_" + voting_id).value = "Name";
						}
						document.getElementById("ealist_count_" + voting_id).innerHTML = newdata2;
					});
					
			});
		});
	ealist_ready();
}

function ealist_delete(id, voting_id) {
		//ealist_progressbar(voting_id);
		ealist_count = 0;
		var ealist_name = document.getElementById("ealist_name_" + voting_id).value;
		document.getElementById("ealist_name_" + voting_id).value = "Name";
		ealist_name = "del" + id;
		var ealist_vote = 0;
		var ealist_date = document.getElementById("ealist_date_" + voting_id).value;
		var ealist_url = document.getElementById("ealist_url_" + voting_id).value;
		//var answer = confirm("You are about to delete this person?")
		jQuery(function() {
			jQuery( "#dialog-confirm" ).dialog({
				resizable: false,
				height:180,
				modal: true,
				buttons: {
					"Delete!": function() {
					jQuery( this ).dialog( "close" );
					ealist_count = 0;
					ealist_progressbar(voting_id);
					//jQuery("#ealist_progressbar_" + voting_id).progressbar({ value: 2 });
					//jQuery("#ealist_progressbar_" + voting_id).fadeIn();
					if (ie6 == false) jQuery('#ealist_list_content_' + voting_id).ajaxLoader();
					ealist_entry(ealist_date, ealist_name, ealist_vote, ealist_url, id, voting_id);
				},
				Cancel: function() {
					jQuery( this ).dialog( "close" );
				}
			}
		});
		
	});

}


function ealist_message(data, voting_id) {
		document.getElementById("ealist_message_" + voting_id).innerHTML = data;
		setTimeout(function() {
			jQuery("#ealist_progressbar_" + voting_id).fadeOut();
			jQuery('#ealist_voting_' + voting_id).fadeOut().delay(4000).fadeIn();
			jQuery('#ealist_message_' + voting_id).fadeIn().delay(3500).fadeOut();
		}, 100);
}

function ealist_click() {
			var id = jQuery(this).attr('id');
			var voting_id = id.substr(7,10);
			var facebook_id_2 = jQuery(this).attr('facebook');
			id = id.substr(7);
			ealist_last_id = id;
			var htmlelement = this;
			if (facebook_id_2 != "d41d8cd98f00b204e9800998ecf8427e" && facebook_id_2 != "cfcd208495d565ef66e7dff9f98764da") {			// Check, if list entry is connected to Facebook
				FB.getLoginStatus(function(response) {
					if (response.status === 'connected') {
						var user_id = response.authResponse.userID;
						var user_id_2 = MD5(user_id);
						if (user_id_2 == facebook_id_2 || ealist_current_user != "0") {
							ealist_normal_edit(htmlelement, voting_id, id);
						} else {
							ealist_message("You are not authorized", voting_id);
						}
					} else {
						if (ealist_current_user != "0") {
							ealist_normal_edit(htmlelement, voting_id, id);
						} else {
							ealist_message("Please sign in first!", voting_id);
						}
					}
				});	
			} else {
				if (ealist_forbid_edit == 'no' || ealist_current_user != "0") {
					ealist_normal_edit(this, voting_id, id);
				}
			}
			// document.getElementById("ealist_name").value = id;
}

function ealist_normal_edit(element, voting_id, id) {
	jQuery(element).unbind("click");
	var old_item = element;
	jQuery('#ealist_voting_' + voting_id).fadeOut();
	jQuery('.ealist_result', element).fadeOut(300).delay(5100).fadeIn(400, function() {			// hide results of this entry
		jQuery(old_item).click(ealist_click);
		if ((id) == ealist_last_id) {		// after edit check, if this was the last clicked entry. If yes than show input again

			jQuery('.ealist_voting').fadeIn();
		}
	});						
	jQuery('.ealist_vote', element).delay(300).fadeIn(300).delay(4400).fadeOut();				// show voting buttons of this entry
}