<?php


if ( !class_exists( 'CPK_WPCSV_Debug_Log' ) ) {

class CPK_WPCSV_Debug_Log {

	private $file_path = '';
	private $enabled = FALSE;

	public function __construct( $file_path ) {
		$this->file_path = $file_path;
	}

	public function enable( ) {
		$this->enabled = TRUE;
	}

	public function disable( ) {
		$this->enabled = FALSE;
	}

	public function clear( ) {
		if ( file_exists( $this->file_path ) ) {
			unlink( $this->file_path );
		}
	}

	public function add( $label, $data ) {
		
		if ( !$this->enabled ) return;

		if ( is_array( $data ) || is_object( $data ) ) {
			$value = print_r( $data, TRUE );
		} else {
			$value = $data;
		}

		$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		$msg = "[ {$date} ] {$label}: {$value}\n";

		error_log( $msg, 3, $this->file_path );
	}

} # End class CPK_WPCSV_Debug_Log

} # End if
