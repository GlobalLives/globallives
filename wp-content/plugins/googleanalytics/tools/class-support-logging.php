<?php


class Ga_SupportLogger {

	const EMAIL = 'support@googleanalytics.zendesk.com';
	const LOG_OPTION = 'googleanalytics_sherethis_error_log';

	static $debug_info;
	/**
	 * Constructor.
	 * @return void
	 */
	public function __construct() {
		add_action( 'st_support_show_button', array( $this, 'display_button' ) );
		add_action( 'st_support_save_error',  array( $this, 'save_error' ) );
		$this->get_debug_body();
	}

	public static function send_email() {
		$email = !empty( $_POST[ 'email' ] ) ? sanitize_text_field( $_POST[ 'email' ] ) : '';
		$description = !empty( $_POST[ 'description' ] ) ? __( 'Description of the issue:' ). PHP_EOL . PHP_EOL . sanitize_text_field( $_POST[ 'description' ] ) . PHP_EOL . PHP_EOL  : '';

		if ( !is_email( $email ) ) {
			$response['error'] = 'Don\'t forget to provide your email address!';
		}
		else if ( wp_mail( self::EMAIL, __( 'Debug Report' ), $description . self::$debug_info, 'From: ' . $email ) ) {
			$response['success'] = 'Success! Thank you for sending your debug report, you should receive a confirmation email from us. If you don\'t, please email us directly at <a href="mailto:support@googleanalytics.zendesk.com">support@googleanalytics.zendesk.com</a>.';
		} else {
			$response['error'] = 'Oops! Looks like we weren\'t able to send the email. Please copy and paste the "debug info" into your favorite email client to: <a href="mailto:support@googleanalytics.zendesk.com">support@googleanalytics.zendesk.com</a>. We hope to help soon!';
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Displays a button to email the debugging info.
	 * @return void
	 */
	public function display_button() {
		printf(
			'<a href="%s" class="button button-secondary" target="_blank">Send Debugging Info</a>',
			esc_url( $this->get_mail_link() )
		);
	}


	/**
	 * Saves an error to the log.
	 * @param Exception $err Error to save.
	 * @return void
	 */
	public function save_error( Exception $err ) {
		$cur_log = get_option( self::LOG_OPTION, array() );

		// Creates the error object.
		$new_log = array(
			'message' => $err->getMessage(),
			'stack' => $err->getTraceAsString(),
			'date' => current_time( 'r' ),
		);

		if ( method_exists( $err, 'get_google_error_response' ) ) {
			$new_log['response'] = $err->get_google_error_response();
		}

		$cur_log[] = $new_log;

		// Cap the log at 20 entries for space purposes.
		if ( count( $cur_log ) > 20 ) {
			array_pop( $cur_log );
		}

		// Save.
		update_option( self::LOG_OPTION, $cur_log );
	}

	public function get_debug_body(){
		$body = 'Debug Info:' . PHP_EOL . PHP_EOL;
		$body .= implode( $this->get_debug_info(), PHP_EOL );
		$body .= PHP_EOL . PHP_EOL . 'Error Log:' . PHP_EOL . PHP_EOL;
		$body .= $this->get_formatted_log();
		self::$debug_info = $body;
	}

	/**
	 * Returns a string used for providing an email link.
	 * @return string
	 */
	private function get_mail_link() {

		$body  = 'DESCRIBE ISSUE HERE:' . str_repeat( '%0A', 10 );
		$body .= 'Debug Info:%0A%0A';
		$body .= implode( $this->get_debug_info(), '%0A' );
		$body .= '%0A%0AError Log:%0A%0A';
		$body .= str_replace( "\n", '%0A', $this->get_formatted_log() );

		return add_query_arg( array(
			'subject' => __( 'Debug Report' ),
			'body'    => $body,
		), 'mailto:' . self::EMAIL );
	}


	/**
	 * Gets an array of debugging information about the current system.
	 * @return array
	 */
	private function get_debug_info() {
		$theme   = wp_get_theme();
		$plugins = wp_get_active_and_valid_plugins();

		$data = array(
			'Plugin Version' => GOOGLEANALYTICS_VERSION,
			'WordPress Version' => get_bloginfo( 'version' ),
			'PHP Version' => phpversion(),
			'CURL Version' => $this->get_curl_version(),
			'Site URL' => get_bloginfo( 'wpurl' ),
			'Theme Name' => $theme->get( 'Name' ),
			'Theme URL' => $theme->get( 'ThemeURI' ),
			'Theme Version' => $theme->get( 'Version' ),
			'Active Plugins' => implode( $plugins, ', ' ),
			'Operating System' => $this->get_operating_system(),
			'Web Server' => $_SERVER['SERVER_SOFTWARE'],
			'Current Time' => current_time( 'r' ),
			'Browser' => !empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'Excluded roles' =>  get_option( 'googleanalytics_exclude_roles' ),
			'Manually Tracking ID enabled' => get_option( 'googleanalytics_web_property_id_manually' ),
			'Manually typed Tracking ID' => get_option( 'googleanalytics_web_property_id_manually_value' ),
			'Tracking ID' => get_option( 'googleanalytics_web_property_id' ),
		);
		$formatted = array();
		foreach ( $data as $text => $value ) {
			$formatted[] = sprintf(
				__( $text ) . ': %s',
				$value
			);
		}
		return $formatted;
	}

	/**
	 * Gets CURL version
	 * @return string
	 */
	private function get_curl_version(){
		$curl_version = curl_version();
		return !empty( $curl_version['version'] ) ? $curl_version['version'] : '';
	}

	/**
	 * Gets operating system
	 * @return string
	 */
	private function get_operating_system(){
		if ( function_exists( 'ini_get' ) ) {
			$disabled = explode( ',', ini_get( 'disable_functions' ) );
			return !in_array( 'php_uname', $disabled ) ? php_uname() : PHP_OS;
		}
		return PHP_OS;
	}

	/**
	 * Gets a string of formatted error log entries.
	 * @return string
	 */
	private function get_formatted_log() {
		$log = get_option( self::LOG_OPTION );
		if ( ! $log ) {
			return 'None';
		}

		$text = '';
		foreach ( $log as $error ) {
			foreach ( $error as $key => $value ) {
				$text .= ucwords( $key ) . ': ' . $value . "\n";
			}
		}

		return $text;
	}

}

new Ga_SupportLogger();
