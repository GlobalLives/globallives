<?php

if (!defined('ABSPATH')) exit;

class chkbluserid extends be_module { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// checks the user. Checks Author or login id
		$this->searchname='Allow List Email';
		$user=$post['author'];
		if (empty($user)) return false;
		$blist=$options['blist'];
		return $this->searchList($user,$blist);
	}
}
?>