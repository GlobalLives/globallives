<?php
/**
 * @package WP Smush
 * @subpackage Admin
 * @version 1.0
 *
 * @author Umesh Kumar <umesh@incsub.com>
 *
 * @copyright (c) 2016, Incsub (http://incsub.com)
 */

//Include Bulk UI
require_once WP_SMUSH_DIR . 'lib/class-wp-smush-ui.php';

//Load Shared UI
if ( ! class_exists( 'WDEV_Plugin_Ui' ) ) {
	require_once WP_SMUSH_DIR . 'assets/shared-ui/plugin-ui.php';
}

if ( ! class_exists( 'WpSmushitAdmin' ) ) {
	/**
	 * Show settings in Media settings and add column to media library
	 *
	 */

	/**
	 * Class WpSmushitAdmin
	 *
	 * @property int $remaining_count
	 * @property int $total_count
	 * @property int $smushed_count
	 */
	class WpSmushitAdmin extends WpSmush {

		/**
		 * @var array Settings
		 */
		public $settings;

		public $bulk;

		/**
		 * @var Total count of Attachments for Smushing
		 */
		public $total_count;

		/**
		 * @var Smushed attachments out of total attachments
		 */
		public $smushed_count;

		/**
		 * @var Smushed attachments from selected directories.
		 */
		public $dir_stats;

		/**
		 * @var Smushed attachments out of total attachments
		 */
		public $remaining_count;

		/**
		 * @var Super Smushed attachments count
		 */
		public $super_smushed;

		/**
		 * @var array Unsmushed image ids
		 */
		public $attachments = array();

		/**
		 * @var array Unsmushed image ids
		 */
		public $unsmushed_attachments = array();

		/**
		 * @var array Attachment ids which are smushed
         *
		 */
		public $smushed_attachments = array();

		/**
		 * @var array Image ids that needs to be resmushed
		 */
		public $resmush_ids = array();

		public $mime_types = array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png' );

		/**
		 * @array Stores the stats for all the images
		 */
		public $stats;

		public $bulk_ui = '';

		/**
		 * @var int Limit for allowed number of images per bulk request
		 */
		private $max_free_bulk = 50; //this is enforced at api level too

		public $upgrade_url = 'https://premium.wpmudev.org/project/wp-smush-pro/';

		public $image_sizes = array();

		/**
		 * @var string Stores the headers returned by the latest API call
		 *
		 */
		public $api_headers = array();

		public $page_smush_all = '';

		//List of pages where smush needs to be loaded
		public $pages = array(
			'nggallery-manage-images',
			'gallery_page_wp-smush-nextgen-bulk',
			'post',
			'post-new',
			'upload',
			'settings_page_wp-smush-network',
			'media_page_wp-smush-bulk',
			'media_page_wp-smush-all'
		);

		public $plugin_pages = array(
			'gallery_page_wp-smush-nextgen-bulk',
			'settings_page_wp-smush-network',
			'media_page_wp-smush-bulk',
			'media_page_wp-smush-all'
		);

		/**
		 * Constructor
		 */
		public function __construct() {

			// hook scripts and styles
			add_action( 'admin_init', array( $this, 'register' ) );

			// hook custom screen
			add_action( 'admin_menu', array( $this, 'screen' ) );

			//Network Settings Page
			add_action( 'network_admin_menu', array( $this, 'screen' ) );

			//Handle Smush Bulk Ajax
			add_action( 'wp_ajax_wp_smushit_bulk', array( $this, 'process_smush_request' ) );

			//Handle Smush Single Ajax
			add_action( 'wp_ajax_wp_smushit_manual', array( $this, 'smush_manual' ) );

			//Handle Restore operation
			add_action( 'wp_ajax_smush_resmush_image', array( $this, 'resmush_image' ) );

			//Scan images as per the latest settings
			add_action( 'wp_ajax_scan_for_resmush', array( $this, 'scan_images' ) );

			//Save Settings
			add_action( 'wp_ajax_save_settings', array( $this, 'save_settings' ) );

			add_filter( 'plugin_action_links_' . WP_SMUSH_BASENAME, array(
				$this,
				'settings_link'
			) );
			add_filter( 'network_admin_plugin_action_links_' . WP_SMUSH_BASENAME, array(
				$this,
				'settings_link'
			) );
			//Attachment status, Grid view
			add_filter( 'attachment_fields_to_edit', array( $this, 'filter_attachment_fields_to_edit' ), 10, 2 );

			// Smush Upgrade
			add_action( 'admin_notices', array( $this, 'smush_upgrade' ) );

			// New Features Notice
//			add_action( 'admin_notices', array( $this, 'smush_updated' ) );
//			add_action( 'network_admin_notices', array( $this, 'smush_updated' ) );

			//Handle the smush pro dismiss features notice ajax
			add_action( 'wp_ajax_dismiss_upgrade_notice', array( $this, 'dismiss_upgrade_notice' ) );

			//Handle the smush pro dismiss features notice ajax
			add_action( 'wp_ajax_dismiss_welcome_notice', array( $this, 'dismiss_welcome_notice' ) );

			//Handle the smush pro dismiss features notice ajax
			add_action( 'wp_ajax_dismiss_update_info', array( $this, 'dismiss_update_info' ) );

			//Update the Super Smush count, after the smushing
			add_action( 'wp_smush_image_optimised', array( $this, 'update_lists' ), '', 2 );

			//Delete ReSmush list
			add_action( 'wp_ajax_delete_resmush_list', array( $this, 'delete_resmush_list' ), '', 2 );

			add_action( 'admin_init', array( $this, 'init_settings' ) );

			/**
			 * Prints a membership validation issue notice in Media Library
			 */
			add_action( 'admin_notices', array( $this, 'media_library_membership_notice' ) );

			$this->bulk_ui = new WpSmushBulkUi();

		}

		function init_settings() {
			$this->settings = array(
				'networkwide' => array(
					'label' => esc_html__( 'Enable Network wide settings', 'wp-smushit' ),
					'desc'  => esc_html__( 'If disabled sub sites can override the individual Smush settings.', 'wp-smushit' )
				),
				'auto'        => array(
					'label' => esc_html__( 'Automatically smush my images on upload', 'wp-smushit' ),
					'desc'  => esc_html__( 'When you upload images to the media library, we’ll automatically optimize them.', 'wp-smushit' )
				),
				'keep_exif'   => array(
					'label' => esc_html__( 'Preserve image EXIF data', 'wp-smushit' ),
					'desc'  => esc_html__( 'EXIF data stores camera settings, focal length, date, time and location information in image files. EXIF data makes image files larger but if you are a photographer you may want to preserve this information.', 'wp-smushit' )
				),
				'resize'      => array(
					'label' => esc_html__( 'Resize original images', 'wp-smushit' ),
					'desc'  => esc_html__( 'Save a ton of space by not storing over-sized images on your server. Set image maximum width and height and large images will be automatically scaled before being added to the media library.', 'wp-smushit' )
				),
				'lossy'       => array(
					'label' => esc_html__( 'Super-smush my images', 'wp-smushit' ),
					'desc'  => esc_html__( 'Compress images up to 2x more than regular smush with almost no visible drop in quality.', 'wp-smushit' )
				),
				'original'    => array(
					'label' => esc_html__( 'Include my original full-size images', 'wp-smushit' ),
					'desc'  => esc_html__( 'WordPress crops and resizes every image you upload for embedding on your site. By default, Smush only compresses these cropped and resized images, not your original full-size images. To save space on your server, activate this setting to smush your original images, too. Note: This doesn’t usually improve page speed.', 'wp-smushit' )
				),
				'backup'      => array(
					'label' => esc_html__( 'Make a copy of my original images', 'wp-smushit' ),
					'desc'  => esc_html__( 'Save your original full-size images so you can restore them at any point. Note: Activating this setting will significantly increase the size of your uploads folder by nearly twice as much.', 'wp-smushit' )
				),
				'png_to_jpg'  => array(
					'label' => esc_html__( 'Convert PNG to JPEG (lossy)', 'wp-smushit' ),
					'desc'  => sprintf( esc_html__( "When you compress a PNG file, Smush will check if converting the file to JPEG will further reduce its size. %s Note: PNGs with transparency will be ignored and Smush will only convert the file format if it results in a smaller file size. This will change the file’s name and extension, and any hard-coded URLs will need to be updated.%s", 'wp-smushit' ), "<br /><strong>", "</strong>" )
				)
			);

			/**
			 * Allow to add other settings via filtering the variable
             *
			 */
			$this->settings = apply_filters('wp_smush_settings', $this->settings );

			//Initialize Image dimensions
			$this->image_sizes = $this->image_dimensions();
		}

		/**
		 * Adds smush button and status to attachment modal and edit page if it's an image
		 *
		 *
		 * @param array $form_fields
		 * @param WP_Post $post
		 *
		 * @return array $form_fields
		 */
		function filter_attachment_fields_to_edit( $form_fields, $post ) {
			if ( ! wp_attachment_is_image( $post->ID ) ) {
				return $form_fields;
			}
			$form_fields['wp_smush'] = array(
				'label'         => __( 'WP Smush', 'wp-smushit' ),
				'input'         => 'html',
				'html'          => $this->smush_status( $post->ID ),
				'show_in_edit'  => true,
				'show_in_modal' => true,
			);

			return $form_fields;
		}

		/**
		 * Add Bulk option settings page
		 */
		function screen() {
			global $admin_page_suffix;

			//Bulk Smush Page for each site
			$admin_page_suffix = add_media_page( 'Bulk WP Smush', 'WP Smush', 'edit_others_posts', 'wp-smush-bulk', array(
				$this->bulk_ui,
				'ui'
			) );

			//Network Settings Page
			$page = 'settings.php';
			$cap  = 'manage_network_options';

			add_submenu_page( $page, 'WP Smush', 'WP Smush', $cap, 'wp-smush', array(
				$this->bulk_ui,
				'ui'
			) );

			//For Nextgen gallery Pages, check later in enqueue function
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		}

		/**
		 * Register js and css
		 */
		function register() {

			//Main JS
			wp_register_script( 'wp-smushit-admin-js', WP_SMUSH_URL . 'assets/js/wp-smushit-admin.js', array(
				'jquery'
			), WP_SMUSH_VERSION );

			//Notice JS
			wp_register_script( 'wp-smushit-notice-js', WP_SMUSH_URL . 'assets/js/notice.js', array(
				'jquery'
			), WP_SMUSH_VERSION );

			/* Register Style */
			wp_register_style( 'wp-smushit-admin-css', WP_SMUSH_URL . 'assets/css/wp-smushit-admin.css', array(), WP_SMUSH_VERSION );
			//Notice CSS
			wp_register_style( 'wp-smushit-notice-css', WP_SMUSH_URL . 'assets/css/notice.css', array(), WP_SMUSH_VERSION );

			//jQuery tree
			wp_register_script( 'jqft-js', WP_SMUSH_URL . 'assets/js/jQueryFileTree.js', array(
				'jquery'
			), WP_SMUSH_VERSION, true );
			wp_register_style( 'jqft-css', WP_SMUSH_URL . 'assets/css/jQueryFileTree.min.css', array(), WP_SMUSH_VERSION );

			//Dismiss Update Info
			$this->dismiss_update_info();
		}

		/**
		 * enqueue js and css
		 */
		function enqueue() {

			$current_screen = get_current_screen();
			$current_page   = $current_screen->base;

			/**
			 * Allows to disable enqueuing smush files on a particular page
			 */
			$enqueue_smush = apply_filters( 'wp_smush_enqueue', true );

			//Load js and css on all admin pages, in order t display install/upgrade notice
			// And If upgrade/install message is dismissed or for pro users, Do not enqueue script
			if ( get_option( 'wp-smush-hide_upgrade_notice' ) || get_site_option( 'wp-smush-hide_upgrade_notice' ) || $this->validate_install() ) {
				/** @var $pages List of screens where script needs to be loaded */

				//Do not enqueue, unless it is one of the required screen
				if ( ! $enqueue_smush || ! in_array( $current_page, $this->pages ) ) {

					return;
				}
			}

			wp_enqueue_script( 'wp-smushit-admin-js' );

			//Style
			wp_enqueue_style( 'wp-smushit-admin-css' );

			$this->load_shared_ui( $current_page );

			//Enqueue Google Fonts for Tooltip On Media Pages, These are loaded by shared UI, but we
			// aren't loading shared UI on media library pages
			if ( ! wp_style_is( 'wdev-plugin-google_fonts', 'enqueued' ) ) {
				wp_enqueue_style(
					'wdev-plugin-google_fonts',
					'https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700|Roboto:400,500,300,300italic'
				);
			}

			//Load on Smush all page only
			if ( 'media_page_wp-smush-bulk' == $current_page ) {
				//Load Jquery tree on specified page
				wp_enqueue_script( 'jqft-js' );
				wp_enqueue_style( 'jqft-css' );
			}

			// localize translatable strings for js
			$this->localize();
		}

		/**
		 * Localize Translations
		 */
		function localize() {
			global $current_screen, $wpsmush_settings, $wpsmush_db;
			$current_page = ! empty( $current_screen ) ? $current_screen->base : '';

			$handle = 'wp-smushit-admin-js';

			$wp_smush_msgs = array(
				'resmush'                 => esc_html__( 'Super-Smush', 'wp-smushit' ),
				'smush_now'               => esc_html__( 'Smush Now', 'wp-smushit' ),
				'error_in_bulk'           => esc_html__( '{{errors}} image(s) were skipped due to an error.', 'wp-smushit' ),
				'all_resmushed'           => esc_html__( 'All images are fully optimized.', 'wp-smushit' ),
				'restore'                 => esc_html__( "Restoring image..", "wp-smushit" ),
				'smushing'                => esc_html__( "Smushing image..", "wp-smushit" ),
				'checking'                => esc_html__( "Checking images..", "wp-smushit" ),
				'membership_valid'        => esc_html__( "We successfully verified your membership, all the Pro features should work completely. ", "wp-smushit" ),
				'membership_invalid'      => esc_html__( "Your membership couldn't be verified.", "wp-smushit" ),
				'missing_path'            => esc_html__( "Missing file path.", "wp-smushit" ),
				//Used by Directory Smush
				'unfinished_smush_single' => esc_html__( "image could not be smushed.", "wp-smushit" ),
				'unfinished_smush'        => esc_html__( "images could not be smushed.", "wp-smushit" ),
				'already_optimised'       => esc_html__( "Already Optimized", "wp-smushit" ),
				'ajax_error'              => esc_html__( "Ajax Error", "wp-smushit" ),
				'all_done'                => esc_html__( "All Done!", "wp-smushit" ),
				'all_done'                => esc_html__( "All Done!", "wp-smushit" ),
			);

			wp_localize_script( $handle, 'wp_smush_msgs', $wp_smush_msgs );

			//Load the stats on selected screens only
			if ( $current_page == 'media_page_wp-smush-bulk' ) {

				//Get resmush list, If we have a resmush list already, localize those ids
				if ( $resmush_ids = get_option( "wp-smush-resmush-list" ) ) {
					//get the attachments, and get lossless count
					$this->resmush_ids = $resmush_ids;
				}

				//Setup all the stats
				$this->setup_global_stats( true );

				//Localize smushit_ids variable, if there are fix number of ids
				$this->unsmushed_attachments = ! empty( $_REQUEST['ids'] ) ? array_map( 'intval', explode( ',', $_REQUEST['ids'] ) ) : array();

				if ( empty( $this->unsmushed_attachments ) ) {
					//Get attachments if all the images are not smushed
					$this->unsmushed_attachments = $this->remaining_count > 0 ? $wpsmush_db->get_unsmushed_attachments() : array();
					$this->unsmushed_attachments = ! empty( $this->unsmushed_attachments ) && is_array( $this->unsmushed_attachments ) ? array_values( $this->unsmushed_attachments ) : $this->unsmushed_attachments;
				}

				//Array of all smushed, unsmushed and lossless ids
				$data = array(
					'count_supersmushed' => $this->super_smushed,
					'count_smushed'      => $this->smushed_count,
					'count_total'        => $this->total_count,
					'count_images'       => $this->stats['total_images'],
					'unsmushed'          => $this->unsmushed_attachments,
					'resmush'            => $this->resmush_ids,
					'size_before'        => $this->stats['size_before'],
					'size_after'         => $this->stats['size_after'],
					'savings_bytes'      => $this->stats['bytes'],
					'savings_resize'     => $this->stats['resize_savings'],
					'savings_conversion' => $this->stats['conversion_savings'],
					'savings_dir_smush'  => $this->dir_stats
				);
			} else {
				$data = array(
					'count_supersmushed' => '',
					'count_smushed'      => '',
					'count_total'        => '',
					'count_images'       => '',
					'unsmushed'          => '',
					'resmush'            => '',
					'savings_bytes'      => '',
					'savings_resize'     => '',
					'savings_conversion' => '',
					'savings_supersmush' => '',
					'pro_savings'        => ''
				);

			}

			$data['resize_sizes'] = $this->get_max_image_dimensions();

			$data['timeout'] = WP_SMUSH_TIMEOUT * 1000; //Convert it into ms

			wp_localize_script( 'wp-smushit-admin-js', 'wp_smushit_data', $data );

			//Check if settings were changed for a multisite, and localize whether to run re-check on page load
			if ( is_multisite() && get_site_option( WP_SMUSH_PREFIX . 'networkwide' ) && ! is_network_admin() ) {
				//Check the last settings stored in db
				$settings = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . 'last_settings', '' );

				//Get current settings
				$c_settings = $this->get_serialised_settings();

				//If not same, Set a variable to run re-check on page load
				if( $settings != $c_settings ) {
					wp_localize_script( 'wp-smushit-admin-js', 'wp_smush_run_re_check', array( 1 ) );
				}
			}

		}

		/**
		 * Runs the expensive queries to get our global smush stats
		 *
		 * @param bool $force_update Whether to Force update the Global Stats or not
		 *
		 */
		function setup_global_stats( $force_update = false ) {
			global $wpsmush_db, $wpsmush_dir;

			// Set directory smush status.
			$this->dir_stats = $wpsmush_dir->total_stats();

			//Setup Attachments and total count
			$wpsmush_db->total_count( true );

			$this->stats = $this->global_stats( $force_update );

			if ( empty( $this->smushed_attachments ) ) {
				//Get smushed attachments
				$this->smushed_attachments = $wpsmush_db->smushed_count( true );
			}

			//Get supersmushed iamges count
			if ( empty( $this->super_smushed ) ) {

				$this->super_smushed = $wpsmush_db->super_smushed_count();
			}

			// Set pro savings.
			$this->set_pro_savings();

			// Set smushed count
			$this->smushed_count = ! empty( $this->smushed_attachments ) ? count( $this->smushed_attachments ) : 0;
			$this->remaining_count = $this->remaining_count();
		}

		/**
		 * Set pro savings stats if not premium user.
		 *
		 * For non-premium users, show expected avarage savings based
		 * on the free version savings.
		 */
		function set_pro_savings() {

			global $WpSmush;

			// No need this already premium.
			if ( $WpSmush->validate_install() ) {
				return;
			}

			//Initialize
			$this->stats['pro_savings'] = array(
				'percent' => 0,
				'savings' => 0,
			);

			// Default values.
			$savings = $this->stats['percent'] > 0 ? $this->stats['percent'] : 0;
			$savings_bytes = $this->stats['human'] > 0 ? $this->stats['bytes'] : "0";
			$orig_diff = 2.22058824;
			if ( ! empty( $savings ) && $savings > 49  ) {
			   $orig_diff = 1.22054412;
			}
			//Calculate Pro savings
			if( !empty( $savings ) ) {
				$savings       = $orig_diff * $savings;
				$savings_bytes = $orig_diff * $savings_bytes;
			}

			// Set pro savings in global stats.
			if ( $savings > 0 ) {
				$this->stats['pro_savings'] = array(
					'percent' => number_format_i18n( $savings, 1 ),
					'savings' => size_format( $savings_bytes, 1 ),
				);
			}
		}

		/**
		 * Processes the Smush request and sends back the next id for smushing
		 *
		 * Bulk Smushing Handler
		 *
		 */
		function process_smush_request() {

			global $WpSmush, $wpsmush_helper;

			// turn off errors for ajax result
			@error_reporting( 0 );

			$should_continue = true;

			if ( empty( $_REQUEST['attachment_id'] ) ) {
				wp_send_json_error( 'missing id' );
			}

			if ( ! $this->validate_install() ) {
				//Free version bulk smush, check the transient counter value
				$should_continue = $this->check_bulk_limit();
			}

			//If the bulk smush needs to be stopped
			if ( ! $should_continue ) {
				wp_send_json_error(
					array(
						'error'    => 'bulk_request_image_limit_exceeded',
						'continue' => false
					)
				);
			}

			$error = '';
			$send_error = false;

			$attachment_id = (int) ( $_REQUEST['attachment_id'] );

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
			if ( ! apply_filters( 'wp_smush_image', true, $attachment_id ) ) {
				$send_error = true;
				$error = $this->filter_error( esc_html__( "Attachment $attachment_id was skipped.", "wp-smushit" ), $attachment_id );
			}

			//Get the file path for backup
			$attachment_file_path = $wpsmush_helper->get_attached_file( $attachment_id );

			//Download if not exists
			do_action('smush_file_exists', $attachment_file_path, $attachment_id );

			//Take Backup
			global $wpsmush_backup;
			$wpsmush_backup->create_backup( $attachment_file_path, '', $attachment_id );

			if ( ! $send_error ) {
				//Proceed only if Smushing Transient is not set for the given attachment id
				if ( ! get_transient( 'smush-in-progress-' . $attachment_id ) ) {

					//Set a transient to avoid multiple request
					set_transient( 'smush-in-progress-' . $attachment_id, true, WP_SMUSH_TIMEOUT );

					$original_meta = wp_get_attachment_metadata( $attachment_id, true );

					//Resize the dimensions of the image
					/**
					 * Filter whether the existing image should be resized or not
					 *
					 * @since 2.3
					 *
					 * @param bool $should_resize , Set to True by default
					 *
					 * @param $attachment_id Image Attachment ID
					 *
					 */
					if ( $should_resize = apply_filters( 'wp_smush_resize_media_image', true, $attachment_id ) ) {
						$updated_meta  = $this->resize_image( $attachment_id, $original_meta );
						$original_meta = ! empty( $updated_meta ) ? $updated_meta : $original_meta;
					}

					global $wpsmush_pngjpg;

					//Convert PNGs to JPG
					$original_meta = $wpsmush_pngjpg->png_to_jpg( $attachment_id, $original_meta );

					$smush = $WpSmush->resize_from_meta_data( $original_meta, $attachment_id );
					wp_update_attachment_metadata( $attachment_id, $original_meta );
				}

				//Delete Transient
				delete_transient( 'smush-in-progress-' . $attachment_id );
			}

			$smush_data         = get_post_meta( $attachment_id, $this->smushed_meta_key, true );
			$resize_savings     = get_post_meta( $attachment_id, WP_SMUSH_PREFIX . 'resize_savings', true );
			$conversion_savings = $wpsmush_helper->get_pngjpg_savings( $attachment_id );

			$stats = array(
				'count'              => ! empty( $smush_data['sizes'] ) ? count( $smush_data['sizes'] ) : 0,
				'size_before'        => ! empty( $smush_data['stats'] ) ? $smush_data['stats']['size_before'] : 0,
				'size_after'         => ! empty( $smush_data['stats'] ) ? $smush_data['stats']['size_after'] : 0,
				'savings_resize'     => $resize_savings > 0 ? $resize_savings : 0,
				'savings_conversion' => $conversion_savings['bytes'] > 0 ? $conversion_savings : 0,
				'is_lossy'           => ! empty( $smush_data ['stats'] ) ? $smush_data['stats']['lossy'] : false
			);

			if ( isset( $smush ) && is_wp_error( $smush ) ) {

				$send_error = true;

				$error = $smush->get_error_message();
				//Check for timeout error and suggest to filter timeout
				if ( strpos( $error, 'timed out' ) ) {
					$error = esc_html__( "Smush request timed out, You can try setting a higher value for `WP_SMUSH_API_TIMEOUT`.", "wp-smushit" );
				}
			} else {
				//Check if a resmush request, update the resmush list
				if ( ! empty( $_REQUEST['is_bulk_resmush'] ) && 'false' != $_REQUEST['is_bulk_resmush'] && $_REQUEST['is_bulk_resmush'] ) {
					$this->update_resmush_list( $attachment_id );
				}
			}

			if ( ! $send_error ) {
				/**
				 * Runs after a image is succesfully smushed
				 */
				do_action( 'image_smushed', $attachment_id, $stats );
			}

			/**
			 * Used internally to modify the error message
			 *
			 */
			$error = $this->filter_error( $error, $attachment_id );

			//Wrap the error message in div
			$error = !empty( $error ) ? sprintf( '<p class="wp-smush-error-message">%s</p>', $error ) : $error;

			if ( ! $send_error ) {
				//Update the bulk Limit count
				$this->update_smush_count();
			}

			//Send ajax response
			$send_error ? wp_send_json_error( array(
				'stats'        => $stats,
				'error_msg'    => $error,
				'show_warning' => intval( $this->show_warning() )

			) ) : wp_send_json_success( array(
				'stats'        => $stats,
				'show_warning' => intval( $this->show_warning() )
			) );

		}

		/**
		 * Handle the Ajax request for smushing single image
		 *
		 * @uses smush_single()
		 */
		function smush_manual() {

			// turn off errors for ajax result
			@error_reporting( 0 );

			if ( ! current_user_can( 'upload_files' ) ) {
				wp_die( __( "You don't have permission to work with uploaded files.", 'wp-smushit' ) );
			}

			if ( ! isset( $_GET['attachment_id'] ) ) {
				wp_die( __( 'No attachment ID was provided.', 'wp-smushit' ) );
			}

			$attachemnt_id = intval( $_GET['attachment_id'] );

			/**
			 * Filter: wp_smush_image
			 *
			 * Whether to smush the given attachment id or not
			 *
			 */
			if ( ! apply_filters( 'wp_smush_image', true, $attachemnt_id ) ) {
			    $error = $this->filter_error( esc_html__( "Attachment Skipped - Check `wp_smush_image` filter.", "wp-smushit" ), $attachemnt_id );
				wp_send_json_error( array(
					'error_msg'    => sprintf( '<p class="wp-smush-error-message">%s</p>', $error ),
					'show_warning' => intval( $this->show_warning() )
				) );
			}

			//Pass on the attachment id to smush single function
			$this->smush_single( $attachemnt_id );
		}

		/**
		 * Smush single images
		 *
		 * @param $attachment_id
		 * @param bool $return Return/Echo the stats
		 *
		 * @return array|string|void
		 */
		function smush_single( $attachment_id, $return = false ) {

			//If the smushing transient is already set, return the status
			if ( get_transient( 'smush-in-progress-' . $attachment_id ) || get_transient( "wp-smush-restore-$attachment_id" ) ) {
				//Get the button status
				$status = $this->set_status( $attachment_id, false, true );
				if ( $return ) {
					return $status;
				} else {
					wp_send_json_success( $status );
				}
			}

			//Set a transient to avoid multiple request
			set_transient( 'smush-in-progress-' . $attachment_id, true, WP_SMUSH_TIMEOUT );

			global $WpSmush, $wpsmush_pngjpg, $wpsmush_helper;

			$attachment_id = absint( (int) ( $attachment_id ) );

			//Get the file path for backup
			$attachment_file_path = $wpsmush_helper->get_attached_file( $attachment_id );

			//Download file if not exists
			do_action('smush_file_exists', $attachment_file_path, $attachment_id );

			//Take Backup
			global $wpsmush_backup;
			$wpsmush_backup->create_backup( $attachment_file_path, '', $attachment_id );

			//Get the image metadata from $_POST
			$original_meta = ! empty( $_POST['metadata'] ) ? $_POST['metadata'] : '';

			$original_meta = empty( $original_meta ) ? wp_get_attachment_metadata( $attachment_id ) : $original_meta;

			//Send image for resizing, if enabled resize first before any other operation
			$updated_meta = $this->resize_image( $attachment_id, $original_meta );

			//Convert PNGs to JPG
			$updated_meta = $wpsmush_pngjpg->png_to_jpg( $attachment_id, $updated_meta );

			$original_meta = ! empty( $updated_meta ) ? $updated_meta : $original_meta;

			//Smush the image
			$smush = $WpSmush->resize_from_meta_data( $original_meta, $attachment_id );

			//Update the details, after smushing, so that latest image is used in hook
			wp_update_attachment_metadata( $attachment_id, $original_meta );

			//Get the button status
			$status = $WpSmush->set_status( $attachment_id, false, true );

			//Delete the transient after attachment meta is updated
			delete_transient( 'smush-in-progress-' . $attachment_id );

			//Send Json response if we are not suppose to return the results

			/** Send stats **/
			if ( is_wp_error( $smush ) ) {
				if ( $return ) {
					return array( 'error' => $smush->get_error_message() );
				} else {
					wp_send_json_error( array( 'error_msg'    => '<p class="wp-smush-error-message">' . $smush->get_error_message() . '</p>',
					                           'show_warning' => intval( $this->show_warning() )
					) );
				}
			} else {
				if ( $return ) {
					return $status;
				} else {
					wp_send_json_success( $status );
				}
			}
		}

		/**
		 * Check bulk sent count, whether to allow further smushing or not
		 *
		 * @param bool $reset To hard reset the transient
		 *
		 * @param string $key Transient Key - bulk_sent_count/dir_sent_count
		 *
		 * @return bool
		 */
		function check_bulk_limit( $reset = false, $key = 'bulk_sent_count' ) {

			$transient_name = WP_SMUSH_PREFIX . $key;

			$bulk_sent_count = get_transient( $transient_name );

			//Check if bulk smush limit is less than limit
			if ( ! $bulk_sent_count || $bulk_sent_count < $this->max_free_bulk ) {
				$continue = true;
			} elseif ( $bulk_sent_count == $this->max_free_bulk ) {
				//If user has reached the limit, reset the transient
				$continue = false;
				$reset    = true;
			} else {
				$continue = false;
			}

			//If we need to reset the transient
			if ( $reset ) {
				set_transient( $transient_name, 0, 60 );
			}

			return $continue;
		}

		/**
		 * Update the image smushed count in transient
		 *
		 * @param string $key
		 *
		 */
		function update_smush_count( $key = 'bulk_sent_count' ) {

			$transient_name = WP_SMUSH_PREFIX . $key;

			$bulk_sent_count = get_transient( $transient_name );

			//If bulk sent count is not set
			if ( false === $bulk_sent_count ) {

				//start transient at 0
				set_transient( $transient_name, 1, 200 );

			} else if ( $bulk_sent_count < $this->max_free_bulk ) {

				//If lte $this->max_free_bulk images are sent, increment
				set_transient( $transient_name, $bulk_sent_count + 1, 200 );

			}
		}

		/**
		 * Returns remaining count
		 *
		 * @return int
		 */
		function remaining_count() {

			//Check if the resmush count is equal to remaining count
			$resmush_count = count( $this->resmush_ids );
			if ( $resmush_count > 0 && $resmush_count == $this->smushed_count ) {
				return $resmush_count;
			}

			return ( $this->total_count - $this->smushed_count );
		}

		/**
		 * Display Thumbnails, if bulk action is choosen
		 *
		 * @Note: Not in use right now, Will use it in future for Media Bulk action
		 *
		 */
		function selected_ui( $send_ids, $received_ids ) {
			if ( empty( $received_ids ) ) {
				return;
			}

			?>
			<div id="select-bulk" class="wp-smush-bulk-wrap">
				<p>
					<?php
					printf(
						__(
							'<strong>%d of %d images</strong> were sent for smushing:',
							'wp-smushit'
						),
						count( $send_ids ), count( $received_ids )
					);
					?>
				</p>
				<ul id="wp-smush-selected-images">
					<?php
					foreach ( $received_ids as $attachment_id ) {
						$this->attachment_ui( $attachment_id );
					}
					?>
				</ul>
			</div>
			<?php
		}

		/**
		 * Display the bulk smushing button
		 *
		 * @param bool $resmush
		 *
		 * @param bool $return Whether to return the button content or print it
		 *
		 * @return Returns or Echo the content
		 */
		function setup_button( $resmush = false, $return = false ) {
			$button   = $this->button_state( $resmush );
			$disabled = ! empty( $button['disabled'] ) ? ' disabled="disabled"' : '';
			$content  = '<button class="button button-primary ' . $button['class'] . '"
			        name="smush-all" ' . $disabled . '>
				<span>' . $button['text'] . '</span>
			</button>';

			//If We need to return the content
			if ( $return ) {
				return $content;
			}

			echo $content;
		}

		/**
		 * Get all the attachment meta, sum up the stats and return
		 *
		 * @param bool $force_update , Whether to forcefully update the Cache
		 *
		 * @return array|bool|mixed
		 *
		 * @todo: remove id from global stats stored in db
		 *
		 */
		function global_stats( $force_update = false ) {

			if ( ! $force_update && $stats = get_option( 'smush_global_stats' ) ) {
				if ( ! empty( $stats ) && ! empty( $stats['size_before'] ) ) {
					if ( isset( $stats['id'] ) ) {
						unset( $stats['id'] );
					}

					return $stats;
				}
			}

			global $wpdb, $wpsmush_db;

			$smush_data = array(
				'size_before' => 0,
				'size_after'  => 0,
				'percent'     => 0,
				'human'       => 0,
				'bytes'       => 0
			);

			/**
			 * Allows to set a limit of mysql query
			 * Default value is 2000
			 */
			$limit      = $this->query_limit();
			$offset     = 0;
			$query_next = true;

			$supersmushed_count                = 0;
			$smush_data['total_images']        = 0;

			while ( $query_next ) {

				$global_data = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key=%s LIMIT $offset, $limit", "wp-smpro-smush-data" ) );
				if ( ! empty( $global_data ) ) {
					foreach ( $global_data as $data ) {

						//Skip attachment, if in re-smush list
						if ( ! empty( $this->resmush_ids ) && in_array( $data->post_id, $this->resmush_ids ) ) {
							continue;
						}

						$smush_data['id'][] = $data->post_id;
						if ( ! empty( $data->meta_value ) ) {
							$meta = maybe_unserialize( $data->meta_value );
							if ( ! empty( $meta['stats'] ) ) {

								//Check for lossy Compression
								if ( 1 == $meta['stats']['lossy'] ) {
									$supersmushed_count += 1;
								}

								//If the image was optimised
								if ( !empty( $meta['stats'] ) && $meta['stats']['size_before'] > $meta['stats']['size_after'] ) {
									//Total Image Smushed
									$smush_data['total_images'] += ! empty( $meta['sizes'] ) ? count( $meta['sizes'] ) : 0;

									$smush_data['size_before'] += ! empty( $meta['stats']['size_before'] ) ? (int) $meta['stats']['size_before'] : 0;
									$smush_data['size_after']  += ! empty( $meta['stats']['size_after'] ) ? (int) $meta['stats']['size_after'] : 0;
								}
							}
						}
					}
				}

				$smush_data['bytes'] = $smush_data['size_before'] - $smush_data['size_after'];

				//Update the offset
				$offset += $limit;

				//Compare the Offset value to total images
				if ( ! empty( $this->total_count ) && $this->total_count <= $offset ) {
					$query_next = false;
				} elseif ( ! $global_data ) {
					//If we didn' got any results
					$query_next = false;
				}

			}

			// Add directory smush image bytes.
			if ( ! empty( $this->dir_stats['bytes'] ) && $this->dir_stats['bytes'] > 0 ) {
				$smush_data['bytes'] += $this->dir_stats['bytes'];
			}
			// Add directory smush image total size.
			if ( ! empty( $this->dir_stats['orig_size'] ) && $this->dir_stats['orig_size'] > 0 ) {
				$smush_data['size_before'] += $this->dir_stats['orig_size'];
			}
			// Add directory smush saved size.
			if ( ! empty( $this->dir_stats['image_size'] ) && $this->dir_stats['image_size'] > 0 ) {
				$smush_data['size_after'] += $this->dir_stats['image_size'];
			}
			// Add directory smushed images.
			if ( ! empty( $this->dir_stats['optimised'] ) && $this->dir_stats['optimised'] > 0 ) {
				$smush_data['total_images'] += $this->dir_stats['optimised'];
			}

			//Resize Savings
			$resize_savings               = $wpsmush_db->resize_savings( false );
			$smush_data['resize_savings'] = ! empty( $resize_savings['bytes'] ) ? $resize_savings['bytes'] : 0;

			//Conversion Savings
			$conversion_savings               = $wpsmush_db->conversion_savings( false );
			$smush_data['conversion_savings'] = ! empty( $conversion_savings['bytes'] ) ? $conversion_savings['bytes'] : 0;

			if ( ! isset( $smush_data['bytes'] ) || $smush_data['bytes'] < 0 ) {
				$smush_data['bytes'] = 0;
			}

			//Add the resize savings to bytes
			$smush_data['bytes'] += $smush_data['resize_savings'];
			$smush_data['size_before'] += $resize_savings['size_before'];
			$smush_data['size_after'] += $resize_savings['size_after'];

			//Add Conversion Savings
			$smush_data['bytes'] += $smush_data['conversion_savings'];
			$smush_data['size_before'] += $conversion_savings['size_before'];
			$smush_data['size_after'] += $conversion_savings['size_after'];

			if ( $smush_data['size_before'] > 0 ) {
				$smush_data['percent'] = ( $smush_data['bytes'] / $smush_data['size_before'] ) * 100;
			}

			//Round off precentage
			$smush_data['percent'] = round( $smush_data['percent'], 1 );

			$smush_data['human'] = size_format( $smush_data['bytes'], 1 );

			//Setup Smushed attachment ids
			$this->smushed_attachments = ! empty( $smush_data['id'] ) ? $smush_data['id'] : '';

			//Super Smushed attachment count
			$this->super_smushed = $supersmushed_count;

			//Remove ids from stats
			unset( $smush_data['id'] );

			//Update Cache
			update_option( 'smush_global_stats', $smush_data, false );

			return $smush_data;
		}

		/**
		 * Get all the attachment meta, sum up the stats and return
		 *
		 * @param bool $force_update , Whether to forcefully update the Cache
		 *
		 * @return array|bool|mixed Stats
		 */
		function global_stats_from_ids( $force_update = false ) {

			if ( ! $force_update && $stats = get_option( 'smush_global_stats' ) ) {
				if ( ! empty( $stats ) ) {
					return $stats;
				}
			}

			global $wpsmush_db, $wpsmush_helper;
			if ( empty( $this->smushed_attachments ) ) {
				$this->smushed_attachments = $wpsmush_db->smushed_count( true );
			}

			$smush_data                 = array(
				'size_before' => 0,
				'size_after'  => 0,
				'percent'     => 0,
				'human'       => 0
			);
			$smush_data['count']        = 0;
			$smush_data['total_images'] = 0;

			if ( ! empty( $this->smushed_attachments ) && is_array( $this->smushed_attachments ) ) {
				//Iterate over all the attachments
				foreach ( $this->smushed_attachments as $attachment ) {
					//Get all the Savings for each image
					$smush_stats        = get_post_meta( $attachment, 'wp-smpro-smush-data', true );
					$resize_savings     = get_post_meta( $attachment, WP_SMUSH_PREFIX . 'resize_savings', true );
					$conversion_savings = $wpsmush_helper->get_pngjpg_savings( $attachment );

					$smush_data['count'] += 1;
					$smush_data['total_images'] += ! empty( $smush_stats['sizes'] ) ? count( $smush_stats['sizes'] ) : 0;

					//Sum up all the stats
					if ( ! empty( $smush_stats['sizes'] ) ) {
						foreach ( $smush_stats['sizes'] as $size_k => $size_savings ) {
							//size_before from optimisation stats
							$size_before = $size_savings->size_before;
							$size_after  = $size_savings->size_after;
							if ( 'full' == $size_k ) {
								//Check for savings from resizing for the original image
								if ( ! empty( $resize_savings['size_before'] ) && $resize_savings['size_before'] > $size_before ) {
									$size_before = $resize_savings['size_before'];
								}
								//Check for savings from resizing for the original image
								if ( ! empty( $resize_savings['size_after'] ) && $resize_savings['size_after'] < $size_after ) {
									$size_after = $resize_savings['size_after'];
								}
							}

							//Add conversion savings, if available
							if ( ! empty( $conversion_savings['bytes'] ) ) {
								$smush_data['size_before'] += $conversion_savings['size_before'];
								$smush_data['size_after']  += $conversion_savings['size_after'];
							}
						}

						//Resize Savings: If full image wasn't optimised, but resized, combine the stats
						if ( empty( $smush_stats['sizes']['full'] ) && ! empty( $resize_savings ) && $resize_savings['bytes'] > 0 ) {
							$smush_data['size_before'] += $resize_savings['size_before'];
							$smush_data['size_after'] += $resize_savings['size_after'];
						}

						//Conversion Savings: If full image wasn't optimised, but Conversion saved few bytes
						if ( empty( $smush_stats['sizes']['full'] ) && ! empty( $conversion_savings['full'] ) && $conversion_savings['full']['bytes'] > 0 ) {
							$smush_data['size_before'] += $conversion_savings['full']['size_before'];
							$smush_data['size_after'] += $conversion_savings['full']['size_after'];
						}
					}
				}
				$smush_data['bytes'] = $smush_data['size_before'] - $smush_data['size_after'];

				//Resize Savings
				$resize_savings               = $wpsmush_db->resize_savings( false );
				$smush_data['resize_savings'] = ! empty( $resize_savings['bytes'] ) ? $resize_savings['bytes'] : 0;

				//Conversion Savings
				$conversion_savings               = $wpsmush_db->conversion_savings( false );
				$smush_data['conversion_savings'] = ! empty( $conversion_savings['bytes'] ) ? $conversion_savings['bytes'] : 0;

				if ( $smush_data['size_before'] > 0 ) {
					$smush_data['percent'] = ( $smush_data['bytes'] / $smush_data['size_before'] ) * 100;
				}

				//Round off precentage
				$smush_data['percent'] = round( $smush_data['percent'], 1 );

				$smush_data['human'] = size_format( $smush_data['bytes'], 1 );

			}
			//Update Cache
			update_option( 'smush_global_stats', $smush_data, false );

			return $smush_data;
		}

		/**
		 * Returns Bulk smush button id and other details, as per if bulk request is already sent or not
		 *
		 * @param $resmush
		 *
		 * @return array
		 */

		private function button_state( $resmush ) {
			$button = array(
				'cancel' => false,
			);
			if ( $this->validate_install() && $resmush ) {

				$button['text']  = __( 'Bulk Smush Now', 'wp-smushit' );
				$button['class'] = 'wp-smush-button wp-smush-resmush wp-smush-all';

			} else {

				// if we have nothing left to smush, disable the buttons
				if ( $this->smushed_count === $this->total_count ) {
					$button['text']     = __( 'All Done!', 'wp-smushit' );
					$button['class']    = 'wp-smush-finished disabled wp-smush-finished';
					$button['disabled'] = 'disabled';

				} else {

					$button['text']  = __( 'Bulk Smush Now', 'wp-smushit' );
					$button['class'] = 'wp-smush-button';

				}
			}

			return $button;
		}

		/**
		 * Get the smush button text for attachment
		 *
		 * @param $id Attachment ID for which the Status has to be set
		 *
		 * @return string
		 */
		function smush_status( $id ) {
			global $WpSmush;

			//Show Temporary Status, For Async Optimisation, No Good workaround
			if ( ! get_transient( "wp-smush-restore-$id" ) && ! empty( $_POST['action'] ) && 'upload-attachment' == $_POST['action'] && $WpSmush->is_auto_smush_enabled() ) {
				// the status
				$status_txt = __( 'Smushing in progress..', 'wp-smushit' );

				// we need to show the smush button
				$show_button = false;

				// the button text
				$button_txt = __( 'Smush Now!', 'wp-smushit' );

				return $this->column_html( $id, $status_txt, $button_txt, $show_button, true, false, true );
			}
			//Else Return the normal status
			$response = trim( $this->set_status( $id, false ) );

			return $response;
		}


		/**
		 * Adds a smushit pro settings link on plugin page
		 *
		 * @param $links
		 *
		 * @return array
		 */
		function settings_link( $links, $url_only = false ) {

			$settings_page = is_multisite() ? network_admin_url( 'settings.php?page=wp-smush' ) : admin_url( 'upload.php?page=wp-smush-bulk' );
			$settings      = '<a href="' . $settings_page . '">' . __( 'Settings', 'wp-smushit' ) . '</a>';

			//Return Only settings page link
			if ( $url_only ) {
				return $settings_page;
			}

			//Added a fix for weird warning in multisite, "array_unshift() expects parameter 1 to be array, null given"
			if ( ! empty( $links ) ) {
				array_unshift( $links, $settings );
			} else {
				$links = array( $settings );
			}

			return $links;
		}

		/**
		 * Shows Notice for free users, displays a discount coupon
		 */
		function smush_upgrade() {

			//Return, If a pro user, or not super admin, or don't have the admin privilleges
			if ( $this->validate_install() || ! current_user_can( 'edit_others_posts' ) || ! is_super_admin() ) {
				return;
			}

			//No need to show it on bulk smush
			if ( isset( $_GET['page'] ) && 'wp-smush-bulk' == $_GET['page'] ) {
				return;
			}

			//Return if notice is already dismissed
			if ( get_option( 'wp-smush-hide_upgrade_notice' ) || get_site_option( 'wp-smush-hide_upgrade_notice' ) ) {
				return;
			}

			$install_type = get_site_option( 'wp-smush-install-type', false );

			if ( ! $install_type ) {
				if ( $this->smushed_count > 0 ) {
					$install_type = 'existing';
				} else {
					$install_type = 'new';
				}
				update_site_option( 'wp-smush-install-type', $install_type );
			}

			//Container Header
			echo $this->bulk_ui->installation_notice();
		}

		/**
		 * Get the smushed attachments from the database, except gif
		 *
		 * @global object $wpdb
		 *
		 * @return object query results
		 */
		function get_smushed_attachments() {

			global $wpdb;

			$allowed_images = "( 'image/jpeg', 'image/jpg', 'image/png' )";

			$limit      = $this->query_limit();
			$offset     = 0;
			$query_next = true;

			while ( $query_next ) {
				// get the attachment id, smush data
				$sql     = "SELECT p.ID as attachment_id, p.post_mime_type as type, ms.meta_value as smush_data"
				           . " FROM $wpdb->posts as p"
				           . " LEFT JOIN $wpdb->postmeta as ms"
				           . " ON (p.ID= ms.post_id AND ms.meta_key='wp-smpro-smush-data')"
				           . " WHERE"
				           . " p.post_type='attachment'"
				           . " AND p.post_mime_type IN " . $allowed_images
				           . " ORDER BY p . ID DESC"
				           // add a limit
				           . " LIMIT " . $limit;
				$results = $wpdb->get_results( $sql );

				//Update the offset
				$offset += $limit;
				if ( !empty( $this->total_count ) && $this->total_count <= $offset ) {
					$query_next = false;
				} else if ( ! $results || empty( $results ) ) {
					$query_next = false;
				}
			}

			return $results;
		}

		/**
		 * Store a key/value to hide the smush features on bulk page
		 */
		function dismiss_welcome_notice() {
			update_site_option( 'wp-smush-hide_smush_welcome', 1 );
			wp_send_json_success();
		}

		/**
		 * Store a key/value to hide the smush features on bulk page
		 */
		function dismiss_upgrade_notice( $ajax = true ) {
			update_site_option( 'wp-smush-hide_upgrade_notice', 1 );
			//No Need to send json response for other requests
			if ( $ajax ) {
				wp_send_json_success();
			}
		}

		/**
		 * Remove the Update info
		 *
		 * @param bool $remove_notice
		 *
		 */
		function dismiss_update_info( $remove_notice = false ) {

			//From URL arg
			if ( isset( $_GET['dismiss_smush_update_info'] ) && 1 == $_GET['dismiss_smush_update_info'] ) {
				$remove_notice = true;
			}

			//From Ajax
			if ( ! empty( $_REQUEST['action'] ) && 'dismiss_update_info' == $_REQUEST['action'] ) {
				$remove_notice = true;
			}

			//Update Db
			if ( $remove_notice ) {
				update_site_option( 'wp-smush-hide_update_info', 1 );
			}

		}

		/**
		 * Resmush the image
		 *
		 * @uses smush_single()
		 *
		 */
		function resmush_image() {

			//Check Empty fields
			if ( empty( $_POST['attachment_id'] ) || empty( $_POST['_nonce'] ) ) {
				wp_send_json_error( array(
					'error'   => 'empty_fields',
					'message' => '<div class="wp-smush-error">' . esc_html__( "Image not smushed, fields empty.", "wp-smushit" ) . '</div>'
				) );
			}
			//Check Nonce
			if ( ! wp_verify_nonce( $_POST['_nonce'], "wp-smush-resmush-" . $_POST['attachment_id'] ) ) {
				wp_send_json_error( array(
					'error'   => 'empty_fields',
					'message' => '<div class="wp-smush-error">' . esc_html__( "Image couldn't be smushed as the nonce verification failed, try reloading the page.", "wp-smushit" ) . '</div>'
				) );
			}

			$image_id = intval( $_POST['attachment_id'] );

			$smushed = $this->smush_single( $image_id, true );

			//If any of the image is restored, we count it as success
			if ( ! empty( $smushed['status'] ) ) {

				//Send button content
				wp_send_json_success( array( 'button' => $smushed['status'] . $smushed['stats'] ) );

			} elseif ( ! empty( $smushed['error'] ) ) {

				//Send Error Message
				wp_send_json_error( array( 'message' => '<div class="wp-smush-error">' . __( "Unable to smush image", "wp-smushit" ) . '</div>' ) );

			}
		}

		/**
		 * Scans all the smushed attachments to check if they need to be resmushed as per the
		 * current settings, as user might have changed one of the configurations "Lossy", "Keep Original", "Preserve Exif"
		 */
		function scan_images() {

			global $WpSmush, $wpsmushnextgenadmin, $wpsmush_db, $wpsmush_settings, $wpsmush_helper, $wpsmush_resize, $wpsmushit_admin;

			check_ajax_referer( 'save_wp_smush_options', 'wp_smush_options_nonce' );

			$resmush_list = array();

			//Save settings only if networkwide settings are disabled
			if ( ( ! is_multisite() || ! $wpsmush_settings->is_network_enabled() ) && ( ! isset( $_REQUEST['process_settings'] ) || 'false' != $_REQUEST['process_settings'] ) ) {
				//Save Settings
				$wpsmush_settings->process_options();
			}

			//If there aren't any images in the library, return the notice
			if ( 0 == $wpsmush_db->get_media_attachments( true ) ) {
				$notice = esc_html__( "We haven’t found any images in your media library yet so there’s no smushing to be done!", "wp-smushit" );
				$resp   = '<div class="wp-smush-notice wp-smush-resmush-message" tabindex="0"><i class="dev-icon dev-icon-tick"></i> ' . $notice . '
				<i class="dev-icon dev-icon-cross"></i>
				</div>';

				//Save serialized settings
				$this->save_serialized_settings();

				wp_send_json_success( array(
					'notice'      => $resp,
					'super_smush' => $WpSmush->lossy_enabled
				) );
			}

			//Default Notice, to be displayed at the top of page
			//Show a message, at the top
			$message = esc_html__( 'Yay! All images are optimized as per your current settings.', 'wp-smushit' );
			$resp    = '<div class="wp-smush-notice wp-smush-resmush-message" tabindex="0"><i class="dev-icon dev-icon-tick"></i> ' . $message . '
				<i class="dev-icon dev-icon-cross"></i>
				</div>';

			//Scanning for NextGen or Media Library
			$type = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : '';

			//If a user manually runs smush check
			$return_ui = isset( $_REQUEST['get_ui'] ) && 'true' == $_REQUEST['get_ui'] ? true : false;

			//Update the variables
			$WpSmush->initialise();

			//Logic: If none of the required settings is on, don't need to resmush any of the images
			//We need at least one of these settings to be on, to check if any of the image needs resmush
			//Allow to smush Upfront images as well
			$upfront_active = class_exists( 'Upfront' );

			//Initialize Media Library Stats
			if ( 'nextgen' != $type && empty( $this->remaining_count ) ) {
				$this->setup_global_stats();
			}

			//Intialize NextGen Stats
			if ( 'nextgen' == $type && is_object( $wpsmushnextgenadmin ) && empty( $wpsmushnextgenadmin->remaining_count ) ) {
				$wpsmushnextgenadmin->setup_stats();
			}

			$key = 'nextgen' == $type ? 'wp-smush-nextgen-resmush-list' : 'wp-smush-resmush-list';

			$remaining_count = 'nextgen' == $type ? $wpsmushnextgenadmin->remaining_count : $this->remaining_count;

			if ( 0 == $remaining_count && ! $WpSmush->lossy_enabled && ! $WpSmush->smush_original && $WpSmush->keep_exif && ! $upfront_active ) {
				delete_option( $key );
				//Save serialized settings
				$this->save_serialized_settings();
				wp_send_json_success( array( 'notice' => $resp ) );
			}

			//Set to empty by default
			$ajax_response = '';

			//Get Smushed Attachments
			if ( 'nextgen' != $type ) {

				//Get list of Smushed images
				$attachments = ! empty( $this->smushed_attachments ) ? $this->smushed_attachments : $wpsmush_db->smushed_count( true );
			} else {
				global $wpsmushnextgenstats;

				//Get smushed attachments list from nextgen class, We get the meta as well
				$attachments = $wpsmushnextgenstats->get_ngg_images();

			}

			$image_count = $super_smushed_count = $smushed_count = 0;
			//Check if any of the smushed image needs to be resmushed
			if ( ! empty( $attachments ) && is_array( $attachments ) ) {
				$stats       = array(
					'size_before'        => 0,
					'size_after'         => 0,
					'savings_resize'     => 0,
					'savings_conversion' => 0
				);

				// Initialize resize class.
				$wpsmush_resize->initialize();

				foreach ( $attachments as $attachment_k => $attachment ) {

					//Skip if already in resmuhs list
					if ( ! empty( $wpsmushit_admin->resmush_ids ) && in_array( $attachment, $wpsmushit_admin->resmush_ids ) ) {
						continue;
					}
					$should_resmush = false;

					//For NextGen we get the metadata in the attachment data itself
					if ( is_array( $attachment ) && ! empty( $attachment['wp_smush'] ) ) {
						$smush_data = $attachment['wp_smush'];
					} else {
						//Check the current settings, and smush data for the image
						$smush_data = get_post_meta( $attachment, $this->smushed_meta_key, true );
					}

					//If the image is already smushed
					if ( is_array( $smush_data ) && ! empty( $smush_data['stats'] ) ) {

						//If we need to optmise losslessly, add to resmush list
						$smush_lossy = $WpSmush->lossy_enabled && ! $smush_data['stats']['lossy'];

						//If we need to strip exif, put it in resmush list
						$strip_exif = ! $WpSmush->keep_exif && isset( $smush_data['stats']['keep_exif'] ) && ( 1 == $smush_data['stats']['keep_exif'] );

						//If Original image needs to be smushed
						$smush_original = $WpSmush->smush_original && empty( $smush_data['sizes']['full'] );

						if ( $smush_lossy || $strip_exif || $smush_original ) {
							$should_resmush = true;
						}

						//If Image needs to be resized
						if ( ! $should_resmush ) {
							$should_resmush = $wpsmush_resize->should_resize( $attachment );
						}

						//If image can be converted
						if ( ! $should_resmush ) {
							global $wpsmush_pngjpg;
							$should_resmush = $wpsmush_pngjpg->can_be_converted( $attachment );
						}

						//If the image needs to be resmushed add it to the list
						if ( $should_resmush ) {
							$resmush_list[] = 'nextgen' == $type ? $attachment_k : $attachment;
							continue;
						} else {
							if ( 'nextgen' != $type ) {
								$resize_savings     = get_post_meta( $attachment, WP_SMUSH_PREFIX . 'resize_savings', true );
								$conversion_savings = $wpsmush_helper->get_pngjpg_savings( $attachment );

								//Increase the smushed count
								$smushed_count += 1;

								//Get the image count
								$image_count += ( ! empty( $smush_data['sizes'] ) && is_array( $smush_data['sizes'] ) ) ? sizeof( $smush_data['sizes'] ) : 0;

								//If the image is in resmush list, and it was super smushed earlier
								$super_smushed_count += ( $smush_data['stats']['lossy'] ) ? 1 : 0;

								//Add to the stats
								$stats['size_before'] += ! empty( $smush_data['stats'] ) ? $smush_data['stats']['size_before'] : 0;
								$stats['size_before'] += ! empty( $resize_savings['size_before'] ) ? $resize_savings['size_before'] : 0;
								$stats['size_before'] += ! empty( $conversion_savings['size_before'] ) ? $conversion_savings['size_before'] : 0;

								$stats['size_after'] += ! empty( $smush_data['stats'] ) ? $smush_data['stats']['size_after'] : 0;
								$stats['size_after'] += ! empty( $resize_savings['size_after'] ) ? $resize_savings['size_after'] : 0;
								$stats['size_after'] += ! empty( $conversion_savings['size_after'] ) ? $conversion_savings['size_after'] : 0;

								$stats['savings_resize']     += ! empty( $resize_savings ) ? $resize_savings['bytes'] : 0;
								$stats['savings_conversion'] += ! empty( $conversion_savings ) ? $conversion_savings['bytes'] : 0;
							}
						}
					}
				}

				//Check for Upfront images that needs to be smushed
				if ( $upfront_active && 'nextgen' != $type ) {
					$upfront_attachments = $wpsmush_db->get_upfront_images( $resmush_list );
					if ( ! empty( $upfront_attachments ) && is_array( $upfront_attachments ) ) {
						foreach ( $upfront_attachments as $u_attachment_id ) {
							if ( ! in_array( $u_attachment_id, $resmush_list ) ) {
								//Check if not smushed
								$upfront_images = get_post_meta( $u_attachment_id, 'upfront_used_image_sizes', true );
								if ( ! empty( $upfront_images ) && is_array( $upfront_images ) ) {
									//Iterate over all the images
									foreach ( $upfront_images as $image ) {
										//If any of the element image is not smushed, add the id to resmush list
										//and skip to next image
										if ( empty( $image['is_smushed'] ) || 1 != $image['is_smushed'] ) {
											$resmush_list[] = $u_attachment_id;
											break;
										}
									}
								}
							}
						}
					}
				}//End Of Upfront loop

				//Store the resmush list in Options table
				update_option( $key, $resmush_list, false );
			}

			//Delete resmush list if empty
			if ( empty( $resmush_list ) ) {
				//Delete the resmush list
				delete_option( $key );
			}

			//Return the Remsmush list and UI to be appended to Bulk Smush UI
			if ( $return_ui ) {
				if ( 'nextgen' != $type ) {
					//Set the variables
					$this->resmush_ids = $resmush_list;

				} else {
					//To avoid the php warning
					$wpsmushnextgenadmin->resmush_ids = $resmush_list;
				}

				if ( ( $count = count( $resmush_list ) ) > 0 || $this->remaining_count > 0 ) {

					if ( $count ) {
						$show = true;

						$count += 'nextgen' == $type ? $wpsmushnextgenadmin->remaining_count : $this->remaining_count;

						$ajax_response = $this->bulk_ui->bulk_resmush_content( $count, $show );
					}
				}
			}

			if ( ! empty( $resmush_list ) || $remaining_count > 0 ) {
				$message = sprintf( esc_html__( "You have images that need smushing. %sBulk smush now!%s", "wp-smushit" ), '<a href="#" class="wp-smush-trigger-bulk">', '</a>' );
				$resp    = '<div class="wp-smush-notice wp-smush-resmush-message wp-smush-resmush-pending" tabindex="0"><i class="dev-icon dev-icon-tick"></i> ' . $message . '
							<i class="dev-icon dev-icon-cross"></i>
						</div>';
			}

			//Append the directory smush stats
            $dir_smush_stats = get_option('dir_smush_stats');
			if ( ! empty( $dir_smush_stats ) && is_array( $dir_smush_stats ) ) {

				if ( ! empty( $dir_smush_stats['dir_smush'] ) && ! empty( $dir_smush_stats['optimised'] ) ) {
					$dir_smush_stats = $dir_smush_stats['dir_smush'];
					$image_count += $dir_smush_stats['optimised'];
				}

				//Add directory smush stats if not empty
				if ( ! empty( $dir_smush_stats['image_size'] ) && ! empty( $dir_smush_stats['orig_size'] ) ) {
					$stats['size_before'] += $dir_smush_stats['orig_size'];
					$stats['size_after']  += $dir_smush_stats['image_size'];
				}
			}

			//If there is a Ajax response return it, else return null
			$return = ! empty( $ajax_response ) ? array(
				"resmush_ids"        => $resmush_list,
				"content"            => $ajax_response,
				'count_image'        => $image_count,
				'count_supersmushed' => $super_smushed_count,
				'count_smushed'      => $smushed_count,
				'size_before'        => $stats['size_before'],
				'size_after'         => $stats['size_after'],
				'savings_resize'     => $stats['savings_resize'],
				'savings_conversion' => $stats['savings_conversion']
			) : array();

			//Include the count
			if ( ! empty( $count ) && $count ) {
				$return['count'] = $count;
			}

			$return['notice']      = $resp;
			$return['super_smush'] = $WpSmush->lossy_enabled;
			if ( $WpSmush->lossy_enabled && 'nextgen' == $type ) {
				$ss_count                    = $wpsmush_db->super_smushed_count( 'nextgen', $wpsmushnextgenstats->get_ngg_images( 'smushed' ) );
				$return['super_smush_stats'] = sprintf( '<strong><span class="smushed-count">%d</span>/%d</strong>', $ss_count, $wpsmushnextgenadmin->total_count );
			}

			//Save serialized settings
			$this->save_serialized_settings();

			wp_send_json_success( $return );

		}

		/**
		 * Remove the given attachment id from resmush list and updates it to db
		 *
		 * @param $attachment_id
		 * @param string $mkey
		 *
		 */
		function update_resmush_list( $attachment_id, $mkey = 'wp-smush-resmush-list' ) {
			$resmush_list = get_option( $mkey );

			//If there are any items in the resmush list, Unset the Key
			if ( ! empty( $resmush_list ) && count( $resmush_list ) > 0 ) {
				$key = array_search( $attachment_id, $resmush_list );
				if ( $resmush_list ) {
					unset( $resmush_list[ $key ] );
				}
				$resmush_list = array_values( $resmush_list );
			}

			//If Resmush List is empty
			if ( empty( $resmush_list ) || 0 == count( $resmush_list ) ) {
				//Delete resmush list
				delete_option( $mkey );
			} else {
				update_option( $mkey, $resmush_list, false );
			}
		}

		/**
		 * Returns current user name to be displayed
		 * @return string
		 */
		function get_user_name() {
			//Get username
			$current_user = wp_get_current_user();
			$name         = ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;

			return $name;
		}

		/**
		 * Format Numbers to short form 1000 -> 1k
		 *
		 * @param $number
		 *
		 * @return string
		 */
		function format_number( $number ) {
			if ( $number >= 1000 ) {
				return $number / 1000 . "k";   // NB: you will want to round this
			} else {
				return $number;
			}
		}

		/**
		 * Add/Remove image id from Super Smushed images count
		 *
		 * @param int $id Image id
		 *
		 * @param string $op_type Add/remove, whether to add the image id or remove it from the list
		 *
		 * @return bool Whether the Super Smushed option was update or not
		 *
		 */
		function update_super_smush_count( $id, $op_type = 'add', $key = 'wp-smush-super_smushed' ) {

			//Get the existing count
			$super_smushed = get_option( $key, false );

			//Initialize if it doesn't exists
			if ( ! $super_smushed || empty( $super_smushed['ids'] ) ) {
				$super_smushed = array(
					'ids' => array()
				);
			}

			//Insert the id, if not in there already
			if ( 'add' == $op_type && ! in_array( $id, $super_smushed['ids'] ) ) {

				$super_smushed['ids'][] = $id;

			} elseif ( 'remove' == $op_type && false !== ( $k = array_search( $id, $super_smushed['ids'] ) ) ) {

				//Else remove the id from the list
				unset( $super_smushed['ids'][ $k ] );

				//Reset all the indexes
				$super_smushed['ids'] = array_values( $super_smushed['ids'] );

			}

			//Add the timestamp
			$super_smushed['timestamp'] = current_time( 'timestamp' );

			update_option( $key, $super_smushed, false );

			//Update to database
			return true;
		}

		/**
		 * Checks if the image compression is lossy, stores the image id in options table
		 *
		 * @param int $id Image Id
		 *
		 * @param array $stats Compression Stats
		 *
		 * @param string $key Meta Key for storing the Super Smushed ids (Optional for Media Library)
		 *                    Need To be specified for NextGen
		 *
		 * @return bool
		 */
		function update_lists( $id, $stats, $key = '' ) {
			//If Stats are empty or the image id is not provided, return
			if ( empty( $stats ) || empty( $id ) || empty( $stats['stats'] ) ) {
				return false;
			}

			//Update Super Smush count
			if ( isset( $stats['stats']['lossy'] ) && 1 == $stats['stats']['lossy'] ) {
				if ( empty( $key ) ) {
					update_post_meta( $id, 'wp-smush-lossy', 1 );
				} else {
					$this->update_super_smush_count( $id, 'add', $key );
				}
			}

			//Check and update re-smush list for media gallery
			if ( ! empty( $this->resmush_ids ) && in_array( $id, $this->resmush_ids ) ) {
				$this->update_resmush_list( $id );
			}

		}

		/**
		 * Delete the resmush list for Nextgen or the Media Library
		 *
		 * Return Stats in ajax response
		 *
		 */
		function delete_resmush_list() {

			global $wpsmush_db;
			$stats = array();

			$key   = ! empty( $_POST['type'] ) && 'nextgen' == $_POST['type'] ? 'wp-smush-nextgen-resmush-list' : 'wp-smush-resmush-list';
			if ( 'nextgen' != $_POST['type'] ) {
				$resmush_list = get_option( $key );
				if ( ! empty( $resmush_list ) && is_array( $resmush_list ) ) {
					$stats = $wpsmush_db->get_savings_for_attachments( $resmush_list );
				}
			}

			//Delete the resmush list
			delete_option( $key );
			wp_send_json_success( array( 'stats' => $stats ) );
		}

		/**
		 * Allows to bulk restore the images, if there is any backup for them
		 */
		function bulk_restore() {
			global $wpsmush_db, $wpsmush_backup;
			$smushed_attachments = ! empty( $this->smushed_attachments ) ? $this->smushed_attachments : $wpsmush_db->smushed_count( true );
			foreach ( $smushed_attachments as $attachment ) {
				$wpsmush_backup->restore_image( $attachment->attachment_id, false );
			}
		}

		/**
		 * Loads the Shared UI to on all admin pages
		 *
		 * @param $current_page
		 */
		function load_shared_ui( $current_page ) {
			//If class method exists, load shared UI
			if ( class_exists( 'WDEV_Plugin_Ui' ) ) {

				if ( method_exists( 'WDEV_Plugin_Ui', 'load' ) && in_array( $current_page, $this->plugin_pages ) ) {

					//Load Shared UI
					WDEV_Plugin_Ui::load( WP_SMUSH_URL . 'assets/shared-ui/', false );
				}
			}
		}

		/** Get the Maximum Width and Height settings for WrodPress
		 *
		 * @return array, Array of Max. Width and Height for image
		 *
		 */
		function get_max_image_dimensions() {
			global $_wp_additional_image_sizes;

			$width = $height = 0;
			$limit = 9999; //Post-thumbnail

			$image_sizes = get_intermediate_image_sizes();

			// Create the full array with sizes and crop info
			foreach ( $image_sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$size_width  = get_option( "{$size}_size_w" );
					$size_height = get_option( "{$size}_size_h" );
				} elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
					$size_width  = $_wp_additional_image_sizes[ $size ]['width'];
					$size_height = $_wp_additional_image_sizes[ $size ]['height'];
				}

				//Skip if no width and height
				if ( ! isset( $size_width, $size_height ) ) {
					continue;
				}

				//If within te limit, check for a max value
				if ( $size_width <= $limit ) {
					$width = max( $width, $size_width );
				}

				if ( $size_height <= $limit ) {
					$height = max( $height, $size_height );
				}
			}

			return array(
				'width'  => $width,
				'height' => $height
			);
		}

		/**
		 * Perform the resize operation for the image
		 *
		 * @param $attachment_id
		 *
		 * @param $meta
		 *
		 * @return mixed
		 */
		function resize_image( $attachment_id, $meta ) {
			if ( empty( $attachment_id ) || empty( $meta ) ) {
				return $meta;
			}
			global $wpsmush_resize;

			return $wpsmush_resize->auto_resize( $attachment_id, $meta );
		}

		/**
		 * Limit for all the queries
		 *
		 * @return int|mixed|void
		 *
		 */
		function query_limit() {
			$limit = apply_filters( 'wp_smush_query_limit', 2000 );
			$limit = !empty( $this->total_count ) && $limit > $this->total_count ? $this->total_count : $limit;
			$limit = intval( $limit );

			return $limit;
		}

		/**
		 * Filter the number of results fetched at once for NextGen queries
		 *
		 * @return int|mixed|void
		 *
		 */
		function nextgen_query_limit() {
			$limit = apply_filters( 'wp_smush_nextgen_query_limit', 1000 );
			$limit = intval( $limit );

			return $limit;
		}

		/**
		 * Show Update info in admin Notice
		 *
		 */
		function smush_updated() {
			//@todo: Update Smush Update Notice for next release
			//Make sure to not display this message for next release
			$plugin_data = get_plugin_data( WP_SMUSH_DIR . 'wp-smush.php', false, false );
			$version     = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';

			//If Versions Do not match
			if ( empty( $version ) || $version != WP_SMUSH_VERSION ) {
				return true;
			}

			//Do not display it for other users
			if ( ! is_super_admin() || ! current_user_can( 'manage_options' ) ) {
				return true;
			}

			//If dismissed, Delete the option on Plugin Activation, For alter releases
			if ( 1 == get_site_option( 'wp-smush-hide_update_info' ) ) {
				return true;
			}

			//Get Plugin dir, Return if it's WP Smush Pro installation
			$dir_path = get_plugin_dir();
			if ( ! empty( $dir_path ) && strpos( $dir_path, 'wp-smush-pro' ) !== false ) {
				return true;
			}

			//Do not display the notice on Bulk Smush Screen
			global $current_screen;
			if ( ! empty( $current_screen->base ) && ( 'media_page_wp-smush-bulk' == $current_screen->base || 'gallery_page_wp-smush-nextgen-bulk' == $current_screen->base || 'settings_page_wp-smush-network' == $current_screen->base ) ) {
				return true;
			}

			$upgrade_url = add_query_arg(
				array(
					'utm_source'   => 'Smush-Free',
					'utm_medium'   => 'Banner',
					'utm_campaign' => 'now-with-async'
				),
				$this->upgrade_url
			);
			$settings_link = is_multisite() ? network_admin_url( 'settings.php?page=wp-smush' ) : admin_url( 'upload.php?page=wp-smush-bulk' );

			$settings_link = '<a href="' . $settings_link . '" title="' . esc_html__( "Review your setting now.", "wp-smushit" ) . '">';
			$upgrade_link  = '<a href="' . esc_url( $upgrade_url ) . '" title="' . esc_html__( "WP Smush Pro", "wp-smushit" ) . '">';
			$message_s     = sprintf( esc_html__( "Welcome to the newest version of WP Smush! In this update we've added the ability to bulk smush images in directories outside your uploads folder.", 'wp-smushit' ), WP_SMUSH_VERSION, '<strong>', '</strong>' );

			//Message for network admin
			$message_s .= is_multisite() ? sprintf( esc_html__( " And as a multisite user, you can manage %sSmush settings%s globally across all sites!", 'wp-smushit' ), $settings_link, '</a>' ) : '';

			//Upgrade link for free users
			$message_s .= ! $this->validate_install() ? sprintf( esc_html__( " %sFind out more here >>%s", "wp-smushit" ), $upgrade_link, '</a>' ) : '';
			?>
			<div class="notice notice-info is-dismissible wp-smush-update-info">
				<p><?php echo $message_s; ?></p>
			</div><?php
			//Notice JS
			wp_enqueue_script('wp-smushit-notice-js', '', array(), '', true );
		}

		/**
		 * Check whether to skip a specific image size or not
		 *
		 * @param string $size Registered image size
		 *
		 * @return bool true/false Whether to skip the image size or not
		 *
		 */
		function skip_image_size (  $size = '' ) {
			global $wpsmush_settings;

			//No image size specified, Don't skip
			if( empty( $size ) ) {
				return false;
			}

			$image_sizes = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX.'image_sizes' );

			//If Images sizes aren't set, don't skip any of the image size
			if( false === $image_sizes ) {
				return false;
			}

			//Check if the size is in the smush list
			if( is_array( $image_sizes ) && !in_array(  $size, $image_sizes ) ) {
				return true;
			}

		}

		/**
		 * Get registered image sizes with dimension
		 *
		 */
		function image_dimensions() {
			global $_wp_additional_image_sizes;
			$additional_sizes = get_intermediate_image_sizes();
			$sizes = array();

			// Create the full array with sizes and crop info
			foreach( $additional_sizes as $_size ) {
				if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
					$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
					$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
					$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
				} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
					$sizes[ $_size ] = array(
						'width' => $_wp_additional_image_sizes[ $_size ]['width'],
						'height' => $_wp_additional_image_sizes[ $_size ]['height'],
						'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
					);
				}
			}
			//Medium Large
			if ( !isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) ) {
				$width  = intval( get_option( 'medium_large_size_w' ) );
				$height = intval( get_option( 'medium_large_size_h' ) );

				$sizes['medium_large'] = array(
					'width'  => $width,
					'height' => $height
				);
			}

			return $sizes;

		}

		/**
		 * Prints the Membership Validation issue notice
		 *
		 */
		function media_library_membership_notice() {

			//No need to print it for free version
			if( !$this->validate_install() ) {
				return;
			}
			//Show it on Media Library page only
			$screen = get_current_screen();
			$screen_id = !empty( $screen ) ? $screen->id : '';
			//Do not show notice anywhere else
			if( empty( $screen ) || 'upload' != $screen_id ) {
				return;
			}

			echo $this->bulk_ui->get_user_validation_message( $notice = true );
		}

		/**
		 * Save settings, Used for networkwide option
		 */
		function save_settings() {
			//Validate Ajax request
			check_ajax_referer( 'save_wp_smush_options', 'nonce' );

			global $wpsmush_settings;
			//Save Settings
			$wpsmush_settings->process_options();
			wp_send_json_success();

		}

		/**
		 * Returns a serialised string of current settings
		 *
		 * @return Serialised string of settings
		 *
		 */
		function get_serialised_settings() {
			global $wpsmush_settings;
			$settings = array();
			foreach ( $this->settings as $key => $val ) {
				$settings[ $key ] = $wpsmush_settings->get_setting( WP_SMUSH_PREFIX . $key );
			}
			$settings = maybe_serialize( $settings );

			return $settings;
		}

		/**
		 * Stores the latest settings in serialised form in DB For the current settings
		 *
		 * No need to store the serialised settings, if network wide settings is disabled
		 * because the site would run the scan when settings are saved
		 *
		 */
		function save_serialized_settings() {
			//Return -> Single Site | If network settings page | Networkwide Settings Disabled
			if ( ! is_multisite() || is_network_admin() || ! get_site_option( WP_SMUSH_PREFIX . 'networkwide' ) ) {
				return;
			}
			global $wpsmush_settings;
			$c_settings = $this->get_serialised_settings();
			$wpsmush_settings->update_setting( WP_SMUSH_PREFIX . 'last_settings', $c_settings );
		}

		/**
		 * Allows to filter the error message sent to the user
		 *
		 * @param string $error
		 * @param string $attachment_id
		 *
		 * @return mixed|null|string|void
		 */
		function filter_error( $error = '', $attachment_id = '' ) {
			if ( empty( $error ) ) {
				return null;
			}
			/**
			 * Used internally to modify the error message
			 *
			 */
			$error = apply_filters( 'wp_smush_error', $error, $attachment_id );

			return $error;
		}

	}

	global $wpsmushit_admin;
	$wpsmushit_admin = new WpSmushitAdmin();
}