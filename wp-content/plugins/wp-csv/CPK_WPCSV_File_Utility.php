<?php

if ( !class_exists( 'CPK_WPCSV_File_Utility' ) ) {

class CPK_WPCSV_File_Utility {

	private $full_path;
	private $mime_type;

	public function __construct( $full_path = '' ) {
		$this->full_path = $full_path;
	}
	
	public function set_file( $full_path ) {
		$this->full_path = $full_path;
		$this->mime_type = $this->get_mime_type( );
	}

	public function send_to_browser( $destination_file_name, $redirect_to_referer = TRUE  ) {
		
		if ( headers_sent( ) ) {
			error_log( "Headers already sent.  Download of {$this->full_path} failed." );
			die( 'Headers already sent' );
		}

		// Required for some browsers
		if ( ini_get( 'zlib.output_compression' ) )
		  ini_set( 'zlib.output_compression', 'Off' );

		// File Exists?
		if ( is_dir( $this->full_path ) || !file_exists( $this->full_path ) ) {
			return FALSE; # To stop hitting the 'exit' command at the bottom of this function.
		}

		if ( ob_get_contents( ) ) { # Make sure no junk is included in the file	
			ob_end_clean( );
		}

		header( "Content-type: {$this->mime_type}" );
		header( 'Content-Disposition: attachment; filename="' . $destination_file_name . '"' );
		header( 'Cache-Control: no-store, no-cache' );	

		if ( ob_get_contents( ) ) { # Make __absolutely__ :) sure no junk is included in the file
			ob_end_flush( );
			flush( );
		}

		readfile( $this->full_path );
		die( );
		
	}
	
	private function get_mime_type( ) {
		if ( version_compare( phpversion( ), '5.3', '<' ) || !function_exists( 'finfo_open' ) ) {
			if ( function_exists( 'mime_content_type' ) ) {
				return mime_content_type( $this->full_path );
			} else {
				return 'text/csv'; # Give up and try a good default.
			}
		} else {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			return finfo_file( $finfo, $this->full_path );
		}
	}
} # End class CPK_WPCSV_File_Utility

} # End if
