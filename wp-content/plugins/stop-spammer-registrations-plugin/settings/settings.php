<?php

// this is the new settings pages for stop spammers.
// This is loaded only when users who can change settings are logged in.
if (!defined('ABSPATH')) exit;



function kpg_ss_admin_menu_l() {
	$icon2='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAAAAACo4kLRAAAA5UlEQVQY02P4DwS/251dwMC5/TeIzwASa4rcDAWRTb8hgkhiUFEGVDGIKAOaGFiUoR1NDCjazuC8uTusc2l6evrkNclJq9elZzRtdmZwWSPkxtNvxmlU76SqabWSw4Sz14XBZbb8qoIFm2WXreZfs15wttRmv2yg4CYVzpDNQMHpWps36zcLZEjXAwU3r8oRbgMKTlHZvFm7lcMoeBNQsNlks2sZUHAV97wlPAukgNYDBdeIKnAvBApuDucTCFgJEXTevKh89ubNEzZs3tzWvHlDP1DQGbvjsXoTa4BgDzrsgYwZHQBqzOv51ZaiYwAAAABJRU5ErkJggg==';
    $iconpng=KPG_SS_PLUGIN_URL.'images/sticon.png';

	add_menu_page( 
	"Stop Spammers", //$page_title,
	"Stop Spammers", //$menu_title,
	'manage_options', //$capability,
	'stop_spammers', //$menu_slug,
	'kpg_ss_summary', // $function
	$iconpng, //$icon_url,
	78.92   //$position 
	);	
	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Stop Spammers Summary", //$page_title,
	"Summary", //$menu_title,
	'manage_options', //$capability,
	'stop_spammers', //$menu_slug,
	'kpg_ss_summary' // $function
	);	
	
	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Allow Requests", //$page_title,
	"Allow Requests", //$menu_title,
	'manage_options', //$capability,
	'ss_allowrequests', //$menu_slug,
	'kpg_ss_allowreq' // $function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Log Report", //$page_title,
	'Log Report', //$menu_title,
	'manage_options', //$capability,
	'ss_reports', //$menu_slug,
	'kpg_ss_reports' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Cache", //$page_title,
	'Cache', //$menu_title,
	'manage_options', //$capability,
	'ss_cache', //$menu_slug,
	'kpg_ss_cache' // function
	);	
	
	if (function_exists('is_multisite') && is_multisite()) {
		add_submenu_page(
		'stop_spammers', // plugins parent
		"Stop Spammers Network", //$page_title,
		'Network', //$menu_title,
		'manage_options', //$capability,
		'ss_network', //$menu_slug,
		'kpg_ss_network'
		);	
	}
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Spam and Login protection options", //$page_title,
	'Protection options', //$menu_title,
	'manage_options', //$capability,
	'ss_options', //$menu_slug,
	'kpg_ss_options' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Stop Spammers Allow Lists", //$page_title,
	'Allow Lists', //$menu_title,
	'manage_options', //$capability,
	'ss_allow_list', //$menu_slug,
	'kpg_ss_allowlist_settings' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Stop Spammers Block Lists", //$page_title,
	'Block Lists', //$menu_title,
	'manage_options', //$capability,
	'ss_deny_list', //$menu_slug,
	'kpg_ss_denylist_settings' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Stop Spammers Web Services and API Settings", //$page_title,
	'Web Services', //$menu_title,
	'manage_options', //$capability,
	'kpg_ss_webservices_settings', //$menu_slug,
	'kpg_ss_webservices_settings'
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Challenge and Deny Options", //$page_title,
	'Challenge &amp; Deny', //$menu_title,
	'manage_options', //$capability,
	'ss_challenge', //$menu_slug,
	'kpg_ss_challenges' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Threat Scan", //$page_title,
	'Threat Scan', //$menu_title,
	'manage_options', //$capability,
	'ss_threat_scan', //$menu_slug,
	'kpg_ss_threat_scan' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Other WordPress Options Maintenance", //$page_title,
	'Other WP Options', //$menu_title,
	'manage_options', //$capability,
	'ss_option_maint', //$menu_slug,
	'kpg_ss_option_maint' // function
	);	
	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Plugin Diagnostics", //$page_title,
	'Diagnostics', //$menu_title,
	'manage_options', //$capability,
	'ss_diagnostics', //$menu_slug,
	'kpg_ss_diagnostics' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Add Ons", //$page_title,
	'Addons', //$menu_title,
	'manage_options', //$capability,
	'ss_addons', //$menu_slug,
	'kpg_ss_addons' // function
	);	
	add_submenu_page(
	'stop_spammers', // plugins parent
	"Keep This Plugin Alive", //$page_title,
	'<span style="font-weight:bold;">Contribute!</span>', //$menu_title,
	'manage_options', //$capability,
	'ss_contribute', //$menu_slug,
	'kpg_ss_contribute' // function
	);	

}
function kpg_ss_summary() {
	include_setting("kpg_ss_summary.php");
}
function kpg_ss_network() {
	include_setting("kpg_ss_network.php");
}
function kpg_ss_webservices_settings() {
	include_setting("kpg_ss_webservices_settings.php");
}
function kpg_ss_allowlist_settings() {
	include_setting("kpg_ss_allowlist_settings.php");
}
function kpg_ss_denylist_settings() {
	include_setting("kpg_ss_denylist_settings.php");
}
function kpg_ss_options() {
	include_setting("kpg_ss_options.php");
	echo "Options settings";
}
function kpg_ss_access() {
	include_setting("kpg_ss_access.php");
}
function kpg_ss_reports() {
	include_setting("kpg_ss_reports.php");
}
function kpg_ss_cache() {
	include_setting("kpg_ss_cache.php");
}
function kpg_ss_threat_scan() {
	include_setting("kpg_ss_threat_scan.php");
}
function kpg_ss_option_maint() {
	include_setting("kpg_ss_option_maint.php");
}
function kpg_ss_change_admin() {
	include_setting("kpg_ss_change_admin.php");
	echo "Change Admin Login";
}
function kpg_ss_challenges() {
	include_setting("kpg_ss_challenge.php");
}
function kpg_ss_contribute() {
	include_setting("kpg_ss_contribute.php");
}
function kpg_ss_diagnostics() {
	include_setting("kpg_ss_diagnostics.php");
}
function kpg_ss_addons() {
	include_setting("kpg_ss_addons.php");
}
function kpg_ss_allowreq() {
	include_setting("kpg_ss_allowreq.php");
}




function include_setting($file) {
	sfs_errorsonoff();
	$ppath=plugin_dir_path( __FILE__ );
	if (file_exists($ppath.$file)) {
		require_once($ppath.$file);
	} else {
		echo "<br>Missing file:$ppath $file <br>";
	}
	sfs_errorsonoff('off');
	
}

?>