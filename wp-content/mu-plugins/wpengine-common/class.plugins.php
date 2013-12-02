<?php 
/** 
 * Class PluginsConfig Handles customization/configurations needed for third party plugins like woocommerce
 */
if( !class_exists("PluginsConfig") ) {
	class PluginsConfig {
		static $plugins = array( 
			'woocommerce/woocommerce.php' 				=> "woocommerce", 
			"easy-digital-downloads/easy-digital-downloads.php" 	=> "easy-digital-downloads"
		);

		var $skip = array();

		public function __construct() {}
		
		public static function sniff() {
			foreach( self::$plugins as $plugin => $codename ) {
				if( is_plugin_active($plugin) ) {	
					PluginsConfig::notify($codename);		
				}
			}
		}
		
		public static function notify($codename) {
			//not using our class cuz it's throwing errors. 
			$uri = "https://api.wpengine.com/1.2/index.php";
			$uri = add_query_arg( array( 
				"method"=>"nginx-profile-add", 
				"profile"=>$codename, 
				"location"=>"nginx-before-in-location",
				"account_name"=>PWP_NAME,
				"wpe_apikey"=>WPE_APIKEY 
				), 
			$uri);
			$resp = wp_remote_get($uri);
			$r = json_decode($resp['body'],1);
			if( @$r['error'] ) {
				error_log("WPE API [error]: ".$r['error_msg']);
			} elseif( @$r['success'] ) {
				error_log("WPE API [success]:  $codename ".$r['data']);
			}
		}

	}
}
