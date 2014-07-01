<?php
/*
Plugin Name: Google Sitemap
Plugin URI: http://bestwebsoft.com/plugin/
Description: Plugin to add google sitemap file in Google Webmaster Tools account.
Author: BestWebSoft
Version: 2.9.0
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*
	© Copyright 2014  BestWebSoft  ( http://support.bestwebsoft.com )

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

/*============================================ Function for adding menu and submenu ====================*/
if ( ! function_exists( 'gglstmp_admin_menu' ) ) {
	function gglstmp_admin_menu() {
		global $bstwbsftwppdtplgns_options, $wpmu, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename(__FILE__);

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( 1 == $wpmu ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {
			$plugin_with_newer_menu = $base;
			foreach ( $bstwbsftwppdtplgns_options['bws_menu']['version'] as $key => $value ) {
				if ( $bws_menu_version < $value && is_plugin_active( $base ) ) {
					$plugin_with_newer_menu = $key;
				}
			}
			$plugin_with_newer_menu = explode( '/', $plugin_with_newer_menu );
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
			if ( file_exists( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' ) )
				require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' );
			else
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
			$bstwbsftwppdtplgns_added_menu = true;			
		}

		add_menu_page( __( 'BWS Plugins', 'sitemap' ), __( 'BWS Plugins', 'sitemap' ), 'manage_options', 'bws_plugins', 'bws_add_menu_render', plugins_url( 'images/px.png', __FILE__ ), 1001 );
		add_submenu_page( 'bws_plugins', __( 'Google Sitemap Settings', 'sitemap' ), __( 'Google Sitemap', 'sitemap' ), 'manage_options', "google-sitemap-plugin.php", 'gglstmp_settings_page' );
		
		global $url_home, $url, $url_send, $url_send_sitemap;		
		$url_home			=	home_url( "/" );
		$url				=	urlencode( $url_home );
		$url_send			=	"https://www.google.com/webmasters/tools/feeds/sites/";
		$url_send_sitemap	=	"https://www.google.com/webmasters/tools/feeds/";
	}
}

/* Function adds language files */
if ( ! function_exists( 'gglstmp_init' ) ) {
	function gglstmp_init() {
		load_plugin_textdomain( 'sitemap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );		
	}
}

if ( ! function_exists( 'gglstmp_admin_init' ) ) {
	function gglstmp_admin_init() {
		global $bws_plugin_info, $gglstmp_plugin_info;

		$gglstmp_plugin_info = get_plugin_data( __FILE__ );	

		if ( isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '83', 'version' => $gglstmp_plugin_info["Version"] );

		gglstmp_plugin_version_check();

		if ( isset( $_GET['page'] ) && "google-sitemap-plugin.php" == $_GET['page'] )
			gglstmp_register_settings();
	}
}

/*============================================ Function for register of the plugin settings on init core ====================*/
if ( ! function_exists( 'gglstmp_register_settings' ) ) {
	function gglstmp_register_settings() {
		global $wpmu, $gglstmp_settings, $gglstmp_plugin_info;

		$gglstmp_option_defaults = array( 'page', 'post' );

		if ( 1 == $wpmu ) {
			if ( ! get_site_option( 'gglstmp_settings' ) )
				add_site_option( 'gglstmp_settings', $gglstmp_option_defaults );
			
			$gglstmp_settings = get_site_option( 'gglstmp_settings' );
		} else {
			if ( ! get_option( 'gglstmp_settings' ) )
				add_option( 'gglstmp_settings', $gglstmp_option_defaults );
			
			$gglstmp_settings = get_option( 'gglstmp_settings' );
		}
	}
}

/* Function check if plugin is compatible with current WP version  */
if ( ! function_exists ( 'gglstmp_plugin_version_check' ) ) {
	function gglstmp_plugin_version_check() {
		global $wp_version, $gglstmp_plugin_info;
		$require_wp		=	"3.0"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
	 	if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $gglstmp_plugin_info['Name'] . " </strong> " . __( 'requires', 'sitemap' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'sitemap') . "<br /><br />" . __( 'Back to the WordPress', 'sitemap') . " <a href='" . get_admin_url( null, 'plugins.php' ) . "'>" . __( 'Plugins page', 'sitemap') . "</a>." );
			}
		}
	}
}

/*============================================ Function for creating sitemap file ====================*/
if ( ! function_exists( 'gglstmp_sitemapcreate' ) ) {
	function gglstmp_sitemapcreate() {
		global $wpdb;

		if ( isset( $_POST['gglstmp_settings'] ) )
			$gglstmp_settings = $_POST['gglstmp_settings'];
		else
			global $gglstmp_settings;

		$str = "";
		foreach ( $gglstmp_settings as $val ) {
			if ( $str != "" )
				$str .= ", ";
			$str .= "'" . $val . "'";
		}
		$xml = new DomDocument( '1.0', 'utf-8' );
		$xml_stylesheet_path = plugins_url() . "/google-sitemap-plugin/sitemap.xsl";

		$xslt = $xml->createProcessingInstruction( 'xml-stylesheet', "type=\"text/xsl\" href=\"$xml_stylesheet_path\"" );
		$xml->appendChild( $xslt );
		$urlset = $xml->appendChild( $xml->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9','urlset' ) );

		if ( ! empty( $str ) ) {
			$loc = $wpdb->get_results( "SELECT ID, post_modified, post_status, post_type, ping_status FROM $wpdb->posts WHERE post_status = 'publish' AND post_type IN (" . $str . ")" );

			foreach ( $loc as $val ) {
				$url = $urlset->appendChild( $xml->createElement( 'url' ) );
				$loc = $url->appendChild( $xml->createElement( 'loc' ) );
				$permalink = get_permalink( $val->ID );
				$loc->appendChild( $xml->createTextNode( $permalink ) );
				$lastmod = $url->appendChild( $xml->createElement( 'lastmod' ) );
				$now = $val->post_modified;
				$date = date( 'Y-m-d\TH:i:sP', strtotime( $now ) );
				$lastmod->appendChild( $xml -> createTextNode( $date ) );
				$changefreq = $url -> appendChild( $xml->createElement( 'changefreq' ) );
				$changefreq->appendChild( $xml->createTextNode( 'monthly' ) );
				$priority = $url->appendChild( $xml->createElement( 'priority' ) );
				$priority->appendChild( $xml->createTextNode( 1.0 ) );
			}
			$xml->formatOutput = true;
		}

		if ( is_multisite() ) {
			$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', home_url() ) ) );
			$xml->save( ABSPATH . 'sitemap_' . $home_url . '.xml' );
		} else {
			$xml->save( ABSPATH . 'sitemap.xml' );
		}
	}
}

/*============================================ Function for creating setting page ====================*/
if ( ! function_exists ( 'gglstmp_settings_page' ) ) {
	function gglstmp_settings_page() {
		global $url_home, $gglstmp_settings, $url, $wp_version, $gglstmp_plugin_info;
		$message = $error = "";
		$gglstmp_robots = get_option( 'gglstmp_robots' );
		$url_robot = ABSPATH . "robots.txt";

		if ( is_multisite() ) {
			$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', home_url() ) ) );
			$url_sitemap = ABSPATH . "sitemap_" . $home_url .".xml";
		} else {
			$url_sitemap = ABSPATH . "sitemap.xml";
		}

		if ( isset( $_POST['gglstmp_new'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'gglstmp_nonce_name' ) ) {
			$message =  __( "Your Sitemap file is created in the site root directory.", 'sitemap' );
			gglstmp_sitemapcreate();
		}

		if ( isset( $_REQUEST['gglstmp_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'gglstmp_nonce_name' ) ) {
			$gglstmp_settings = isset( $_REQUEST['gglstmp_settings'] ) ? $_REQUEST['gglstmp_settings'] : array();
			update_option( 'gglstmp_settings', $gglstmp_settings );
			$message .= " " . __( "Settings saved." , 'sitemap' );
			if ( ! isset( $_POST['gglstmp_checkbox'] ) ) {
				if ( get_option( 'gglstmp_robots' ) )
					update_option( 'gglstmp_robots', 0 );
				$gglstmp_robots = get_option( 'gglstmp_robots' );
			}
		}
		/*============================ Adding location of sitemap file to the robots.txt =============*/
		if ( isset( $_POST['gglstmp_checkbox'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'gglstmp_nonce_name' ) ) {
			if ( file_exists( $url_robot ) && ! is_multisite() ) {
				$fp = fopen( ABSPATH . 'robots.txt', "a+" );
				$flag = false;
				while ( false !== ( $line = fgets( $fp ) ) ) {
					if ( $line == "Sitemap: " . $url_home . "sitemap.xml" )
						$flag = true;
				}
				if ( ! $flag )
					fwrite( $fp, "\nSitemap: " . $url_home . "sitemap.xml" );
				fclose ( $fp );
			}

			if ( false === get_option( 'gglstmp_robots' ) )
				add_option( 'gglstmp_robots', 1 );
			else
				update_option( 'gglstmp_robots', 1 );

			$gglstmp_robots = get_option( 'gglstmp_robots' );

			if ( $message == "" )
				$message =  __( "Settings saved.", 'sitemap' );
		}

		/*$gglstmp_result = $wpdb->get_results( "SELECT post_type FROM " . $wpdb->posts . " WHERE post_type NOT IN ( 'revision', 'attachment', 'nav_menu_item' ) GROUP BY post_type" );*/
		$gglstmp_result = get_post_types( '', 'names' );
		unset( $gglstmp_result['revision'] );
		unset( $gglstmp_result['attachment'] );
		unset( $gglstmp_result['nav_menu_item'] );

		/*================================ Different checks for the valid entering data ===================*/
		if ( isset( $_POST['gglstmp_menu'] ) && ( ! isset( $_POST['gglstmp_email'] ) || ! isset( $_POST['gglstmp_passwd'] ) || empty( $_POST['gglstmp_email'] ) || empty( $_POST['gglstmp_passwd'] ) ) ) {
			$error = __( 'Please enter your login and password for remote work with Google Webmaster Tools', 'sitemap' );
		} elseif ( isset( $_POST['gglstmp_email'] ) && isset( $_POST['gglstmp_passwd'] ) && isset( $_POST['gglstmp_menu'] ) && "ad" != $_POST['gglstmp_menu'] && "del" != $_POST['gglstmp_menu'] && "inf" != $_POST['gglstmp_menu'] ) {
			$error = __( 'You should choose at least one action for remote work with Google Webmaster Tools', 'sitemap' );
		} elseif ( isset( $_POST['gglstmp_email'] ) && isset( $_POST['gglstmp_passwd'] ) && isset( $_POST['gglstmp_menu'] ) && ! empty( $_POST['gglstmp_email'] ) && ! empty( $_POST['gglstmp_passwd'] ) ) {
			/*=================== Connecting to the google account =================*/
			$data = array(
				'accountType'	=>	'GOOGLE',
				'Email'			=>	trim( $_POST['gglstmp_email'] ),
				'Passwd'		=>	trim( $_POST['gglstmp_passwd'] ),
				'service'		=>	'sitemaps'
			);
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin" );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			curl_setopt( $ch,  CURLOPT_UNRESTRICTED_AUTH, true );
			$hasil = curl_exec( $ch );
			curl_close( $ch );
			$httpResponseAr = explode( "\n", $hasil );
			$httpParsedResponseAr = array();
			foreach ( $httpResponseAr as $i => $rVal ) {
				if ( false !== strpos( $rVal, "=" ) ) {
					list( $qKey, $qVal ) = explode ( "=", $rVal );
					$httpParsedResponseAr[ $qKey ] = $qVal;
				}
			}
			$au = isset( $httpParsedResponseAr["Auth"] ) ? $httpParsedResponseAr["Auth"] : false;
			if ( ! $au && ( $_POST['gglstmp_email'] ) && ( $_POST['gglstmp_passwd'] ) )
				$error = __( 'Login and password do not match. Please try again', 'sitemap' );
		}
		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			global $bstwbsftwppdtplgns_options;

			$bws_license_key = ( isset( $_POST['bws_license_key'] ) ) ? trim( $_POST['bws_license_key'] ) : "";

			if ( isset( $_POST['bws_license_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_license_nonce_name' ) ) {
				if ( '' != $bws_license_key ) { 
					if ( strlen( $bws_license_key ) != 18 ) {
						$error = __( "Wrong license key", 'sitemap' );
					} else {
						$bws_license_plugin = trim( $_POST['bws_license_plugin'] );	
						if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] ) && $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] < ( time() + (24 * 60 * 60) ) ) {
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] + 1;
						} else {
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = 1;
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] = time();
						}	

						/* download Pro */
						if ( !function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active_for_network' ))
							require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

						$all_plugins = get_plugins();
						$active_plugins = get_option( 'active_plugins' );
						
						if ( ! array_key_exists( $bws_license_plugin, $all_plugins ) ) {
							$current = get_site_transient( 'update_plugins' );
							if ( is_array( $all_plugins ) && !empty( $all_plugins ) && isset( $current ) && is_array( $current->response ) ) {
								$to_send = array();
								$to_send["plugins"][ $bws_license_plugin ] = array();
								$to_send["plugins"][ $bws_license_plugin ]["bws_license_key"] = $bws_license_key;
								$to_send["plugins"][ $bws_license_plugin ]["bws_illegal_client"] = true;
								$options = array(
									'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3 ),
									'body' => array( 'plugins' => serialize( $to_send ) ),
									'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );
								$raw_response = wp_remote_post( 'http://bestwebsoft.com/wp-content/plugins/paid-products/plugins/update-check/1.0/', $options );

								if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
									$error = __( "Something went wrong. Try again later. If the error will appear again, please, contact us <a href=http://support.bestwebsoft.com>BestWebSoft</a>. We are sorry for inconvenience.", 'sitemap' );
								} else {
									$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
									
									if ( is_array( $response ) && !empty( $response ) ) {
										foreach ( $response as $key => $value ) {
											if ( "wrong_license_key" == $value->package ) {
												$error = __( "Wrong license key", 'sitemap' ); 
											} elseif ( "wrong_domain" == $value->package ) {
												$error = __( "This license key is bind to another site", 'sitemap' );
											} elseif ( "you_are_banned" == $value->package ) {
												$error = __( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'sitemap' );
											}
										}
										if ( '' == $error ) {
											$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;

											$url = 'http://bestwebsoft.com/wp-content/plugins/paid-products/plugins/downloads/?bws_first_download=' . $bws_license_plugin . '&bws_license_key=' . $bws_license_key . '&download_from=5';
											$uploadDir = wp_upload_dir();
											$zip_name = explode( '/', $bws_license_plugin );
										    if ( file_put_contents( $uploadDir["path"] . "/" . $zip_name[0] . ".zip", file_get_contents( $url ) ) ) {
										    	@chmod( $uploadDir["path"] . "/" . $zip_name[0] . ".zip", octdec( 755 ) );
										    	if ( class_exists( 'ZipArchive' ) ) {
													$zip = new ZipArchive();
													if ( $zip->open( $uploadDir["path"] . "/" . $zip_name[0] . ".zip" ) === TRUE ) {
														$zip->extractTo( WP_PLUGIN_DIR );
														$zip->close();
													} else {
														$error = __( "Failed to open the zip archive. Please, upload the plugin manually", 'sitemap' );
													}								
												} elseif ( class_exists( 'Phar' ) ) {
													$phar = new PharData( $uploadDir["path"] . "/" . $zip_name[0] . ".zip" );
													$phar->extractTo( WP_PLUGIN_DIR );
												} else {
													$error = __( "Your server does not support either ZipArchive or Phar. Please, upload the plugin manually", 'sitemap' );
												}
												@unlink( $uploadDir["path"] . "/" . $zip_name[0] . ".zip" );										    
											} else {
												$error = __( "Failed to download the zip archive. Please, upload the plugin manually", 'sitemap' );
											}

											/* activate Pro */
											if ( file_exists( WP_PLUGIN_DIR . '/' . $zip_name[0] ) ) {			
												array_push( $active_plugins, $bws_license_plugin );
												update_option( 'active_plugins', $active_plugins );
												$pro_plugin_is_activated = true;
											} elseif ( '' == $error ) {
												$error = __( "Failed to download the zip archive. Please, upload the plugin manually", 'sitemap' );
											}																				
										}
									} else {
										$error = __( "Something went wrong. Try again later or upload the plugin manually. We are sorry for inconvienience.", 'sitemap' ); 
					 				}
					 			}
				 			}
						} else {
							/* activate Pro */
							if ( ! ( in_array( $bws_license_plugin, $active_plugins ) || is_plugin_active_for_network( $bws_license_plugin ) ) ) {			
								array_push( $active_plugins, $bws_license_plugin );
								update_option( 'active_plugins', $active_plugins );
								$pro_plugin_is_activated = true;
							}						
						}
						update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			 		}
			 	} else {
		 			$error = __( "Please, enter Your license key", 'sitemap' );
		 		}
		 	}
		} ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( "Google Sitemap", 'sitemap' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( !isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=google-sitemap-plugin.php"><?php _e( 'Settings', 'sitemap' ); ?></a>
				<a class="nav-tab<?php if ( isset( $_GET['action'] ) && 'extra' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=google-sitemap-plugin.php&amp;action=extra"><?php _e( 'Extra settings', 'sitemap' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/plugin/google-sitemap-plugin/#faq" target="_blank"><?php _e( 'FAQ', 'sitemap' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=google-sitemap-plugin.php&amp;action=go_pro"><?php _e( 'Go PRO', 'sitemap' ); ?></a>
			</h2>
			<div id="gglstmp_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'sitemap' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'sitemap' ); ?></p></div>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['gglstmp_submit'] ) || $message == "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) { ?>
				<form action="admin.php?page=google-sitemap-plugin.php" method='post' id="gglstmp_auth" name="gglstmp_auth">
					<?php /*=============================== Creating sitemap file ====================================*/
					if ( file_exists( $url_sitemap ) ) {
						if ( is_multisite() ) {
							echo '<p><a href="' . $url_home . "sitemap_" . $home_url . '.xml" target="_new">' . __( "The Sitemap file", 'sitemap' ) . "</a> " . __( "already exists. If you would like to replace it with a new one, please choose the necessary box below. All other actions will overwrite the existing file.", 'sitemap' ) . "</p>";
						} else {		
							echo '<p><a href="' . $url_home . 'sitemap.xml" target="_new">' . __( "The Sitemap file", 'sitemap' ) . "</a> " . __( "already exists. If you would like to replace it with a new one, please choose the necessary box below. All other actions will overwrite the existing file.", 'sitemap' ) . "</p>";
						}
					} else {
						gglstmp_sitemapcreate();
						if ( is_multisite() ) {
							echo '<p><a href="' . $url_home . "sitemap_" . $home_url . '.xml" target="_new">' . __( "Your Sitemap file", 'sitemap' ) . "</a> " . __( "is created in the site root directory.", 'sitemap' ) . "</p>";
						} else {
							echo '<p><a href="' . $url_home . 'sitemap.xml" target="_new">' . __( "Your Sitemap file", 'sitemap' ) . "</a> " . __( "is created in the site root directory.", 'sitemap' ) . "</p>";
						}
					}
					/*========================================== Recreating sitemap file ====================================*/
					if ( is_multisite() ) {
						echo '<p>' . __( "If you do not want a sitemap file to be added to Google Webmaster Tools automatically, you can do it using", 'sitemap' ) . " <a href=\"https://www.google.com/webmasters/tools/home?hl=en\">". __( "this", 'sitemap' ) . "</a> ". __( "link - sign in, choose the necessary site, go to 'Sitemaps' and fill out the mandatory field", 'sitemap' ) . " - '" . $url_home . "sitemap_" . $home_url . ".xml'.</p>";
					} else {
						echo '<p>' . __( "If you do not want a sitemap file to be added to Google Webmaster Tools automatically, you can do it using", 'sitemap' ) . " <a href=\"https://www.google.com/webmasters/tools/home?hl=en\">". __( "this", 'sitemap' ) . "</a> ". __( "link - sign in, choose the necessary site, go to 'Sitemaps' and fill out the mandatory field", 'sitemap' ) . " - '" . $url_home . "sitemap.xml'.</p>";
					} ?>
					<table class="form-table">
						<tr valign="top">
							<td colspan="2">
								<label><input type='checkbox' name='gglstmp_new' value="1" /> <?php _e( "I want to create a new sitemap file or update the existing one", 'sitemap' ); ?></label>
							</td>
						</tr>
						<?php if ( is_multisite() ) { ?>
							<tr valign="top">
								<td colspan="2">
									<label><input type='checkbox' disabled="disabled" name='gglstmp_checkbox' value="1" <?php if ( 1 == $gglstmp_robots ) echo 'checked="checked"'; ?> /> <?php _e( "I want to add sitemap file path in robots.txt", 'sitemap' );?></label>
									<p style="color:red"><?php _e( "Since you are using multisiting, the plugin does not allow to add a sitemap to robots.txt", 'sitemap' ); ?></div>
								</td>
							</tr>
						<?php } else { ?>
							<tr valign="top">
								<td colspan="2">
									<label><input type='checkbox' name='gglstmp_checkbox' value="1" <?php if ( 1 == $gglstmp_robots ) echo 'checked="checked"'; ?> /> <?php _e( "I want to add sitemap file path in", 'sitemap' ); ?> <a href="<?php echo $url_home; ?>robots.txt" target="_new">robots.txt</a></label>
								</td>
							</tr>
						<?php } ?>
						<tr valign="top">
							<th scope="row" colspan="2"><?php _e( 'Please choose the necessary post types the links to which are to be added to the sitemap:', 'sitemap' ); ?> </th>
						</tr>
						<tr valign="top">
							<td colspan="2">
								<?php
								foreach ( $gglstmp_result as $key => $value ) { ?>
									<label><input type="checkbox" <?php echo ( in_array( $value, $gglstmp_settings ) ?  'checked="checked"' : "" ); ?> name="gglstmp_settings[]" value="<?php echo $value; ?>"/><span style="text-transform: capitalize; padding-left: 5px;"><?php echo $value; ?></span></label><br />
								<?php } ?>
							</td>
						</tr>
					</table>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">	
							<div class="bws_table_bg"></div>											
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th><?php _e( 'XML Sitemap "Change Frequency" parameter', 'sitemap_pro' ); ?></th>
									<td>
										<select name="gglstmppr_sitemap_change_frequency">
											<option value="always"><?php _e( 'Always', 'sitemap_pro' ); ?></option>
											<option value="hourly"><?php _e( 'Hourly', 'sitemap_pro' ); ?></option>
											<option value="daily"><?php _e( 'Daily', 'sitemap_pro' ); ?></option>
											<option value="weekly"><?php _e( 'Weekly', 'sitemap_pro' ); ?></option>
											<option value="monthly"><?php _e( 'Monthly', 'sitemap_pro' ); ?></option>
											<option value="yearly"><?php _e( 'Yearly', 'sitemap_pro' ); ?></option>
											<option value="never"><?php _e( 'Never', 'sitemap_pro' ); ?></option>
										</select><br />
										<span style="color: #888888;font-size: 10px;"><?php _e( 'This value is used in the sitemap file and provides general information to search engines. The sitemap itself is generated once and will be re-generated when you create or update any post or page.', 'sitemap_pro' ); ?></span>
									</td>
								</tr>			
							</table>	
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'sitemap' ); ?> 
								<a href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Sitemap Pro"><?php _e( 'Learn More', 'sitemap' ); ?></a>				
							</div>
							<a class="bws_button" href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>#purchase" target="_blank" title="Google Sitemap Pro">
								<?php _e( 'Go', 'sitemap' ); ?> <strong>PRO</strong>
							</a>	
							<div class="clear"></div>					
						</div>
					</div>
					<table class="form-table">
						<?php if ( ! function_exists( 'curl_init' ) ) { ?>
							<tr valign="top">
								<td colspan="2" class="gglstmppr_error">
									<?php echo __( "This hosting does not support сURL, so you cannot add a sitemap file automatically.", 'sitemap' ); ?>
								</td>
							</tr>
						<?php } else { ?>
							<tr valign="top">
								<td colspan="2">
									<?php echo __( "Please enter your Google account login and password in order to add or delete a site and a sitemap file automatically or get information about this site in Google Webmaster Tools.", 'sitemap' ); ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Settings for remote work with Google Webmaster Tools', 'sitemap' ); ?></th>
								<td>
									<input placeholder="<?php _e( "Login", 'sitemap' );	?>" type='text' name='gglstmp_email' value="<?php if ( isset( $_REQUEST['gglstmp_email'] ) ) echo  $_REQUEST['gglstmp_email']; ?>" /><br />
									<input placeholder="<?php _e( "Password", 'sitemap' ); ?>" type='password' name='gglstmp_passwd' value="<?php if ( isset( $_REQUEST['gglstmp_passwd'] ) ) echo  $_REQUEST['gglstmp_passwd']; ?>" /><br />
									<label><input type='radio' name='gglstmp_menu' value="ad" /> <?php _e( "I want to add this site to Google Webmaster Tools", 'sitemap' ); ?></label><br />
									<label><input type='radio' name='gglstmp_menu' value="del" /> <?php _e( "I want to delete this site from Google Webmaster Tools", 'sitemap' ); ?></label><br />
									<label><input type='radio' name='gglstmp_menu' value="inf" /> <?php _e( "I want to get info about this site in Google Webmaster Tools", 'sitemap' ); ?></label><br />
									<span style="color: #888888;font-size: 10px;">
										<?php _e( 'In case you failed to add a sitemap to Google automatically using this plugin, it is possible to do it manually', 'sitemap' ); ?>: 
										<a href="https://docs.google.com/document/d/1VOJx_OaasVskCqi9fsAbUmxfsckoagPU5Py97yjha9w/edit"><?php _e( 'View the Instruction', 'sitemap' ); ?></a>
									</span>
								</td>
							</tr>
						<?php }	?>
					</table>				
					<input type="hidden" name="gglstmp_submit" value="submit" />
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'sitemap' ); ?>" />
					</p>
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'gglstmp_nonce_name' ); ?>
				</form>
				<?php if ( isset( $au ) && false !== $au && ( $_POST['gglstmp_email'] ) && ( $_POST['gglstmp_passwd'] ) ) {
					if ( "inf" == $_POST['gglstmp_menu'] ) {
						gglstmp_info_site( $au );/* Getting info about the site in google webmaster tools account */
					} else if ( "ad" == $_POST['gglstmp_menu'] ) {
						gglstmp_add_site( $au ); /* Adding site and verifying its ownership */
						gglstmp_add_sitemap( $au );/* Adding sitemap file to the google webmaster tools account */
					} else if ( "del" == $_POST['gglstmp_menu'] ) {
						gglstmp_del_site( $au );/* Deleting site from google webmaster tools */
					}
				} ?>
				<div class="clear"></div>					
				<div class="bws-plugin-reviews">
					<div class="bws-plugin-reviews-rate">
						<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'sitemap' ); ?>: 
						<a href="http://wordpress.org/support/view/plugin-reviews/google-sitemap-plugin" target="_blank" title="Google sitemap reviews"><?php _e( 'Rate the plugin', 'sitemap' ); ?></a>
					</div>
					<div class="bws-plugin-reviews-support">
						<?php _e( 'If there is something wrong about it, please contact us', 'sitemap' ); ?>: 
						<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
					</div>
				</div>
			<?php } elseif ( 'extra' == $_GET['action'] ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">	
						<div class="bws_table_bg"></div>											
						<table class="form-table bws_pro_version">
							<tr valign="top">
								<td colspan="2">
									<?php _e( 'Please choose the necessary post types the links to which are to be added to the sitemap:', 'sitemap' ); ?>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2">
									<label>
										<input disabled="disabled" checked="checked" id="gglstmppr_jstree_url" type="checkbox" name="gglstmppr_jstree_url" value="1" />
										<?php _e( "Show URL for pages", 'sitemap' );?>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2">
									<img src="<?php echo plugins_url( 'images/pro_screen_1.png', __FILE__ ); ?>" alt="<?php _e( "Example of site pages' tree", 'sitemap' ); ?>" title="<?php _e( "Example of site pages' tree", 'sitemap' ); ?>" />
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2">
									<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'sitemap' ); ?>" />
								</td>
							</tr>				
						</table>	
					</div>
					<div class="bws_pro_version_tooltip">
						<div class="bws_info">
							<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'sitemap' ); ?> 
							<a href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Sitemap Pro"><?php _e( 'Learn More', 'sitemap' ); ?></a>				
						</div>
						<a class="bws_button" href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>#purchase" target="_blank" title="Google Sitemap Pro">
							<?php _e( 'Go', 'sitemap' ); ?> <strong>PRO</strong>
						</a>	
						<div class="clear"></div>					
					</div>
				</div>
			<?php } elseif ( 'go_pro' == $_GET['action'] ) { ?>
				<?php if ( isset( $pro_plugin_is_activated ) && true === $pro_plugin_is_activated ) { ?>
					<script type="text/javascript">
						window.setTimeout( function() {
						    window.location.href = 'admin.php?page=google-sitemap-pro.php';
						}, 5000 );
					</script>				
					<p><?php _e( "Congratulations! The PRO version of the plugin is successfully download and activated.", 'sitemap' ); ?></p>
					<p>
						<?php _e( "Please, go to", 'sitemap' ); ?> <a href="admin.php?page=google-sitemap-pro.php"><?php _e( 'the setting page', 'sitemap' ); ?></a> 
						(<?php _e( "You will be redirected automatically in 5 seconds.", 'sitemap' ); ?>)
					</p>
				<?php } else { ?>
					<form method="post" action="admin.php?page=google-sitemap-plugin.php&amp;action=go_pro">
						<p>
							<?php _e( 'You can download and activate', 'sitemap' ); ?> 
							<a href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Sitemap Pro">PRO</a> 
							<?php _e( 'version of this plugin by entering Your license key.', 'sitemap' ); ?><br />
							<span style="color: #888888;font-size: 10px;">
								<?php _e( 'You can find your license key on your personal page Client area, by clicking on the link', 'sitemap' ); ?> 
								<a href="http://bestwebsoft.com/wp-login.php">http://bestwebsoft.com/wp-login.php</a> 
								<?php _e( '(your username is the email you specify when purchasing the product).', 'sitemap' ); ?>
							</span>
						</p>
						<?php if ( isset( $bstwbsftwppdtplgns_options['go_pro']['google-sitemap-pro/google-sitemap-pro.php']['count'] ) &&
							'5' < $bstwbsftwppdtplgns_options['go_pro']['google-sitemap-pro/google-sitemap-pro.php']['count'] &&
							$bstwbsftwppdtplgns_options['go_pro']['google-sitemap-pro/google-sitemap-pro.php']['time'] < ( time() + ( 24 * 60 * 60 ) ) ) { ?>
							<p>
								<input disabled="disabled" type="text" name="bws_license_key" value="<?php echo $bws_license_key; ?>" />
								<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Activate', 'sitemap' ); ?>" />
							</p>
							<p>
								<?php _e( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'sitemap' ); ?>
							</p>
						<?php } else { ?>
							<p>
								<input type="text" name="bws_license_key" value="<?php echo $bws_license_key; ?>" />
								<input type="hidden" name="bws_license_plugin" value="google-sitemap-pro/google-sitemap-pro.php" />
								<input type="hidden" name="bws_license_submit" value="submit" />
								<input type="submit" class="button-primary" value="<?php _e( 'Activate', 'sitemap' ); ?>" />
								<?php wp_nonce_field( plugin_basename(__FILE__), 'bws_license_nonce_name' ); ?>
							</p>
						<?php } ?>
					</form>
				<?php }
			} ?>
		</div>
	<?php }
}

if ( ! function_exists( 'gglstmp_robots_add_sitemap' ) ) {
	function gglstmp_robots_add_sitemap( $output, $public ) {
		if ( '0' == $public ) {
			return $output;
		} else {
			if ( false === strpos( $output, 'Sitemap' ) ) {
				if ( is_multisite() ) {
					$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', home_url() ) ) );
					$output .= "Sitemap: " . home_url( "/" ) . "sitemap_" . $home_url . ".xml";
				} else {
					$output .= "Sitemap: " . home_url( "/" ) . "sitemap.xml";
				}
				return $output;
			}
		}
	}
}

/*============================================ Function for adding style ====================*/
if ( ! function_exists( 'gglstmp_add_plugin_stylesheet' ) ) {
	function gglstmp_add_plugin_stylesheet() {
		if ( isset( $_GET['page'] ) && "google-sitemap-plugin.php" == $_GET['page'] )
			wp_enqueue_script( 'gglstmp_script', plugins_url( 'js/script.js' , __FILE__ ) );
	}
}

/*============================================ Curl function ====================*/
if ( ! function_exists( 'gglstmp_curl_funct' ) ) {
	function gglstmp_curl_funct( $au, $url_send, $type_request, $content ) {
		$headers  =  array(
			"Content-type: application/atom+xml; charset=\"utf-8\"",
			"Authorization: GoogleLogin auth=" . $au
		);
		$chx = curl_init();
		curl_setopt( $chx, CURLOPT_URL, $url_send );
		curl_setopt( $chx, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $chx, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $chx, CURLOPT_RETURNTRANSFER, true );
		if ( "GET" == $type_request ) {
			curl_setopt( $chx, CURLOPT_HTTPGET, true );
		}
		if ( "POST" == $type_request ) {
			curl_setopt( $chx, CURLOPT_POST, true );
			curl_setopt( $chx, CURLOPT_POSTFIELDS, $content );
		}
		if ( "DELETE" == $type_request ) {
			curl_setopt( $chx, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		}
		if ( "PUT" == $type_request ) {
			curl_setopt( $chx, CURLOPT_CUSTOMREQUEST, 'PUT' );
			curl_setopt( $chx, CURLOPT_POSTFIELDS, $content );
		}
		$hasilx = curl_exec( $chx );
		curl_close( $chx );
		return $hasilx;
	}
}

/*============================================ Function to get info about site ====================*/
if ( ! function_exists( 'gglstmp_info_site' ) ) {
	function gglstmp_info_site( $au ) {
		global $url_home, $url, $url_send, $url_send_sitemap;
		$hasilx = gglstmp_curl_funct( $au, $url_send . $url, "GET", false );
		/*========================= Getting info about site in google webmaster tools ====================*/
		echo "<h3>" . __( "I want to get info about this site in Google Webmaster Tools", 'sitemap' ) . "</h3>";
		if ( $hasilx == "Site not found" ) {
			echo __( "This site is not added to the Google Webmaster Tools account", 'sitemap' ) . "<br />";
		} else {
			$hasils = gglstmp_curl_funct( $au, $url_send . $url, "GET", false );
			echo "<pre>";
			$p = xml_parser_create();
			xml_parse_into_struct( $p, $hasils, $vals, $index );
			xml_parser_free( $p );
			foreach ( $vals as $val ) {
				if ( "WT:VERIFIED" == $val["tag"] )
					$ver = $val["value"];
			}
			$hasils = gglstmp_curl_funct( $au, $url_send_sitemap . $url . "/sitemaps/", "GET", false );
			echo "</pre>";
			$p = xml_parser_create();
			xml_parse_into_struct( $p, $hasils, $vals, $index );
			xml_parser_free( $p );
			foreach ( $vals as $val ) {
			if ( "WT:SITEMAP-STATUS" == $val["tag"] )
				$sit = $val["value"];
			}
			echo __( "Site URL:", 'sitemap' ) . ' ' . $url_home . "<br />";
			echo __( "Site verification:", 'sitemap' ) . ' ';
			if ( "true" == $ver )
				echo __( "verified", 'sitemap' ) . "<br />";
			else
				echo __( "not verified", 'sitemap' ) . "<br />";
			echo __( "Sitemap file:", 'sitemap' ) . ' ';
			if ( isset( $sit ) )
				echo __( "added", 'sitemap' ) . "<br />";
			else
				echo __( "not added", 'sitemap' ) . "<br />";
		}
		echo "<br />";
	}
}

/*============================================ Deleting site from google webmaster tools ====================*/
if ( ! function_exists( 'gglstmp_del_site' ) ) {
	function gglstmp_del_site( $au ) {
		global $url, $url_send;
		$hasil3 = gglstmp_curl_funct( $au, $url_send . $url, "DELETE", false );
	}
}

/*============================================ Adding site to the google webmaster tools ====================*/
if ( ! function_exists( 'gglstmp_add_site' ) ) {
	function gglstmp_add_site( $au ) {
		global $url_home, $url, $url_send;
		$content = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">"
		 ."<atom:content src=\"" . $url_home . "\" />"
		 ."</atom:entry>\n";
		$hasil1 = gglstmp_curl_funct( $au, $url_send, "POST", $content );
		preg_match( '/(google)[a-z0-9]*\.html/', $hasil1, $matches );
		/*===================== Creating html file for verifying site ownership ====================*/
		if ( ! empty($matches) )
			$m1="../" . $matches[0];
		if ( ! ( file_exists ( $m1 ) ) ) {
		$fp = fopen ("../" . $matches[0], "w+" );
		fwrite( $fp, "google-site-verification: " . $matches[0] );
		fclose ( $fp );
		}
		/*============================= Verifying site ownership ====================*/
		$content  = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">"
		."<atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#site-info'/>"
		."<wt:verification-method type=\"htmlpage\" in-use=\"true\"/>"
		."</atom:entry>";
		$hasil2 = gglstmp_curl_funct( $au, $url_send . $url, "PUT", $content );
	}
}

/*============================================ Adding sitemap file ====================*/
if ( ! function_exists( 'gglstmp_add_sitemap' ) ) {
	function gglstmp_add_sitemap( $au ) {
		global $url_home, $url, $url_send_sitemap;
		if ( is_multisite() ) {
			$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', home_url() ) );
			$content  = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">"
			."<atom:id>" . $url_home . "sitemap_" . $home_url . ".xml</atom:id>"
			."<atom:category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/webmasters/tools/2007#sitemap-regular\"/>"
			."<wt:sitemap-type>WEB</wt:sitemap-type>"
			."</atom:entry>";
		} else {
			$content  = "<atom:entry xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:wt=\"http://schemas.google.com/webmasters/tools/2007\">"
			."<atom:id>" . $url_home . "sitemap.xml</atom:id>"
			."<atom:category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/webmasters/tools/2007#sitemap-regular\"/>"
			."<wt:sitemap-type>WEB</wt:sitemap-type>"
			."</atom:entry>";
		}
		$hasil1 = gglstmp_curl_funct( $au, $url_send_sitemap . $url . "/sitemaps/", "POST", $content );
	}
}

/*============================================ Updating the sitemap after a post or page is trashed or published ====================*/
if ( ! function_exists( 'gglstmp_update_sitemap' ) ) {
	function gglstmp_update_sitemap( $post_id ) {
		if ( ! wp_is_post_revision( $post_id ) ) {
			if ( 'publish' == get_post_status( $post_id ) || 'trash' == get_post_status( $post_id ) || 'future' == get_post_status( $post_id ) ) {
				gglstmp_register_settings();
				gglstmp_sitemapcreate();
			}
		}
	}
}

/*============================================ Adding setting link in activate plugin page ====================*/
if ( ! function_exists( 'gglstmp_action_links' ) ) {
	function gglstmp_action_links( $links, $file ) {
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin )
			$this_plugin = plugin_basename( __FILE__ );
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=google-sitemap-plugin.php">' . __( 'Settings', 'sitemap' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}

if ( ! function_exists( 'gglstmp_links' ) ) {
	function gglstmp_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=google-sitemap-plugin.php">' . __( 'Settings','sitemap' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/google-sitemap-plugin/faq/" target="_blank">' . __( 'FAQ','sitemap' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support','sitemap' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'gglstmp_plugin_banner' ) ) {
	function gglstmp_plugin_banner() {
		global $hook_suffix;	
		if ( $hook_suffix == 'plugins.php' ) {  
			$banner_array = array(
				array( 'sndr_hide_banner_on_plugin_page', 'sender/sender.php', '0.5' ),
				array( 'srrl_hide_banner_on_plugin_page', 'user-role/user-role.php', '1.4' ),	
				array( 'pdtr_hide_banner_on_plugin_page', 'updater/updater.php', '1.12' ),
				array( 'cntctfrmtdb_hide_banner_on_plugin_page', 'contact-form-to-db/contact_form_to_db.php', '1.2' ),
				array( 'cntctfrmmlt_hide_banner_on_plugin_page', 'contact-form-multi/contact-form-multi.php', '1.0.7' ),	
				array( 'gglmps_hide_banner_on_plugin_page', 'bws-google-maps/bws-google-maps.php', '1.2' ),		
				array( 'fcbkbttn_hide_banner_on_plugin_page', 'facebook-button-plugin/facebook-button-plugin.php', '2.29' ),
				array( 'twttr_hide_banner_on_plugin_page', 'twitter-plugin/twitter.php', '2.34' ),
				array( 'pdfprnt_hide_banner_on_plugin_page', 'pdf-print/pdf-print.php', '1.7.1' ),
				array( 'gglplsn_hide_banner_on_plugin_page', 'google-one/google-plus-one.php', '1.1.4' ),
				array( 'gglstmp_hide_banner_on_plugin_page', 'google-sitemap-plugin/google-sitemap-plugin.php', '2.8.4' ),
				array( 'cntctfrmpr_for_ctfrmtdb_hide_banner_on_plugin_page', 'contact-form-pro/contact_form_pro.php', '1.14' ),
				array( 'cntctfrm_for_ctfrmtdb_hide_banner_on_plugin_page', 'contact-form-plugin/contact_form.php', '3.62' ),
				array( 'cntctfrm_hide_banner_on_plugin_page', 'contact-form-plugin/contact_form.php', '3.47' ),	
				array( 'cptch_hide_banner_on_plugin_page', 'captcha/captcha.php', '3.8.4' ),
				array( 'gllr_hide_banner_on_plugin_page', 'gallery-plugin/gallery-plugin.php', '3.9.1' )				
			);
			global $gglstmp_plugin_info;

			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$active_plugins = get_option( 'active_plugins' );			
			$all_plugins = get_plugins();
			$this_banner = 'gglstmp_hide_banner_on_plugin_page';
			foreach ( $banner_array as $key => $value ) {
				if ( $this_banner == $value[0] ) {
					global $wp_version, $bstwbsftwppdtplgns_cookie_add;
					if ( ! isset( $bstwbsftwppdtplgns_cookie_add ) ) {
						echo '<script type="text/javascript" src="' . plugins_url( 'js/c_o_o_k_i_e.js', __FILE__ ) . '"></script>';
						$bstwbsftwppdtplgns_cookie_add = true;
					} ?>
					<script type="text/javascript">		
							(function($) {
								$(document).ready( function() {		
									var hide_message = $.cookie( "gglstmp_hide_banner_on_plugin_page" );
									if ( hide_message == "true") {
										$( ".gglstmp_message" ).css( "display", "none" );
									} else {
										$( ".gglstmp_message" ).css( "display", "block" );
									};
									$( ".gglstmp_close_icon" ).click( function() {
										$( ".gglstmp_message" ).css( "display", "none" );
										$.cookie( "gglstmp_hide_banner_on_plugin_page", "true", { expires: 32 } );
									});	
								});
							})(jQuery);				
						</script>	
					<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">				                      
						<div class="gglstmp_message bws_banner_on_plugin_page" style="display: none;">
							<img class="gglstmp_close_icon close_icon" title="" src="<?php echo plugins_url( 'images/close_banner.png', __FILE__ ); ?>" alt=""/>
							<div class="button_div">
								<a class="button" target="_blank" href="http://bestwebsoft.com/plugin/google-sitemap-pro/?k=8fbb5d23fd00bdcb213d6c0985d16ec5&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'sitemap' ); ?></a>				
							</div>
							<div class="text">
								<?php _e( "It's time to upgrade your <strong>Google Sitemap plugin</strong> to <strong>PRO</strong> version", 'sitemap' ); ?>!<br />
								<span><?php _e( 'Extend standard plugin functionality with new great options', 'sitemap' ); ?>.</span>
							</div> 		
							<div class="icon">			
								<img title="" src="<?php echo plugins_url( 'images/banner.png', __FILE__ ); ?>" alt=""/>
							</div>	
						</div>  
					</div>
					<?php break;
				}
				if ( isset( $all_plugins[ $value[1] ] ) && $all_plugins[ $value[1] ]["Version"] >= $value[2] && ( 0 < count( preg_grep( '/' . str_replace( '/', '\/', $value[1] ) . '/', $active_plugins ) ) || is_plugin_active_for_network( $value[1] ) ) && ! isset( $_COOKIE[ $value[0] ] ) ) {
					break;
				}
			}    
		}
	}
}

/*============================================ Function for delete of the plugin settings on register_activation_hook ====================*/
if ( ! function_exists( 'gglstmp_delete_settings' ) ) {
	function gglstmp_delete_settings() {
		delete_site_option( 'gglstmp_settings' );
		delete_option( 'gglstmp_settings' );
		delete_site_option( 'gglstmp_robots' );
		delete_option( 'gglstmp_robots' );
	}
}

add_action( 'admin_menu', 'gglstmp_admin_menu' );

add_action( 'init', 'gglstmp_init' );
add_action( 'admin_init', 'gglstmp_admin_init' );

add_action( 'admin_enqueue_scripts', 'gglstmp_add_plugin_stylesheet' );

add_action( 'save_post', 'gglstmp_update_sitemap' );
add_action( 'trashed_post ', 'gglstmp_update_sitemap' );

if ( 1 == get_option( 'gglstmp_robots' ) )
	add_filter( 'robots_txt', 'gglstmp_robots_add_sitemap', 10, 2 );

add_filter( 'plugin_action_links', 'gglstmp_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'gglstmp_links', 10, 2 );

add_action( 'admin_notices', 'gglstmp_plugin_banner' );

register_uninstall_hook( __FILE__, 'gglstmp_delete_settings'); /* uninstall plugin */
?>