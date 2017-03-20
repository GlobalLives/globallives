<?php

if (!defined('ABSPATH')) exit;
class kpg_ss_get_bcache { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// gets the innerhtml for cache - same as get gcache except for names
		$badips=$stats['badips'];
		$cachedel='delete_bcache';
		$container='badips';
		$trash=KPG_SS_PLUGIN_URL.'images/trash.png';
		$tdown=KPG_SS_PLUGIN_URL.'images/tdown.png';
		$tup=KPG_SS_PLUGIN_URL.'images/tup.png'; 
		$whois=KPG_SS_PLUGIN_URL.'images/whois.png'; 
		$stophand=KPG_SS_PLUGIN_URL.'images/stop.png';
		$search=KPG_SS_PLUGIN_URL.'images/search.png'; 
		$ajaxurl=admin_url('admin-ajax.php');
		$show='';
		foreach ($badips as $key => $value) {
			$who="<a title=\"whois\" target=\"_stopspam\" href=\"http://lacnic.net/cgi-bin/lacnic/whois?lg=EN&query=$key\"><img src=\"$whois\" width=\"16px\"/></a>";
			$show.="<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a> ";
			// try ajax on the delete from bad cache
			$onclick="onclick=\"sfs_ajax_process('$key','$container','$cachedel','$ajaxurl');return false;\"";
			$show.=" <a href=\"\" $onclick title=\"Delete $key from Cache\" alt=\"Delete $key from Cache\" ><img src=\"$trash\" width=\"16px\" /></a> ";			
			$onclick="onclick=\"sfs_ajax_process('$key','$container','add_black','$ajaxurl');return false;\"";
			$show.=" <a href=\"\" $onclick title=\"Add to $key Deny List\" alt=\"Add to Deny List\" ><img src=\"$tdown\" width=\"16px\" /></a> ";
			$onclick="onclick=\"sfs_ajax_process('$key','$container','add_white','$ajaxurl');return false;\"";
			$show.=" <a href=\"\" $onclick title=\"Add to $key Allow List\" alt=\"Add to Allow List\" ><img src=\"$tup\" width=\"16px\" /></a>";
			$show.=$who;
			$show.="<br/>";
		}
		return $show;
	}
}






?>