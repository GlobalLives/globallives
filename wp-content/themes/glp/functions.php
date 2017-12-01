<?php

foreach (glob(get_template_directory() . '/inc/*.php') as $filename) {
	require $filename;
}

# Additional Settings

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'remove_admin_bar');

function my_deregister_styles() {
	wp_deregister_style( 'wp-admin' );
}
add_action('wp_print_styles', 'my_deregister_styles', 100);

/**
* Disable the emoji's
*/
function disable_emojis() {
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

/**
* Filter function used to remove the tinymce emoji plugin.
* 
* @param array $plugins 
* @return array Difference betwen the two arrays
*/
function disable_emojis_tinymce( $plugins ) {
if ( is_array( $plugins ) ) {
return array_diff( $plugins, array( 'wpemoji' ) );
} else {
return array();
}
}

/**
* Remove emoji CDN hostname from DNS prefetching hints.
*
* @param array $urls URLs to print for resource hints.
* @param string $relation_type The relation type the URLs are printed for.
* @return array Difference betwen the two arrays.
*/
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
if ( 'dns-prefetch' == $relation_type ) {
/** This filter is documented in wp-includes/formatting.php */
$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

$urls = array_diff( $urls, array( $emoji_svg_url ) );
}

return $urls;
}

// disable default dashboard widgets
function disable_default_dashboard_widgets() {
  $user = wp_get_current_user();
  if ( in_array( 'editor', (array) $user->roles ) || in_array( 'author', (array) $user->roles ) ) {
    // disable default dashboard widgets
    //remove_meta_box('dashboard_right_now', 'dashboard', 'core');
    remove_meta_box('dashboard_activity', 'dashboard', 'core');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
    remove_meta_box('dashboard_plugins', 'dashboard', 'core');
    remove_meta_box('dashboard_right_now', 'dashboard', 'core');
    remove_meta_box('dashboard_primary', 'dashboard', 'core');
    remove_meta_box('wpe_dify_news_feed', 'dashboard', 'core');

    remove_meta_box('simple_history_dashboard_widget', 'dashboard', 'normal');
    remove_meta_box('tribe_dashboard_widget', 'dashboard', 'normal');
  }
}
add_action('admin_menu', 'disable_default_dashboard_widgets');

/**
 * vpm_default_hidden_meta_boxes
 */
function vpm_default_hidden_meta_boxes( $hidden, $screen ) {
    // Grab the current post type
    $post_type = $screen->post_type;

    $hidden = array(
        'dashboard_quick_press'
    );

    // If we're on a 'post'...
    if ( $post_type == 'post' ) {
        // Define which meta boxes we wish to hide
        $hidden = array(
            'dashboard_quick_press'
        );
        // Pass our new defaults onto WordPress
        return $hidden;
    }
    // If we are not on a 'post', pass the
    // original defaults, as defined by WordPress
    return $hidden;
}
add_action( 'default_hidden_meta_boxes', 'vpm_default_hidden_meta_boxes', 10, 2 );