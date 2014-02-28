<?php
/**
 * Scripts and stylesheets
 */

function glp_queue() {

	// Deregister jQuery because we're loading it in head.php
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', '', '', '1.10.0', false);
	}

	if (WP_ENV == 'DEV') { // Local development environment, load scripts separately.

		// Stylesheets
		wp_enqueue_style('glp_style', get_template_directory_uri() . '/css/style.min.css', false, null);
		wp_enqueue_style('jquery-ui-custom', get_template_directory_uri() . '/css/jquery-ui/jquery-ui-1.9.2.custom.min.css');

		// Register scripts: wp_register_script( $handle, $src, $deps, $ver, $in_footer )

		wp_register_script('glp_main', get_template_directory_uri() . '/js/main.js', array('jquery','jquery-cycle'), null, true);
		wp_register_script('glp_video', get_template_directory_uri() . '/js/video.js', array('jquery','jquery-ui-slider', 'jquery-ui-touch'), null, true);

		wp_register_script('d3', get_template_directory_uri() . '/js/vendor/d3.v3.min.js', false, null, true);
		wp_register_script('bootstrap', get_template_directory_uri() . '/js/vendor/bootstrap-2.3.2.min.js', 'jquery', null, true);
		wp_register_script('jquery-cycle', get_template_directory_uri() . '/js/vendor/jquery.cycle.lite.js', 'jquery','1.7', true);
		wp_register_script('jquery-ui-slider', false, false, false, true);
		wp_register_script('jquery-ui-touch', get_template_directory_uri() . '/js/vendor/jquery.ui.touch-punch.min.js', array('jquery','jquery-ui-slider'), false, true);
		wp_register_script('addthis', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-510832576c1fd9d6', false, null, true);

		// Enqueue scripts for ALL pages
		wp_enqueue_script('glp_main');
	    wp_enqueue_script('glp_video');
		wp_enqueue_script('bootstrap');
		wp_enqueue_script('addthis');
	
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-touch');

		// Enqueue scripts for "Single" pages
		if (is_single() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	
		// Enqueue scripts for "Explore", "Series", and "Pariticpant" pages
		if (is_page('explore') || is_tax('series') || is_singular('participant')) {
			wp_enqueue_script('d3');
			wp_enqueue_script('jquery-cycle');
		}

		// Localize glpAjax.ajaxurl
		wp_localize_script('glp_main', 'glpAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

	} else { // Production environment, load concatented scripts.

		// Stylesheets
		wp_enqueue_style('glp_style', get_template_directory_uri() . '/css/style.min.css', false, null);
		wp_enqueue_style('jquery-ui-custom', get_template_directory_uri() . '/css/jquery-ui/jquery-ui-1.9.2.custom.min.css');

		// Register scripts: wp_register_script( $handle, $src, $deps, $ver, $in_footer )

		wp_register_script('glp_app', get_template_directory_uri() . '/js/app.min.js', 'glp_plugins', null, true);
		wp_register_script('glp_bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', false, null, true);
		wp_register_script('glp_plugins', get_template_directory_uri() . '/js/jquery-plugins.min.js', false, null, true);
		wp_register_script('d3', get_template_directory_uri() . '/js/d3.min.js', false, null, true);
		wp_register_script('addthis', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-510832576c1fd9d6', false, null, true);

		// Enqueue scripts for ALL pages
		wp_enqueue_script('glp_app');
		wp_enqueue_script('glp_bootstrap');
		wp_enqueue_script('glp_plugins');
		wp_enqueue_script('addthis');
	
		// Enqueue scripts for "Single" pages
		if (is_single() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	
		// Enqueue scripts for "Explore", "Series", and "Participant" pages
		if (is_page('explore') || is_tax('series') || is_singular('participant')) {
			wp_enqueue_script('d3');
		}

		// Localize glpAjax.ajaxurl
		wp_localize_script('glp_app', 'glpAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

	}

}

add_action('wp_enqueue_scripts', 'glp_queue', 100);