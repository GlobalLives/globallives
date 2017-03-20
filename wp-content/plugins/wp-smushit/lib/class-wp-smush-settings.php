<?php

if ( ! class_exists( 'WpSmushSettings' ) ) {
	class WpSmushSettings {

		function __construct() {
		}

		/**
		 * Check if form is submitted and process it
		 *
		 * @return null
		 */
		function process_options() {

			if ( ! is_user_logged_in() ) {
				return false;
			}

			global $wpsmushit_admin, $wpsmush_settings;

			//Store that we need not redirect again on plugin activation
			update_site_option( 'wp-smush-hide_smush_welcome', true );

			// var to temporarily assign the option value
			$setting = null;

			//Store Option Name and their values in an array
			$settings = array();

			//Save whether to use the settings networkwide or not ( Only if in network admin )
			if ( ! empty( $_POST['action'] ) && 'save_settings' == $_POST['action'] ) {
				if ( ! isset( $_POST['wp-smush-networkwide'] ) ) {
					//Save the option to disable nwtwork wide settings and return
					update_site_option( WP_SMUSH_PREFIX . 'networkwide', 0 );
				} else {
					//Save the option to disable nwtwork wide settings and return
					update_site_option( WP_SMUSH_PREFIX . 'networkwide', 1 );
				}
			}

			// process each setting and update options
			foreach ( $wpsmushit_admin->settings as $name => $text ) {

				// formulate the index of option
				$opt_name = WP_SMUSH_PREFIX . $name;

				// get the value to be saved
				$setting = isset( $_POST[ $opt_name ] ) ? 1 : 0;

				$settings[ $opt_name ] = $setting;

				// update the new value
				$wpsmush_settings->update_setting( $opt_name, $setting );

				// unset the var for next loop
				unset( $setting );
			}

			//Save the selected image sizes
			$image_sizes = ! empty( $_POST['wp-smush-image_sizes'] ) ? $_POST['wp-smush-image_sizes'] : array();
			$image_sizes = array_filter( array_map( "sanitize_text_field", $image_sizes ) );
			$wpsmush_settings->update_setting( WP_SMUSH_PREFIX . 'image_sizes', $image_sizes );

			//Update Resize width and height settings if set
			$resize_sizes['width']  = isset( $_POST['wp-smush-resize_width'] ) ? intval( $_POST['wp-smush-resize_width'] ) : 0;
			$resize_sizes['height'] = isset( $_POST['wp-smush-resize_height'] ) ? intval( $_POST['wp-smush-resize_height'] ) : 0;

			// update the resize sizes
			$wpsmush_settings->update_setting( WP_SMUSH_PREFIX . 'resize_sizes', $resize_sizes );

			//Store the option in table
			$wpsmush_settings->update_setting( 'wp-smush-settings_updated', 1 );

			//Delete Show Resmush option
			if ( isset( $_POST['wp-smush-keep_exif'] ) && ! isset( $_POST['wp-smush-original'] ) && ! isset( $_POST['wp-smush-lossy'] ) ) {
				//@todo: Update Resmush ids
			}

		}

		/**
		 * Checks whether the settings are applicable for the whole network/site or Sitewise ( Multisite )
		 * @todo: Check in subdirectory installation as well
		 */
		function is_network_enabled() {
			//If Single site return true
			if ( ! is_multisite() ) {
				return true;
			}

			//Check if the settings are network wide or site wise
			$networkwide = get_site_option( WP_SMUSH_PREFIX . 'networkwide', true );
			if ( $networkwide ) {
				return true;
			}

			return false;
		}

		/**
		 * Returns the value of given setting key, based on if network settings are enabled or not
		 *
		 * @param string $name Setting to fetch
		 * @param string $default Default Value
		 *
		 * @return bool|mixed|void
		 *
		 */
		function get_setting( $name = '', $default = '' ) {

			if( empty( $name ) ) {
				return false;
			}

			return $this->is_network_enabled() ? get_site_option( $name, $default ) : get_option( $name, $default );
		}

		/**
		 * Update value for given setting key
		 *
		 * @param string $name Key
		 * @param string $value Value
		 *
		 * @return bool If the setting was updated or not
		 */
		function update_setting( $name = '', $value = '' ) {

			if( empty( $name ) ) {
				return false;
			}

			return $this->is_network_enabled() ? update_site_option( $name, $value ) : update_option( $name, $value );
		}

		/**
		 * Delete the given key name
		 *
		 * @param string $name Key
		 *
		 * @return bool If the setting was updated or not
		 */
		function delete_setting( $name = '' ) {

			if( empty( $name ) ) {
				return false;
			}

			return $this->is_network_enabled() ? delete_site_option( $name ) : delete_option( $name );
		}

	}
	global $wpsmush_settings;
	$wpsmush_settings = new WpSmushSettings();
}