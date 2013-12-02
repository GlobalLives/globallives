<?php

// Class to replace WPDB by wrapping it.
class WPEngine_WPDB_Wrapper {
	
	private $wpdb;
	
	// Create with existing $wpdb object to wrap it
	public function __construct( $wpdb )
	{
		$this->wpdb = $wpdb;
	}
	
	// All method invocations pass through, and we might do stuff before and after.
	public function __call( $name, $args )
	{
		//$state = $this->pre_call($name,$args);
		$result = call_user_func_array( array($this->wpdb,$name), $args );
		//$this->post_call($name,$args,$result,$state);
		return $result;
	}
	public static function __callStatic( $name, $args )
	{
		//$state = $this->pre_call($name,$args);
		$result = call_user_func_array( array('wpdb',$name), $args );
		//$this->post_call($name,$args,$result,$state);
		return $result;
	}
	
	// Member variables are passed through
	public function __set( $name, $value )
	{
		$this->wpdb->$name = $value;
	}
	public function __get( $name )
	{
		return $this->wpdb->$name;
	}
	public function __isset( $name )
	{
		return isset($this->wpdb->$name);
	}
	public function __unset( $name )
	{
		unset($this->wpdb->$name);
	}
	
	
}

$wpe_wpdb_wrapper = new WPEngine_WPDB_Wrapper( $wpdb );
$wpdb = $wpe_wpdb_wrapper;

