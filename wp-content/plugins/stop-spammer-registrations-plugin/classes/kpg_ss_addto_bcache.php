<?php

if (!defined('ABSPATH')) exit;
class kpg_ss_addto_bcache { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
	    extract($stats);
	    extract($options);
		$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		while (count($badips)>$kpg_sp_cache) array_shift($badips);
		$nowtimeout=date('Y/m/d H:i:s',time()-(4*3600) + ( get_option( 'gmt_offset' ) * 3600 ));
		$badips[$ip]=$now;
		foreach($badips as $key=>$data) {
			if ($data<$nowtimeout) {
				unset($badips[$key]);
			}
		}
		$stats['badips']=$badips;
		kpg_ss_set_stats($stats);
		return $badips;
	}
}






?>