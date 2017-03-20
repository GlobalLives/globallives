<?php
/*
  Plugin Name: WP Engine System
  Plugin URI: http://wpengine.com/plugins
  Description: WP Engine-specific services and options
  Author: WP Engine
  Version: 3.2.1
  Changelog: (see changelog.txt)
 */

// Our plugin
define( 'WPE_PLUGIN_BASE', __FILE__ );

// Allow changing the version number in only one place (the header above)
$plugin_data = get_file_data( WPE_PLUGIN_BASE, array( 'Version' => 'Version' ) );
define( 'WPE_PLUGIN_VERSION', $plugin_data['Version'] );

require_once(dirname(__FILE__)."/wpengine-common/plugin.php");

if ( getenv( 'WPE_HEARTBEAT_AUTOSAVE_ONLY' ) == 'on' ) {
	require_once __DIR__ . '/wpengine-common/class.heartbeatthrottle.php';
	$heartbeat_throttle = new WPE_Heartbeat_Throttle();
	$heartbeat_throttle->register();
}

// Force destroy login cookies if invalid, expired, etc. This prevents stale cookies (which never expire
// in the browser) from cache busting.
// This feature is controlled by an environment variable, but defaulted to on.
if ( getenv( 'WPENGINE_CLEAR_EXPIRED_COOKIES' ) !== 'off' ) {
	require_once __DIR__ . '/wpengine-common/class.cookies.php';
	\wpe\plugin\Cookies::register_hooks();
}


add_action( 'parse_request', 'wpesec_prevent_user_enumeration', 999 );
function wpesec_prevent_user_enumeration() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		return;
	if ( is_admin() )
		return;
	if ( 0 !== preg_match( '#wp-comments-post#', $_SERVER['REQUEST_URI'] ) )
		return;
	if ( ! isset( $_REQUEST['author'] ) )
		return;
	if ( ! is_numeric( $_REQUEST['author'] ) )
		return;

	error_log( 'preventing possible attempt to enumerate users' );
	if ( ! headers_sent() ) {
		header( 'HTTP/1.0 403 Forbidden' );
	}
	die;
}

// Enforce sanity checking on wp_sessions. This became a problem when EDD had a bug that had sessions
// expiring in the year 2058.
require_once __DIR__ . '/wpengine-common/class.sessionsanity.php';
$wpe_session_sanity = new \wpe\plugin\SessionSanity();
$wpe_session_sanity->register_hooks();

// Custom site preview
require_once( __DIR__ . '/wpengine-common/class.site-preview.php' );
\WPE\Site_Preview::get_instance()->register_hooks();

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

//Some user-plugins have site_filters that don't always persist the query args that we set in wpe_filter_site_url
//So let's up our priority to 99 (default 10) so that our filter gets run later than the other plugin filters
add_filter( 'site_url', 'wpe_filter_site_url', 99, 4 );
add_filter( 'network_site_url', 'wpe_filter_site_url', 99, 3 );
/**
 * Filter the value returned for 'site_url'
 *
 * This function will only filter the url if it is the 'login_post' scheme. If
 * not, then the value is unchanged
 *
 * @since 1.0
 *
 * @param string $url     The unfiltered URL to return
 * @param string $path    The relative path
 * @param string $scheme  The scheme to use, such as http vs. https
 * @param int    $blog_id The blog ID for the URL
 * @return string The new URL
 */
function wpe_filter_site_url( $url, $path, $scheme, $blog_id = 1 ) {
	// Filter the login_post scheme
	$changeme = array('login_post');
	if ('4.4' === get_bloginfo('version')) { // XXX possible regression in 4.4 release
		$changeme[] = 'login';
	}
	if ( in_array($scheme, $changeme ) ) {
		$url = add_query_arg( array( 'wpe-login'=> PWP_NAME ) , $url );
	}
	// Filter comment posts - from wp-includes/comment-template.php form action string
	elseif ( $path == '/wp-comments-post.php' ) {
		$url = add_query_arg( array( 'wpe-comment-post'=> PWP_NAME ) , $url );
	}

	return $url;
}

if ( ! function_exists( 'current_action' ) ) :
/**
 * Retrieve the name of the current action.
 *
 * This function was added in WordPress 3.9, but some sites
 * are still running old versions of WordPress and therefore need
 * us to define this function.
 *
 * The current_filter() function has been around for a long
 * time (2.5) and so there shouldn't be any issue with calling
 * that function.
 *
 * @uses  current_filter()
 *
 * @return string Hook name of the current action.
 */
function current_action() {
	return current_filter();
}
endif;

/*
 * Disable core updates and emails.
 *
 * WP Engine handles WordPress updates. Due to our security setup auto-updates will fail anyway. Better to turn them
 * off completely than to have site owners receive emails about a failed update.
 *
 * These filters are all set to a priority of 9999 so that we're more likely to get the last say in the matter.
 *
 * - 'auto_update_core' determines whether an auto update is even attempted at all.
 * - 'auto_update_translation' determines whether to auto update language files.
 * - 'auto_core_update_send_email' determines whether to send a "success", "fail", or "critical fail" email after
 *   an auto update is attempted. Setting this to false is a bit redundant after turning off auto-updates
 *   altogether, but we're just being sure.
 * - 'send_core_update_notification_email' determines whether to alert a site admin that an update is available.
 */
add_filter( 'auto_update_core', '__return_false', 9999 );
add_filter( 'auto_update_translation', '__return_false', 9999 );
add_filter( 'auto_core_update_send_email', '__return_false', 9999 );
add_filter( 'send_core_update_notification_email', '__return_false', 9999 );
