<?php
/**
 * @package WP Smush
 * @subpackage Admin
 * @version 2.3
 *
 * @author Umesh Kumar <umesh@incsub.com>
 *
 * @copyright (c) 2016, Incsub (http://incsub.com)
 */
if ( ! class_exists( 'WpSmushResize' ) ) {

	/**
	 * Class WpSmushResize
	 */
	class WpSmushResize {

		/**
		 * @var int Specified width for resizing images
		 *
		 */
		public $max_w = 0;

		/**
		 * @var int Specified Height for resizing images
		 *
		 */
		public $max_h = 0;

		/**
		 * @var bool If resizing is enabled or not
		 */
		public $resize_enabled = false;

		function __construct() {
			/**
			 * Initialize class variables, after all stuff has been loaded
			 */
			add_action( 'admin_init', array( $this, 'initialize' ) );

		}

		/**
		 * Get the settings for resizing
		 */
		function initialize() {
			//Do not initialize unless in the WP Backend Or On one of the smush pages
			if ( ! is_user_logged_in() || ! is_admin() ) {
				return;
			}

			global $wpsmush_settings, $wpsmushit_admin;
			$current_screen = get_current_screen();

			if( !empty( $current_screen ) ) {
				//Do not Proceed if not on one of the required screens
				$current_page = $current_screen->base;
				if ( ! in_array( $current_page, $wpsmushit_admin->pages ) ) {
					return;
				}
			}

			//If resizing is enabled
			$this->resize_enabled = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'resize' );

			$resize_sizes = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'resize_sizes', array() );

			//Resize width and Height
			$this->max_w = ! empty( $resize_sizes['width'] ) ? $resize_sizes['width'] : 0;
			$this->max_h = ! empty( $resize_sizes['height'] ) ? $resize_sizes['height'] : 0;
		}

		/**
		 *  Check whether Image should be resized or not
		 * @param string $id Attachment ID
		 * @param string $meta Attachment Metadata
		 *
		 * @return bool Should resize or not
		 *
		 */
		public function should_resize( $id = '', $meta = '' ) {

			global $wpsmush_helper;

			//If resizing not enabled, or if both max width and height is set to 0, return
			if ( ! $this->resize_enabled || ( $this->max_w == 0 && $this->max_h == 0 ) ) {
				return false;
			}

			$file_path = $wpsmush_helper->get_attached_file( $id );

			if ( ! empty( $file_path ) ) {

				// Skip: if "noresize" is included in the filename, Thanks to Imsanity
				if ( strpos( $file_path, 'noresize' ) !== false ) {
					return false;
				}

				//If file doesn't exists, return
				if ( ! file_exists( $file_path ) ) {
					return false;
				}

			}

			//Check for a supported mime type
			global $wpsmushit_admin, $wpsmush_settings;

			//Get image mime type
			$mime = get_post_mime_type( $id );

			$mime_supported = in_array( $mime, $wpsmushit_admin->mime_types );

			//If type of upload doesn't matches the criteria return
			if ( ! empty( $mime ) && ! $mime_supported = apply_filters( 'wp_smush_resmush_mime_supported', $mime_supported, $mime ) ) {
				return false;
			}

			//Check if already resized
			$resize_meta = get_post_meta( $id, WP_SMUSH_PREFIX . 'resize_savings', true );
			if ( ! empty( $resize_meta ) ) {
				return false;
			}

			//Get attachment metadata
			$meta = empty( $meta ) ? wp_get_attachment_metadata( $id ) : $meta;

			if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
				$oldW = $meta['width'];
				$oldH = $meta['height'];

				$resize_dim = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'resize_sizes' );

				$maxW = ! empty( $resize_dim['width'] ) ? $resize_dim['width'] : 0;
				$maxH = ! empty( $resize_dim['height'] ) ? $resize_dim['height'] : 0;

				if ( ( $oldW > $maxW && $maxW > 0 ) || ( $oldH > $maxH && $maxH > 0 ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Handles the Auto resizing of new uploaded images
		 *
		 * @param $id Attachment ID
		 * @param $meta Attachment Metadata
		 *
		 * @return mixed Updated/Original Metadata if image was resized or not
		 */
		function auto_resize( $id, $meta ) {

			if ( empty( $id ) || ! wp_attachment_is_image( $id ) ) {
				return $meta;
			}

			//Do not perform resize while restoring images/ Editing images
			if ( ! empty( $_REQUEST['do'] ) && ( 'restore' == $_REQUEST['do'] || 'scale' == $_REQUEST['do'] ) ) {
				return $meta;
			}

			global $wpsmush_helper;

			$savings = array(
				'bytes'       => 0,
				'size_before' => 0,
				'size_after'  => 0
			);

			//Check if the image should be resized or not
			$should_resize = $this->should_resize( $id, $meta );

			/**
			 * Filter whether the uploaded image should be resized or not
			 *
			 * @since 2.3
			 *
			 * @param bool $should_resize
			 *
			 * @param array $upload {
			 *    Array of upload data.
			 *
			 * @type string $file Filename of the newly-uploaded file.
			 * @type string $url URL of the uploaded file.
			 * @type string $type File type.
			 * }
			 *
			 * @param string $context The type of upload action. Values include 'upload' or 'sideload'.
			 *
			 */
			if ( ! $should_resize = apply_filters( 'wp_smush_resize_uploaded_image', $should_resize, $id, $meta ) ) {
				return $meta;
			}

			//Good to go
			$file_path = $wpsmush_helper->get_attached_file( $id );

			$original_file_size = filesize( $file_path );

			$resize = $this->perform_resize( $file_path, $original_file_size, $id, $meta );

			//If resize wasn't successful
			if ( ! $resize || $resize['filesize'] == $original_file_size ) {
				update_post_meta( $id, WP_SMUSH_PREFIX . 'resize_savings', $savings );
				return $meta;
			}

			//Else Replace the Original file with resized file
			$replaced = $this->replcae_original_image( $file_path, $resize, $id, $meta );

			if ( $replaced ) {
				//Clear Stat Cache, Else the size obtained is same as the original file size
				clearstatcache();

				//Updated File size
				$u_file_size = filesize( $file_path );

				$savings['bytes']       = $original_file_size > $u_file_size ? $original_file_size - $u_file_size : 0;
				$savings['size_before'] = $original_file_size;
				$savings['size_after']  = $u_file_size;

				//Store savings in meta data
				if ( ! empty( $savings ) ) {
					update_post_meta( $id, WP_SMUSH_PREFIX . 'resize_savings', $savings );
				}

				$meta['width']  = ! empty( $resize['width'] ) ? $resize['width'] : $meta['width'];
				$meta['height'] = ! empty( $resize['height'] ) ? $resize['height'] : $meta['height'];

				/**
				 * Called after the image has been successfully resized
				 * Can be used to update the stored stats
				 */
				do_action( 'wp_smush_image_resized', $id, $savings );

			}

			return $meta;

		}

		/**
		 * Generates the new image for specified width and height,
		 * Checks if the size of generated image is greater,
		 *
		 * @param $file_path Original File path
		 * @param $original_file_size File size before optimisation
		 * @param $id Attachment ID
		 * @param string $meta Attachment Metadata
		 * @param bool $unlink Whether to unlink the original image or not
		 *
		 * @return array|bool|false If the image generation was successful
		 */
		function perform_resize( $file_path, $original_file_size, $id, $meta = '', $unlink = true ) {

			/**
			 * Filter the resize image dimensions
			 *
			 * @since 2.3
			 *
			 * @param array $sizes {
			 *    Array of sizes containing max width and height for all the uploaded images.
			 *
			 * @type int $width Maximum Width For resizing
			 * @type int $height Maximum Height for resizing
			 * }
			 *
			 * @param string $file_path Original Image file path
			 *
			 * @param array $upload {
			 *    Array of upload data.
			 *
			 * @type string $file Filename of the newly-uploaded file.
			 * @type string $url URL of the uploaded file.
			 * @type string $type File type.
			 * }
			 *
			 */
			$sizes = apply_filters( 'wp_smush_resize_sizes', array(
				'width'  => $this->max_w,
				'height' => $this->max_h
			), $file_path, $id );

			$data = image_make_intermediate_size( $file_path, $sizes['width'], $sizes['height'] );

			//If the image wasn't resized
			if ( empty( $data['file'] ) || is_wp_error( $data ) ) {
				return false;
			}

			//Check if file size is lesser than original image
			$resize_path = path_join( dirname( $file_path ), $data['file'] );
			if ( ! file_exists( $resize_path ) ) {
				return false;
			}

			$data['file_path'] = $resize_path;

			$file_size        = filesize( $resize_path );
			$data['filesize'] = $file_size;
			if ( $file_size > $original_file_size ) {
				//Don't Unlink for nextgen images
				if ( $unlink ) {
					$this->maybe_unlink( $resize_path, $meta );
				}
			}

			return $data;
		}

		/**
		 * Replace the original file with resized file
		 *
		 * @param $file_path
		 * @param $resized
		 * @param string $attachment_id
		 * @param string $meta
		 *
		 * @return bool
		 */
		function replcae_original_image( $file_path, $resized, $attachment_id = '', $meta = '' ) {

			$replaced = @copy( $resized['file_path'], $file_path );
			$this->maybe_unlink( $resized['file_path'], $meta );

			return $replaced;
		}

		/**
		 * @param $filename
		 *
		 * @return mixed
		 */
		function file_name( $filename ) {
			if ( empty( $filename ) ) {
				return $filename;
			}

			return $filename . 'tmp';
		}

		/**
		 * Do not unlink the resized file if the name is similar to one of the image sizes
		 *
		 * @param $path Image File Path
		 * @param $meta Image Meta
		 *
		 * @return bool
		 */
		function maybe_unlink( $path, $meta ) {
			if ( empty( $path ) ) {
				return true;
			}

			//Unlink directly if meta value is not specified
			if ( empty( $meta ) || empty( $meta['sizes'] ) ) {
				@unlink( $path );
			}

			$unlink = true;
			//Check if the file name is similar to one of the image sizes
			$path_parts = pathinfo( $path );
			$filename   = ! empty( $path_parts['basename'] ) ? $path_parts['basename'] : $path_parts['filename'];
			foreach ( $meta['sizes'] as $image_size ) {
				if ( false === strpos( $image_size['file'], $filename ) ) {
					continue;
				}
				$unlink = false;
			}
			if ( $unlink ) {
				@unlink( $path );
			}

			return true;

		}
	}

	/**
	 * Initialise class
	 */
	global $wpsmush_resize;
	$wpsmush_resize = new WpSmushResize();
}