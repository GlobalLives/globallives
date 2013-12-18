<?php
/**
 * Required by WordPress.
 *
 * Keep this file clean and only use it for requires.
 */

require( get_template_directory() . '/inc/keys.php' );		// Field Keys for Advanced Custom Fields
require( get_template_directory() . '/inc/ajax.php' );		// AJAX functions using admin-ajax.php
require( get_template_directory() . '/inc/layouts.php' );	// Wrap the theme in layout.php
require( get_template_directory() . '/inc/menus.php' );		// Add custom menus
require( get_template_directory() . '/inc/queue.php' );		// Enqueue styles and scripts
require( get_template_directory() . '/inc/types.php' );		// Add custom post types
require( get_template_directory() . '/inc/widgets.php' );	// Register custom widgets
require( get_template_directory() . '/inc/helpers.php' );	// Other useful functions
require( get_template_directory() . '/inc/settings.php' );	// Add a Theme Settings page to admin


# Additional Settings

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'remove_admin_bar');