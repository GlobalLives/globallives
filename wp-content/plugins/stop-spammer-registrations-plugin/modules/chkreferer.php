<?php

if (!defined('ABSPATH')) exit;

class chkreferer extends be_module {
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		$this->searchname='HTTP_REFERER check';
		// only check this on posts, but we can double check
		if (!$_SERVER['REQUEST_METHOD'] === 'POST') return false;
		$ref='';
		// made it this far. there is a post
		if (array_key_exists('HTTP_REFERER',$_SERVER)) {
			$ref=$_SERVER['HTTP_REFERER'];
		}
		$ua='';
		if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
			$ua=$_SERVER['HTTP_USER_AGENT'];
		}
		$a=array(false,'');
		if (strpos(strtolower($ua),'iphone')===false&&strpos(strtolower($ua),'ipad')===false) return false;
		// require the referer
		// check to see if our domain is found in the referer
		$host=$_SERVER['HTTP_HOST'];
		if (empty($ref)) return 'Missing HTTP_REFERER';
		if (empty($host)) return 'Missing HTTP_HOST';
		// some servers have an empty host for some reason
		// some servers and links from https to http and back don't send a referer
		if (empty($ref)) return false; // had to do this because sometimes legit ones are null?
		if (strpos(strtolower($ref),strtolower($host))===false) {
			// bad referer - must be from this site.
			return "Invalid HTTP_REFERER";
		}

		return false;
	}
}
?>
