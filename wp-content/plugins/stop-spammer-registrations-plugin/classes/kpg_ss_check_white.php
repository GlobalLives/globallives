<?php


if (!defined('ABSPATH')) exit;

class kpg_ss_check_white extends be_module { 
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		$email=$post['email'];
		//$p=print_r($post,true);
		//if ($post['email']=='tester@tester.com') {
			//return false; // use to test plugin
		//}
		
		// can't ever block local server because of cron jobs
		$ip=kpg_get_ip(); // we are losing ip occasionally
		// for addons
		$addons=array();
		$addons=apply_filters('kpg_ss_addons_allow',$addons);
		// these are the allow before addons
		// returns array 
		//[0]=class location,[1]=class name (also used as counter),[2]=addon name,
		//[3]=addon author, [4]=addon description
		if (!empty($addons)&&is_array($addons)) {
			foreach($addons as $add) {
				if (!empty($add)&&is_array($add)) {
					$reason=be_load($add);
					if ($reason!==false) {
						// need to log a passed hit on post here.
						kpg_ss_log_good(kpg_get_ip(),$reason,$add[1],$add);	// aded get ip because it might be altered				
						return $reason;
					}
				}
			}
		}
		// checks the list of Allow List items according to the options being set
		// if cloudflare or ip is local then the deny tests for ips are not done.
		$actions=array(
		'chkcloudflare', // moved back as first check because it fixes the ip if it is cloudflare
		'chkadminlog',
		'chkaws',
		'chkgcache',
		'chkgenallowlist',
		'chkgoogle',
		'chkmiscallowlist',
		'chkpaypal',
		'chkscripts',
		//'chkvalidip', // handled in deny testing
		'chkwlem',
		'chkwluserid',
		'chkwlist',
		'chkyahoomerchant'
		);
		foreach ($actions as $chk) {	
			if ($options[$chk]=='Y') {
				$reason=be_load($chk,kpg_get_ip(),$stats,$options,$post);
				if ($reason!==false) {
					// need to log a passed hit on post here.
					kpg_ss_log_good(kpg_get_ip(),$reason,$chk);	
					return $reason;
				}
			} else {
				//sfs_debug_msg('no wl check '.$chk);
			}
		}
		// these are the allow after addons
		// returns array 
		//[0]=class location,[1]=class name (also used as counter),[2]=addon name,
		//[3]=addon author, [4]=addon description

		return false;
	}
}
?>