<?php

/**
 * Ga_Sharethis class
 *
 * Preparing request and parsing response from Sharethis Platform Api
 *
 * @author wle@adips.com
 * @version 1.0
 */
class Ga_Sharethis {

	const GA_SHARETHIS_ALERTS_ERROR = 'Trending content alerts are temporarily unavailable, please try again later or contact support@sharethis.com';

	public static function get_body( $data ) {
		$body = $data->getBody();
		return json_decode( $body );
	}

	/**
	 * Create sharethis options
	 */
	public static function create_sharethis_options( $api_client ) {
		$data = array();
		if ( Ga_Helper::should_create_sharethis_property() ) {
			$domain				 = parse_url( get_site_url(), PHP_URL_HOST );
			$query_params		 = array( 'domain' => $domain );
			$response			 = $api_client->call( 'ga_api_create_sharethis_property', array(
				$query_params
			) );
			$sharethis_options	 = self::get_sharethis_options( $response );
			if ( !empty( $sharethis_options[ 'id' ] ) ) {
				add_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID, $sharethis_options[ 'id' ] );
			}
			if ( !empty( $sharethis_options[ 'secret' ] ) ) {
				add_option( Ga_Admin::GA_SHARETHIS_PROPERTY_SECRET, $sharethis_options[ 'secret' ] );
			}
		}

		return $data;
	}

	public static function get_sharethis_options( $response ) {
		$body	 = self::get_body( $response );
		$options = array();
		if ( !empty( $body ) ) {
			foreach ( $body as $key => $value ) {
				if ( $key == '_id' ) {
					$options[ 'id' ] = $value;
				} else if ( $key == 'secret' ) {
					$options[ 'secret' ] = $value;
				} else if ( $key == 'error' ) {
					$options[ 'error' ] = $value;
				}
			}
		} else {
			$options[ 'error' ] = self::GA_SHARETHIS_ALERTS_ERROR;
		}
		return $options;
	}

	public static function sharethis_installation_verification( $api_client ) {
		if ( Ga_Helper::should_verify_sharethis_installation() ) {
			$query_params	 = array(
				'id'	 => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID ),
				'secret' => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_SECRET )
			);
			$response		 = $api_client->call( 'ga_api_sharethis_installation_verification', array(
				$query_params
			) );
			$result			 = self::get_verification_result( $response );
			if ( !empty( $result ) ) {
				add_option( Ga_Admin::GA_SHARETHIS_VERIFICATION_RESULT, true );
			}
		}
	}

	public static function get_verification_result( $response ) {
		$body = self::get_body( $response );
		if ( !empty( $body->{"status"} ) ) {
			return true;
		}
		return false;
	}

	public static function load_sharethis_trending_alerts( $api_client ) {
		if ( Ga_Helper::should_load_trending_alerts() ) {
			$query_params	 = array(
				'id'	 => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID ),
				'secret' => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_SECRET )
			);
			$response		 = $api_client->call( 'ga_api_sharethis_get_trending_alerts', array(
				$query_params
			) );
			return self::get_alerts( $response );
		}
	}

	public static function get_alerts( $response ) {
		$body = self::get_body( $response );
		if ( !empty( $body ) ) {
			if ( !empty( $body[ 'error' ] ) ) {
				return (object) array( 'error' => self::GA_SHARETHIS_ALERTS_ERROR );
			}

			return $body;
		} else {
			return array();
		}
	}

}
