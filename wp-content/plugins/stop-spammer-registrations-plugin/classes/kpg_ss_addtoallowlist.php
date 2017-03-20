<?php

if (!defined('ABSPATH')) exit;

class kpg_ss_addtoallowlist { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// adds to allowlist - used to add admin to allowlist or to add a comment author to allowlist
		$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		$wlist=$options['wlist'];
		//$ip=kpg_get_ip();
		// add this ip to your Allow List
		if (!in_array($ip,$wlist)) $wlist[]=$ip;
		$options['wlist']=$wlist;
		kpg_ss_set_options($options);
		
		// need  to remove from caches
		$badips=$stats['badips'];
		if (array_key_exists($ip,$badips)) {
			unset($badips[$ip]);
			$stats['badips']=$badips;
		}
		$goodips=$stats['goodips'];
		if (array_key_exists($ip,$goodips)) {
			unset($goodips[$ip]);
			$stats['goodips']=$goodips;
		}
		kpg_ss_set_stats($stats);
		return false;
	}
}
?>