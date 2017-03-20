<?php

/**
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-02-01
 * Time: 09:46
 */
class Ga_Frontend_Controller extends Ga_Controller_Core {

	public static function googleanalytics_get_script() {
		if ( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) {
			$web_property_id = Ga_Frontend::get_web_property_id();
			if ( Ga_Helper::should_load_ga_javascript( $web_property_id ) ) {
				$javascript = Ga_View_Core::load( 'ga_code', array(
					'data' => array(
						Ga_Admin::GA_WEB_PROPERTY_ID_OPTION_NAME => $web_property_id
					)
				), true );
				echo strip_tags( $javascript );
			}
		} else {
			wp_redirect( home_url() );
		}
		exit();
	}

}
