<?php
/*
  Plugin Name: WP Engine System
  Plugin URI: http://wpengine.com/plugins
  Description: WP Engine-specific services and options
  Author: WP Engine
  Version: 2.1.1
  Changelog: (see changelog.txt)
 */

// Our plugin
define( 'WPE_PLUGIN_BASE', __FILE__ );

// Allow changing the version number in only one place (the header above)
$plugin_data = get_file_data( WPE_PLUGIN_BASE, array( 'Version' => 'Version' ) );
define( 'WPE_PLUGIN_VERSION', $plugin_data['Version'] );

//setup wpe plugin url
if(is_multisite()) {
	define('WPE_PLUGIN_URL', network_site_url('/wp-content/mu-plugins/wpengine-common'));
} else {
	define( 'WPE_PLUGIN_URL', content_url('/mu-plugins/wpengine-common') );
}

require_once(dirname(__FILE__)."/wpengine-common/plugin.php");

// Login-Lockout plugin, indirect here so that it works with mu-plugin rules
$lla_path = dirname(__FILE__)."/limit-login-attempts/limit-login-attempts.php";
if ( file_exists($lla_path) ) { require_once($lla_path); }

// Prevent weird problems with logging in due to Object Caching
// example: password has been changed, but Object Cache still holds old password, and therefore prevents login
if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
    add_action( 'login_head', 'wp_cache_flush' );
}


// Useful for multisite: Add a Site ID column to the Network Admin > Sites page
if ( is_multisite() ) {
    add_filter( 'wpmu_blogs_columns', 'wpe_site_id' );
    function wpe_site_id( $columns ) {
        $columns['site_id'] = __( 'ID', 'site_id' );
        return $columns;
    }

    add_action( 'manage_sites_custom_column', 'wpe_site_id_columns', 10, 3 );
    add_action( 'manage_blogs_custom_column', 'wpe_site_id_columns', 10, 3 );
    function wpe_site_id_columns( $column, $blog_id ) {
        if ( $column == 'site_id' ) {
            echo $blog_id;
        }
    }
}

//temporary location for login-protection script
//@TODO should be it's own plugin probably

add_filter( 'site_url', 'wpe_filter_site_url', 0, 4 );
/**
 * Filter the value returned for 'site_url'
 *
 * This function will only filter the url if it is the 'login_post' scheme. If
 * not, then the value is unchanged
 *
 * @since 1.0
 *
 * @param string $url The unfiltered URL to return
 * @param string $path The relative path
 * @param string $scheme The scheme to use, such as http vs. https
 * @param int $blog_id The blog ID for the URL
 * @return string The new URL
 */
function wpe_filter_site_url( $url, $path, $scheme, $blog_id ) {
    // We only want to filter the login_post scheme
    if ( $scheme == 'login_post' ) {
	$url = add_query_arg( array( 'wpe-login'=> PWP_NAME ) , $url );
    }
    return $url;
}

// Disable core updates and emails.
add_filter( 'auto_update_core', '__return_false' );
add_filter( 'auto_core_update_send_email', '__return_false' );
