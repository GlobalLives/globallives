<?php

if (!defined('ABSPATH')) exit;

class chkscripts extends be_module { 
	// some scripts need to be Allow Listed. So far wp_cron.php, but maybe some others - ajax?
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
	    $sname=$this->getSname();
	    if(strpos($sname,'wp-cron.php')!==false) return "allow wp-cron";
	   // if(strpos($sname,'admin.php?')!==false) return "allow admin.php?";
	    if(strpos($sname,'admin-ajax.php')!==false) return "allow admin-ajax.php"; // necessary?
	   
		return false;
	}
}
?>