<?php
/*
Plugin Name: Verify Google Webmaster Tools
Plugin URI: http://wordpress.org/extend/plugins/verify-google-webmaster-tools/
Description: Adds <a href="http://www.google.com/webmasters/">Google Webmaster Tools</a> verification meta-tag.
Version: 1.3
Author: Audrius Dobilinskas
Author URI: http://onlineads.lt/author/audrius
*/

if (!defined('WP_CONTENT_URL'))
      define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR'))
      define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_URL'))
      define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR'))
      define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

function activate_google_webmaster_tools() {
  add_option('gwebmasters_code', 'Paste your Google Webmaster Tools verification code here');
}

function deactive_google_webmaster_tools() {
  delete_option('gwebmasters_code');
}

function admin_init_google_webmaster_tools() {
  register_setting('google_webmaster_tools', 'gwebmasters_code');
}

function admin_menu_google_webmaster_tools() {
  add_options_page('Google Webmaster Tools', 'Google Webmaster Tools', 'manage_options', 'google_webmaster_tools', 'options_page_google_webmaster_tools');
}

function options_page_google_webmaster_tools() {
  include(WP_PLUGIN_DIR.'/verify-google-webmaster-tools/options.php');  
}

function google_webmaster_tools() {
  $gwebmasters_code = get_option('gwebmasters_code');
?>

<!-- Google Webmaster Tools plugin for WordPress -->
<?php echo $gwebmasters_code ?>

<?php
}

register_activation_hook(__FILE__, 'activate_google_webmaster_tools');
register_deactivation_hook(__FILE__, 'deactive_google_webmaster_tools');

if (is_admin()) {
  add_action('admin_init', 'admin_init_google_webmaster_tools');
  add_action('admin_menu', 'admin_menu_google_webmaster_tools');
}

if (!is_admin()) {
  add_action('wp_head', 'google_webmaster_tools');
}

?>