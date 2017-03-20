<?php

if (!defined('ABSPATH')) exit;

class chkwluserid extends be_module { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// checks the user. Dangerous to allow a white listed user. Spammers could use it.
		$this->searchname='Allow List Email';
		$user=$post['author'];
		if (empty($user)) return false;
		$wlist=$options['wlist'];
		return $this->searchList($user,$wlist);
	}
}
?>