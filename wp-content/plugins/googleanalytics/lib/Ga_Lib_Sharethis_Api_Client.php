<?php

class Ga_Lib_Sharethis_Api_Client extends Ga_Lib_Api_Client {

	static $instance = null;

	const GA_SHARETHIS_ENDPOINT = 'platform-api.sharethis.com/v1.0/property';

	const USE_CACHE = false;

	private function __construct() {}

	/**
	 * Returns API client instance.
	 *
	 * @return Ga_Lib_Api_Client|null
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new Ga_Lib_Sharethis_Api_Client();
		}

		return self::$instance;
	}

	function call_api_method( $callback, $args ) {
		$callback = array( get_class( $this ), $callback );
		if ( is_callable( $callback ) ) {
			try {
				if ( !empty( $args ) ) {
					if ( is_array( $args ) ) {
						return call_user_func_array( $callback, $args );
					} else {
						return call_user_func_array( $callback, array( $args ) );
					}
				} else {
					return call_user_func( $callback );
				}
			} catch ( Ga_Lib_Api_Request_Exception $e ) {
				throw new Ga_Lib_Sharethis_Api_Client_Exception( $e->getMessage() );
			}
		} else {
			throw new Ga_Lib_Sharethis_Api_Client_Exception( wp_json_encode( array( 'error' => '[' . get_class( $this ) . ']Unknown method: ' . $callback ) ) );
		}
	}

	/**
	 * Sends request for Sharethis api
	 *
	 * @param $query_params
	 *
	 * @return Ga_Lib_Api_Response Returns response object
	 */
	private function ga_api_create_sharethis_property( $query_params ) {
		$request = Ga_Lib_Api_Request::get_instance(self::USE_CACHE);
		try {
			$response = $request->make_request( $this->add_protocol( self::GA_SHARETHIS_ENDPOINT ), wp_json_encode( $query_params ), true );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Sharethis_Api_Client_InvalidDomain_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	private function ga_api_sharethis_installation_verification( $query_params ) {
		$request = Ga_Lib_Api_Request::get_instance(self::USE_CACHE);
		try {
			$response = $request->make_request( 'https://' . self::GA_SHARETHIS_ENDPOINT . '/verify', wp_json_encode( $query_params ), true );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Sharethis_Api_Client_Verify_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	private function ga_api_sharethis_get_trending_alerts( $query_params ) {
		$url	 = $this->add_protocol( add_query_arg( $query_params, self::GA_SHARETHIS_ENDPOINT . '/notifications' ) );
		$request = Ga_Lib_Api_Request::get_instance(self::USE_CACHE);
		try {
			$response = $request->make_request( $url, null, true );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Sharethis_Api_Client_Alerts_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	private function ga_api_sharethis_user_invite( $query_params ) {
		$request = Ga_Lib_Api_Request::get_instance(self::USE_CACHE);
		try {
			$response = $request->make_request( 'https://' . self::GA_SHARETHIS_ENDPOINT . '/user/join', wp_json_encode( $query_params ), true );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			throw new Ga_Lib_Sharethis_Api_Client_Invite_Exception( $e->getMessage() );
		}

		return new Ga_Lib_Api_Response( $response );
	}

	private function add_protocol( $url ) {
		return ( is_ssl() ) ? 'https://' . $url : 'http://' . $url;
	}

}

class Ga_Lib_Sharethis_Api_Client_Exception extends Ga_Lib_Api_Client_Exception {

	function __construct( $msg ) {
		$data = json_decode( $msg, true );
		parent::__construct( !empty( $data[ 'error' ] ) ? $data[ 'error' ] : $msg  );
	}

}

class Ga_Lib_Sharethis_Api_Client_InvalidDomain_Exception extends Ga_Lib_Sharethis_Api_Client_Exception {

	function __construct( $msg ) {
		parent::__construct( $msg );
	}

}

class Ga_Lib_Sharethis_Api_Client_Invite_Exception extends Ga_Lib_Sharethis_Api_Client_Exception {

	function __construct( $msg ) {
		parent::__construct( $msg );
	}

}

class Ga_Lib_Sharethis_Api_Client_Alerts_Exception extends Ga_Lib_Sharethis_Api_Client_Exception {

	function __construct( $msg ) {
		parent::__construct( $msg );
	}

}

class Ga_Lib_Sharethis_Api_Client_Verify_Exception extends Ga_Lib_Sharethis_Api_Client_Exception {

	function __construct( $msg ) {
		parent::__construct( $msg );
	}

}
