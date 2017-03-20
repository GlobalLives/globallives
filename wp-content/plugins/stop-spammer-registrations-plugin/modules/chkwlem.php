<?php

if (!defined('ABSPATH')) exit;

class chkwlem extends be_module { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// checks the email. Not sure I want to allow a Allow List on email. Maybe won't include.
		$this->searchname='Allow List Email';
		$email=$post['email'];
		if (empty($email)) return false;
		$wlist=$options['wlist'];
		return $this->searchList($email,$wlist);
	}
}
?>