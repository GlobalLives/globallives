<?php

if (!defined('ABSPATH')) exit;
class kpg_ss_remove_gcache { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
	    extract($stats);
	    extract($options);
		while (count($goodips)>$kpg_sp_good) array_shift($goodips);
		$nowtimeout=date('Y/m/d H:i:s',time()-(4*3600) + ( get_option( 'gmt_offset' ) * 3600 ));
		foreach($goodips as $key=>$data) {
			if ($data<$nowtimeout) {
				unset($goodips[$key]);
			}
			if ($key==$ip) {
				unset($goodips[$key]);
			}
		}
		$stats['goodips']=$goodips;
		kpg_ss_set_stats($stats);
		return $goodips; // return the array so ajax can show it
	}
}






?>