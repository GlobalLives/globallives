<?php
if ( !class_exists( 'CPK_WPCSV_CSV' ) ) {

class CPK_WPCSV_CSV {

	public $errors = Array( );
	public $delimiter = ',';
	public $enclosure = '"';

	const DEFAULT_CHARACTER_ENCODING = 'UTF-8';

	public function __construct( ) {
		ini_set( 'auto_detect_line_endings', TRUE );
		if ( function_exists('iconv' ) && PHP_VERSION_ID < 50600 ) {
			iconv_set_encoding('internal_encoding', self::DEFAULT_CHARACTER_ENCODING );
			iconv_set_encoding('input_encoding', self::DEFAULT_CHARACTER_ENCODING );
			iconv_set_encoding('output_encoding', self::DEFAULT_CHARACTER_ENCODING );
		} elseif ( PHP_VERSION_ID >= 50600 ) {
			ini_set( 'default_charset', self::DEFAULT_CHARACTER_ENCODING );
		}
		$this->log_model = new CPK_WPCSV_Log_Model( );
	}

	public function save( $csv_data = Array( ), $filename = 'csvdata', $path = '/tmp' ) {

		error_log( $filename );

		if ( empty( $csv_data ) ) return FALSE;

		$file_path = $path . '/' . $filename . '.csv';

		$error_message = "Unable to write to file (location: '{$file_path}').  Perhaps try checking the file and folder via FTP.  If there's no file, it may be a permissions issue.  If there's a file, but it's only partially complete, then make sure you haven't run out of disk space.";

		$file = fopen( $file_path, 'ab' );
		foreach( $csv_data as $csv_row ) {
			$clean_csv_row = $this->remove_vulnerability( $csv_row );
			$write_successful = fputcsv( $file, $clean_csv_row, $this->delimiter, $this->enclosure );
			if ( !$write_successful ) {
				$this->errors[] = $error_message;
				break;
			}
		}
		fclose( $file );
			
		return FALSE;
	}

	private function remove_vulnerability( $row ) {
		
		if ( is_array( $row ) && !empty( $row ) ) {
			foreach( $row as &$field ) {
				if ( in_array( substr( $field, 0, 1 ), Array( '-', '+', '=' ) ) ) {
					$field = "'" . $field;
				}
			} # End foreach
		} # End if

		return $row;
	}

	public function line_count( $file_path, $offset = -1 ) {

		$linecount = 0;
		
		$file = fopen( $file_path, 'r' );

		while( $row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure ) ) {
			if ( is_array( $row ) && count( $row ) > 1 ) {
				$linecount++;
			}
		}

		fclose( $file );
		
		return $linecount + $offset; # Don't count CSV header row
	}

	public function load( $file_path, $start = 0, $limit = 500 ) {

		if ( !$this->file_valid( $file_path ) ) return FALSE;

		$file = fopen( $file_path, 'r' );

		$csv_data = Array( );

		if ( $title_row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure ) ) {

			// Intercept 'id' field and change to 'ID'.  Needs to be 'id' to prevent an excel bug, but ID is preferable to match the posts table.
			if ( $title_row[0] == 'id' ) $title_row[0] = 'ID';

			for ( $i = 1; $i < $start; $i++ ) {
			       	$row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure );
			}
			
			$count = 0;

			while ( $row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure ) ) {
				if ( $count >= $limit ) break;
				if ( is_array( $row ) && count( $row ) == count( $title_row ) ) {
					$csv_data[] = array_combine( $title_row, $row );
					$count++;
				}
			}
		}

		return $csv_data;
	}

	public function file_valid( $file_path ) {

		$file = fopen( $file_path, 'r' );

		$title_row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure );

		if ( $title_row === FALSE ) {
			$this->log_model->add_message( "Unable to read first line of file (location:{$file_path})." );
			$this->log_model->store_messages( );
			return FALSE;
		}
		
		if ( is_array( $title_row ) && empty( $title_row ) ) {
			$this->log_model->add_message( "File seems to be empty." );
			$this->log_model->store_messages( );
			return FALSE;
		}
		if ( count( $title_row ) == 1 ) {
			$this->log_model->add_message( "Only one column found.  Are you sure your spreadsheet program saved this file with the correct delimiter and enclosure characters (must match WP CSV Settings)?" );
			$this->log_model->store_messages( );
			return FALSE;
		}

		$first_row = fgetcsv( $file, NULL, $this->delimiter, $this->enclosure );

		if ( count( $title_row ) <> count( $first_row ) ) {
			$this->log_model->add_message( "Different number of columns found in first and second rows.  Operation aborted to prevent data corruption." );
			$this->log_model->store_messages( );
			return FALSE;
		}

		return TRUE;
	}

}
}
