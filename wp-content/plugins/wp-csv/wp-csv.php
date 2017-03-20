<?php
/*
Plugin Name: WP CSV
Plugin URI: http://cpkwebsolutions.com/plugins/wp-csv
Description: A powerful, yet easy to use, CSV Importer/Exporter for Wordpress posts and pages. 
Version: 1.8.0.0
Author: CPK Web Solutions
Author URI: http://cpkwebsolutions.com
Text Domain: wp-csv

	LICENSE

	Copyright 2012  CPK Web Solutions  (email : paul@cpkwebsolutions.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Load libraries
spl_autoload_register( 'spl_autoload_classes' );

function spl_autoload_classes( $name ) {

	if ( class_exists( $name ) ) return FALSE;

	$folders = Array( '' );
	
	if ( is_array( $folders ) && !empty( $folders ) ) {
		foreach( $folders as $folder ) {
			$file = dirname( __FILE__ ) . "/{$folder}{$name}.php";
			if ( file_exists( $file ) ) {
				require_once( $file );
			}
		} # End foreach
	} # End if

}

// Initialise main class
if ( !class_exists( 'CPK_WPCSV' ) ) {

	class CPK_WPCSV {

		private $view;
		private $csv;
		private $wpcsv;
		private $backup_url;
		private $settings;
		private $option_name = '_pws_wpcsv_settings';


		const IMPORT_FILE_NAME = 'wpcsv-import.csv';
		const DEBUG_FILE_NAME = 'wpcsv-debug.log';

		const IMPORT_DOWNLOAD_PREFIX = 'wpcsv-import-';
		const EXPORT_DOWNLOAD_PREFIX = 'wpcsv-export-';

		const ERROR_MISSING_POST_ID = 1;
		const ERROR_MISSING_POST_PARENT = 2;
		const ERROR_MISSING_AUTHOR = 3;
		const ERROR_INVALID_TAXONOMY_TERM = 4;

		public function __construct( ) { // Constructor

			ob_start( );

			if ( !session_id( ) ) session_start( );
			$this->view = new CPK_WPCSV_View( );
			$this->csv = new CPK_WPCSV_CSV( );
			$this->log = new CPK_WPCSV_Log_Model( );

			$backup_url = '';

			$settings = Array( 
				'delimiter' => ',',
				'enclosure' => '"',
				'date_format' => 'US',
				'encoding' => 'UTF-8',
				'csv_path' => $this->get_csv_folder( ),
				'export_hidden_custom_fields' => 1,
				'include_field_list' => Array( '*' ),
				'exclude_field_list' => Array( ),
				'include_attachments' => 0,
				'post_type_status_exclude_filter' => Array( ),
				'limit' => 100,
				'post_fields' => Array( 'wp_ID', 'wp_post_date', 'wp_post_modified', 'wp_post_status', 'wp_post_title', 'wp_post_content', 'wp_post_excerpt', 'wp_post_parent', 'wp_post_name', 'wp_post_type', 'wp_post_mime_type', 'wp_ping_status', 'wp_comment_status', 'wp_menu_order', 'wp_post_author' ),
				'mandatory_fields' => Array( 'wp_ID', 'wp_post_date', 'wp_post_title' ),
				'access_level' => 'manage_options',
				'debug' => 0,
				'frontend_shortcode' => 0
			);

			add_option( $this->option_name, $settings ); // Does nothing if already exists

			$this->settings = get_option( $this->option_name );

			$this->settings = $this->update_settings( $this->settings );

			$old_version = $this->settings['version'];

			$this->settings['version'] = '1801';

			if ( $old_version != $this->settings['version'] ) {
				$this->upgrade_db_tables( );
			}

			$current_keys = Array( );
			if ( is_array( $this->settings ) ) {
				$current_keys = array_keys( $this->settings );
			}

			foreach( array_keys( $settings ) as $key ) {
				if ( !in_array( $key, $current_keys ) || $this->settings[ $key ] === '' ) {
					$this->settings[ $key ] = $settings[ $key ];
				}

				if ( $key == 'limit' ) {
					$this->settings[ $key ] = $settings[ $key ];
				}

				if ( $key == 'post_fields' ) {
					$this->settings[ $key ] = $settings[ $key ];
				}
				
				if ( $key == 'mandatory_fields' ) {
					$this->settings[ $key ] = $settings[ $key ];
				}
			}
			
			$this->wpcsv = new CPK_WPCSV_Engine( $this->settings );
			$this->debug = new CPK_WPCSV_Debug_Log( $this->settings['csv_path'] . '/' . self::DEBUG_FILE_NAME );

			if ( $this->settings['debug'] ) $this->debug->enable( );

			$this->wpcsv->set_debugger( $this->debug );
			
			$this->save_settings( );

			$this->csv->delimiter = $this->settings['delimiter'];
			$this->csv->enclosure = $this->settings['enclosure'];
			$this->csv->encoding = $this->settings['encoding'];

			$this->cleanup_folder( );

			add_action( 'init', Array( $this, 'init' ) );
		
		}

		private function cleanup_folder( ) {
			$files = glob( "{$this->settings['csv_path']}/wpcsv*.csv" );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					$mtime = filemtime( $file );
					$cutoff = strtotime( '-1 day' );
					if ( $mtime < $cutoff ) unlink( $file );
				}
			}
		}

		private function upgrade_db_tables( ) {
			$export_queue = new CPK_WPCSV_Export_Queue_Model( );

			$export_queue->drop_table( );
			$export_queue->create_table( );
		}

		public function init( ) {

			if ( is_admin( ) ) {
				add_action( 'admin_enqueue_scripts', Array( $this, 'load_assets' ) );
			} else {
				add_action( 'wp_enqueue_scripts', Array( $this, 'load_assets' ) );
			}

			if ( is_admin( ) ) {
				add_filter( 'plugin_action_links', Array( $this, 'cpk_add_settings_link' ), 10, 2 );
				add_action( 'plugins_loaded', Array( $this, 'cpk_load_text_domain' ) );

				add_action( 'admin_menu', Array( $this, 'admin_menus' ) );
				add_action( 'admin_notices', Array( $this, 'display_notices' ) );

				add_action( 'wp_ajax_process_export', Array( $this, 'process_export' ) );
				add_action( 'wp_ajax_process_import', Array( $this, 'process_import' ) );
			}

			# Shortcode
			if ( $this->settings['frontend_shortcode'] ) {
				add_shortcode( 'wpcsv_export_form', Array( $this, 'export_shortcode' ) );
				add_action( 'wp_ajax_nopriv_process_export', Array( $this, 'process_export' ) );
			}

			# Frontend Download
			if ( isset( $_GET['wpcsv_export'] ) ) {
				$this->download_page( );
			}
		}

		public function load_assets( ) {

			$this->load_css( );
			$this->load_js( );
		}

		private function load_js( ) {
			
			if ( $this->settings['frontend_shortcode'] && !is_admin( ) ) {
				wp_enqueue_script( 'jquery-ui-datepicker' );
			}

			if ( is_admin( ) ) {
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'wpcsv-scripts', plugins_url( '/js/wpcsv.js', __FILE__ ), Array( ), $this->settings['version'], TRUE );
			}
		}

		private function load_css( ) {

			if ( is_admin( ) || $this->settings['frontend_shortcode'] ) {
				wp_register_style( 'cpk_wpcsv_styles', plugins_url( '/css/cpk_wpcsv.css', __FILE__ ) );
				wp_enqueue_style( 'cpk_wpcsv_styles' );
			}
		}

		public function admin_menus( ) {

			$capability = $this->settings['access_level'];

			if ( function_exists( 'add_menu_page' ) && function_exists( 'add_submenu_page' ) ) {
				add_menu_page( __( 'WP CSV' ), __( 'WP CSV' ), $capability, 'wpcsv-settings', Array( $this, 'settings_page' ), NULL, NULL );
				add_submenu_page( 'wpcsv-settings', __( 'Settings' ), __( 'Settings' ), $capability, 'wpcsv-settings', Array( $this, 'settings_page' ) );
				add_submenu_page( 'wpcsv-settings', __( 'Export' ), __( 'Export' ), $capability, 'wpcsv-export', Array( $this, 'export_page' ) );
				add_submenu_page( 'wpcsv-settings', __( 'Import' ), __( 'Import' ), $capability, 'wpcsv-import', Array( $this, 'import_page' ) );
				add_submenu_page( 'wpcsv-settings-hidden', __( 'Report' ), __( 'Report' ), $capability, 'wpcsv-report', Array( $this, 'report_page' ) );
				add_submenu_page( 'wpcsv-settings-hidden', __( 'Download' ), __( 'Download' ), $capability, 'wpcsv-download', Array( $this, 'download_page' ) );
				
				# Help users find the new settings page
				add_submenu_page( 'tools.php', __( 'WP CSV' ), __( 'WP CSV' ), $capability, 'wpcsv-old', Array( $this, 'old_settings_page' ) );
			}
		}	

		private function update_settings( $settings ) {
			if ( $settings['access_level'] == 'administrator' ) {
				$settings['access_level'] == 'manage_options';
			}


			if ( isset( $settings['post_type'] ) && isset( $settings['post_status'] ) ) {
				$posts_model = new CPK_WPCSV_Posts_Model( $settings );
			
				$args = Array(
					"{$settings['post_type']}" => Array( 
						"{$settings['post_status']}" => FALSE
					)
				);

				$settings['post_type_status_exclude_filter'] = $posts_model->get_post_type_status_combos( $args, TRUE );
				
				unset( $settings['post_type'] );
				unset( $settings['post_status'] );
			}

			return $settings;
		}

		public function set_settings( $settings ) {
			$this->settings = $settings;
			$this->save_settings( );
		}

		public function folder_writable( $path ) {

			return ( is_dir( $path ) && is_writable( $path ) );
		}

		public function file_writable( $path ) {
			if ( $this->folder_writable( $path ) ) {
				$success = file_put_contents( "{$path}/.wpcsv-test", 'Just a test file.  You can delete this file if you like.' );
			}

			if ( $success ) {
				unlink( "{$path}/.wpcsv-test" );
				return TRUE;
			}
			
		}

		public function add_htaccess( $path ) {
			if ( $this->folder_writable( $path ) ) {
				return file_put_contents( "{$path}/.htaccess", 'Deny from all' );
			}
		}

		public function get_paths( $sub_folder = '' ) {

			$uploads_dir = wp_upload_dir( );

			# In order of preference
			return Array( 
				sys_get_temp_dir( ) . '/' . $sub_folder,
				ABSPATH . $sub_folder,
				WP_CONTENT_DIR . '/' . $sub_folder,
				$uploads_dir['basedir'] . '/' . $sub_folder
			);
		}

		public function get_csv_folder( ) {

			$chosen_folder = '';

			$paths = $this->get_paths( 'wpcsv_backups' );

			foreach( $paths as $p ) {
				if ( ( !file_exists( $p ) && ( mkdir( $p, 0755 ) ) || $this->folder_writable( $p ) ) ) {
					$chosen_folder = $p;
					break;
				}
			}

			# This will create .htaccess files below the web root (ie sys_temp, but shouldn't cause any harm)
			if ( $chosen_folder && $this->add_htaccess( $chosen_folder ) ) {
				return $chosen_folder;
			}

		}

		public function old_settings_page( ) {
			echo "<h1>WP CSV Has Moved!</h1><p>There is now a main menu item called WP CSV with 'settings', 'import', and 'export' sub menus.</p>";
		}

		public function settings_page( ) {

			if ( !$this->authorized( ) ) wp_redirect( wp_get_referer( ) );

			$error = NULL;
			$imagefolder = NULL;

			if ( !empty( $_POST ) ) {

				if ( isset( $_POST['type_status_exclude'] ) ) {
					$this->settings['post_type_status_exclude_filter'] = $_POST['type_status_exclude'];
				} else {
					$this->settings['post_type_status_exclude_filter'] = Array( );
				}

				if ( isset( $_POST['imagefolder'] ) ) {
					$_POST['imagefolder'] = trim( $_POST['imagefolder'], '/ ' );
				} else {
					$_POST['imagefolder'] = '';
				}

				$imagefolder = WP_CONTENT_DIR . '/uploads/' . $_POST['imagefolder'];
				if ( is_dir( $imagefolder ) ) {
					$this->settings['imagefolder'] = $_POST['imagefolder'];
				} else {
					$_POST['action'] = 'settings';
					$error = "ERROR - Folder could not be opened: $imagefolder";
					$imagefolder = $_POST['imagefolder'];
				}
				$this->settings['date_format'] = $_POST['date_format'];

				$this->settings['delimiter'] = substr( stripslashes( $_POST['delimiter'] ), 0, 1 );
				$this->settings['enclosure'] = substr( stripslashes( $_POST['enclosure'] ), 0, 1 );

				if ( isset( $_POST['export_hidden_custom_fields'] ) ) {
					$this->settings['export_hidden_custom_fields'] = 1;
				} else {
					$this->settings['export_hidden_custom_fields'] = 0;
				}
				
				if ( isset( $_POST['frontend_shortcode'] ) ) {
					$this->settings['frontend_shortcode'] = 1;
				} else {
					$this->settings['frontend_shortcode'] = 0;
				}

				if ( isset( $_POST['debug'] ) ) {
					$this->settings['debug'] = 1;
				} else {
					$this->settings['debug'] = 0;
				}
				
				if ( isset( $_POST['include_attachments'] ) ) {
					$this->settings['include_attachments'] = 1;
				} else {
					$this->settings['include_attachments'] = 0;
				}

				$this->settings['include_field_list'] = preg_split( '/(,|\s)/', $_POST['include_field_list'] );
				
				$this->settings['exclude_field_list'] =  preg_split( '/(,|\s)/', $_POST['exclude_field_list'] );
				$this->settings['post_type'] = ( !empty( $_POST['custom_post'] ) ) ? $_POST['custom_post'] : NULL;
				$this->settings['post_status'] = ( !empty( $_POST['post_status'] ) ) ? $_POST['post_status'] : NULL;
				
				if ( !empty( $_POST['access_level'] ) ) $this->settings['access_level'] = $_POST['access_level'];
				if ( empty( $this->settings['access_level'] ) ) $this->settings['access_level'] = 'administrator';

				$this->settings['limit'] = 100;

				$this->save_settings();
			}

			$options = $this->settings;
		
			global $wpdb;
			$sql = "SELECT count(ID) FROM {$wpdb->posts} WHERE post_status IN ( 'publish', 'draft', 'future' )";
			$options['total_rows'] = $wpdb->get_var( $sql );
			$options['error'] =  $error;
			$sql = "SELECT DISTINCT post_status FROM {$wpdb->posts}";
			$options['post_status_list'] = array_unique( array_merge( $wpdb->get_col( $sql ), Array( 'publish', 'draft', 'future', 'private', 'trash' ) ) );

			$posts_model = new CPK_WPCSV_Posts_Model( $this->settings );

			$options['type_status_filters'] = $posts_model->get_post_type_status_combos( $this->settings['post_type_status_exclude_filter'] );

			$options['hc'] = new CPK_WPCSV_Html_Components( );

			$this->view->page( 'settings', $options );
			
		}

		public function export_page( ) {

			if ( !$this->authorized( ) ) wp_redirect( wp_get_referer( ) );

			$error = NULL;

			$filename = self::EXPORT_DOWNLOAD_PREFIX;
			$debug_filename = self::DEBUG_FILE_NAME;

			$settings = $this->settings;
			$settings['frontend']['export_id'] = wp_create_nonce( 'wpcsv_frontend_export' );
			$this->prepare_export( $settings );

			$settings['export_id'] = $settings['frontend']['export_id'];
			$settings['export_link'] = site_url( ) . "/wp-admin/admin.php?page=wpcsv-download&wpcsv_export={$settings['frontend']['export_id']}";
			$settings['debug_link'] = site_url( ) . "/wp-admin/admin.php?page=wpcsv-download&wpcsv_export=debug";
			
			$this->view->page( 'export', $settings );

		}

		public function import_page( ) {

			if ( !$this->authorized( ) ) wp_redirect( wp_get_referer( ) );

			$this->debug->clear( );
			$this->log->empty_table( );
			$max_memory = ini_get( 'memory_limit' );
			$max_execution_time = $this->wpcsv->get_max_execution_time( );
			$memory_usage = $this->wpcsv->get_memory_usage( );
			$this->log->add_message( __( "Max Memory: {$max_memory} (This is a server setting.)", 'wp-csv' ), 'Info' );
			$this->log->add_message( __( "Max Execution Time: {$max_execution_time} (This is a server setting.)", 'wp-csv' ), 'Info' );
			$this->log->add_message( __( "Initial Memory Usage: {$memory_usage}% (This is on the import page, before the plugin does any real work)", 'wp-csv' ), 'Info' );
			$this->log->store_messages( );
			
			$options = Array( );
			$options['error'] = '';
			$error = '';
			
			$file_destination = $this->settings['csv_path'] . '/' . self::IMPORT_FILE_NAME;

			# Prepare to upload file
			if ( empty( $_POST ) && empty( $_FILES ) ) {
				unset( $file_destination );
			} elseif ( !empty( $_POST ) ) { # File was uploaded

				if ( $_FILES['uploadedfile']['name'] == '' ) {
					$options['error'] = 'You must select a file to upload and import.';
				}

				if ( empty( $_FILES ) || ( isset( $_SERVER['CONTENT_LENGTH'] ) && (int)$_SERVER['CONTENT_LENGTH'] > $this->convert_to_bytes( ini_get( 'post_max_size' ) ) ) ) {
					$options['error'] = "The file you uploaded appears to be larger than your 'post_max_size' and/or 'upload_max_filesize' PHP ini settings!  Please make the file smaller or talk to your web host.";
				} 
				
				if ( !isset( $options['error'] ) || empty( $options['error'] ) ) {
					$source = $_FILES['uploadedfile']['tmp_name'];
					move_uploaded_file( $source, $file_destination );

					if ( file_exists( $file_destination ) ) {
						$line = fgetcsv( fopen( $file_destination, 'r' ), NULL, $this->settings['delimiter'], $this->settings['enclosure'] );
						if ( $line[0] != 'wp_ID' ) {
							$error = "<p>The file uploaded does not seem to be a valid WP CSV import file.  Please note that the column heading format changed in version 1.7.0! ('ID' became 'wp_ID', etc).</p> ";
							$error .= "<p>Expecting the first column to be 'wp_ID', found '{$line[0]}'</p>";
							$error .= "<p>File was uploaded to: {$file_destination}</p>";
						}
					}

					
					$options['file_name'] = $_FILES['uploadedfile']['name'];
					$options['error'] = $error;
				}

			}
			
			$this->view->page( 'import', $options );
		}
			
		public function report_page( ) {

			if ( !$this->authorized( ) ) wp_redirect( wp_get_referer( ) );

			$error = NULL;

			$this->log->add_message( __( "Limit: {$this->settings['limit']} (This is how many rows WP CSV can process at a time based on available server resources.  You should expect it to fluctuate.)", 'wp-csv' ), 'Info' );
			$this->log->store_messages( );
			$options = array_merge( Array( 'info_messages' => $this->log->get_message_list( 'Info' ), 'warning_messages' => $this->log->get_message_list( 'Warning' ), 'error_messages' => $this->log->get_message_list( 'Error' ) ), $this->settings );
			$options['error'] =  $error;
			$this->view->page( 'report', $options );

		}

		public function download_page( ) {

			if ( !$this->settings['frontend_shortcode'] || is_admin( ) ) { 
				if ( empty( $_GET['wpcsv_export'] ) || !$this->authorized( ) ) {
					wp_redirect( wp_get_referer( ) );
				}
			}
			
			$file_utility = new CPK_WPCSV_File_Utility( );

			# Clean export_id for security purposes
			$export_id = preg_replace( '/[^a-zA-Z0-9]/', '', $_GET['wpcsv_export'] );

			$date = date( 'YmdHis' );

			if ( $export_id == 'debug' ) {
				$file_path = $this->settings['csv_path'] . '/' . self::DEBUG_FILE_NAME;
				$download_name = "wpcsv-debug-{$date}.csv";
			} else {
				$file_path = $this->settings['csv_path'] . '/' . self::EXPORT_DOWNLOAD_PREFIX . $export_id . '.csv';
				$download_name = self::EXPORT_DOWNLOAD_PREFIX . "{$date}.csv";
			}
			$file_utility->set_file( $file_path );
			$file_utility->send_to_browser( $download_name, TRUE );
			
			wp_redirect( wp_get_referer( ) );
			die( );
		}

		private function authorized( ) {

			# Prevent unauthorized access
			if ( !function_exists( 'is_user_logged_in' ) ) exit;
			if ( !is_user_logged_in( ) ) exit;
			$current_user = wp_get_current_user( );
			$options = get_option( '_pws_wpcsv_settings' );
			if ( !current_user_can( $this->settings['access_level'] ) ) exit;

			return TRUE;
		}

		private function convert_to_bytes( $ini_size ) {
			$units = array( 'B'=>0, 'KB'=>1, 'M' => 2, 'MB'=>2, 'G' => 3, 'GB'=>3, 'TB'=>4, 'PB'=>5, 'EB'=>6, 'ZB'=>7, 'YB'=>8 );
			list( $number, $unit ) = preg_split('#(?<=\d)(?=[a-z])#i', $ini_size );

			return $number * pow( 1024, $units[ $unit ] );
		}

		public function file_permissions_problem( ) {
			if ( !$this->folder_writable( $this->settings['csv_path'] ) ) return TRUE;
			if ( !$this->file_writable( $this->settings['csv_path'] ) ) return TRUE;
		}

		public function display_notices() {

			if ( !$this->folder_writable( $this->settings['csv_path'] ) || !$this->file_writable( $this->settings['csv_path'] ) ) {
				$this->settings['csv_path'] = $this->get_csv_folder( );
				$this->save_settings( );
			}

			if ( $this->file_permissions_problem( ) && $_GET['page'] == 'wp-csv.php' ) {
				$paths = $this->get_paths( );

				$path_html = '';
				if ( is_array( $paths ) && !empty( $paths ) ) {
					foreach( $paths as $path ) {
						$path_html .= "<li>{$path}</li>";
					} # End foreach
				} # End if
				$html = "<div class='error'>
				<h4>WP CSV</h4><p>There is a problem with the file permissions on your server.  For the following locations, WP CSV was unable to create the 'wpcsv_backups' folder and/or unable to create a new file within that folder:</p><blockquote><ul>$path_html</ul></blockquote><p>These locations are listed in order of preference (from most secure to least secure, although precautions are taken to protect your data when the files are publicly accessible).</p>
				</div>";
				echo $html;
			}
			
		}

		public function save_settings( ) {
			update_option( $this->option_name, $this->settings );
			
			// A bit ugly but necessary, refactor later
			$this->csv->delimiter = $this->settings['delimiter'];
			$this->csv->enclosure = $this->settings['enclosure'];
			$this->csv->encoding = $this->settings['encoding'];

			$this->wpcsv->settings = $this->settings;
		}

		public function prepare_export( $settings = NULL ) {

			if ( !isset( $settings ) ) $settings = $this->settings;

			$this->wpcsv->prepare( $settings );
		}
		
		public function process_export( ) {
			
			if ( !isset( $_GET['export_id'] ) ) trigger_error( 'No export id provided!' );

			$export_id = $_GET['export_id'];

			$start 	= isset( $_GET['start'] ) ? $_GET['start'] : 0;

			$total = $this->wpcsv->get_total( $export_id );

			if ( $total == 0 ) {
				echo json_encode( Array( 'position' => 0, 'percentagecomplete' => -1 ) );
				die( );
			}

			$include_headings = ( $start == 0 );

			$number_processed = $this->wpcsv->export( $include_headings, $export_id );

			$position = $start + $number_processed;

			$ret_percentage = round( ( ( $position - 1 ) / $total ) * 100 );

			$errors = ob_get_clean( );

			if ( $errors ) {
				$this->log->add_message( $errors, 'Error' );
				$this->log->store_messages( );
				$status = ob_get_status( );
				if ( !empty( $status ) ) ob_clean( ); # Run again to ensure no extra output was created
			}

			echo json_encode( Array( 'position' => $position, 'percentagecomplete' => $ret_percentage, 'errors' => $errors ) );
			die( );
		}
		
		public function process_import( ) {

			$file = $this->settings['csv_path'] . '/' . self::IMPORT_FILE_NAME;
			
			$start = $_GET['start'];
			
			$this->csv->delimiter = $this->settings['delimiter'];

			$this->csv->enclosure = $this->settings['enclosure'];

			$total = ( $_GET['lines'] == 0 ) ? $this->csv->line_count( $file ) : $_GET['lines'];
			
			$rows = $this->csv->load( $file, $start, $this->settings['limit'] );
			
			$number_processed = $this->wpcsv->import( $rows );

			$position = $start + $number_processed;
			
			$ret_percentage = round( ( ( $position - 1 ) / $total ) * 100 );

			$errors = ob_get_clean( );

			if ( $errors ) {
				$this->log->add_message( 'Error Message', 'Error', $errors );
				$this->log->store_messages( );
				$status = ob_get_status( );
				if ( !empty( $status ) ) ob_clean( ); # Run again to ensure no extra output was created
			}

			echo json_encode( Array( 'position' => $position, 'percentagecomplete' => $ret_percentage, 'lines' => $total ) );
			die( );
		}
		
		public function export_shortcode( $atts ) {
			
			if ( !$this->settings['frontend_shortcode'] ) return 'CSV export is disabled!';

			$settings = shortcode_atts( $this->settings, $atts );

			$g = $_GET;

			if ( !empty( $g ) && isset( $g['wpcsv_export_id'] ) && wp_verify_nonce( $g['wpcsv_export_id'], 'wpcsv_frontend_export' ) ) {
				$settings['frontend']['export_id'] = $g['wpcsv_export_id'];
				$settings['frontend']['start_date'] = $g['wpcsv_start_date'];
				$settings['frontend']['end_date'] = $g['wpcsv_end_date'];
				$this->prepare_export( $settings );
			}

			ob_start( );

			include_once( dirname( __FILE__ ) . '/shortcode_view.php' );

			$output = ob_get_clean( );

			return $output;	
			
		}

		public function cpk_add_settings_link( $links, $file ) {
			if ( $file == 'wp-csv/wp-csv.php' ) {
				$settings_link = "<a href='admin.php?page=wpcsv-settings'>Settings</a>";
				$links = array_merge( $links, array( $settings_link ) );
			}

			return $links;
		}

		public function cpk_load_text_domain( ) {
			load_plugin_textdomain( 'wp-csv', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		}

	}
}

global $cpk_wpcsv;
$cpk_wpcsv = new CPK_WPCSV( );
