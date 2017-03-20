<?php

if (!defined('ABSPATH')) exit;

class chklong { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		$this->searchname='email length';
		if (array_key_exists('email',$post)) {
			$email=$post['email'];
			if (!empty($email)) {
				if (strlen($email)>64) {
					return "email too long:$email";
				}
				if (strlen($email)<5) {
					return "email too short:$email";
				}
			}
		}
		if (array_key_exists('author',$post)) {
			if (!empty($post['author'])) {
				$author=$post['author'];
				if (strlen($post['author'])>64) {
					return "author too long:$author";
				}
				// short author is OK?.
				if (strlen($post['author'])<3) {
					return "author too short:$author";
				}
			}
		}
		if (array_key_exists('psw',$post)) {
			if (!empty($post['psw'])) {
				$psw=$post['psw'];
				if (strlen($post['psw'])>32) {
					return "Password too long: $psw";
				}
			}
		}
		return false;
	}
}
?>