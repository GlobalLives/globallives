<?php
/**
 * Monitor what the requests going to wp-admin/admin-ajax.php
 *
 * This is useful for determining the cause of high traffic to the admin-ajax.php file
 *
 * @package Monitor Admin Ajax
 * @author Donovan Hernandez
 */

// If it's not a POST request, just move along
if ( $_SERVER['REQUEST_METHOD'] != "POST" ) {
	return;
}

// We only want to monitor admin-ajax.php hits.
if ( strpos( $_SERVER['PHP_SELF'], 'wp-admin/admin-ajax.php' ) === false ) {
	return;
}

// Load the class
if ( ! class_exists( 'Monitor_Admin_Ajax', false ) ) {
	require_once( WPE_PLUGIN_DIR . '/class-monitor_admin_ajax.php' );
}

$logger = Monitor_Admin_Ajax::get_instance();
$logger->write_log();
