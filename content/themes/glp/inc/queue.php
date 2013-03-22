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
	
	wp_register_script('glp_d3', 'http://d3js.org/d3.v3.min.js', false, null, false);
	if (is_page('explore')) {
		wp_enqueue_script('glp_d3');
	}

	wp_register_script('glp_main', get_template_directory_uri() . '/js/main.min.js', false, null, false);
        wp_localize_script('glp_main', 'glpAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	wp_enqueue_script('glp_main');
	wp_register_script('glp_bootstrap', get_template_directory_uri() . '/js/vendor/bootstrap-2.2.2.min.js', false, null, false);
	wp_enqueue_script('glp_bootstrap');
	wp_register_script('glp_addthis', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-510832576c1fd9d6', false, null, false);
	wp_enqueue_script('glp_addthis');
        
        if ( is_single() && 'participant' == get_post_type() ) {

            // Individual jquery ui components are not available via cdn. 
            // This one queue will load the component dependencies (ui-core, ui-widget, ui-mouse).
            wp_enqueue_script('jquery-ui-slider', false, false, false, true);
            wp_enqueue_script('jquery-ui-touch', get_template_directory_uri() . '/js/vendor/jquery.ui.touch-punch.min.js', array('jquery','jquery-ui-slider'), false, true);
            
            // The CDN themes are packaged for all ui components and are not minimised, therefore we'll load the small version locally.
            wp_enqueue_style('jquery-ui-custom', get_template_directory_uri() . '/css/jquery-ui/jquery-ui-1.9.2.custom.min.css');
        }
}

add_action('wp_enqueue_scripts', 'glp_queue', 100);