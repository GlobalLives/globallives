<?php
//class to handle wpe-related ajax
class Wpe_Ajax {
	/**
	* Since this class has to be hooked statically into the wp_ajax_ hook we don't need the standard constructor (though we may want it later). 
	* This class assumes a POST request containing the wpe-action variable and runs a method based on whether it is set.
	*/
	public static function instance() {
		$method = str_replace('-','_',$_REQUEST['wpe-action']);
		if( method_exists( 'Wpe_Ajax', $method ) ) {
			call_user_func_array(array('Wpe_Ajax',$method), array());
		} else {
			die('Method not found');
		}
		die();
	}

	/**
	* Hide a pointer 
	* If a pointer variable is sent with this request then the value is added to the usermeta
	*/ 
	public function hide_pointer() {
		if( !is_user_logged_in() )
			wp_die("Must be an autheticated user");
 
		$pointer = $_REQUEST['pointer'];
		$user = wp_get_current_user(); 
		
		add_user_meta($user->ID,'hide-pointer', esc_attr( $_REQUEST['pointer'] ) );
	}

	/**
	* Lookup Tables
	* 
	*/
	public function lookup_tables() {
		global $wpdb;
		$result = $wpdb->get_col("SHOW TABLES;");
		print json_encode($result);
	}

	/**
	* Deploy From Staging
	* Sends the api request to deploy from staging
	*/
	public function deploy_staging() {
		if( !is_user_logged_in() )
                        wp_die("Must be an autheticated user");

                if( ! current_user_can('administrator') )
                        wp_die("Must be an administrator");

		if( !defined("PWP_NAME") OR !defined('WPE_APIKEY') ) 
			echo "This process could not be started.";

		require_once(WPE_PLUGIN_DIR.'/class-wpeapi.php');
		
		$db_mode = @$_REQUEST['db_mode'] ?: 'default';		
		$email = @$_REQUEST['email'] ?: get_option('admin_email');		
		$tables = @$_REQUEST['tables'] ?: false;

		$api = new WPE_API();
		$api->set_arg('method','deploy-from-staging');
		$api->set_arg('db_mode', esc_attr($db_mode) );
		$api->set_arg('email',	esc_attr($email) );
		if( $tables )
			$api->set_arg('tables', implode('&',$tables));
		$api->set_arg('headers', "Host:api.wpengine.com");
		$api->post();
		if( !$api->is_error() ) {
			echo "Your request has been submitted. You will receive an email once it has been processed";
		} else {
			echo $api->is_error();
		}
	}

}
