<?php
//Settings Class
require_once WP_SMUSH_DIR . "lib/class-wp-smush-settings.php";

//Migration Class
require_once WP_SMUSH_DIR . "lib/class-wp-smush-migrate.php";

//Stats
require_once WP_SMUSH_DIR . "lib/class-wp-smush-db.php";

//Include Resize class
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-resize.php';

//Include PNG to JPG Converter
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-png_jpg.php';

//Include Social Sharing
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-share.php';

//Include Image backup class
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-backup.php';

//Include Smush Async class
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-async.php';

if ( ! class_exists( 'WpSmush' ) ) {

	class WpSmush {

		var $version = WP_SMUSH_VERSION;

		/**
		 * @var Stores the value of is_pro function
		 */
		private $is_pro;

		/**
		 * Api server url to check api key validity
		 *
		 */
		var $api_server = 'https://premium.wpmudev.org/api/smush/v1/check/';

		/**
		 * Meta key to save smush result to db
		 *
		 *
		 */
		var $smushed_meta_key = 'wp-smpro-smush-data';

		/**
		 * Meta key to save migrated version
		 *
		 */
		var $migrated_version_key = "wp-smush-migrated-version";

		/**
		 * Super Smush is enabled or not
		 * @var bool
		 */
		var $lossy_enabled = false;

		/**
		 * Whether to Smush the original Image
		 * @var bool
		 */
		var $smush_original = false;

		/**
		 * Whether to Preserver the exif data or not
		 * @var bool
		 */
		var $keep_exif = false;

		/** @var  Attachment id for the Image being smushed currently */
		var $attachment_id;
		/**
		 * Attachment type, being smushed currently
		 *
		 * @var string Possible values, "wp", "nextgen"
		 *
		 */
		var $media_type = 'wp';

		/**
		 * Constructor
		 */
		function __construct() {

			//Redirect to Settings page
			add_action( 'activated_plugin', array( $this, 'wp_smush_redirect' ) );

			/**
			 * Smush image (Auto Smush ) when `wp_update_attachment_metadata` filter is fired
			 */
			add_filter( 'wp_update_attachment_metadata', array(
				$this,
				'smush_image'
			), 15, 2 );

			//Delete Backup files
			add_action( 'delete_attachment', array(
				$this,
				'delete_images'
			), 12 );

			//Optimise WP Retina 2x images
			add_action( 'wr2x_retina_file_added', array( $this, 'smush_retina_image' ), 20, 3 );

			//Add Smush Columns
			add_filter( 'manage_media_columns', array( $this, 'columns' ) );
			add_action( 'manage_media_custom_column', array( $this, 'custom_column' ), 10, 2 );
			add_filter( 'manage_upload_sortable_columns', array( $this, 'sortable_column' ) );
			//Manage column sorting
			add_action( 'pre_get_posts', array( $this, 'smushit_orderby' ) );

			//Enqueue Scripts, And Initialize variables
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			//Load NextGen Gallery, if hooked too late or early, auto smush doesn't works, also Load after settings have been saved on init action
			add_action( 'plugins_loaded', array( $this, 'load_nextgen' ), 90 );

			//Send Smush Stats for pro members
			add_filter( 'wpmudev_api_project_extra_data-912164', array( $this, 'send_smush_stats' ) );

			//Send Smush Stats for pro members
			add_action( 'wp_ajax_smush_show_warning', array( $this, 'show_warning_ajax' ) );

			//Instanitate the Async class
			add_action( 'plugins_loaded', array( $this, 'wp_smush_async' ) );

			//Handle the Async optimisation
			add_action( 'wp_async_wp_generate_attachment_metadata', array( $this, 'wp_smush_handle_async' ) );

			//Handle the Async optimisation
			add_action( 'wp_async_wp_save_image_editor_file', array( $this, 'wp_smush_handle_editor_async' ), '', 2 );

		}

		/**
		 * Initialise the setting variables
		 */
		function initialise() {

			global $wpsmush_settings;

			//Check if Lossy enabled
			$opt_lossy           = WP_SMUSH_PREFIX . 'lossy';
			$this->lossy_enabled = $this->validate_install() && $wpsmush_settings->get_setting( $opt_lossy, false );

			//Check if Smush Original enabled
			$opt_original         = WP_SMUSH_PREFIX . 'original';
			$this->smush_original = $this->validate_install() && $wpsmush_settings->get_setting( $opt_original, false );

			//Check Whether to keep exif or not
			$opt_keep_exif   = WP_SMUSH_PREFIX . 'keep_exif';
			$this->keep_exif = $wpsmush_settings->get_setting( $opt_keep_exif, false );
		}

		function admin_init() {

			global $wpsmush_dir;

			//Handle Notice dismiss
			$this->dismiss_smush_upgrade();

			//Perform Migration if required
			$this->migrate();

			//Initialize variables
			$this->initialise();

			//Create a clas object, if doesn't exists
			if ( empty( $wpsmush_dir ) && class_exists( 'WpSmushDir' ) ) {
				$wpsmush_dir = new WpSmushDir();
			}
			$wpsmush_dir->create_table();
		}

		/**
		 * Process an image with Smush.
		 *
		 * @param string $file_path Absolute path to the image
		 *
		 * @return array|bool
		 *
		 */
		function do_smushit( $file_path = '' ) {
			$errors   = new WP_Error();
			$dir_name = dirname( $file_path );

			//Check if file exists and the directory is writable
			if ( empty( $file_path ) ) {
				$errors->add( "empty_path", __( "File path is empty", 'wp-smushit' ) );
			} elseif ( ! file_exists( $file_path ) || ! is_file( $file_path ) ) {
				// check that the file exists
				$errors->add( "file_not_found", sprintf( __( "Could not find %s", 'wp-smushit' ), $file_path ) );
			} elseif ( ! is_writable( $dir_name ) ) {
				// check that the file is writable
				$errors->add( "not_writable", sprintf( __( "%s is not writable", 'wp-smushit' ), $dir_name ) );
			}

			$file_size = file_exists( $file_path ) ? filesize( $file_path ) : '';

			//Check if premium user
			$max_size = $this->validate_install() ? WP_SMUSH_PREMIUM_MAX_BYTES : WP_SMUSH_MAX_BYTES;

			//Check if file exists
			if ( $file_size == 0 ) {
				$errors->add( "image_not_found", '<p>' . sprintf( __( 'Skipped (%s), image not found. Attachment: %s', 'wp-smushit' ), size_format( $file_size, 1 ), basename( $file_path ) ) . '</p>' );
			} elseif ( $file_size > $max_size ) {
				//Check size limit
				$errors->add( "size_limit", '<p>' . sprintf( __( 'Skipped (%s), size limit exceeded. Attachment: %s', 'wp-smushit' ), size_format( $file_size, 1 ), basename( $file_path ) ) . '</p>' );
			}

			if ( count( $errors->get_error_messages() ) ) {
				return $errors;
			}

			// save original file permissions
			clearstatcache();
			$perms = fileperms( $file_path ) & 0777;

			/** Send image for smushing, and fetch the response */
			$response = $this->_post( $file_path, $file_size );

			if ( ! $response['success'] ) {
				$errors->add( "false_response", $response['message'] );
			} else if ( empty( $response['data'] ) ) {
				//If there is no data
				$errors->add( "no_data", __( 'Unknown API error', 'wp-smushit' ) );
			}

			if ( count( $errors->get_error_messages() ) ) {
				return $errors;
			}

			//If there are no savings, or image returned is bigger in size
			if ( ( ! empty( $response['data']->bytes_saved ) && intval( $response['data']->bytes_saved ) <= 0 )
			     || empty( $response['data']->image )
			) {
				return $response;
			}
			$tempfile = $file_path . ".tmp";

			//Add the file as tmp
			file_put_contents( $tempfile, $response['data']->image );

			//replace the file
			$success = @rename( $tempfile, $file_path );

			//if tempfile still exists, unlink it
			if ( file_exists( $tempfile ) ) {
				@unlink( $tempfile );
			}

			//If file renaming failed
			if ( ! $success ) {
				@copy( $tempfile, $file_path );
				@unlink( $tempfile );
			}

			//Some servers are having issue with file permission, this should fix it
			if ( empty( $perms ) || ! $perms ) {
				//Source: WordPress Core
				$stat  = stat( dirname( $file_path ) );
				$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
			}
			@ chmod( $file_path, $perms );

			return $response;
		}

		/**
		 * Fills $placeholder array with values from $data array
		 *
		 * @param array $placeholders
		 * @param array $data
		 *
		 * @return array
		 */
		function _array_fill_placeholders( array $placeholders, array $data ) {
			$placeholders['percent']     = $data['compression'];
			$placeholders['bytes']       = $data['bytes_saved'];
			$placeholders['size_before'] = $data['before_size'];
			$placeholders['size_after']  = $data['after_size'];
			$placeholders['time']        = $data['time'];

			return $placeholders;
		}

		/**
		 * Returns signature for single size of the smush api message to be saved to db;
		 *
		 * @return array
		 */
		function _get_size_signature() {
			return array(
				'percent'     => 0,
				'bytes'       => 0,
				'size_before' => 0,
				'size_after'  => 0,
				'time'        => 0
			);
		}

		/**
		 * Optimises the image sizes
		 *
		 * Note: Function name is bit confusing, it is for optimisation, and calls the resizing function as well
		 *
		 * Read the image paths from an attachment's meta data and process each image
		 * with wp_smushit().
		 *
		 * @param $meta
		 * @param null $ID
		 *
		 * @return mixed
		 */
		function resize_from_meta_data( $meta, $ID = null ) {

			global $wpsmush_settings;

			//Flag to check, if original size image should be smushed or not
			$original   = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'original', false );
			$smush_full = ( $this->validate_install() && $original == 1 ) ? true : false;

			$errors = new WP_Error();
			$stats  = array(
				"stats" => array_merge( $this->_get_size_signature(), array(
						'api_version' => - 1,
						'lossy'       => - 1,
						'keep_exif'   => false
					)
				),
				'sizes' => array()
			);

			$size_before = $size_after = $compression = $total_time = $bytes_saved = 0;

			if ( $ID && wp_attachment_is_image( $ID ) === false ) {
				return $meta;
			}

			//Set attachment id and Media type
			$this->attachment_id = $ID;
			$this->media_type    = "wp";

			//File path and URL for original image
			$attachment_file_path = get_attached_file( $ID );

			// If images has other registered size, smush them first
			if ( ! empty( $meta['sizes'] ) ) {

				//if smush original is set to false, otherwise smush
				//Check for large size, we will set a flag to leave the original untouched
				if ( ! $smush_full ) {
					if ( array_key_exists( 'large', $meta['sizes'] ) ) {
						$smush_full = false;
					} else {
						$smush_full = true;
					}
				}

				if ( class_exists( 'finfo' ) ) {
					$finfo = new finfo( FILEINFO_MIME_TYPE );
				} else {
					$finfo = false;
				}

				global $wpsmushit_admin;
				foreach ( $meta['sizes'] as $size_key => $size_data ) {

					//Check if registered size is supposed to be Smushed or not
					if ( 'full' != $size_key && $wpsmushit_admin->skip_image_size( $size_key ) ) {
						continue;
					}

					// We take the original image. The 'sizes' will all match the same URL and
					// path. So just get the dirname and replace the filename.
					$attachment_file_path_size = path_join( dirname( $attachment_file_path ), $size_data['file'] );

					if ( $finfo ) {
						$ext = file_exists( $attachment_file_path_size ) ? $finfo->file( $attachment_file_path_size ) : '';
					} elseif ( function_exists( 'mime_content_type' ) ) {
						$ext = mime_content_type( $attachment_file_path_size );
					} else {
						$ext = false;
					}
					if ( $ext ) {
						$valid_mime = array_search(
							$ext,
							array(
								'jpg' => 'image/jpeg',
								'png' => 'image/png',
								'gif' => 'image/gif',
							),
							true
						);
						if ( false === $valid_mime ) {
							continue;
						}
					}
					/**
					 * Allows to skip a image from smushing
					 *
					 * @param bool , Smush image or not
					 * @$size string, Size of image being smushed
					 */
					$smush_image = apply_filters( 'wp_smush_media_image', true, $size_key );
					if ( ! $smush_image ) {
						continue;
					}

					//Store details for each size key
					$response = $this->do_smushit( $attachment_file_path_size );

					if ( is_wp_error( $response ) ) {
						return $response;
					}

					//If there are no stats
					if ( empty( $response['data'] ) ) {
						continue;
					}

					//If the image size grew after smushing, skip it
					if ( $response['data']->after_size > $response['data']->before_size ) {
						continue;
					}

					//All Clear, Store the stat
					//@todo: Move the existing stats code over here, we don't need to do the stats part twice
					$stats['sizes'][ $size_key ] = (object) $this->_array_fill_placeholders( $this->_get_size_signature(), (array) $response['data'] );

					if ( empty( $stats['stats']['api_version'] ) || $stats['stats']['api_version'] == - 1 ) {
						$stats['stats']['api_version'] = $response['data']->api_version;
						$stats['stats']['lossy']       = $response['data']->lossy;
						$stats['stats']['keep_exif']   = ! empty( $response['data']->keep_exif ) ? $response['data']->keep_exif : 0;
					}
				}
				//Upfront Integration
				$stats = $this->smush_upfront_images( $ID, $stats );
			} else {
				$smush_full = true;
			}

			/**
			 * Allows to skip a image from smushing
			 *
			 * @param bool , Smush image or not
			 * @$size string, Size of image being smushed
			 */
			$smush_full_image = apply_filters( 'wp_smush_media_image', true, 'full' );

			//Whether to update the image stats or not
			$store_stats = true;

			//If original size is supposed to be smushed
			if ( $smush_full && $smush_full_image ) {

				$full_image_response = $this->do_smushit( $attachment_file_path );

				if ( is_wp_error( $full_image_response ) ) {
					return $full_image_response;
				}

				//If there are no stats
				if ( empty( $full_image_response['data'] ) ) {
					$store_stats = false;
				}

				//If the image size grew after smushing, skip it
				if ( $full_image_response['data']->after_size > $full_image_response['data']->before_size ) {
					$store_stats = false;
				}

				if ( $store_stats ) {
					$stats['sizes']['full'] = (object) $this->_array_fill_placeholders( $this->_get_size_signature(), (array) $full_image_response['data'] );
				}

				//Api version and lossy, for some images, full image i skipped and for other images only full exists
				//so have to add code again
				if ( empty( $stats['stats']['api_version'] ) || $stats['stats']['api_version'] == - 1 ) {
					$stats['stats']['api_version'] = $full_image_response['data']->api_version;
					$stats['stats']['lossy']       = $full_image_response['data']->lossy;
					$stats['stats']['keep_exif']   = ! empty( $full_image_response['data']->keep_exif ) ? $full_image_response['data']->keep_exif : 0;
				}

			}

			$has_errors = (bool) count( $errors->get_error_messages() );

			//Set smush status for all the images, store it in wp-smpro-smush-data
			if ( ! $has_errors ) {

				$existing_stats = get_post_meta( $ID, $this->smushed_meta_key, true );

				if ( ! empty( $existing_stats ) ) {

					//Update stats for each size
					if ( isset( $existing_stats['sizes'] ) && ! empty( $stats['sizes'] ) ) {

						foreach ( $existing_stats['sizes'] as $size_name => $size_stats ) {
							//if stats for a particular size doesn't exists
							if ( empty( $stats['sizes'][ $size_name ] ) ) {
								$stats['sizes'][ $size_name ] = $existing_stats['sizes'][ $size_name ];
							} else {

								$existing_stats_size = (object) $existing_stats['sizes'][ $size_name ];

								//store the original image size
								$stats['sizes'][ $size_name ]->size_before = ( ! empty( $existing_stats_size->size_before ) && $existing_stats_size->size_before > $stats['sizes'][ $size_name ]->size_before ) ? $existing_stats_size->size_before : $stats['sizes'][ $size_name ]->size_before;

								//Update compression percent and bytes saved for each size
								$stats['sizes'][ $size_name ]->bytes   = $stats['sizes'][ $size_name ]->bytes + $existing_stats_size->bytes;
								$stats['sizes'][ $size_name ]->percent = $this->calculate_percentage( $stats['sizes'][ $size_name ], $existing_stats_size );
							}
						}
					}
				}

				//Sum Up all the stats
				$stats = $this->total_compression( $stats );

				//If there was any compression and there was no error in smushing
				if ( isset( $stats['stats']['bytes'] ) && $stats['stats']['bytes'] >= 0 && ! $has_errors ) {
					/**
					 * Runs if the image smushing was successful
					 *
					 * @param int $ID Image Id
					 *
					 * @param array $stats Smush Stats for the image
					 *
					 */
					do_action( 'wp_smush_image_optimised', $ID, $stats );
				}
				update_post_meta( $ID, $this->smushed_meta_key, $stats );
			}

			unset( $stats );

			//Unset Response
			if ( ! empty( $response ) ) {
				unset( $response );
			}

			return $meta;
		}

		/**
		 * Read the image paths from an attachment's meta data and process each image
		 * with wp_smushit()
		 *
		 * @uses resize_from_meta_data
		 *
		 * @param $meta
		 * @param null $ID
		 *
		 * @return mixed
		 */
		function smush_image( $meta, $ID = null ) {

			//Check if Async is Enabled, Do not run
			if ( defined( 'WP_SMUSH_ASYNC' ) && WP_SMUSH_ASYNC ) {
				return $meta;
			}

			//Return directly if not a image
			if ( ! wp_attachment_is_image( $ID ) ) {
				return $meta;
			}

			//Check if we're restoring the image
			if ( get_transient( "wp-smush-restore-$ID" ) ) {
				return $meta;
			}

			/**
			 * Filter: wp_smush_image
			 *
			 * Whether to smush the given attachment id or not
			 *
			 * @param $skip bool, whether to Smush image or not
			 *
			 * @param $Attachment Id, Attachment id of the image being processed
			 *
			 */
			if ( ! apply_filters( 'wp_smush_image', true, $ID ) ) {
				return false;
			}

			//If the smushing transient is already set, return the status
			if ( get_transient( 'smush-in-progress-' . $ID ) ) {
				return $meta;
			}

			//Set a transient to avoid multiple request
			set_transient( 'smush-in-progress-' . $ID, true, 5 * MINUTE_IN_SECONDS );

			global $wpsmush_resize, $wpsmush_pngjpg, $wpsmush_settings;

			//Check if auto is enabled
			$auto_smush = $this->is_auto_smush_enabled();

			//Optionally Resize Images
			$meta = $wpsmush_resize->auto_resize( $ID, $meta );

			//Auto Smush the new image
			if ( $auto_smush ) {

				//Optionally Convert PNGs to JPG
				$meta = $wpsmush_pngjpg->png_to_jpg( $ID, $meta );

				/** Fix for Hostgator */
				//Check for use of http url, (Hostgator mostly)
				$use_http = wp_cache_get( WP_SMUSH_PREFIX . 'use_http', 'smush' );
				if ( ! $use_http ) {
					$use_http = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'use_http', false );
					wp_cache_add( WP_SMUSH_PREFIX . 'use_http', $use_http, 'smush' );
				}
				if ( $use_http ) {
					//HTTP Url
					define( 'WP_SMUSH_API_HTTP', 'http://smushpro.wpmudev.org/1.0/' );
				}

				$this->resize_from_meta_data( $meta, $ID );

			} else {
				//remove the smush metadata
				delete_post_meta( $ID, $this->smushed_meta_key );
			}

			//Delete Transient
			delete_transient( 'smush-in-progress-' . $ID );

			return $meta;
		}


		/**
		 * Posts an image to Smush.
		 *
		 * @param $file_path path of file to send to Smush
		 * @param $file_size
		 *
		 * @return bool|array array containing success status, and stats
		 */
		function _post( $file_path, $file_size ) {
			global $wpsmushit_admin, $wpsmush_settings;

			$data = false;

			$file_data = file_get_contents( $file_path );

			$headers   = array(
				'accept'       => 'application/json', // The API returns JSON
				'content-type' => 'application/binary', // Set content type to binary
			);

			//Check if premium member, add API key
			$api_key = $this->_get_api_key();
			if ( ! empty( $api_key ) ) {
				$headers['apikey'] = $api_key;
			}

			if ( $this->lossy_enabled && $this->validate_install() ) {
				$headers['lossy'] = 'true';
			} else {
				$headers['lossy'] = 'false';
			}

			$headers['exif'] = $this->keep_exif ? 'true' : 'false';

			$api_url = defined( 'WP_SMUSH_API_HTTP' ) ? WP_SMUSH_API_HTTP : WP_SMUSH_API;
			$args    = array(
				'headers'    => $headers,
				'body'       => $file_data,
				'timeout'    => WP_SMUSH_TIMEOUT,
				'user-agent' => WP_SMUSH_UA,
			);
			//Temporary Increase the limit
			@ini_set('memory_limit','256M');
			$result  = wp_remote_post( $api_url, $args );

			unset( $file_data );//free memory
			if ( is_wp_error( $result ) ) {

				$er_msg = $result->get_error_message();

				//Hostgator Issue
				if ( ! empty( $er_msg ) && strpos( $er_msg, 'SSL CA cert' ) !== false ) {
					//Update DB for using http protocol
					$wpsmush_settings->update_setting( WP_SMUSH_PREFIX . 'use_http', 1 );
				}
				//Handle error
				$data['message'] = sprintf( __( 'Error posting to API: %s', 'wp-smushit' ), $result->get_error_message() );
				$data['success'] = false;
				unset( $result ); //free memory
				return $data;
			} else if ( '200' != wp_remote_retrieve_response_code( $result ) ) {
				//Handle error
				$data['message'] = sprintf( __( 'Error posting to API: %s %s', 'wp-smushit' ), wp_remote_retrieve_response_code( $result ), wp_remote_retrieve_response_message( $result ) );
				$data['success'] = false;
				unset( $result ); //free memory

				return $data;
			}

			//If there is a response and image was successfully optimised
			$response = json_decode( $result['body'] );
			if ( $response && $response->success == true ) {

				//If there is any savings
				if ( $response->data->bytes_saved > 0 ) {
					$image     = base64_decode( $response->data->image ); //base64_decode is necessary to send binary img over JSON, no security problems here!
					$image_md5 = md5( $response->data->image );
					if ( $response->data->image_md5 != $image_md5 ) {
						//Handle error
						$data['message'] = __( 'Smush data corrupted, try again.', 'wp-smushit' );
						$data['success'] = false;
						unset( $image );//free memory
					} else {
						$data['success']     = true;
						$data['data']        = $response->data;
						$data['data']->image = $image;
						unset( $image );//free memory
					}
				} else {
					//just return the data
					$data['success'] = true;
					$data['data']    = $response->data;
				}
				//If is_premium is set in response, send it over to check for member validity
				if ( ! empty( $response->data ) && isset( $response->data->is_premium ) ) {
					$wpsmushit_admin->api_headers['is_premium'] = $response->data->is_premium;
				}
			} else {
				//Server side error, get message from response
				$data['message'] = ! empty( $response->data ) ? $response->data : __( "Image couldn't be smushed", 'wp-smushit' );
				$data['success'] = false;
			}

			unset( $result );//free memory
			unset( $response );//free memory
			return $data;
		}

		/**
		 * Print column header for Smush results in the media library using
		 * the `manage_media_columns` hook.
		 */
		function columns( $defaults ) {
			$defaults['smushit'] = 'WP Smush';

			return $defaults;
		}

		/**
		 * Return the filesize in a humanly readable format.
		 * Taken from http://www.php.net/manual/en/function.filesize.php#91477
		 */
		function format_bytes( $bytes, $precision = 1 ) {
			$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
			$bytes = max( $bytes, 0 );
			$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
			$pow   = min( $pow, count( $units ) - 1 );
			$bytes /= pow( 1024, $pow );

			return round( $bytes, $precision ) . $units[ $pow ];
		}

		/**
		 * Print column data for Smush results in the media library using
		 * the `manage_media_custom_column` hook.
		 */
		function custom_column( $column_name, $id ) {
			if ( 'smushit' == $column_name ) {
				$this->set_status( $id );
			}
		}

		/**
		 * Check if user is premium member, check for api key
		 *
		 * @return mixed|string
		 */
		function validate_install() {

			if ( isset( $this->is_pro ) ) {
				return $this->is_pro;
			}

			//no api key set, always false
			$api_key = $this->_get_api_key();

			if ( empty( $api_key ) ) {
				return false;
			}

			//Flag to check if we need to revalidate the key
			$revalidate = false;

			$api_auth = get_site_option( 'wp_smush_api_auth' );

			//Check if need to revalidate
			if ( ! $api_auth || empty( $api_auth ) || empty( $api_auth[ $api_key ] ) ) {
				$revalidate = true;
			} else {
				$last_checked = $api_auth[ $api_key ]['timestamp'];
				$valid        = $api_auth[ $api_key ]['validity'];

				$diff = $last_checked - current_time( 'timestamp' );

				//Difference in hours
				$diff_h = $diff / 3600;

				//Difference in minutes
				$diff_m = $diff / 60;

				switch ( $valid ) {
					case 'valid':
						//if last checked was more than 12 hours
						if ( $diff_h > 12 ) {
							$revalidate = true;
						}
						break;
					case 'invalid':
						//if last checked was more than 24 hours
						if ( $diff_h > 24 ) {
							$revalidate = true;
						}
						break;
					case 'network_failure':
						//if last checked was more than 5 minutes
						if ( $diff_m > 5 ) {
							$revalidate = true;
						}
						break;
				}
			}
			//If we are suppose to validate api, update the results in options table
			if ( $revalidate ) {

				if ( empty( $api_auth[ $api_key ] ) ) {
					//For api key resets
					$api_auth[ $api_key ] = array();

					//Storing it as valid, unless we really get to know from api call
					$api_auth[ $api_key ]['validity'] = 'valid';
				}

				//Aaron suggested to Update timestamp before making the api call, to avoid any concurrent calls, clever ;)
				$api_auth[ $api_key ]['timestamp'] = current_time( 'timestamp' );
				update_site_option( 'wp_smush_api_auth', $api_auth );

				// call api
				$url = $this->api_server . $api_key;

				$request = wp_remote_get( $url, array(
						"user-agent" => WP_SMUSH_UA,
						"timeout"    => 10
					)
				);

				if ( ! is_wp_error( $request ) && '200' == wp_remote_retrieve_response_code( $request ) ) {
					$result = json_decode( wp_remote_retrieve_body( $request ) );
					if ( ! empty( $result->success ) && $result->success ) {
						$valid = 'valid';
					} else {
						$valid = 'invalid';
					}

				} else {
					$valid = 'network_failure';
				}

				//Reset Value
				$api_auth = array();

				//Add a fresh Timestamp
				$timestamp            = current_time( 'timestamp' );
				$api_auth[ $api_key ] = array( 'validity' => $valid, 'timestamp' => $timestamp );

				//Update API validity
				update_site_option( 'wp_smush_api_auth', $api_auth );

			}

			$this->is_pro = ( 'valid' == $valid );

			return $this->is_pro;
		}

		/**
		 * Returns api key
		 *
		 * @return mixed
		 */
		function _get_api_key() {

			if ( defined( 'WPMUDEV_APIKEY' ) && WPMUDEV_APIKEY ) {
				$api_key = WPMUDEV_APIKEY;
			} else {
				$api_key = get_site_option( 'wpmudev_apikey' );
			}

			return $api_key;
		}

		/**
		 * Returns size saved from the api call response
		 *
		 * @param string $message
		 *
		 * @return string|bool
		 */
		function get_saved_size( $message ) {
			if ( preg_match( '/\((.*)\)/', $message, $matches ) ) {
				return isset( $matches[1] ) ? $matches[1] : false;
			}

			return false;
		}

		/**
		 * Set send button status
		 *
		 * @param $id
		 * @param bool $echo
		 * @param bool $text_only Returns the stats text instead of button
		 * @param bool $wrapper required for `column_html`, to include the wrapper div or not
		 *
		 * @return string|void
		 */
		function set_status( $id, $echo = true, $text_only = false, $wrapper = true ) {
			$status_txt  = $button_txt = $stats = '';
			$show_button = $show_resmush = false;

			$wp_smush_data      = get_post_meta( $id, $this->smushed_meta_key, true );
			$wp_resize_savings  = get_post_meta( $id, WP_SMUSH_PREFIX . 'resize_savings', true );
			$conversion_savings = get_post_meta( $id, WP_SMUSH_PREFIX . 'pngjpg_savings', true );

			$combined_stats = $this->combined_stats( $wp_smush_data, $wp_resize_savings );

			$combined_stats = $this->combine_conversion_stats( $combined_stats, $conversion_savings );

			$attachment_data = wp_get_attachment_metadata( $id );

			// if the image is smushed
			if ( ! empty( $wp_smush_data ) ) {

				$image_count    = count( $wp_smush_data['sizes'] );
				$bytes          = isset( $combined_stats['stats']['bytes'] ) ? $combined_stats['stats']['bytes'] : 0;
				$bytes_readable = ! empty( $bytes ) ? size_format( $bytes, 1 ) : '';
				$percent        = isset( $combined_stats['stats']['percent'] ) ? $combined_stats['stats']['percent'] : 0;
				$percent        = $percent < 0 ? 0 : $percent;

				if ( isset( $wp_smush_data['stats']['size_before'] ) && $wp_smush_data['stats']['size_before'] == 0 && ! empty( $wp_smush_data['sizes'] ) ) {
					$status_txt  = __( 'Already Optimized', 'wp-smushit' );
					$show_button = false;
				} else {
					if ( $bytes == 0 || $percent == 0 ) {
						$status_txt = __( 'Already Optimized', 'wp-smushit' );

						//Show resmush link, if the settings were changed
						$show_resmush = $this->show_resmush( $id, $wp_smush_data );
						if ( $show_resmush ) {
							$status_txt .= '<br />' . $this->get_resmsuh_link( $id );
						}

					} elseif ( ! empty( $percent ) && ! empty( $bytes_readable ) ) {
						$status_txt = $image_count > 1 ? sprintf( __( "%d images reduced ", 'wp-smushit' ), $image_count ) : __( "Reduced ", 'wp-smushit' );

						$stats_percent = number_format_i18n( $percent, 2, '.', '' );
						$stats_percent = $stats_percent > 0 ? sprintf( "(  %01.1f%% )", $stats_percent ) : '';
						$status_txt .= sprintf( __( "by %s %s", 'wp-smushit' ), $bytes_readable, $stats_percent );

						$file_path = get_attached_file( $id );
						$size      = file_exists( $file_path ) ? filesize( $file_path ) : 0;
						if ( $size > 0 ) {
							$size       = size_format( $size, 1 );
							$image_size = sprintf( __( "<br /> Image Size: %s", "wp-smushit" ), $size );
							$status_txt .= $image_size;
						}

						$show_resmush = $this->show_resmush( $id, $wp_smush_data );

						if ( $show_resmush ) {
							$status_txt .= '<br />' . $this->get_resmsuh_link( $id );
						}

						//Restore Image: Check if we need to show the restore image option
						$show_restore = $this->show_restore_option( $id, $attachment_data );

						if ( $show_restore ) {
							if ( $show_resmush ) {
								//Show Separator
								$status_txt .= ' | ';
							} else {
								//Show the link in next line
								$status_txt .= '<br />';
							}
							$status_txt .= $this->get_restore_link( $id );
						}

						//Detailed Stats: Show detailed stats if available
						if ( ! empty( $wp_smush_data['sizes'] ) ) {

							if ( $show_resmush || $show_restore ) {
								//Show Separator
								$status_txt .= ' | ';
							} else {
								//Show the link in next line
								$status_txt .= '<br />';
							}

							//Detailed Stats Link
							$status_txt .= sprintf( '<a href="#" class="wp-smush-action smush-stats-details wp-smush-title" tooltip="%s">%s [<span class="stats-toggle">+</span>]</a>', esc_html__( "Detailed stats for all the image sizes", "wp-smushit" ), esc_html__( "Smush stats", 'wp-smushit' ) );

							//Stats
							$stats = $this->get_detailed_stats( $id, $wp_smush_data, $attachment_data );

							if ( ! $text_only ) {
								$status_txt .= $stats;
							}
						}
					}
				}
				/** Super Smush Button  */
				//IF current compression is lossy
				if ( ! empty( $wp_smush_data ) && ! empty( $wp_smush_data['stats'] ) ) {
					$lossy    = ! empty( $wp_smush_data['stats']['lossy'] ) ? $wp_smush_data['stats']['lossy'] : '';
					$is_lossy = $lossy == 1 ? true : false;
				}

				//Check image type
				$image_type = get_post_mime_type( $id );

				//Check if premium user, compression was lossless, and lossy compression is enabled
				//If we are displaying the resmush option already, no need to show the Super Smush button
				if ( ! $show_resmush && $this->validate_install() && ! $is_lossy && $this->lossy_enabled && $image_type != 'image/gif' ) {
					// the button text
					$button_txt  = __( 'Super-Smush', 'wp-smushit' );
					$show_button = true;
				}

			} elseif ( get_transient( 'smush-in-progress-' . $id ) ) {
				// the status
				$status_txt = __( 'Smushing in progress..', 'wp-smushit' );

				//Set WP Smush data to true in order to show the text
				$wp_smush_data = true;

				// we need to show the smush button
				$show_button = false;

				// the button text
				$button_txt = '';
			} else {

				// the status
				$status_txt = __( 'Not processed', 'wp-smushit' );

				// we need to show the smush button
				$show_button = true;

				// the button text
				$button_txt = __( 'Smush Now!', 'wp-smushit' );
			}
			if ( $text_only ) {
				//For ajax response
				return array(
					'status'       => $status_txt,
					'stats'        => $stats,
					'show_warning' => intval( $this->show_warning() )
				);
			}

			//If we are not showing smush button, append progree bar, else it is already there
			if ( ! $show_button ) {
				$status_txt .= $this->progress_bar();
			}

			$text = $this->column_html( $id, $status_txt, $button_txt, $show_button, $wp_smush_data, $echo, $wrapper );
			if ( ! $echo ) {
				return $text;
			}
		}

		/**
		 * Print the column html
		 *
		 * @param string $id Media id
		 * @param string $status_txt Status text
		 * @param string $button_txt Button label
		 * @param boolean $show_button Whether to shoe the button
		 * @param bool $smushed Whether image is smushed or not
		 * @param bool $echo If true, it directly outputs the HTML
		 * @param bool $wrapper Whether to return the button with wrapper div or not
		 *
		 * @return string|void
		 */
		function column_html( $id, $status_txt = "", $button_txt = "", $show_button = true, $smushed = false, $echo = true, $wrapper = true ) {
			$allowed_images = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );

			// don't proceed if attachment is not image, or if image is not a jpg, png or gif
			if ( ! wp_attachment_is_image( $id ) || ! in_array( get_post_mime_type( $id ), $allowed_images ) ) {
				return;
			}

			$class = $smushed ? '' : ' hidden';
			$html  = '<p class="smush-status' . $class . '">' . $status_txt . '</p>';
			// if we aren't showing the button
			if ( ! $show_button ) {
				if ( $echo ) {
					echo $html;

					return;
				} else {
					if ( ! $smushed ) {
						$class = ' currently-smushing';
					} else {
						$class = ' smushed';
					}

					return $wrapper ? '<div class="smush-wrap' . $class . '">' . $html . '</div>' : $html;
				}
			}
			if ( ! $echo ) {
				$button_class = $wrapper ? 'button button-primary wp-smush-send' : 'button wp-smush-send';
				$html .= '
				<button  class="' . $button_class . '" data-id="' . $id . '">
	                <span>' . $button_txt . '</span>
				</button>';
				if ( ! $smushed ) {
					$class = ' unsmushed';
				} else {
					$class = ' smushed';
				}

				$html .= $this->progress_bar();
				$html = $wrapper ? '<div class="smush-wrap' . $class . '">' . $html . '</div>' : $html;

				return $html;
			} else {
				$html .= '<button class="button wp-smush-send" data-id="' . $id . '">
                    <span>' . $button_txt . '</span>
				</button>';
				$html = $html . $this->progress_bar();
				echo $html;
			}
		}

		/**
		 * Migrates smushit api message to the latest structure
		 *
		 *
		 * @return void
		 */
		function migrate() {

			if ( ! version_compare( $this->version, "1.7.1", "lte" ) ) {
				return;
			}

			$migrated_version = get_site_option( $this->migrated_version_key );

			if ( $migrated_version === $this->version ) {
				return;
			}

			global $wpdb;

			$q       = $wpdb->prepare( "SELECT * FROM `" . $wpdb->postmeta . "` WHERE `meta_key`=%s AND `meta_value` LIKE %s ", "_wp_attachment_metadata", "%wp_smushit%" );
			$results = $wpdb->get_results( $q );

			if ( count( $results ) < 1 ) {
				return;
			}

			$migrator = new WpSmushMigrate();
			foreach ( $results as $attachment_meta ) {
				$migrated_message = $migrator->migrate_api_message( maybe_unserialize( $attachment_meta->meta_value ) );
				if ( $migrated_message !== array() ) {
					update_post_meta( $attachment_meta->post_id, $this->smushed_meta_key, $migrated_message );
				}
			}

			update_site_option( $this->migrated_version_key, $this->version );

		}

		/**
		 * Updates the smush stats for a single image size
		 *
		 * @param $id
		 * @param $stats
		 * @param $image_size
		 */
		function update_smush_stats_single( $id, $smush_stats, $image_size = '' ) {
			//Return, if we don't have image id or stats for it
			if ( empty( $id ) || empty( $smush_stats ) || empty( $image_size ) ) {
				return false;
			}
			$data = $smush_stats['data'];
			//Get existing Stats
			$stats = get_post_meta( $id, $this->smushed_meta_key, true );
			//Update existing Stats
			if ( ! empty( $stats ) ) {

				//Update stats for each size
				if ( isset( $stats['sizes'] ) ) {

					//if stats for a particular size doesn't exists
					if ( empty( $stats['sizes'][ $image_size ] ) ) {
						//Update size wise details
						$stats['sizes'][ $image_size ] = (object) $this->_array_fill_placeholders( $this->_get_size_signature(), (array) $data );
					} else {
						//Update compression percent and bytes saved for each size
						$stats['sizes'][ $image_size ]->bytes   = $stats['sizes'][ $image_size ]->bytes + $data->bytes_saved;
						$stats['sizes'][ $image_size ]->percent = $stats['sizes'][ $image_size ]->percent + $data->compression;
					}
				}
			} else {
				//Create new stats
				$stats                         = array(
					"stats" => array_merge( $this->_get_size_signature(), array(
							'api_version' => - 1,
							'lossy'       => - 1
						)
					),
					'sizes' => array()
				);
				$stats['stats']['api_version'] = $data->api_version;
				$stats['stats']['lossy']       = $data->lossy;
				$stats['stats']['keep_exif']   = ! empty( $data->keep_exif ) ? $data->keep_exif : 0;

				//Update size wise details
				$stats['sizes'][ $image_size ] = (object) $this->_array_fill_placeholders( $this->_get_size_signature(), (array) $data );
			}
			//Calculate the total compression
			$stats = $this->total_compression( $stats );

			update_post_meta( $id, $this->smushed_meta_key, $stats );

		}

		/**
		 * Smush Retina images for WP Retina 2x, Update Stats
		 *
		 * @param $id
		 * @param $retina_file
		 * @param $image_size
		 */
		function smush_retina_image( $id, $retina_file, $image_size ) {

			//Initialize attachment id and media type
			$this->attachment_id = $id;
			$this->media_type    = "wp";

			/**
			 * Allows to Enable/Disable WP Retina 2x Integration
			 */
			$smush_retina_images = apply_filters( 'smush_retina_images', true );

			//Check if Smush retina images is enbled
			if ( ! $smush_retina_images ) {
				return;
			}
			//Check for Empty fields
			if ( empty( $id ) || empty( $retina_file ) || empty( $image_size ) ) {
				return;
			}

			//Do not smush if auto smush is turned off
			if ( ! $this->is_auto_smush_enabled() ) {
				return;
			}

			/**
			 * Allows to skip a image from smushing
			 *
			 * @param bool , Smush image or not
			 * @$size string, Size of image being smushed
			 */
			$smush_image = apply_filters( 'wp_smush_media_image', true, $image_size );
			if ( ! $smush_image ) {
				return;
			}

			$stats = $this->do_smushit( $retina_file );
			//If we squeezed out something, Update stats
			if ( ! is_wp_error( $stats ) && ! empty( $stats['data'] ) && isset( $stats['data'] ) && $stats['data']->bytes_saved > 0 ) {

				$image_size = $image_size . '@2x';

				$this->update_smush_stats_single( $id, $stats, $image_size );
			}
		}

		/**
		 * Return a list of images not smushed and reason
		 *
		 * @param $image_id
		 * @param $size_stats
		 * @param $attachment_metadata
		 *
		 * @return array
		 */
		function get_skipped_images( $image_id, $size_stats, $attachment_metadata ) {
			$skipped = array();

			//Get a list of all the sizes, Show skipped images
			$media_size = get_intermediate_image_sizes();

			//Full size
			$full_image = get_attached_file( $image_id );

			//If full image was not smushed, reason 1. Large Size logic, 2. Free and greater than 1Mb
			if ( ! array_key_exists( 'full', $size_stats ) ) {
				//For free version, Check the image size
				if ( ! $this->validate_install() ) {
					//For free version, check if full size is greater than 1 Mb, show the skipped status
					$file_size = file_exists( $full_image ) ? filesize( $full_image ) : '';
					if ( ! empty( $file_size ) && ( $file_size / WP_SMUSH_MAX_BYTES ) > 1 ) {
						$skipped[] = array(
							'size'   => 'full',
							'reason' => 'size_limit'
						);
					} else {
						$skipped[] = array(
							'size'   => 'full',
							'reason' => 'large_size'
						);
					}
				} else {
					//Paid version, Check if we have large size
					if ( array_key_exists( 'large', $size_stats ) ) {
						$skipped[] = array(
							'size'   => 'full',
							'reason' => 'large_size'
						);
					}

				}
			}
			//For other sizes, check if the image was generated and not available in stats
			if ( is_array( $media_size ) ) {
				foreach ( $media_size as $size ) {
					if ( array_key_exists( $size, $attachment_metadata['sizes'] ) && ! array_key_exists( $size, $size_stats ) && ! empty( $size['file'] ) ) {
						//Image Path
						$img_path   = path_join( dirname( $full_image ), $size['file'] );
						$image_size = file_exists( $img_path ) ? filesize( $img_path ) : '';
						if ( ! empty( $image_size ) && ( $image_size / WP_SMUSH_MAX_BYTES ) > 1 ) {
							$skipped[] = array(
								'size'   => 'full',
								'reason' => 'size_limit'
							);
						}
					}
				}
			}

			return $skipped;
		}

		/**
		 * Skip messages respective to their ids
		 *
		 * @param $msg_id
		 *
		 * @return bool
		 */
		function skip_reason( $msg_id ) {
			$count           = count( get_intermediate_image_sizes() );
			$smush_orgnl_txt = sprintf( esc_html__( "When you upload an image to WordPress it automatically creates %s thumbnail sizes that are commonly used in your pages. WordPress also stores the original full-size image, but because these are not usually embedded on your site we don’t Smush them. Pro users can override this.", 'wp_smushit' ), $count );
			$skip_msg        = array(
				'large_size' => $smush_orgnl_txt,
				'size_limit' => esc_html__( "Image couldn't be smushed as it exceeded the 1Mb size limit, Pro users can smush images with size upto 32Mb.", "wp-smushit" )
			);
			$skip_rsn        = ! empty( $skip_msg[ $msg_id ] ) ? esc_html__( " Skipped", 'wp-smushit', 'wp-smushit' ) : '';
			$skip_rsn        = ! empty( $skip_rsn ) ? $skip_rsn . '<span tooltip="' . $skip_msg[ $msg_id ] . '"><i class="dashicons dashicons-editor-help"></i></span>' : '';

			return $skip_rsn;
		}

		/**
		 * Shows the image size and the compression for each of them
		 *
		 * @param $image_id
		 * @param $wp_smush_data
		 *
		 * @return string
		 */
		function get_detailed_stats( $image_id, $wp_smush_data, $attachment_metadata ) {

			global $wpsmushit_admin;

			$stats      = '<div id="smush-stats-' . $image_id . '" class="smush-stats-wrapper hidden">
				<table class="wp-smush-stats-holder">
					<thead>
						<tr>
							<th><strong>' . esc_html__( 'Image size', 'wp-smushit' ) . '</strong></th>
							<th><strong>' . esc_html__( 'Savings', 'wp-smushit' ) . '</strong></th>
						</tr>
					</thead>
					<tbody>';
			$size_stats = $wp_smush_data['sizes'];

			//Reorder Sizes as per the maximum savings
			uasort( $size_stats, array( $this, "cmp" ) );

			if ( ! empty( $attachment_metadata['sizes'] ) ) {
				//Get skipped images
				$skipped = $this->get_skipped_images( $image_id, $size_stats, $attachment_metadata );

				if ( ! empty( $skipped ) ) {
					foreach ( $skipped as $img_data ) {
						$skip_class = $img_data['reason'] == 'size_limit' ? ' error' : '';
						$stats .= '<tr>
					<td>' . strtoupper( $img_data['size'] ) . '</td>
					<td class="smush-skipped' . $skip_class . '">' . $this->skip_reason( $img_data['reason'] ) . '</td>
				</tr>';
					}

				}
			}
			//Show Sizes and their compression
			foreach ( $size_stats as $size_key => $size_value ) {
				$dimensions = '';
				//Get the dimensions for the image size if available
				if ( ! empty( $wpsmushit_admin->image_sizes ) && ! empty( $wpsmushit_admin->image_sizes[ $size_key ] ) ) {
					$dimensions = $wpsmushit_admin->image_sizes[ $size_key ]['width'] . 'x' . $wpsmushit_admin->image_sizes[ $size_key ]['height'];
				}
				$dimensions = ! empty( $dimensions ) ? sprintf( " <br /> (%s)", $dimensions ) : '';
				if ( $size_value->bytes > 0 ) {
					$percent = round( $size_value->percent, 1 );
					$percent = $percent > 0 ? ' ( ' . $percent . '% )' : '';
					$stats .= '<tr>
					<td>' . strtoupper( $size_key ) . $dimensions . '</td>
					<td>' . size_format( $size_value->bytes, 1 ) . $percent . '</td>
				</tr>';
				}
			}
			$stats .= '</tbody>
				</table>
			</div>';

			return $stats;
		}

		/**
		 * Compare Values
		 *
		 * @param $a
		 * @param $b
		 *
		 * @return int
		 */
		function cmp( $a, $b ) {
			return $a->bytes < $b->bytes;
		}

		/**
		 * Check if NextGen is active or not
		 * Include and instantiate classes
		 */
		function load_nextgen() {
			if ( ! class_exists( 'C_NextGEN_Bootstrap' ) || ! $this->validate_install() ) {
				return;
			}

			global $wpsmush_settings;

			//Check if integration is Enabled or not
			//Smush NextGen key
			$opt_nextgen     = WP_SMUSH_PREFIX . 'nextgen';
			$opt_nextgen_val = $wpsmush_settings->get_setting( $opt_nextgen, false );
			if ( ! $opt_nextgen_val ) {
				return;
			}

			require_once( WP_SMUSH_DIR . '/lib/class-wp-smush-nextgen.php' );
			require_once( WP_SMUSH_DIR . '/lib/nextgen-integration/class-wp-smush-nextgen-admin.php' );
			require_once( WP_SMUSH_DIR . '/lib/nextgen-integration/class-wp-smush-nextgen-stats.php' );
			require_once( WP_SMUSH_DIR . '/lib/nextgen-integration/class-wp-smush-nextgen-bulk.php' );

			global $wpsmushnextgen, $wpsmushnextgenadmin, $wpsmushnextgenstats;
			//Initialize Nextgen support
			$wpsmushnextgen      = new WpSmushNextGen();
			$wpsmushnextgenstats = new WpSmushNextGenStats();
			$wpsmushnextgenadmin = new WpSmushNextGenAdmin();
			new WPSmushNextGenBulk();
		}

		/**
		 * Add the Smushit Column to sortable list
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		function sortable_column( $columns ) {
			$columns['smushit'] = 'smushit';

			return $columns;
		}

		/**
		 * Orderby query for smush columns
		 */
		function smushit_orderby( $query ) {

			global $current_screen;

			//Filter only media screen
			if ( ! is_admin() || ( ! empty( $current_screen ) && $current_screen->base != 'upload' ) ) {
				return;
			}

			$orderby = $query->get( 'orderby' );

			if ( isset( $orderby ) && 'smushit' == $orderby ) {
				$query->set( 'meta_query', array(
					'relation' => 'OR',
					array(
						'key'     => $this->smushed_meta_key,
						'compare' => 'EXISTS'
					),
					array(
						'key'     => $this->smushed_meta_key,
						'compare' => 'NOT EXISTS'
					)
				) );
				$query->set( 'orderby', 'meta_value_num' );
			}

			return $query;

		}

		/**
		 * If any of the image size have a backup file, show the restore option
		 *
		 * @param $attachment_data
		 *
		 * @return bool
		 */
		function show_restore_option( $image_id, $attachment_data ) {
			global $wpsmushit_admin;

			//No Attachment data, don't go ahead
			if ( empty( $attachment_data ) ) {
				return false;
			}

			//Get the image path for all sizes
			$file = get_attached_file( $image_id );

			//Check backup for Full size
			$backup = $wpsmushit_admin->get_image_backup_path( $file );

			//Check for backup of full image
			if ( file_exists( $backup ) ) {
				return true;
			}

			//Additional Backup Check for JPEGs converted from PNG
			$pngjpg_savings = get_post_meta( $image_id, WP_SMUSH_PREFIX . 'pngjpg_savings', true );
			if ( ! empty( $pngjpg_savings ) ) {
				//Get the original File path and check if it exists
				$backup = get_post_meta( $image_id, WP_SMUSH_PREFIX . 'original_file', true );
				$backup = $this->original_file( $backup );

				if ( ! empty( $backup ) && file_exists( $backup ) ) {
					return true;
				}
			}

			if ( ! empty( $attachment_data['sizes'] ) ) {
				//Check for backup of image sizes
				foreach ( $attachment_data['sizes'] as $image_size ) {
					$size_path        = path_join( dirname( $file ), $image_size['file'] );
					$size_backup_path = $wpsmushit_admin->get_image_backup_path( $size_path );
					if ( file_exists( $size_backup_path ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Returns a restore link for given image id
		 *
		 * @param $image_id
		 *
		 * @return bool|string
		 */
		function get_restore_link( $image_id, $type = 'wp' ) {
			if ( empty( $image_id ) ) {
				return false;
			}

			$class = 'wp-smush-action wp-smush-title';
			$class .= 'wp' == $type ? ' wp-smush-restore' : ' wp-smush-nextgen-restore';

			$ajax_nonce = wp_create_nonce( "wp-smush-restore-" . $image_id );

			return sprintf( '<a href="#" tooltip="%s" data-id="%d" data-nonce="%s" class="%s">%s</a>', esc_html__( "Restore original image.", "wp-smushit" ), $image_id, $ajax_nonce, $class, esc_html__( "Restore image", "wp-smushit" ) );
		}

		/**
		 * Returns the HTML for progress bar
		 *
		 * @return string
		 */
		function progress_bar() {
			return '<div class="wp-smush-progress animate hidden"><span></span></div>';
		}

		/**
		 * If auto smush is set to true or not, default is true
		 *
		 * @return int|mixed|void
		 */
		function is_auto_smush_enabled() {

			global $wpsmush_settings;

			$auto_smush = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'auto', false );

			//Keep the auto smush on by default
			if ( $auto_smush === false ) {
				$auto_smush = 1;
			}

			return $auto_smush;
		}

		/**
		 * Generates a Resmush link for a image
		 *
		 * @param $image_id
		 *
		 * @return bool|string
		 */
		function get_resmsuh_link( $image_id, $type = 'wp' ) {

			if ( empty( $image_id ) ) {
				return false;
			}
			$class = 'wp-smush-action wp-smush-title';
			$class .= 'wp' == $type ? ' wp-smush-resmush' : ' wp-smush-nextgen-resmush';

			$ajax_nonce = wp_create_nonce( "wp-smush-resmush-" . $image_id );

			return sprintf( '<a href="#" tooltip="%s" data-id="%d" data-nonce="%s" class="%s">%s</a>', esc_html__( "Smush image including original file.", "wp-smushit" ), $image_id, $ajax_nonce, $class, esc_html__( "Resmush image", "wp-smushit" ) );
		}

		/**
		 * Returns the backup path for attachment
		 *
		 * @param $attachment_path
		 *
		 * @return bool|string
		 *
		 */
		function get_image_backup_path( $attachment_path ) {
			//If attachment id is not available, return false
			if ( empty( $attachment_path ) ) {
				return false;
			}
			$path = pathinfo( $attachment_path );

			//If we don't have complete filename return false
			if ( empty( $path['extension'] ) ) {
				return false;
			}

			$backup_name = trailingslashit( $path['dirname'] ) . $path['filename'] . ".bak." . $path['extension'];

			return $backup_name;
		}

		/**
		 * Deletes all the backup files when an attachment is deleted
		 * Update Resmush List
		 * Update Super Smush image count
		 *
		 * @param $image_id
		 */
		function delete_images( $image_id ) {
			global $wpsmush_db;

			//Update the savings cache
			$wpsmush_db->resize_savings( true );

			//Update the savings cache
			$wpsmush_db->conversion_savings( true );

			//If no image id provided
			if ( empty( $image_id ) ) {
				return false;
			}

			//Check and Update resmush list
			if ( $resmush_list = get_option( 'wp-smush-resmush-list' ) ) {
				global $wpsmushit_admin;
				$wpsmushit_admin->update_resmush_list( $image_id, 'wp-smush-resmush-list' );
			}

			/** Delete Backups  **/

			//Check if we have any smush data for image
			$this->delete_backup_files( $image_id );
		}

		/**
		 * Return Global stats
		 *
		 * @return array|bool|mixed
		 */
		function send_smush_stats() {
			global $wpsmushit_admin;

			$stats = $wpsmushit_admin->global_stats_from_ids();

			return $stats;

		}

		/**
		 * Smushes the upfront images and Updates the respective stats
		 *
		 * @param $attachment_id
		 * @param $stats
		 *
		 * @return mixed
		 */
		function smush_upfront_images( $attachment_id, $stats ) {
			//Check if upfront is active or not
			if ( empty( $attachment_id ) || ! class_exists( 'Upfront' ) ) {
				return $stats;
			}

			//Set attachment id and Media type
			$this->attachment_id = $attachment_id;
			$this->media_type    = "upfront";

			//Get post meta to check for Upfront images
			$upfront_images = get_post_meta( $attachment_id, 'upfront_used_image_sizes', true );

			//If there is no upfront meta for the image
			if ( ! $upfront_images || empty( $upfront_images ) || ! is_array( $upfront_images ) ) {
				return $stats;
			}
			//Loop over all the images in upfront meta
			foreach ( $upfront_images as $element_id => $image ) {
				if ( isset( $image['is_smushed'] ) && 1 == $image['is_smushed'] ) {
					continue;
				}
				//Get the image path and smush it
				if ( isset( $image['path'] ) && file_exists( $image['path'] ) ) {
					$res = $this->do_smushit( $image['path'] );
					//If sizes key is not yet initialised
					if ( empty( $stats['sizes'] ) ) {
						$stats['sizes'] = array();
					}

					//If the smushing was successful
					if ( ! is_wp_error( $res ) && ! empty( $res['data'] ) ) {
						if ( $res['data']->bytes_saved > 0 ) {
							//Update attachment stats
							$stats['sizes'][ $element_id ] = (object) $this->_array_fill_placeholders( $this->_get_size_signature(), (array) $res['data'] );
						}

						//Update upfront stats for the element id
						$upfront_images[ $element_id ]['is_smushed'] = 1;
					}
				}
			}
			//Finally Update the upfront meta key
			update_post_meta( $attachment_id, 'upfront_used_image_sizes', $upfront_images );

			return $stats;

		}

		/**
		 * Checks the current settings and returns the value whether to enable or not the resmush option
		 *
		 * @param $show_resmush
		 * @param $wp_smush_data
		 *
		 * @return bool
		 */
		function show_resmush( $id = '', $wp_smush_data ) {
			//Resmush: Show resmush link, Check if user have enabled smushing the original and full image was skipped
			//Or: If keep exif is unchecked and the smushed image have exif
			//PNG To JPEG
			if ( $this->smush_original ) {
				//IF full image was not smushed
				if ( ! empty( $wp_smush_data ) && empty( $wp_smush_data['sizes']['full'] ) ) {
					return true;
				}
			}

			//EXIF Check
			if ( ! $this->keep_exif ) {
				//If Keep Exif was set to true initially, and since it is set to false now
				if ( isset( $wp_smush_data['stats']['keep_exif'] ) && $wp_smush_data['stats']['keep_exif'] == 1 ) {
					return true;
				}
			}

			//PNG to JPEG
			global $wpsmush_pngjpg;
			if ( $wpsmush_pngjpg->can_be_converted( $id ) ) {
				return true;
			}

			return false;

		}

		/**
		 * Calculate saving percentage from existing and current stats
		 *
		 * @param $stats
		 * @param $existing_stats
		 *
		 * @return float
		 */
		function calculate_percentage( $stats = '', $existing_stats = '' ) {
			if ( empty( $stats ) || empty( $existing_stats ) ) {
				return 0;
			}
			$size_before = ! empty( $stats->size_before ) ? $stats->size_before : $existing_stats->size_before;
			$size_after  = ! empty( $stats->size_after ) ? $stats->size_after : $existing_stats->size_after;
			$savings     = $size_before - $size_after;
			if ( $savings > 0 ) {
				$percentage = ( $savings / $size_before ) * 100;
				$percentage = $percentage > 0 ? round( $percentage, 2 ) : $percentage;

				return $percentage;
			}

			return 0;
		}

		/**
		 * Calculate saving percentage for a given size stats
		 *
		 * @param $stats
		 *
		 * @return float|int
		 */
		function calculate_percentage_from_stats( $stats ) {
			if ( empty( $stats ) || ! isset( $stats->size_before, $stats->size_after ) ) {
				return 0;
			}

			$savings = $stats->size_before - $stats->size_after;
			if ( $savings > 0 ) {
				$percentage = ( $savings / $stats->size_before ) * 100;
				$percentage = $percentage > 0 ? round( $percentage, 2 ) : $percentage;

				return $percentage;
			}
		}

		/**
		 * Redirects the user to Plugin settings page on Plugin activation
		 *
		 * @param $plugin Plugin Name
		 *
		 * @return bool
		 */
		function wp_smush_redirect( $plugin ) {

			global $wpsmush_db;

			//Run for only our plugin
			if ( $plugin != WP_SMUSH_BASENAME ) {
				return false;
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return true;
			}

			//Skip if bulk activation, Or if we have to skip redirection
			if ( isset( $_GET['activate-multi'] ) || get_site_option( 'wp-smush-skip-redirect' ) ) {
				return false;
			}

			//If images are already smushed
			if ( $wpsmush_db->smushed_count( false ) > 0 ) {
				return false;
			}

			$url = admin_url( 'upload.php' );
			$url = add_query_arg(
				array(
					'page' => 'wp-smush-bulk'
				),
				$url
			);

			//Store that we need not redirect again
			add_site_option( 'wp-smush-skip-redirect', true );

			exit( wp_redirect( $url ) );
		}

		/**
		 * Clear up all the backup files for the image, if any
		 *
		 * @param $image_id
		 */
		function delete_backup_files( $image_id ) {
			$smush_meta = get_post_meta( $image_id, $this->smushed_meta_key, true );
			if ( empty( $smush_meta ) ) {
				//Return if we don't have any details
				return;
			}

			//Get the attachment details
			$meta = wp_get_attachment_metadata( $image_id );

			//Attachment file path
			$file = get_attached_file( $image_id );

			//Get the backup path
			$backup_name = $this->get_image_backup_path( $file );

			//If file exists, corresponding to our backup path, delete it
			@ unlink( $backup_name );

			//Check meta for rest of the sizes
			if ( ! empty( $meta ) && ! empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size ) {
					//Get the file path
					if ( empty( $size['file'] ) ) {
						continue;
					}

					//Image Path and Backup path
					$image_size_path  = path_join( dirname( $file ), $size['file'] );
					$image_bckup_path = $this->get_image_backup_path( $image_size_path );
					@unlink( $image_bckup_path );
				}
			}
		}

		/**
		 * Manually Dismiss Smush Upgrade notice
		 */
		function dismiss_smush_upgrade() {
			if ( isset( $_GET['remove_smush_upgrade_notice'] ) && 1 == $_GET['remove_smush_upgrade_notice'] ) {
				global $wpsmushit_admin;
				$wpsmushit_admin->dismiss_upgrade_notice( false );
			}
		}

		/**
		 * Iterate over all the size stats and calculate the total stats
		 *
		 * @param $stats
		 *
		 * @return mixed
		 */
		function total_compression( $stats ) {
			$stats['stats']['size_before'] = $stats['stats']['size_after'] = $stats['stats']['time'] = 0;
			foreach ( $stats['sizes'] as $size_stats ) {
				$stats['stats']['size_before'] += ! empty( $size_stats->size_before ) ? $size_stats->size_before : 0;
				$stats['stats']['size_after'] += ! empty( $size_stats->size_after ) ? $size_stats->size_after : 0;
				$stats['stats']['time'] += ! empty( $size_stats->time ) ? $size_stats->time : 0;
			}
			$stats['stats']['bytes'] = ! empty( $stats['stats']['size_before'] ) && $stats['stats']['size_before'] > $stats['stats']['size_after'] ? $stats['stats']['size_before'] - $stats['stats']['size_after'] : 0;
			if ( ! empty( $stats['stats']['bytes'] ) && ! empty( $stats['stats']['size_before'] ) ) {
				$stats['stats']['percent'] = ( $stats['stats']['bytes'] / $stats['stats']['size_before'] ) * 100;
			}

			return $stats;
		}

		/**
		 * Smush and Resizing Stats Combined together
		 *
		 * @param $smush_stats
		 * @param $resize_savings
		 *
		 * @return array Array of all the stats
		 */
		function combined_stats( $smush_stats, $resize_savings ) {
			if ( empty( $smush_stats ) || empty( $resize_savings ) ) {
				return $smush_stats;
			}

			//Full Image
			if ( ! empty( $smush_stats['sizes']['full'] ) ) {
				$smush_stats['sizes']['full']->bytes       = ! empty( $resize_savings['bytes'] ) ? $smush_stats['sizes']['full']->bytes + $resize_savings['bytes'] : $smush_stats['sizes']['full']->bytes;
				$smush_stats['sizes']['full']->size_before = ! empty( $resize_savings['size_before'] ) && ( $resize_savings['size_before'] > $smush_stats['sizes']['full']->size_before ) ? $resize_savings['size_before'] : $smush_stats['sizes']['full']->size_before;
				$smush_stats['sizes']['full']->percent     = ! empty( $smush_stats['sizes']['full']->bytes ) && $smush_stats['sizes']['full']->size_before > 0 ? ( $smush_stats['sizes']['full']->bytes / $smush_stats['sizes']['full']->size_before ) * 100 : $smush_stats['sizes']['full']->percent;

				$smush_stats['sizes']['full']->percent = round( $smush_stats['sizes']['full']->percent, 1 );
			}

			$smush_stats = $this->total_compression( $smush_stats );

			return $smush_stats;
		}

		/**
		 * Combine Savings from PNG to JPG conversion with smush stats
		 *
		 * @param $stats Savings from Smushing the image
		 * @param $conversion_savings Savings from converting the PNG to JPG
		 *
		 * @return Object Total Savings
		 */
		function combine_conversion_stats( $stats, $conversion_savings ) {
			if ( empty( $stats ) || empty( $conversion_savings ) ) {
				return $stats;
			}
			foreach ( $conversion_savings as $size_k => $savings ) {
				if ( ! empty( $stats['sizes'][ $size_k ] ) && ! empty( $savings ) ) {
					$stats['sizes'][ $size_k ]->bytes       = $stats['sizes'][ $size_k ]->bytes + $savings['bytes'];
					$stats['sizes'][ $size_k ]->size_before = $stats['sizes'][ $size_k ]->size_before > $savings['size_before'] ? $stats['sizes'][ $size_k ]->size_before : $savings['size_before'];
					$stats['sizes'][ $size_k ]->percent     = ! empty( $stats['sizes'][ $size_k ]->bytes ) && $stats['sizes'][ $size_k ]->size_before > 0 ? ( $stats['sizes'][ $size_k ]->bytes / $stats['sizes'][ $size_k ]->size_before ) * 100 : $stats['sizes'][ $size_k ]->percent;
					$stats['sizes'][ $size_k ]->percent     = round( $stats['sizes'][ $size_k ]->percent, 1 );
				}
			}

			$stats = $this->total_compression( $stats );

			return $stats;
		}

		/**
		 * Original File path
		 *
		 * @param string $original_file
		 *
		 * @return string File Path
		 *
		 */
		function original_file( $original_file = '' ) {
			$uploads     = wp_get_upload_dir();
			$upload_path = $uploads['basedir'];

			return path_join( $upload_path, $original_file );
		}

		/**
		 * Check whether to show warning or not for Pro users, if they don't have a valid install
		 *
		 * @return bool
		 *
		 */
		function show_warning() {
			//If it's a free setup, Go back right away!
			if ( ! $this->validate_install() ) {
				return false;
			}

			global $wpsmushit_admin;
			// Return. If we don't have any headers
			if ( ! isset( $wpsmushit_admin->api_headers ) ) {
				return false;
			}

			//Show warning, if function says it's premium and api says not premium
			if ( isset( $wpsmushit_admin->api_headers['is_premium'] ) && ! intval( $wpsmushit_admin->api_headers['is_premium'] ) ) {
				return true;
			}

			return false;

		}

		/**
		 * Send JSON response whether to show or not the warning
		 */
		function show_warning_ajax() {
			$show = $this->show_warning();
			wp_send_json( intval( $show ) );
		}

		/**
		 * Initialize the Smush Async class
		 */
		function wp_smush_async() {

			//Don't load the Async task, if user not logged in or not in backend
			if ( ! is_user_logged_in() || ! is_admin() ) {
				return;
			}

			//Check if Async is disabled
			if ( defined( 'WP_SMUSH_ASYNC' ) && ! WP_SMUSH_ASYNC ) {
				return;
			}

			//Instantiate Class
			new WpSmushAsync();
			new WpSmushEditorAsync();
		}

		/**
		 * Send a smush request for the attachment
		 *
		 * @param $id Attachment ID
		 */
		function wp_smush_handle_async( $id ) {

			//If we don't have image id, or the smush is already in progress for the image, return
			if ( empty( $id ) || get_transient( 'smush-in-progress-' . $id ) ) {
				return;
			}

			//If auto Smush is disabled
			if ( ! $this->is_auto_smush_enabled() ) {
				return;
			}

			//Check if we're restoring the image
			if ( get_transient( "wp-smush-restore-$id" ) ) {
				return;
			}

			/**
			 * Filter: wp_smush_image
			 *
			 * Whether to smush the given attachment id or not
			 *
			 * @param $skip bool, whether to Smush image or not
			 *
			 * @param $Attachment Id, Attachment id of the image being processed
			 *
			 */
			if ( ! apply_filters( 'wp_smush_image', true, $id ) ) {
				return;
			}

			global $wpsmushit_admin;
			$wpsmushit_admin->smush_single( $id, true );

		}

		/**
		 * Send a smush request for the attachment
		 *
		 * @param $id Attachment ID
		 */
		function wp_smush_handle_editor_async( $id, $post_data ) {

			//If we don't have image id, or the smush is already in progress for the image, return
			if ( empty( $id ) || get_transient( 'smush-in-progress-' . $id ) ) {
				return;
			}

			//If auto Smush is disabled
			if ( ! $this->is_auto_smush_enabled() ) {
				return;
			}

			/**
			 * Filter: wp_smush_image
			 *
			 * Whether to smush the given attachment id or not
			 *
			 * @param $skip bool, whether to Smush image or not
			 *
			 * @param $Attachment Id, Attachment id of the image being processed
			 *
			 */
			if ( ! apply_filters( 'wp_smush_image', true, $id ) ) {
				return;
			}

			//If filepath is not set or file doesn't exists
			if ( ! isset( $post_data['filepath'] ) || ! file_exists( $post_data['filepath'] ) ) {
				return;
			}

			//Send image for smushing
			$res = $this->do_smushit( $post_data['filepath'] );

			//Exit if smushing wasn't successful
			if ( is_wp_error( $res) || empty( $res['success'] ) || ! $res['success'] ) {
				return;
			}

			//Update stats if it's the full size image
			//Return if it's not the full image size
			if ( $post_data['filepath'] != get_attached_file( $post_data['postid'] ) ) {
				return;
			}

			//Get the existing Stats
			$smush_stats = get_post_meta( $post_data['postid'], $this->smushed_meta_key, true );
			$stats_full  = ! empty( $smush_stats['sizes'] ) && ! empty( $smush_stats['sizes']['full'] ) ? $smush_stats['sizes']['full'] : '';

			if ( empty( $stats_full ) ) {
				return;
			}

			//store the original image size
			$stats_full->size_before = ( ! empty( $stats_full->size_before ) && $stats_full->size_before > $res['data']->before_size ) ? $stats_full->size_before : $res['data']->before_size;
			$stats_full->size_after  = $res['data']->after_size;

			//Update compression percent and bytes saved for each size
			$stats_full->bytes = $stats_full->size_before - $stats_full->size_after;

			$stats_full->percent          = $this->calculate_percentage_from_stats( $stats_full );
			$smush_stats['sizes']['full'] = $stats_full;

			//Update Stats
			update_post_meta( $post_data['postid'], $this->smushed_meta_key, $smush_stats );
		}
	}

	global $WpSmush;
	$WpSmush = new WpSmush();

}

//Include Admin classes
require_once( WP_SMUSH_DIR . 'lib/class-wp-smush-admin.php' );

//Include Directory Smush
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-dir.php';