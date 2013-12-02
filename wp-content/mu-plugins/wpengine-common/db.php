<?php
define("SAVEQUERIES",true);
if ( is_multisite() ) {
	add_action('network_admin_menu','wpe_db_debug_menu',100);
} else {
	add_action('admin_menu', 'wpe_db_debug_menu',100); 
}

add_action('shutdown','wpe_db_log');

/*
 * Debug DB Admin Menu
 */
function wpe_db_debug_menu() {
 	
 	// Variations due to type of site
  if ( is_multisite() ) {
      $capability = 'manage_network';
  } else {
      $capability = 'manage_options';
  }
	
	//add the debug admin menu link
 	add_submenu_page( 'wpengine-common', 'DB Debug', 'DB Debug', $capability, 'wpe-db-debug','wpe_db_debug_page' , 100 );
 			
}


/*
 * Output the admin page
 */
function wpe_db_debug_page() {
	$data = array('queries'=>get_option('wpe_debug_queries'));
	$data['show'] = @$_GET['show']?$_GET['show']:'slowest';

	//create slowest array
	$slowest = array(); 
	foreach($data['queries'] as $k => $v) {
		$v['query'] = $k;
		$time = $v['time'];
		$slowest["$time"] = $v;
	}
	krsort($slowest);
	//create frequent array
	$temp = array(); 
	foreach($data['queries'] as $k => $v) {
		$v['query'] = $k;
		$count = $v['count'];
		$temp["$count"] = $v;
	}
	krsort($temp);
	$data['plugin'] = WpeCommon::instance();
	$data['slowest'] = $slowest;
	$data['frequent'] = $temp;
	WpeCommon::view('admin/debug-db',$data);
}

/*
 * The actual debug action
 */
function wpe_db_log() {
	global $wpdb;
	
	//delete_option('wpe_debug_queries');
	$wpe_db = (array) get_option('wpe_debug_queries');
	$patterns = array();
	if($wpdb->queries) {
		foreach($wpdb->queries as $query) {
		
			//clean up the query to make it easier to spot patterns
			$q = trim(preg_replace("#\/\*(.+)\*\/#",'',$query[0]));
			$q = preg_replace("#(\d+)|(\d+,)#",'%d',$q);
			$q = preg_replace("#IN\s\([%d](.)+\)#",'IN ( %d )',$q);
			$q = preg_replace("#\('.*'(,|)\)#",'( %s )',$q);
			
			//if dont't already have the query logged add it
			if(!array_key_exists($q,$wpe_db)) {
				$wpe_db[$q] = array(
					'time'	=> $query[1],
					'trace'	=> $query[2],
					'count'	=> 1
				);
			//else update the count and average the time
			} else {
				$wpe_db[$q]['count']++;
				$wpe_db[$q]['time'] = number_format(($wpe_db[$q]['time'] + $query[1]) / 2,10 );
			}
		}
		
	}
	//update the stored log.
	update_option('wpe_debug_queries',$wpe_db);
}?>