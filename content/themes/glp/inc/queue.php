<?php
/**
 * Scripts and stylesheets
 */

function glp_queue() {
#  wp_enqueue_style('glp_bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', false, null);
#  wp_enqueue_style('glp_bootstrap_responsive', get_template_directory_uri() . '/css/bootstrap-responsive.min.css', array('glp_bootstrap'), null);
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

  wp_register_script('glp_plugins', get_template_directory_uri() . '/js/plugins.js', false, null, false);
  wp_register_script('glp_main', get_template_directory_uri() . '/js/main.js', false, null, false);
  wp_enqueue_script('glp_plugins');
  wp_enqueue_script('glp_main');
}

add_action('wp_enqueue_scripts', 'glp_queue', 100);

?>