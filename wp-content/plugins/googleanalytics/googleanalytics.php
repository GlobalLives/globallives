<?php

/*
 * Plugin Name: Google Analytics
 * Plugin URI: http://wordpress.org/extend/plugins/googleanalytics/
 * Description: Use Google Analytics on your Wordpress site without touching any code, and view visitor reports right in your Wordpress admin dashboard!
 * Version: 2.1.3
 * Author: ShareThis
 * Author URI: http://sharethis.com
 */
if ( !defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( !defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( !defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}
if ( !defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
if ( !defined( 'GA_NAME' ) ) {
	define( 'GA_NAME', 'googleanalytics' );
}
if ( !defined( 'GA_PLUGIN_DIR' ) ) {
	define( 'GA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . GA_NAME );
}
if ( !defined( 'GA_PLUGIN_URL' ) ) {
	define( 'GA_PLUGIN_URL', WP_PLUGIN_URL . '/' . GA_NAME );
}
if ( !defined( 'GA_MAIN_FILE_PATH' ) ) {
	define( 'GA_MAIN_FILE_PATH', __FILE__ );
}
if ( !defined( 'GA_SHARETHIS_SCRIPTS_INCLUDED' ) ) {
	define( 'GA_SHARETHIS_SCRIPTS_INCLUDED', 0 );
}

/**
 * Prevent to launch the plugin within different plugin dir name
 */
if ( !preg_match( '/(\/|\\\)' . GA_NAME . '(\/|\\\)/', realpath( __FILE__ ), $test ) ) {
	echo _( 'Invalid plugin installation directory. Please verify if the plugin\'s dir name is equal to "' . GA_NAME . '".' );

	// To make able the message above to be displayed in the activation error notice.
	die();
}

define( 'GOOGLEANALYTICS_VERSION', '2.1.3' );
include_once GA_PLUGIN_DIR . '/overwrite/ga_overwrite.php';
include_once GA_PLUGIN_DIR . '/class/Ga_Autoloader.php';
include_once GA_PLUGIN_DIR . '/tools/class-support-logging.php';
Ga_Autoloader::register();
Ga_Hook::add_hooks( GA_MAIN_FILE_PATH );

add_action( 'plugins_loaded', 'Ga_Admin::loaded_googleanalytics' );
add_action( 'init', 'Ga_Helper::init' );
