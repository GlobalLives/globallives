<?php

if (!defined('ABSPATH')) exit;

class chkadminlog extends be_module { 
	// when logging in we need to know if this is a valid login
	// let the user login and if he fails - then we block and log him
	// this is a Allow List option!
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		$sname=$this->getSname();
		if (!class_exists('GoogleAuthenticator')&&strpos($sname,'wp-login.php')!==false&&function_exists('wp_authenticate')) {
			$log=$post['author'];
			$pwd=$post['pwd'];
			if (empty($log)||empty($pwd)) return false;
			$user=@wp_authenticate($log,$pwd);
			
			if (!is_wp_error($user)) { // user login is good
				return 'authenticated user login';
			}
			return false;
		}
		return false;
	}
}
?>