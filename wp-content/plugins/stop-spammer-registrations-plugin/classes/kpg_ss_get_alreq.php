<?php
// this does the get for the tbody in allow requests
if (!defined('ABSPATH')) exit;
class kpg_ss_get_alreq { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		extract($stats);
		extract($options);
		
		$trash=KPG_SS_PLUGIN_URL.'images/trash.png';
		$tdown=KPG_SS_PLUGIN_URL.'images/tdown.png';
		$tup=KPG_SS_PLUGIN_URL.'images/tup.png'; // fix this
		$whois=KPG_SS_PLUGIN_URL.'images/whois.png'; // fix this

		$ajaxurl=admin_url('admin-ajax.php');
		$show='';
		$nwlrequests=array();
		//sfs_debug_msg('wlrequests '.print_r($wlrequests,true));
		foreach ($wlrequests as $key => $value) {
			$sw=true;
			if (!empty($ip)&& $ip!='x') {
				if ($key==$ip) { 
				   //sfs_debug_msg("wlreq matched '$ip'");
					$sw=false;
				}
				if ($ip==trim($value[0])) { // match ip
				   //sfs_debug_msg("wlreq val 0 '$value[0]'");
					$sw=false;
				}				
				if ($ip==trim($value[1])) { // match email
				   //sfs_debug_msg("wlreq val 1 '$value[1]'");
					$sw=false;
				}
			}
			$container='wlreq';
			if($sw) {
				$nwlrequests[$key]=$value;
				$show.="<tr style=\"background-color:white;\">";
				
				$trsh="<a href=\"\" onclick=\"sfs_ajax_process('$key','wlreq','delete_wl_row','$ajaxurl');return false;\" title=\"Delete row\" alt=\"Delete row\" ><img src=\"$trash\" width=\"16px\" /></a>";
				$addtodeny="<a href=\"\"onclick=\"sfs_ajax_process('$value[0]','$container','add_black','$ajaxurl');return false;\" title=\"Add $value[0] to Deny List\" alt=\"Add $value[0] to Deny List\" ><img src=\"$tdown\" width=\"16px\" /></a>";
				$addtoallow="<a href=\"\"onclick=\"sfs_ajax_process('$value[0]','$container','add_white','$ajaxurl');return false;\" title=\"Add $value[0] to allow List\" alt=\"Add $value[0] to allow List\" ><img src=\"$tup\" width=\"16px\" /></a>";

				$show.="<td>$key $trsh $addtodeny $addtoallow</td>";
				
				
				$who="<a title=\"whois\" target=\"_stopspam\" href=\"http://lacnic.net/cgi-bin/lacnic/whois?lg=EN&query=$value[0]\"><img src=\"$whois\" width=\"16px\"/></a> ";
				$trsh="<a href=\"\" onclick=\"sfs_ajax_process('$value[0]','wlreq','delete_wlip','$ajaxurl');return false;\" title=\"Delete  all $value[0]\" alt=\"Delete  all $value[0]\" ><img src=\"$trash\" width=\"16px\" /></a>";
				
				$show.="<td>$value[0] $who $trsh</td>";
				$trsh="<a href=\"\" onclick=\"sfs_ajax_process('$value[1]','wlreq','delete_wlem','$ajaxurl');return false;\" title=\"Delete all $value[1]\" alt=\"Delete all $value[1]\" ><img src=\"$trash\" width=\"16px\" /></a>";
				
				$show.="<td><a target=\"_stopspam\" href=\"mailto:$value[1]?subject=Website access\">$value[1] $trsh</td>";
				$show.="<td>$value[3]</td>";
				$show.="<td>$value[4]</td>";
				$show.="<tr>";
			}
		}
		$stats['wlrequests']=$nwlrequests;
		//sfs_debug_msg('nwlrequests '.print_r($nwlrequests,true));
		if (array_key_exists('addon',$post)) {
			kpg_ss_set_stats($stats,$post['addon']);
		} else {
			kpg_ss_set_stats($stats);
		}
		return $show;
		
		
	}
}





?>