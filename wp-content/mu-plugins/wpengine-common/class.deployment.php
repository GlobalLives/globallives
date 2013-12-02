<?php
/*
 * WpeDeployment 
 *  handles deployment related functions
 *
 */
class WpeDeployment {
	public $status_file;
	public $status;
	public $time;
	public $notice = false;
	public $log = false;
	public $warn = false;
	
	public function __construct() {
		//if a deployment is underway
		$this->status_file = ABSPATH.'/wpe-deploy-status-'.PWP_NAME;

		//stop here if there's no status file
		if( !file_exists($this->status_file) ) return;
		
		//check status and either delete the status file if it is more than five minutes old, else post a nag message
		$this->status = @file_get_contents($this->status_file);
		$this->time = filemtime($this->status_file);
		if( strstr($this->status, 'Deploy Completed') )  {
			$compare = time() - $this->time;
			//if the deploy status is older than five minutes
			if( $compare > 60 * 5 ) { 
				@unlink(ABSPATH.'/wpe-deploy-status-'.PWP_NAME);
			} else {
				add_action('wpe_notices',array($this,'nag'));
			}

		} else {
			$this->warn = 1;
		}

	}

	/*
	 * Singleton Function
	 */	
	static public function instance() {
		static $instance;
		if( $instance ) return $instance;
		$instance = new WpeDeployment();
		return $instance;
	}

	/*
	 * Used to notify javascript that a deploy is under way.	
	 */
	static public function warn() {
		$instance = WpeDeployment::instance();
		return $instance->warn;
	}

	/*
	 * Add an admin notice about the recent deployment
	 */
	public function nag($noticeObj) {
		$noticeObj->notices['messages'][] = array(
			'id'		=> 'deploy-notice-'.date('Y-m-d'),
			'starts' 	=> date('Y-m-d h:i:s', time() - 600),
			'ends'	 	=> date('Y-m-d h:i:s', time() + 600),
			'class'		=> 'alert',
			'type'		=> 'normal',
			'message'	=> 'A deployment was recently completed for this site. If you need to revert to previous state you can do so via the WP Engine <a href="http://my.wpengine.com">User Portal</a>.',
			'force'		=> 1,
		);
	}	

	public function __destruct() { } 
}

