<?php

if (!defined('ABSPATH')) exit;

class chkbbcode { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// searches for bbcodes in post data.
		// bbcodes are the tool of stupid spammers
		$bbcodes=array(
		'[php','[url','[link','[img','[include','[script'
		);
		foreach($post as $key=>$data) {
			foreach($bbcodes as $bb) {
		//sfs_debug_msg("looking for $key - $bb in $data");

				if (stripos($data,$bb)!==false) {
					return "bbcode $bb in $key";
				}
			}
		}
		return false;
	}
}
?>