<?php
/*
Plugin Name: Google Sitemap by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/google-sitemap/
Description: Generate and add XML sitemap to WordPress website. Help search engines index your blog.
Author: BestWebSoft
Text Domain: google-sitemap-plugin
Domain Path: /languages
Version: 3.0.8
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/*
	© Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

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

require_once( dirname( __FILE__ ) . '/includes/deprecated.php' );

/*============================================ Function for adding menu and submenu ====================*/
if ( ! function_exists( 'gglstmp_admin_menu' ) ) {
	function gglstmp_admin_menu() {
		global $gglstmp_options, $wp_version, $submenu, $gglstmp_plugin_info;

		$settings = add_menu_page( __( 'Google Sitemap Settings', 'google-sitemap-plugin' ), 'Google Sitemap', 'manage_options', 'google-sitemap-plugin.php', 'gglstmp_settings_page', 'none' );
		add_submenu_page( 'google-sitemap-plugin.php', __( 'Google Sitemap Settings', 'google-sitemap-plugin' ), __( 'Settings', 'google-sitemap-plugin' ), 'manage_options', 'google-sitemap-plugin.php', 'gglstmp_settings_page' );
		
		if ( ! bws_hide_premium_options_check( $gglstmp_options ) )
			add_submenu_page( 'google-sitemap-plugin.php', __( 'Custom Links', 'google-sitemap-plugin' ), __( 'Custom Links', 'google-sitemap-plugin' ), 'manage_options', 'google-sitemap-custom-links.php', 'gglstmp_settings_page' );

		add_submenu_page( 'google-sitemap-plugin.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'gglstmp-bws-panel', 'bws_add_menu_render' );

		if ( isset( $submenu['google-sitemap-plugin.php'] ) )
			$submenu['google-sitemap-plugin.php'][] = array( 
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'google-sitemap-plugin' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/google-sitemap/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=' . $gglstmp_plugin_info["Version"] . '&wp_v=' . $wp_version );


		add_action( "load-{$settings}", 'gglstmp_add_tabs' );

		global $gglstmp_url_home, $gglstmp_url;
		$gglstmp_url_home			=	site_url( '/' );
		$gglstmp_url				=	urlencode( $gglstmp_url_home );
	}
}

if ( ! function_exists( 'gglstmp_plugins_loaded' ) ) {
	function gglstmp_plugins_loaded() {
		/* Internationalization */
		load_plugin_textdomain( 'google-sitemap-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Function adds language files */
if ( ! function_exists( 'gglstmp_init' ) ) {
	function gglstmp_init() {
		global $gglstmp_plugin_info;

		if ( empty( $gglstmp_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$gglstmp_plugin_info = get_plugin_data( __FILE__ );
		}

		/* add general functions */
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		/* check compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $gglstmp_plugin_info, '3.8' );

		/* Get options from the database */
		gglstmp_register_settings();
	}
}

if ( ! function_exists( 'gglstmp_admin_init' ) ) {
	function gglstmp_admin_init() {
		/* Add variable for bws_menu */
		global $bws_plugin_info, $gglstmp_plugin_info;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '83', 'version' => $gglstmp_plugin_info["Version"] );
		}

		if ( isset( $_GET['page'] ) && "google-sitemap-plugin.php" == $_GET['page'] ) {
			if ( ! session_id() )
				session_start();
		}
	}
}

/*============================================ Function for register of the plugin settings on init core ====================*/
if ( ! function_exists( 'gglstmp_register_settings' ) ) {
	function gglstmp_register_settings() {
		global $gglstmp_options, $gglstmp_plugin_info;

		/** 
		* Renaming old version options
		* @deprecated since 3.0.8
		* @todo remove after 28.10.2017
		*/
		gglstmp_check_old_options();

		if ( ! get_option( 'gglstmp_options' ) ) {
			$options_default = gglstmp_get_options_default();
			add_option( 'gglstmp_options', $options_default );
		}

		$gglstmp_options = get_option( 'gglstmp_options' );

		if ( ! isset( $gglstmp_options['plugin_option_version'] ) || $gglstmp_options['plugin_option_version'] != $gglstmp_plugin_info['Version'] ) {
			$options_default = gglstmp_get_options_default();
			$gglstmp_options = array_merge( $options_default, $gglstmp_options );
			$gglstmp_options['plugin_option_version'] = $gglstmp_plugin_info["Version"];
			/* show pro features */
			$gglstmp_options['hide_premium_options'] = array();
			update_option( 'gglstmp_options', $gglstmp_options );
		}
	}
}

if ( ! function_exists( 'gglstmp_get_options_default' ) ) {
	function gglstmp_get_options_default() {
		global $gglstmp_plugin_info;

		$options_default = array(
			'plugin_option_version' 	=> $gglstmp_plugin_info['Version'],
			'first_install'				=> strtotime( "now" ),
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'	=> 1,
			'post_type'					=> array( 'page', 'post' ),
			'taxonomy'					=> array(),
			'sitemap'					=> array(),			
		);
		return $options_default;
	}
}

/*============================================ Function for creating sitemap file ====================*/
if ( ! function_exists( 'gglstmp_sitemapcreate' ) ) {
	function gglstmp_sitemapcreate() {
		global $wpdb;

		$gglstmp_options = get_option( 'gglstmp_options' );

		$taxonomies = array();
		foreach ( $gglstmp_options['taxonomy'] as $val ) {
			$taxonomies[] = $val;
		}

		$xml                  = new DomDocument( '1.0', 'utf-8' );
		$home_url             = site_url( '/' );
		$xml_stylesheet_path  = ( defined( 'WP_CONTENT_DIR' ) )? $home_url . basename( WP_CONTENT_DIR ) : $home_url . 'wp-content';
		$xml_stylesheet_path .= ( defined( 'WP_PLUGIN_DIR' ) ) ? '/' . basename( WP_PLUGIN_DIR ) . '/google-sitemap-plugin/sitemap.xsl' : '/plugins/google-sitemap-plugin/sitemap.xsl';

		$xslt = $xml->createProcessingInstruction( 'xml-stylesheet', "type=\"text/xsl\" href=\"$xml_stylesheet_path\"" );
		$xml->appendChild( $xslt );
		$gglstmp_urlset = $xml->appendChild( $xml->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9','urlset' ) );

		/* add home page */
		$url = $gglstmp_urlset->appendChild( $xml->createElement( 'url' ) );
		$loc = $url->appendChild( $xml->createElement( 'loc' ) );
		$loc->appendChild( $xml->createTextNode( home_url( '/' ) ) );
		$lastmod = $url->appendChild( $xml->createElement( 'lastmod' ) );
		$lastmod->appendChild( $xml->createTextNode( date( 'Y-m-d\TH:i:sP', time() ) ) );
		$changefreq = $url->appendChild( $xml->createElement( 'changefreq' ) );
		$changefreq->appendChild( $xml->createTextNode( 'monthly' ) );
		$priority = $url->appendChild( $xml->createElement( 'priority' ) );
		$priority->appendChild( $xml->createTextNode( 1.0 ) );
		/* getting an array of the excluded post ids of 'forum', 'topic' and 'reply' post types (hidden and private bbPress forum posts) */
		$excluded_posts_array = $wpdb->get_col( "SELECT `ID` FROM $wpdb->posts WHERE `post_status` IN ('hidden', 'private') AND `post_type` IN ('forum', 'topic', 'reply')" );
		if ( ! empty( $excluded_posts_array ) ) {
			$excluded_posts_string = implode( ', ', $excluded_posts_array );
			while ( true ) {
				$hidden_child_array = $wpdb->get_col( "SELECT `ID` FROM $wpdb->posts WHERE `post_status` NOT IN ('hidden','private') AND `post_type` IN ('forum', 'topic', 'reply') AND `post_parent` IN ($excluded_posts_string)" );
				if ( ! empty( $hidden_child_array ) ) {
					$excluded_posts_array = array_merge( $excluded_posts_array, $hidden_child_array );
					$excluded_posts_string = implode( ', ', $hidden_child_array );
				} else {
					break 1;
				}
			}
		}

		if ( ! empty( $gglstmp_options['post_type'] ) ) {
			$args = array(
				'posts_per_page'	=> -1,
				'exclude'			=> $excluded_posts_array,
				'post_type'			=> $gglstmp_options['post_type'],
				'post_status'		=> 'publish'
			);			
			$loc = get_posts( $args );
			if ( ! empty( $loc ) ) {
				foreach ( $loc as $val ) {
					$gglstmp_url = $gglstmp_urlset->appendChild( $xml->createElement( 'url' ) );
					$loc = $gglstmp_url->appendChild( $xml->createElement( 'loc' ) );
					$permalink = get_permalink( $val->ID );
					$loc->appendChild( $xml->createTextNode( $permalink ) );
					$lastmod = $gglstmp_url->appendChild( $xml->createElement( 'lastmod' ) );
					$now = $val->post_modified;
					$date = date( 'Y-m-d\TH:i:sP', strtotime( $now ) );
					$lastmod->appendChild( $xml->createTextNode( $date ) );
					$changefreq = $gglstmp_url->appendChild( $xml->createElement( 'changefreq' ) );
					$changefreq->appendChild( $xml->createTextNode( 'monthly' ) );
					$priority = $gglstmp_url->appendChild( $xml->createElement( 'priority' ) );
					$priority->appendChild( $xml->createTextNode( 1.0 ) );
				}
			}
		}
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $value ) {

				$terms = get_terms( $value, 'hide_empty=1' );

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term_value ) {
						$gglstmp_url = $gglstmp_urlset->appendChild( $xml->createElement( 'url' ) );
						$loc = $gglstmp_url->appendChild( $xml->createElement( 'loc' ) );
						$permalink = get_term_link( (int)$term_value->term_id, $value );
						$loc->appendChild( $xml->createTextNode( $permalink ) );
						$lastmod = $gglstmp_url->appendChild( $xml->createElement( 'lastmod' ) );

						$now = $wpdb->get_var( "SELECT `post_modified` FROM $wpdb->posts, $wpdb->term_relationships WHERE `post_status` = 'publish' AND `term_taxonomy_id` = " . $term_value->term_taxonomy_id . " AND $wpdb->posts.ID= $wpdb->term_relationships.object_id ORDER BY `post_modified` DESC" );
						$date = date( 'Y-m-d\TH:i:sP', strtotime( $now ) );
						$lastmod->appendChild( $xml->createTextNode( $date ) );
						$changefreq = $gglstmp_url -> appendChild( $xml->createElement( 'changefreq' ) );
						$changefreq->appendChild( $xml->createTextNode( 'monthly' ) );
						$priority = $gglstmp_url->appendChild( $xml->createElement( 'priority' ) );
						$priority->appendChild( $xml->createTextNode( 1.0 ) );
					}
				}
			}
		}

		$xml->formatOutput = true;

		if ( ! is_writable( ABSPATH ) )
			@chmod( ABSPATH, 0755 );

		if ( is_multisite() ) {
			$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ) );
			$xml->save( ABSPATH . 'sitemap_' . $home_url . '.xml' );
		} else {
			$xml->save( ABSPATH . 'sitemap.xml' );
		}

		if ( is_multisite() ) {
			$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ) );
			$xml_file = 'sitemap_' . $home_url . '.xml';
		} else {
			$xml_file = 'sitemap.xml';
		}

		$xml_path = ABSPATH . $xml_file;
		$xml_url  = site_url( '/' ) . $xml_file;
		if ( file_exists( $xml_path ) ) {
			$gglstmp_options['sitemap'] = array(
				'file'		=> $xml_file,
				'path'		=> $xml_path,
				'loc'		=> $xml_url,
				'lastmod'	=> date( 'Y-m-d\TH:i:sP', filemtime( $xml_path ) )
			);
			update_option( 'gglstmp_options', $gglstmp_options );
		}
	}
}

if ( ! function_exists( 'gglstmp_check_sitemap' ) ) {
	function gglstmp_check_sitemap( $gglstmp_url ) {
		$result = wp_remote_get( esc_url_raw( $gglstmp_url ) );
		return $result['response'];
	}
}

if ( ! function_exists ( 'gglstmp_client' ) ) {
	function gglstmp_client() {
		global $gglstmp_plugin_info;
		require_once( dirname( __FILE__ ) . '/google_api/autoload.php' );
		$client = new Google_Client();
		$client->setClientId( '37374817621-7ujpfn4ai4q98q4nb0gaaq5ga7j7u0ka.apps.googleusercontent.com' );
		$client->setClientSecret( 'GMefWPZdRIWk3J7USu6_Kf6_' );
		$client->setScopes( array( 'https://www.googleapis.com/auth/webmasters', 'https://www.googleapis.com/auth/siteverification' ) );
		$client->setRedirectUri( 'urn:ietf:wg:oauth:2.0:oob' );
		$client->setAccessType( 'offline' );
		$client->setDeveloperKey( 'AIzaSyBRFiI5TGKKeteDoDa8T8GkJGxRFa1IMxE' );
		$client->setApplicationName( $gglstmp_plugin_info['Name'] );
		return $client;
	}
}

if ( ! function_exists( 'gglstmp_plugin_status' ) ) {
	function gglstmp_plugin_status( $plugins, $all_plugins, $is_network ) {
		$result = array(
			'status'      => '',
			'plugin'      => '',
			'plugin_info' => array(),
		);
		foreach ( (array)$plugins as $plugin ) {
			if ( array_key_exists( $plugin, $all_plugins ) ) {
				if (
					( $is_network && is_plugin_active_for_network( $plugin ) ) ||
					( ! $is_network && is_plugin_active( $plugin ) )
				) {
					$result['status']      = 'actived';
					$result['plugin']      = $plugin;
					$result['plugin_info'] = $all_plugins[$plugin];
					break;
				} else {
					$result['status']      = 'deactivated';
					$result['plugin']      = $plugin;
					$result['plugin_info'] = $all_plugins[$plugin];
				}

			}
		}
		if ( empty( $result['status'] ) )
			$result['status'] = 'not_installed';
		return $result;
	}
}

/*============================================ Function for creating setting page ====================*/
if ( ! function_exists ( 'gglstmp_settings_page' ) ) {
	function gglstmp_settings_page() {
		global $gglstmp_plugin_info, $gglstmp_list_table; 
		require_once( dirname( __FILE__ ) . '/includes/pro_banners.php' ); ?>
		<div class="wrap">			
			<?php if ( 'google-sitemap-plugin.php' == $_GET['page'] ) { /* Showing settings tab */
				require_once( dirname( __FILE__ ) . '/includes/class-gglstmp-settings.php' );
				$page = new Gglstmp_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
				<h1>Google Sitemap <?php _e( 'Settings', 'google-sitemap-plugin' ); ?></h1>
				<noscript><div class="error below-h2"><p><strong><?php _e( "Please enable JavaScript in Your browser.", 'google-sitemap-plugin' ); ?></strong></p></div></noscript>
				<?php $page->display_content();
			} else { ?>
				<h1><?php _e( 'Custom Links', 'google-sitemap-plugin' ); ?></h1>
				<?php gglstmp_pro_block( "gglstmp_custom_links_block", false );
				bws_plugin_reviews_block( $gglstmp_plugin_info['Name'], 'google-sitemap-plugin' );
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
					$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ) );
					$output .= "Sitemap: " . site_url( "/" ) . "sitemap_" . $home_url . ".xml";
				} else {
					$output .= "Sitemap: " . site_url( "/" ) . "sitemap.xml";
				}
				return $output;
			}
		}
	}
}

/*============================================ Function for adding style ====================*/
if ( ! function_exists( 'gglstmp_add_plugin_stylesheet' ) ) {
	function gglstmp_add_plugin_stylesheet() {
		wp_enqueue_style( 'gglstmp_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		if ( isset( $_GET['page'] ) && "google-sitemap-plugin.php" == $_GET['page'] ) {	
			bws_enqueue_settings_scripts();
		}
	}
}

/*============================================ Function to get info about site ====================*/
if ( ! function_exists( 'gglstmp_info_site' ) ) {
	function gglstmp_info_site( $webmasters, $site_verification ) {
		global $gglstmp_options;

		$instruction_url = 'https://docs.google.com/document/d/1VOJx_OaasVskCqi9fsAbUmxfsckoagPU5Py97yjha9w/';
		$home_url = home_url( '/' );
		$wmt_sites_array = $wmt_sitemaps_arr = array();

		$return = '<table id="gglstmp_manage_table"><tr><th>' . __( 'Website', 'google-sitemap-plugin' ) . '</th>
					<td><a href="' . $home_url . '" target="_blank">' . $home_url . '</a></td></tr>';

		try {
			$wmt_sites = $webmasters->sites->listSites()->getSiteEntry();

			foreach ( $wmt_sites as $site ) {
				$wmt_sites_array[ $site->siteUrl ] = $site->permissionLevel;
			}

			if ( ! array_key_exists( $home_url, $wmt_sites_array ) ) {
				$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>
					<td>' . __( 'Not added', 'google-sitemap-plugin' ) . '</td></tr>';
			} else {

				$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>
					<td class="gglstmp_success">' . __( 'Added', 'google-sitemap-plugin' ) . '</td></tr>';

				$return .= '<tr><th>' . __( 'Verification Status', 'google-sitemap-plugin' ) . '</th>';
				if ( $wmt_sites_array[ $home_url ] == 'siteOwner' )
					$return .= '<td>' . __( 'Verified', 'google-sitemap-plugin' ) . '</td></tr>';
				else
					$return .= '<td>' . __( 'Not verified', 'google-sitemap-plugin' ) . '</td></tr>';

				$webmasters_sitemaps = $webmasters->sitemaps->listSitemaps( $home_url )->getSitemap();

				foreach ( $webmasters_sitemaps as $sitemap ) {
					$wmt_sitemaps_arr[ $sitemap->path ] = ( $sitemap->errors > 0 || $sitemap->warnings > 0 ) ? true : false;
				}

				$return .= '<tr><th>' . __( 'Sitemap Status', 'google-sitemap-plugin' ) . '</th>';

				if ( isset( $gglstmp_options['sitemap']['loc'] ) ) {
					$url_sitemap = $gglstmp_options['sitemap']['loc'];
					if ( ! array_key_exists( $url_sitemap, $wmt_sitemaps_arr ) ) {
						$return .= '<td>' . __( 'Not added', 'google-sitemap-plugin' ) . '</td></tr>';
					} else {						
						if ( ! $wmt_sitemaps_arr[ $url_sitemap ] ) {
							$return .= '<td class="gglstmp_success">' . __( 'Added', 'google-sitemap-plugin' ) . '</td></tr>';
						} else {
							$return .= '<td>' . __( 'Added with errors.', 'google-sitemap-plugin' ) . '<a href="https://www.google.com/webmasters/tools/sitemap-details?hl=en&siteUrl=' . urlencode( $home_url ) . '&sitemapUrl=' . urlencode( $url_sitemap ) . '#ISSUE_FILTER=-1">' . __( 'View errors in Google Webmaster Tools', 'google-sitemap-plugin' ) . '</a></td></tr>';
						}
					}
					$return .= '<tr><th>' . __( 'Sitemap URL', 'google-sitemap-plugin' ) . '</th>
						<td><a href="' . $url_sitemap . '" target="_blank">' . $url_sitemap . '</a></td></tr>';
				} else {
					$return .= '<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . __( 'Please check the sitemap file manually.', 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
				}
			}
		} catch ( Google_Service_Exception $e ) {
			$error = $e->getErrors();
			$sv_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
		} catch ( Google_IO_Exception $e ) {
			$sv_error = $e->getMessage();
		} catch ( Google_Auth_Exception $e ) {
			$sv_error = true;
		} catch ( Exception $e ) {
			$sv_error = $e->getMessage();
		}

		if ( ! empty( $sv_error ) ) {
			if ( $sv_error !== true ) {
				$return .= '<tr><th></th><td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $sv_error . '</td></tr>';
			}
			$return .= '<tr><th></th><td>' . __( "Manual verification required.", 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
		}
		$return .= '</table>';
		return $return;
	}
}

/*============================================ Deleting site from google webmaster tools ====================*/
if ( ! function_exists( 'gglstmp_del_site' ) ) {
	function gglstmp_del_site( $webmasters, $site_verification ) {
		global $gglstmp_options;
		
		$home_url = home_url('/');
		$return = '<table id="gglstmp_manage_table"><tr><th>' . __( 'Website', 'google-sitemap-plugin' ) . '</th>
					<td><a href="' . $home_url . '" target="_blank">' . $home_url . '</a></td></tr>';

		try {
			$webmasters_sitemaps = $webmasters->sitemaps->listSitemaps( $home_url )->getSitemap();
			foreach ( $webmasters_sitemaps as $sitemap ) {
				try {
					$webmasters->sitemaps->delete( $home_url, $sitemap->path );
				} catch ( Google_Service_Exception $e ) {
				} catch ( Google_IO_Exception $e ) {
				} catch ( Google_Auth_Exception $e ) {
				} catch ( Exception $e ) {}
			}

			$webmasters->sites->delete( $home_url );

			$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>
					<td>' . __( 'Deleted', 'google-sitemap-plugin' ) . '</td></tr>';
			unset( $gglstmp_options['site_vererification_code'] );
			update_option( 'gglstmp_options', $gglstmp_options );

		} catch ( Google_Service_Exception $e ) {
			$error = $e->getErrors();
			$sv_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
		} catch ( Google_IO_Exception $e ) {
			$sv_error = $e->getMessage();
		} catch ( Google_Auth_Exception $e ) {
			$sv_error = true;
		} catch ( Exception $e ) {
			$sv_error = $e->getMessage();
		}
		if ( ! empty( $sv_error ) ) {
			$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>
				<td>' . __( 'Not added', 'google-sitemap-plugin' ) . '</td></tr>';
			if ( $sv_error !== true )
				$return .= '<tr><th></th><td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $sv_error . '</td></tr>';
		}
		$return .= '</table>';
		return $return;
	}
}

/*============================================ Adding and verifing site, adding sitemap file to Google Webmaster tools ====================*/
if ( ! function_exists( 'gglstmp_add_site' ) ) {
	function gglstmp_add_site( $webmasters, $site_verification ) {
		global $gglstmp_options;

		$instruction_url = 'https://docs.google.com/document/d/1VOJx_OaasVskCqi9fsAbUmxfsckoagPU5Py97yjha9w/';
		$home_url = home_url( '/' );

		$return = '<table id="gglstmp_manage_table"><tr><th>' . __( 'Website', 'google-sitemap-plugin' ) . '</th>
					<td><a href="' . $home_url . '" target="_blank">' . $home_url . '</a></td></tr>';
		try {
			$webmasters->sites->add( $home_url );
			$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>
					<td class="gglstmp_success">' . __( 'Added', 'google-sitemap-plugin' ) . '</td></tr>';
		} catch ( Google_Service_Exception $e ) {
			$error = $e->getErrors();
			$wmt_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
		} catch ( Google_IO_Exception $e ) {
			$wmt_error = $e->getMessage();
		} catch ( Google_Auth_Exception $e ) {
			$wmt_error = true;
		} catch ( Exception $e ) {
			$wmt_error = $e->getMessage();
		}

		if ( ! empty( $wmt_error ) ) {
			$return .= '<tr><th>' . __( 'Status', 'google-sitemap-plugin' ) . '</th>';
			if ( $wmt_error !== true )
				$return .= '<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $wmt_error . '</td></tr>
				<tr><th></th>';
			$return .= '<td>' . __( "Manual verification required.", 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
		} else {

			try {
				$gglstmp_sv_get_token_request_site = new Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequestSite;
				$gglstmp_sv_get_token_request_site->setIdentifier( $home_url );
				$gglstmp_sv_get_token_request_site->setType( 'SITE' );
				$gglstmp_sv_get_token_request = new Google_Service_SiteVerification_SiteVerificationWebResourceGettokenRequest;
				$gglstmp_sv_get_token_request->setSite( $gglstmp_sv_get_token_request_site );
				$gglstmp_sv_get_token_request->setVerificationMethod( 'META' );
				$getToken = $site_verification->webResource->getToken( $gglstmp_sv_get_token_request );
				$gglstmp_options['site_vererification_code'] = htmlspecialchars( $getToken['token'] );
				if ( preg_match( '|^&lt;meta name=&quot;google-site-verification&quot; content=&quot;(.*)&quot; /&gt;$|', $gglstmp_options['site_vererification_code'] ) ) {
					update_option( 'gglstmp_options', $gglstmp_options );

					$return .= '<tr><th>' . __( 'Verification Code', 'google-sitemap-plugin' ) . '</th>
						<td>' . __( 'Received and added to the site', 'google-sitemap-plugin' ) . '</td></tr>';
				} else {
					$return .= '<tr><th>' . __( 'Verification Code', 'google-sitemap-plugin' ) . '</th>
						<td>' . __( 'Received, but has not been added to the site', 'google-sitemap-plugin' ) . '</td></tr>';
				}
			} catch ( Google_Service_Exception $e ) {
				$error = $e->getErrors();
				$sv_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
			} catch ( Google_IO_Exception $e ) {
				$sv_error = $e->getMessage();
			} catch ( Google_Auth_Exception $e ) {
				$sv_error = true;
			} catch ( Exception $e ) {
				$sv_error = $e->getMessage();
			}

			if ( ! empty( $sv_error ) ) {				
				if ( $sv_error !== true ) {
					$return .= '<tr><th>' . __( 'Verification Code', 'google-sitemap-plugin' ) . '</th>
						<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $sv_error . '</td></tr>';
				}

				$return .= '<tr><th>' . __( 'Verification Status', 'google-sitemap-plugin' ) . '</th>
					<td>' . ___( "The site couldn't be verified. Manual verification required.", 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
			} else {

				try {
					$gglstmp_wmt_resource_site = new Google_Service_SiteVerification_SiteVerificationWebResourceResourceSite;
					$gglstmp_wmt_resource_site->setIdentifier( $home_url );
					$gglstmp_wmt_resource_site->setType( 'SITE' );
					$gglstmp_wmt_resource = new Google_Service_SiteVerification_SiteVerificationWebResourceResource;
					$gglstmp_wmt_resource->setSite( $gglstmp_wmt_resource_site );
					$site_verification->webResource->insert( 'META', $gglstmp_wmt_resource );

					$return .= '<tr><th>' . __( 'Verification Status', 'google-sitemap-plugin' ) . '</th>
						<td class="gglstmp_success">' . ___( 'Verified', 'google-sitemap-plugin' ) . '</td></tr>';
				} catch ( Google_Service_Exception $e ) {
					$error = $e->getErrors();
					$sv_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
				} catch ( Google_IO_Exception $e ) {
					$sv_error = $e->getMessage();
				} catch ( Google_Auth_Exception $e ) {
					$sv_error = true;
				} catch ( Exception $e ) {
					$sv_error = $e->getMessage();
				}

				if ( ! empty( $sv_error ) ) {
					$return .= '<tr><th>' . __( 'Verification Status', 'google-sitemap-plugin' ) . '</th>';
					if ( $sv_error !== true )
						$return .= '<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $sv_error . '</td></tr>
							<tr><th></th>';
					$return .= '<td>' . __( "Manual verification required.", 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
				} else {

					$return .= '<tr><th>' . __( 'Sitemap Status', 'google-sitemap-plugin' ) . '</th>';

					if ( isset( $gglstmp_options['sitemap']['loc'] ) ) {
						$gglstmp_url_sitemap = $gglstmp_options['sitemap']['loc'];
						$gglstmp_check_sitemap = gglstmp_check_sitemap( $gglstmp_url_sitemap );
						if ( $gglstmp_check_sitemap['code'] == 200 ) {
							try {
								$webmasters->sitemaps->submit( $home_url, $gglstmp_url_sitemap );
								$return .= '<td class="gglstmp_success">' . __( 'Added', 'google-sitemap-plugin' ) . '</td></tr>';
							} catch ( Google_Service_Exception $e ) {
								$error = $e->getErrors();
								$wmt_error = isset( $error[0]['message'] ) ? $error[0]['message'] : __( 'Unexpected error', 'google-sitemap-plugin' );
							} catch ( Google_IO_Exception $e ) {
								$wmt_error = $e->getMessage();
							} catch ( Google_Auth_Exception $e ) {
								$wmt_error = true;
							} catch ( Exception $e ) {
								$wmt_error = $e->getMessage();
							}
							if ( ! empty( $wmt_error ) ) {
								if ( $wmt_error !== true )
									$return .= '<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . $wmt_error . '</td></tr>
										<tr><th></th>';
								$return .= '<td>' . __( "Please add the sitemap file manually.", 'google-sitemap-plugin' ) . ' <a target="_blank" href="' . $instruction_url . '">' . __( 'Learn More', 'google-sitemap-plugin' ) . '</a></td></tr>';
							}
						} else {
							$return .= '<td><strong>' . __( 'Error 404', 'google-sitemap-plugin' ) . ':</strong> ' . sprintf( __( 'The sitemap file %s not found.', 'google-sitemap-plugin' ), sprintf( '(<a href="%s">%s</a>)', $gglstmp_options['sitemap']['loc'], $gglstmp_options['sitemap']['file'] ) ) . '</td></tr>';
						}
					} else {
						$return .= '<td><strong>' . __( 'Error', 'google-sitemap-plugin' ) . ':</strong> ' . __( 'The sitemap file not found.', 'google-sitemap-plugin' ) . '</td></tr>';
					}
				}
			}
		}

		$return .= '</table>';
		return $return;
	}
}

/*============================================ Add verification code to the site head ====================*/
if ( ! function_exists( 'gglstmp_add_verification_code' ) ) {
	function gglstmp_add_verification_code() {
		global $gglstmp_options;

		if ( isset( $gglstmp_options['site_vererification_code'] ) ) {
			echo htmlspecialchars_decode( $gglstmp_options['site_vererification_code'] );
		}
	}
}

/*============================================ Check post status before Updating ====================*/
if ( ! function_exists( 'gglstmp_check_post_status' ) ) {
	function gglstmp_check_post_status( $new_status, $old_status, $post ) {
		if ( ! wp_is_post_revision( $post->ID ) ) {
			global $gglstmp_update_sitemap;
			if ( 'publish' == $new_status || 'trash' == $new_status || 'future' == $new_status ) {
			 	$gglstmp_update_sitemap = true;
			} elseif ( ( 'publish' == $old_status || 'future' == $old_status ) &&
				( 'auto-draft' == $new_status || 'draft' == $new_status || 'private' == $new_status || 'pending' == $new_status ) ) {
				$gglstmp_update_sitemap = true;
			}
		}
	}
}

/*============================================ Updating the sitemap after a post or page is trashed or published ====================*/
if ( ! function_exists( 'gglstmp_update_sitemap' ) ) {
	function gglstmp_update_sitemap( $post_id ) {
		if ( ! wp_is_post_revision( $post_id ) ) {
			global $gglstmp_update_sitemap;
			if ( true === $gglstmp_update_sitemap ) {
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
		if ( ! is_network_admin() && ! is_plugin_active( 'google-sitemap-pro/google-sitemap-pro.php' ) ) {
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=google-sitemap-plugin.php">' . __( 'Settings', 'google-sitemap-plugin' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'gglstmp_links' ) ) {
	function gglstmp_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() && ! is_plugin_active( 'google-sitemap-pro/google-sitemap-pro.php' ) )
				$links[] = '<a href="admin.php?page=google-sitemap-plugin.php">' . __( 'Settings', 'google-sitemap-plugin' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538869" target="_blank">' . __( 'FAQ', 'google-sitemap-plugin' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'google-sitemap-plugin' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'gglstmp_plugin_banner' ) ) {
	function gglstmp_plugin_banner() {
		global $hook_suffix, $gglstmp_plugin_info, $gglstmp_options;

		if ( 'plugins.php' == $hook_suffix ) {
			if ( ! $gglstmp_options )
				gglstmp_register_settings();

			if ( isset( $gglstmp_options['first_install'] ) && strtotime( '-1 week' ) > $gglstmp_options['first_install'] )
				bws_plugin_banner( $gglstmp_plugin_info, 'gglstmp', 'google-sitemap', '8fbb5d23fd00bdcb213d6c0985d16ec5', '83', '//ps.w.org/google-sitemap-plugin/assets/icon-128x128.png' );

			bws_plugin_banner_to_settings( $gglstmp_plugin_info, 'gglstmp_options', 'google-sitemap-plugin', 'admin.php?page=google-sitemap-plugin.php' );
		}

		if ( isset( $_REQUEST['page'] ) && 'google-sitemap-plugin.php' == $_REQUEST['page'] )
			bws_plugin_suggest_feature_banner( $gglstmp_plugin_info, 'gglstmp_options', 'google-sitemap-plugin' );
	}
}

/* add help tab  */
if ( ! function_exists( 'gglstmp_add_tabs' ) ) {
	function gglstmp_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'gglstmp',
			'section' 		=> '200538869'
		);
		bws_help_tab( $screen, $args );
	}
}

/**
 * Fires when the new blog has been added or during the blog activation, marking as not spam or as not archived.
 * @since   1.2.9
 * @param   int   $blog_id     Blog ID
 * @return  void
 */
if ( ! function_exists( 'gglstmp_add_sitemap' ) ) {
	function gglstmp_add_sitemap( $blog_id ) {
		global $wpdb;

		/* don`t have to check blog status for new blog */
		if ( 'wpmu_new_blog' != current_filter() ) {
			$blog_details = get_blog_details( $blog_id );
			if (
				! is_object( $blog_details ) ||
				$blog_details->archived == 1 ||
				$blog_details->deleted == 1 ||
				$blog_details->spam == 1
			)
				return;
		}

		$old_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );
		gglstmp_sitemapcreate();
		switch_to_blog( $old_blog );
	}
}

/**
 * Fires when the blog has been deleted or blog status to 'spam', 'deactivated(deleted)' or 'archived'.
 * @since   1.2.9
 * @param   int   $blog_id     Blog ID
 * @return  void
 */
if ( ! function_exists( 'gglstmp_delete_sitemap' ) ) {
	function gglstmp_delete_sitemap( $blog_id ) {

		$site_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", '_', str_replace( 'http://', '', str_replace( 'https://', '', get_site_url( $blog_id ) ) ) );
		$file     = ABSPATH . "sitemap_{$site_url}.xml";

		if ( file_exists( $file ) )
			unlink( $file );
	}
}

/*============================================ Function for delete of the plugin settings on register_activation_hook ====================*/
if ( ! function_exists( 'gglstmp_delete_settings' ) ) {
	function gglstmp_delete_settings() {
		global $wpdb;
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'google-sitemap-pro/google-sitemap-pro.php', $all_plugins ) ) {
			if ( is_multisite() ) {
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					delete_blog_option( $blog_id, 'gglstmp_options' );
					delete_blog_option( $blog_id, 'gglstmp_robots' );
					$site_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", '_',  str_replace( 'http://', '', str_replace( 'https://', '', get_site_url( $blog_id ) ) ) );
					$file     = ABSPATH . "sitemap_{$site_url}.xml";
					if ( file_exists( $file ) )
						unlink( $file );
				}
			} else {
				delete_option( 'gglstmp_options' );
				delete_option( 'gglstmp_robots' );
				$sitemap_path = ABSPATH . "sitemap.xml";
				$sitemap_url  = site_url( '/sitemap.xml' );
				$robots_path  = ABSPATH . "robots.txt";

				if ( file_exists( $sitemap_path ) )
					unlink( $sitemap_path );

				if ( file_exists( $robots_path ) ) {
					if ( ! is_writable( $robots_path ) )
						@chmod( $robots_path, 0755 );
					if ( is_writable( $robots_path ) ) {
						$content = file_get_contents( $robots_path );
						$content = preg_replace( "|\nSitemap: {$sitemap_url}|", '', $content );
						file_put_contents( $robots_path, $content );
					}
				}
			}
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

add_action( 'admin_menu', 'gglstmp_admin_menu' );

add_action( 'init', 'gglstmp_init' );
add_action( 'admin_init', 'gglstmp_admin_init' );

/* initialization */
add_action( 'plugins_loaded', 'gglstmp_plugins_loaded' );

add_action( 'admin_enqueue_scripts', 'gglstmp_add_plugin_stylesheet' );

add_action( 'transition_post_status', 'gglstmp_check_post_status', 10, 3 );
add_action( 'save_post', 'gglstmp_update_sitemap' );
add_action( 'trashed_post', 'gglstmp_update_sitemap' );

if ( 1 == get_option( 'gglstmp_robots' ) )
	add_filter( 'robots_txt', 'gglstmp_robots_add_sitemap', 10, 2 );

add_action( 'wp_head', 'gglstmp_add_verification_code' );

add_filter( 'plugin_action_links', 'gglstmp_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'gglstmp_links', 10, 2 );

add_action( 'admin_notices', 'gglstmp_plugin_banner' );

add_action( 'wpmu_new_blog', 'gglstmp_add_sitemap' );
add_action( 'activate_blog', 'gglstmp_add_sitemap' );
add_action( 'make_undelete_blog', 'gglstmp_add_sitemap' );
add_action( 'unarchive_blog', 'gglstmp_add_sitemap' );
add_action( 'make_ham_blog', 'gglstmp_add_sitemap' );

add_action( 'delete_blog', 'gglstmp_delete_sitemap' );
add_action( 'deactivate_blog', 'gglstmp_delete_sitemap' );
add_action( 'make_delete_blog', 'gglstmp_delete_sitemap' );
add_action( 'archive_blog', 'gglstmp_delete_sitemap' );
add_action( 'make_spam_blog', 'gglstmp_delete_sitemap' );

register_uninstall_hook( __FILE__, 'gglstmp_delete_settings' ); /* uninstall plugin */