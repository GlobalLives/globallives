<?php

class Ga_Frontend {

	const GA_SHARETHIS_PLATFORM_URL = '//platform-api.sharethis.com/js/sharethis.js';

	public static function platform_sharethis() {
		$url = self::GA_SHARETHIS_PLATFORM_URL . '#product=ga';
		if ( get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID ) ) {
			$url = $url . '&property=' . get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID );
		}
		wp_register_script( GA_NAME . '-platform-sharethis', $url, null, null, false );
		wp_enqueue_script( GA_NAME . '-platform-sharethis' );
	}

	/**
	 * Adds frontend actions hooks.
	 */
	public static function add_actions() {
		if ( Ga_Helper::are_features_enabled() ) {
			add_action( 'wp_enqueue_scripts', 'Ga_Frontend::platform_sharethis' );
		}
		add_action( 'wp_footer', 'Ga_Frontend::insert_ga_script' );
	}

	public static function insert_ga_script() {
		if ( Ga_Helper::can_add_ga_code() || Ga_Helper::is_all_feature_disabled() ) {
			Ga_View_Core::load( 'ga_googleanalytics_loader', array(
				'ajaxurl' => add_query_arg( Ga_Controller_Core::ACTION_PARAM_NAME, 'googleanalytics_get_script', home_url() )
			) );
		}
	}

	/**
	 * Gets and returns Web Property Id.
	 *
	 * @return string Web Property Id
	 */
	public static function get_web_property_id() {
		$web_property_id = get_option( Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME );
		if ( Ga_Helper::is_code_manually_enabled() || Ga_Helper::is_all_feature_disabled() ) {
			$web_property_id = get_option( Ga_Admin::GA_WEB_PROPERTY_ID_MANUALLY_VALUE_OPTION_NAME );
		}

		return $web_property_id;
	}

}
