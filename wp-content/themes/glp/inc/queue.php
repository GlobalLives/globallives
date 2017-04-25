<?php
/**
 * Scripts and stylesheets
 */

function glp_queue() {

	// Deregister jQuery because we're loading it in head.php
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', null, null, '2.0.3', false);
	}

	if (WP_ENV=='DEV') {
		// Stylesheets
		wp_enqueue_style('glp_style', get_template_directory_uri() . '/css/style.css', false, filemtime(get_stylesheet_directory() . '/css/style.css'));
		wp_enqueue_style('glp_bootstrap', get_template_directory_uri() . '/css/bootstrap.css', false, filemtime(get_stylesheet_directory() . '/css/style.css'));

		// Register scripts: wp_register_script( $handle, $src, $deps, $ver, $in_footer )
		wp_register_script('glp_app', get_template_directory_uri() . '/js/app.js', array('glp_bootstrap','glp_plugins'), filemtime(get_stylesheet_directory() . '/js/app.js'), true);
		wp_register_script('glp_bootstrap', get_template_directory_uri() . '/js/bootstrap.js', 'jquery', filemtime(get_stylesheet_directory() . '/js/bootstrap.js'), true);
		wp_register_script('glp_plugins', get_template_directory_uri() . '/js/plugins.js', 'jquery', filemtime(get_stylesheet_directory() . '/js/plugins.js'), true);
	} else {
		// Stylesheets
		wp_enqueue_style('glp_style', get_template_directory_uri() . '/css/style.min.css', false, filemtime(get_stylesheet_directory() . '/css/style.min.css'));
		wp_enqueue_style('glp_bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', false, filemtime(get_stylesheet_directory() . '/css/bootstrap.min.css'));

		// Register scripts: wp_register_script( $handle, $src, $deps, $ver, $in_footer )
		wp_register_script('glp_app', get_template_directory_uri() . '/js/app.min.js', array('glp_bootstrap','glp_plugins'), filemtime(get_stylesheet_directory() . '/js/app.min.js'), true);
		wp_register_script('glp_bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', 'jquery', filemtime(get_stylesheet_directory() . '/js/bootstrap.min.js'), true);
		wp_register_script('glp_plugins', get_template_directory_uri() . '/js/plugins.min.js', 'jquery', filemtime(get_stylesheet_directory() . '/js/plugins.min.js'), true);
	}

		// Register scripts: wp_register_script( $handle, $src, $deps, $ver, $in_footer )
		wp_register_script('d3', get_template_directory_uri() . '/js/d3.min.js', false, filemtime(get_stylesheet_directory() . '/js/d3.min.js'), true);
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

add_action('wp_enqueue_scripts', 'glp_queue', 100);