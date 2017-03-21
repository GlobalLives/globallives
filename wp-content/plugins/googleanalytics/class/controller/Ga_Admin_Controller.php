<?php

/**
 * Manages actions in the admin area.
 *
 * Created by PhpStorm.
 * User: mdn
 * Date: 2017-01-25
 * Time: 09:50
 */
class Ga_Admin_Controller extends Ga_Controller_Core {

	const ACTION_SHARETHIS_INVITE = 'ga_action_sharethis_invite';

	/**
	 * Redirects to Google oauth authentication endpoint.
	 */
	public static function ga_action_auth() {
		if ( Ga_Helper::are_features_enabled() ) {
			header( 'Location:' . Ga_Admin::api_client()->create_auth_url() );
		} else {
			wp_die( Ga_Helper::ga_oauth_notice( __( 'Please accept the terms to use this feature' ) ) );
		}
	}

	/**
	 * Handle Sharethis invite action
	 */
	public static function ga_action_sharethis_invite() {

		if ( self::verify_nonce( self::ACTION_SHARETHIS_INVITE ) ) {
			$email		 = !empty( $_POST[ 'sharethis_invite_email' ] ) ? $_POST[ 'sharethis_invite_email' ] : null;
			$response	 = null;
			if ( !empty( $email ) ) {
				$data = array(
					'id'		 => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_ID ),
					'secret'	 => get_option( Ga_Admin::GA_SHARETHIS_PROPERTY_SECRET ),
					'product'	 => 'viral-notifications',
					'role'		 => 'admin', // array_shift(Ga_Helper::get_user_roles())
					'email'		 => $email
				);

				$response	 = Ga_Admin::api_client( Ga_Admin::GA_SHARETHIS_API_ALIAS )->call( 'ga_api_sharethis_user_invite', array( $data ) );
				$errors		 = Ga_Admin::api_client( Ga_Admin::GA_SHARETHIS_API_ALIAS )->get_errors();

				if ( !empty( $errors ) ) {
					$msg = '';
					foreach ( $errors as $error ) {
						$msg .= $error[ 'message' ];
					}
					$msg = Ga_Helper::create_url_msg( $msg, Ga_Admin::NOTICE_ERROR );
				} else {
					$msg = Ga_Helper::create_url_msg( _( 'An invite was sent to this email' ), Ga_Admin::NOTICE_SUCCESS );
				}
			}
		} else {
			$msg = Ga_Helper::create_url_msg( _( 'Invalid request.' ), Ga_Admin::NOTICE_ERROR );
		}

		wp_redirect( admin_url( Ga_Helper::create_url( Ga_Helper::GA_TRENDING_PAGE_URL, array( 'ga_msg' => $msg ) ) ) );
	}

	/**
	 * Sets accept terms option to TRUE.
	 */
	public static function ga_action_update_terms() {
		update_option( Ga_Admin::GA_SHARETHIS_TERMS_OPTION_NAME, true );

		wp_redirect( admin_url( Ga_Helper::GA_SETTINGS_PAGE_URL ) );
	}

	/**
	 * Enables all features option.
	 */
	public static function ga_action_enable_all_features() {
		Ga_Helper::update_option( Ga_Admin::GA_DISABLE_ALL_FEATURES, false );

		$url = !empty( $_GET[ 'page' ] ) ? Ga_Helper::create_url( admin_url( 'admin.php' ), array( 'page' => $_GET[ 'page' ] ) ) : admin_url( Ga_Helper::create_url( Ga_Helper::GA_SETTINGS_PAGE_URL ) );

		wp_redirect( $url );
	}

	/**
	 * Disables all features option.
	 */
	public static function ga_action_disable_all_features() {
		Ga_Helper::update_option( Ga_Admin::GA_DISABLE_ALL_FEATURES, true );

		$url = !empty( $_GET[ 'page' ] ) ? Ga_Helper::create_url( admin_url( 'admin.php' ), array( 'page' => $_GET[ 'page' ] ) ) : admin_url( Ga_Helper::create_url( Ga_Helper::GA_SETTINGS_PAGE_URL ) );

		wp_redirect( $url );
	}
	
	public static function validate_ajax_data_change_post( $post ) {
		$error = 0;

		if ( self::verify_nonce( 'ga_ajax_data_change' ) ) {
			if ( !empty( $post[ 'date_range' ] ) ) {
				if ( !is_string( $post[ 'date_range' ] ) ) {
					$error ++;
				}
			} else {
				$error ++;
			}

			if ( !empty( $post[ 'metric' ] ) ) {
				if ( !is_string( $post[ 'metric' ] ) ) {
					$error ++;
				}
			} else {
				$error ++;
			}
		} else {
			$error ++;
		}

		return $error == 0;
	}
}
