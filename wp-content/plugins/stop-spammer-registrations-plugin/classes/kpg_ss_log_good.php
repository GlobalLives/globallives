<?php
// adds to the good cache and log

if (!defined('ABSPATH')) exit;

class kpg_ss_log_good extends be_module { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// are we getting stats?
	    extract($stats);
		extract($post);
		
		$sname=$this->getSname();
	    $now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		// updates counters. Adds to log list. Adds to good cache. Then updates stats when done
		// start with the counters - does some extra checks in case the stats file gets corrupted
		if (array_key_exists('cntpass',$stats)) $stats['cntpass']++; else $stats['cntpass']=1;
		// now the cache - need to purge it for time and length
		$kpg_sp_good=$options['kpg_sp_good'];
		$goodips[$ip]=$now;
		asort($goodips);
		while (count($goodips)>$kpg_sp_good) array_shift($goodips);
		$nowtimeout=date('Y/m/d H:i:s',time()-(4*3600) + ( get_option( 'gmt_offset' ) * 3600 ));
		foreach($goodips as $key=>$data) {
			if ($data<$nowtimeout) {
				unset($goodips[$key]);
			}
		}
		$stats['goodips']=$goodips;
		// now we need to log the ip and reason
		$blog='';
		if (function_exists('is_multisite') && is_multisite()) {
			global $blog_id;
			if (!isset($blog_id)||$blog_id!=1) {
				$blog=$blog_id;
			}
		}

		// 
		$kpg_sp_hist=$options['kpg_sp_hist'];
		while (count($hist)>$kpg_sp_hist) array_shift($hist);
		$hist[$now]=array($ip,$email,$author,$sname,$reason,$blog);
		
		
		$stats['hist']=$hist;
		if (array_key_exists('addon',$post)) {
			kpg_ss_set_stats($stats,$post['addon']); // from a plugin
		} else {
			// have to figure out why we are here - it is because registration did this - try to fix.
		}
	}


}



?>