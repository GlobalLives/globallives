<?php
/*
Plugin Name: Force Strong Passwords - WPE Edition
Plugin URI: https://github.com/boogah/Force-Strong-Passwords
Description: Forces users to set either a strong or medium strength password.
Version: 1.6.4
Author: Jason Cosper
Author URI: http://jasoncosper.com
License: GPLv2
*/

// mu-plugins/slt-force-strong-passwords.php
if ( getenv( 'WPENGINE_FORCE_STRONG_PASSWORDS' ) !== 'off' ) {
	require WPMU_PLUGIN_DIR.'/force-strong-passwords/slt-force-strong-passwords.php';
}
