<?php
/**
 * @todo File doc needed
 */

/**
 * @todo Class doc needed
 */
class Monitor_Admin_Ajax {

	/**
	 * The single instance of this class
	 *
	 * @var Monitor_Admin_Ajax
	 */
	protected static $instance = null;

	/**
	 * The location of the log file to write to
	 *
	 * @var string
	 */
	protected $log_file = '';

	/**
	 *
	 * @return Monitor_Admin_Ajax The instance of this class
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Set up various things needed for this class
	 */
	protected function __construct() {
		// Set up the path to the log file
		$this->log_file = WP_CONTENT_DIR . '/__wpe_admin_ajax.log';
	}

	/**
	 * Generate a message that can be written to the log file
	 *
	 * @return string The message to write
	 */
	protected function _create_message() {
		$message = sprintf(
			'** %1$s | %2$s | %3$s \n%4$s\n%5$s\n',
			date( 'r' ),
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			print_r( $_POST, true ),
			print_r( $_REQUEST, true )
		);

		return $message;
	}

	/**
	 * Write a message to the log file
	 *
	 * @param string $message The message to write to the log
	 */
	protected function _write_log( $message ) {
		// Crerate the file if it doesn't exist
		if ( ! is_file( $this->log_file ) ) {
			$start_log_date = date( 'l jS \of F Y h:i:s A' );
			file_put_contents( $this->log_file, "--- log file start $start_log_date ---\n" );
		}

		// Now write to the file
		file_put_contents( $this->log_file, "\n$message\n\n", FILE_APPEND );
	}

	/**
	 * Write data to the log file
	 */
	public function write_log() {
		$this->_write_log( $this->_create_message() );
	}
}
