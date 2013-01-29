<?php
/**
 * Scripts and stylesheets
 */

function glp_queue() {
	wp_enqueue_style('glp_style', get_template_directory_uri() . '/css/style.min.css', false, null);

	// jQuery is loaded in header.php using the same method from HTML5 Boilerplate:
	// Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline
	// It's kept in the header instead of footer to avoid conflicts with plugins.
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', '', '', '1.8.3', false);
	}

	if (is_single() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}

	wp_register_script('glp_main', get_template_directory_uri() . '/js/main.min.js', false, null, false);
	wp_enqueue_script('glp_main');
	wp_register_script('glp_bootstrap', get_template_directory_uri() . '/js/vendor/bootstrap-2.2.2.min.js', false, null, false);
	wp_enqueue_script('glp_bootstrap');
	wp_register_script('glp_addthis', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-510832576c1fd9d6', false, null, false);
	wp_enqueue_script('glp_addthis');
}

add_action('wp_enqueue_scripts', 'glp_queue', 100);