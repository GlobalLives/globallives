<?php

if (!defined('ABSPATH')) exit;
class kpg_ss_addto_gcache { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
	    extract($stats);
	    extract($options);
		$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		while (count($goodips)>$kpg_sp_good) array_shift($goodips);
		$nowtimeout=date('Y/m/d H:i:s',time()-(4*3600) + ( get_option( 'gmt_offset' ) * 3600 ));
		$goodips[$ip]=$now;
		foreach($goodips as $key=>$data) {
			if ($data<$nowtimeout) {
				unset($goodips[$key]);
			}
		}
		$stats['goodips']=$goodips;
		// if we add to good cache we need to delete from bad cache
		if (array_key_exists($ip,$goodips)) {
			unset($goodips[$ip]);
			$stats['goodips']=$goodips;
		}
		kpg_ss_set_stats($stats);
		return goodips;
	}
}






?>