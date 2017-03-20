<?php 
/** 
 * Class PluginsConfig Handles customization/configurations needed for third party plugins like woocommerce
 */
if( !class_exists("PluginsConfig") ) {
	class PluginsConfig {
		static $plugins = array( 
			'woocommerce/woocommerce.php' 				=> 'woocommerce', 
			'easy-digital-downloads/easy-digital-downloads.php' 	=> 'easy-digital-downloads',
			'backupbuddy/backupbuddy.php' 				=> 'backupbuddy',
		);

		var $skip = array();

		public function __construct() {}

		public static function sniff() {
			foreach( self::$plugins as $plugin => $codename ) {
				if( is_plugin_active($plugin) ) {
					PluginsConfig::notifyActive($codename);
				}
			}
		}

		public static function pluginActivated($plugin) {
			if (array_key_exists($plugin, self::$plugins)) {
				self::notifyActive(self::$plugins[$plugin]);
			}
		}

		public static function pluginDeactivated($plugin) {
			if (array_key_exists($plugin, self::$plugins)) {
				self::notifyInactive(self::$plugins[$plugin]);
			}
		}

		private static function notifyActive($codename) {
			self::doApiRequest('nginx-profile-add', $codename);
		}

		private static function notifyInactive($codename) {
			self::doApiRequest('nginx-profile-remove', $codename);
		}
		
		private static function doApiRequest($method, $codename) {
			//not using our class cuz it's throwing errors. 
			$uri = "https://api.wpengine.com/1.2/index.php";
			$uri = add_query_arg( array(
				"method"=>$method,
				"profile"=>$codename,
				"location"=>"nginx-before-in-location",
				"account_name"=>PWP_NAME,
				"wpe_apikey"=>WPE_APIKEY
				),
			$uri);
			$resp = wp_remote_get($uri);

			if( is_wp_error( $resp ) ) {
				error_log( "doApiRequest wp_remote_get wp_error: " . $resp->get_error_message() );
			}
			else {
				$r = json_decode($resp['body'],1);
				if( isset( $r['error'] ) ) {
					error_log( "WPE API [error]: " . $r['error_msg'] );
				} elseif( isset( $r['success'] ) ) {
					error_log( "WPE API [success]: $codename " . $r['data'] );
				}
			}
		}
	}
}
/*
 * Throttle remote connections for EWWW Image Optimizer Cloud users.
 */
function wpe_ewww_cloud_throttle() {
	if ( is_plugin_active( 'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php' ) ) {
		set_option( 'ewww_image_optimizer_delay', 5 );
	}
}
add_action( 'admin_init', 'wpe_ewww_cloud_throttle' ); 
