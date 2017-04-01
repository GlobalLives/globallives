<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Gglstmp_Settings_Tabs' ) ) {
	class Gglstmp_Settings_Tabs extends Bws_Settings_Tabs {
		public $htaccess_options = false,
			$htaccess_active = false,
			$robots, $htaccess, $client, $blog_prefix,
			$manage_info = '';
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $gglstmp_options, $gglstmp_plugin_info;

			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'google-sitemap-plugin' ) ),
				'display' 		=> array( 'label' => __( 'Structure', 'google-sitemap-plugin' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'google-sitemap-plugin' ) ),
				'license'		=> array( 'label' => __( 'License Key', 'google-sitemap-plugin' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $gglstmp_plugin_info,
				'prefix' 			 => 'gglstmp',
				'default_options' 	 => gglstmp_get_options_default(),
				'options' 			 => $gglstmp_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'google-sitemap-plugin',
				'pro_page' 			 => 'admin.php?page=google-sitemap-pro.php',
				'bws_license_plugin' => 'google-sitemap-pro/google-sitemap-pro.php',
				'link_key' 			 => '28d4cf0b4ab6f56e703f46f60d34d039',
				'link_pn' 			 => '83'
			) );

			if ( ! $this->is_multisite )
				$this->robots = get_option( 'gglstmp_robots' );

			/* Check htaccess plugin */
			if ( $this->is_multisite && ! is_subdomain_install() ) {
				$all_plugins = get_plugins();
				$this->htaccess = gglstmp_plugin_status( array( 'htaccess/htaccess.php', 'htaccess-pro/htaccess-pro.php' ), $all_plugins, false );
				$this->htaccess_options = false;
				if ( $this->htaccess['status'] == 'actived' ) {
					global $htccss_options;
					register_htccss_settings();
					$this->htaccess_options = &$htccss_options;
					$this->htaccess_active = true;
					if ( function_exists( 'htccss_check_xml_access' ) ) {
						$htaccess_check = htccss_check_xml_access();
						if ( $htaccess_check != $this->htaccess_options['allow_xml'] ) {
							$this->htaccess_options['allow_xml'] = $htaccess_check;
							update_site_option( 'htccss_options', $this->htaccess_options );
						}
					}
				}
			}

			if ( function_exists( 'curl_init' ) ) {
				$this->client = gglstmp_client();
				$this->blog_prefix = '_' . get_current_blog_id();
				if ( ! isset( $_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ] ) && isset( $this->options['authorization_code'] ) ) {
					$_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ] = $this->options['authorization_code'];
				}
				if ( isset( $_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ] ) ) {
					$this->client->setAccessToken( $_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ] );
				}
			}

			add_filter( get_parent_class( $this ) . '_additional_restore_options', array( $this, 'additional_restore_options' ) );
			add_filter( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			if ( isset( $_POST['gglstmp_logout'] ) ) {
				unset( $_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ], $this->options['authorization_code'] );
				update_option( 'gglstmp_options', $this->options );
			} elseif ( isset( $_POST['gglstmp_authorize'] ) && ! empty( $_POST['gglstmp_authorization_code'] ) ) {
				try {
					$this->client->authenticate( $_POST['gglstmp_authorization_code'] );
					$this->options['authorization_code'] = $_SESSION[ 'gglstmp_authorization_code' . $this->blog_prefix ] = $this->client->getAccessToken();
					update_option( 'gglstmp_options', $this->options );
				} catch ( Exception $e ) {}
			} elseif ( isset( $_POST['gglstmp_menu_add'] ) || isset( $_POST['gglstmp_menu_delete'] ) || isset( $_POST['gglstmp_menu_info'] ) ) {
				if ( $this->client->getAccessToken() ) {
					$webmasters = new Google_Service_Webmasters( $this->client );
					$site_verification  = new Google_Service_SiteVerification( $this->client );
					if ( isset( $_POST['gglstmp_menu_info'] ) ) {
						$this->manage_info .= gglstmp_info_site( $webmasters, $site_verification );
					} elseif ( isset( $_POST['gglstmp_menu_add'] ) ) {
						$this->manage_info .= gglstmp_add_site( $webmasters, $site_verification );
					} else {
						$this->manage_info .= gglstmp_del_site( $webmasters, $site_verification );
					}
				}
			} else {

				if ( $this->htaccess_active && $this->htaccess_options && function_exists( 'htccss_generate_htaccess' ) ) {
					$gglstmp_allow_xml = ( isset( $_POST[ 'gglstmp_allow_xml' ] ) && $_POST[ 'gglstmp_allow_xml' ] == 1 ) ? 1 : 0;
					if ( $gglstmp_allow_xml != $this->htaccess_options['allow_xml']  ) {
						$this->htaccess_options['allow_xml'] = $gglstmp_allow_xml;
						update_site_option( 'htccss_options', $this->htaccess_options );
						htccss_generate_htaccess();
					}
				}

				$post_types = isset( $_REQUEST['gglstmp_post_types'] ) ? $_REQUEST['gglstmp_post_types'] : array();
				$taxonomys = isset( $_REQUEST['gglstmp_taxonomies'] ) ? $_REQUEST['gglstmp_taxonomies'] : array();
				if ( $this->options['post_type'] != $post_types || $this->options['taxonomy'] != $taxonomys )
					$sitemapcreate = true;

				$this->options['post_type'] = $post_types;
				$this->options['taxonomy'] = $taxonomys;

				update_option( 'gglstmp_options', $this->options );

				if ( isset( $sitemapcreate ) )
					gglstmp_sitemapcreate();

				/*============================ Adding location of sitemap file to the robots.txt =============*/
				if ( ! $this->is_multisite ) {
					$robots_flag = isset( $_POST['gglstmp_checkbox'] ) ? 1 : 0;
					$url_robot = ABSPATH . 'robots.txt';
					if ( file_exists( $url_robot ) ) {
						if ( ! is_writable( $url_robot ) )
							@chmod( $url_robot, 0755 );
						if ( is_writable( $url_robot ) ) {
							$file_content = file_get_contents( $url_robot );
							if ( isset( $_POST['gglstmp_checkbox'] ) && ! preg_match( '|Sitemap: ' . $gglstmp_url_home . 'sitemap.xml|', $file_content ) ) {
								file_put_contents( $url_robot, $file_content . "\nSitemap: " . $gglstmp_url_home . "sitemap.xml" );
							} elseif ( preg_match( "|Sitemap: " . $gglstmp_url_home . "sitemap.xml|", $file_content ) && ! isset( $_POST['gglstmp_checkbox'] ) ) {
								$file_content = preg_replace( "|\nSitemap: " . $gglstmp_url_home . "sitemap.xml|", '', $file_content );
								file_put_contents( $url_robot, $file_content );
							}
						} else {
							$error = __( 'Cannot edit "robots.txt". Check your permissions', 'google-sitemap-plugin' );
							$robots_flag = 0;
						}
					}

					if ( false === get_option( 'gglstmp_robots' ) )
						add_option( 'gglstmp_robots', $robots_flag );
					else
						update_option( 'gglstmp_robots', $robots_flag );
					$this->robots = get_option( 'gglstmp_robots' );
				}

				$message = __( 'Settings saved.', 'google-sitemap-plugin' );
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Google Sitemap Settings', 'google-sitemap-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>			
			<table class="form-table gglstmp_settings_form">
				<?php if ( ! $this->is_multisite ) {
					$disabled = '';
					$checked  = 1 == $this->robots ? ' checked="checked"' : '';
					/* for robots.txt we need to use site_url instead home_url ! */
					$link     = '<a href="' . site_url( '/' ) . 'robots.txt" target="_blank">robots.txt</a>';
					$notice   = '';
				} else {
					$disabled = ' disabled="disabled"';
					$checked  = '';
					$link     = 'robots.txt';
					$notice   = '<p style="color:red">' . sprintf( __( 'Since you are using multisiting, the plugin does not allow to add a sitemap to %s.', 'google-sitemap-plugin' ), '"robots.txt"' ) . '</p>';
				} ?>
				<tr>
					<th>Robots.txt</th>
					<td>
						<input type='checkbox'<?php echo $disabled; ?> name='gglstmp_checkbox' value="1"<?php echo $checked; ?> /> 
						<span class="bws_info"><?php printf( __( "Enable to add a sitemap file path to the %s file.", 'google-sitemap-plugin' ), $link ); ?></span>
						<?php echo $notice; ?>
					</td>
				</tr>
				<?php if ( $this->is_multisite && ! is_subdomain_install() ) {
					$attr_checked = $attr_disabled = '';
					$htaccess_plugin_notice = __( 'This option will be applied to all websites in the network.', 'google-sitemap-plugin' );
					if ( 'deactivated' == $this->htaccess['status'] ) {
						$attr_disabled = 'disabled="disabled"';
						$htaccess_plugin_notice = '<a href="' . network_admin_url( '/plugins.php' ) . '">' . __( 'Activate', 'google-sitemap-plugin' ) . '</a>';
					} elseif ( 'not_installed' == $this->htaccess['status'] ) {
						global $wp_version;
						$attr_disabled = 'disabled="disabled"';
						$htaccess_plugin_notice = '<a href="https://bestwebsoft.com/products/wordpress/plugins/htaccess/?k=bc745b0c9d4b19ba95ae2c861418e0df&pn=106&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version . '">' . __( 'Install Now', 'google-sitemap-plugin' ) . '</a>';
					}
					if ( '' != $this->change_permission_attr ) {
						$attr_disabled = 'disabled="disabled"';
					}
					if ( '1' == $this->htaccess_options['allow_xml'] && $attr_disabled == '' ) {
						$attr_checked = 'checked="checked"';
					} ?>
					<tr id="gglstmp_allow_xml_block">
						<th><?php printf( __( '%s Plugin', 'google-sitemap-plugin' ), 'Htaccess' ); ?></th>
						<td>
							<input <?php printf( "%s %s", $attr_checked, $attr_disabled ); ?> type="checkbox" name="gglstmp_allow_xml" value="1" /> <span class="bws_info"><?php printf( __( 'Enable to allow XML files access using %s plugin.', 'google-sitemap-plugin' ), 'Htaccess' ); ?> <?php echo $htaccess_plugin_notice; ?></span>
							<?php echo bws_add_help_box( __( 'The following string will be added to your .htaccess file', 'google-sitemap-plugin' ) . ': <code>RewriteRule ([^/]+\.xml)$ $1 [L]</code>' ); ?>
						</td>
					</tr>
				<?php } ?>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-sitemap-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<?php gglstmp_frequency_block(); ?>
						</table>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
			<table class="form-table gglstmp_settings_form">
				<tr>
					<th><?php _e( 'Google Webmaster Tools', 'google-sitemap-plugin' ); ?></th>
					<td>
						<?php if ( ! $this->client ) { ?>		
							<?php _e( "This hosting does not support сURL, so you cannot add a sitemap file automatically.", 'google-sitemap-plugin' ); ?>
						<?php } else { ?>
							<?php if ( ! isset( $_POST['gglstmp_logout'] ) && $this->client->getAccessToken() ) { ?>
								<input <?php echo $this->change_permission_attr; ?> class="button-secondary bws_no_bind_notice" name="gglstmp_logout" type="submit" value="<?php _e( 'Logout from Google Webmaster Tools', 'google-sitemap-plugin' ); ?>" />
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Manage Website with Google Webmaster Tools', 'google-sitemap-plugin' ); ?></th>
							<td>
								<input<?php echo $this->change_permission_attr; ?> class="button-secondary bws_no_bind_notice" type='submit' name='gglstmp_menu_add' value="<?php _e( 'Add', 'google-sitemap-plugin' ); ?>" />
								<input<?php echo $this->change_permission_attr; ?> class="button-secondary bws_no_bind_notice" type='submit' name='gglstmp_menu_delete' value="<?php _e( 'Delete', 'google-sitemap-plugin' ); ?>" />
								<input<?php echo $this->change_permission_attr; ?> class="button-secondary bws_no_bind_notice" type='submit' name='gglstmp_menu_info' value="<?php _e( 'Get Info', 'google-sitemap-plugin' ); ?>" />
								<div class="bws_info">
									<?php _e( "Add, delete or get info about this website using your Google Webmaster Tools account.", 'google-sitemap-plugin' ); ?>
								</div>
								<?php echo $this->manage_info;
							} else {
								$gglstmp_state = mt_rand();
								$this->client->setState( $gglstmp_state );
								$_SESSION[ 'gglstmp_state' . $this->blog_prefix ] = $this->client;
								$gglstmp_auth_url = $this->client->createAuthUrl(); ?>
								<a <?php echo $this->change_permission_attr; ?> id="gglstmp_authorization_button" class="button-secondary button" href="<?php echo $gglstmp_auth_url; ?>" target="_blank" onclick="window.open(this.href,'','top='+(screen.height/2-560/2)+',left='+(screen.width/2-640/2)+',width=640,height=560,resizable=0,scrollbars=0,menubar=0,toolbar=0,status=1,location=0').focus(); return false;"><?php _e( 'Get Authorization Code', 'google-sitemap-plugin' ); ?></a>
								<div id="gglstmp_authorization_form">
									<input <?php echo $this->change_permission_attr; ?> id="gglstmp_authorization_code" class="bws_no_bind_notice" name="gglstmp_authorization_code" type="text" maxlength="100" autocomplete="off" />
									<input <?php echo $this->change_permission_attr; ?> id="gglstmp_authorize" class="button-secondary button bws_no_bind_notice" name="gglstmp_authorize" type="submit" value="<?php _e( 'Authorize', 'google-sitemap-plugin' ); ?>" />
								</div>
								<?php if ( isset( $_POST['gglstmp_authorization_code'] ) && isset( $_POST['gglstmp_authorize'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'gglstmp_nonce_name' ) ) { ?>
									<div id="gglstmp_authorize_error"><?php _e( 'Invalid authorization code. Please try again.', 'google-sitemap-plugin' ); ?></div>
								<?php }
							}
						} ?>
						<div class="bws_info">
							<?php _e( 'You can also add your sitemap to Google Webmaster Tools manually.', 'google-sitemap-plugin' ); ?>&nbsp;<a target="_blank" href="https://docs.google.com/document/d/1VOJx_OaasVskCqi9fsAbUmxfsckoagPU5Py97yjha9w/"><?php _e( 'Read the instruction', 'google-sitemap-plugin' ); ?></a>
						</div>
					</td>
				</tr>
			</table>
		<?php }

		/**
		 *
		 */
		public function tab_display() { 
			$post_types = get_post_types( '', 'names' );
			unset( $post_types['revision'], $post_types['attachment'], $post_types['nav_menu_item'] );

			$taxonomies = array(
				'category' => __( 'Post category', 'google-sitemap-plugin' ),
				'post_tag' => __( 'Post tag', 'google-sitemap-plugin' )
			); ?>
			<h3 class="bws_tab_label"><?php _e( 'Sitemap Structure', 'google-sitemap-plugin' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table gglstmp_settings_form">
				<tr>
					<th><?php _e( 'Post Types', 'google-sitemap-plugin' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $post_types as $value ) { ?>
								<label><input type="checkbox" <?php if ( in_array( $value, $this->options['post_type'] ) ) echo 'checked="checked"'; ?> name="gglstmp_post_types[]" value="<?php echo $value; ?>"/><span style="text-transform: capitalize; padding-left: 5px;"><?php echo $value; ?></span></label><br />
							<?php } ?>
						</fieldset>
						<span class="bws_info"><?php _e( 'Enable to add post type links to the sitemap.', 'google-sitemap-plugin' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Taxonomies', 'google-sitemap-plugin' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $taxonomies as $key => $value ) { ?>
								<label><input type="checkbox" <?php if ( in_array( $key, $this->options['taxonomy'] ) ) echo 'checked="checked"'; ?> name="gglstmp_taxonomies[]" value="<?php echo $key; ?>"/><span style="padding-left: 5px;"><?php echo $value; ?></span></label><br />
							<?php } ?>
						</fieldset>
						<span class="bws_info"><?php _e( 'Enable to taxonomy links to the sitemap.', 'google-sitemap-plugin' ); ?></span>
					</td>
				</tr>
			</table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-sitemap-plugin' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<?php gglstmp_extra_block(); ?>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php }
		}

		/**
		 * Custom functions for "Restore plugin options to defaults"
		 * @access public
		 */
		public function additional_restore_options( $default_options ) {
			$url_robot = ABSPATH . 'robots.txt';
			/* remove sitemap.xml */
			if ( $this->is_multisite ) {
				$home_url = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ) );
				@unlink( ABSPATH . "sitemap_" . $home_url . ".xml" );
			} else {
				@unlink( ABSPATH . "sitemap.xml" );
			}
			
			/* clear robots.txt */
			if ( file_exists( $url_robot ) ) {
				if ( ! is_writable( $url_robot ) )
					@chmod( $url_robot, 0755 );
				if ( is_writable( $url_robot ) ) {
					$file_content = file_get_contents( $url_robot );
					if ( preg_match( "|Sitemap: " . $gglstmp_url_home . "sitemap.xml|", $file_content ) ) {
						$file_content = preg_replace( "|\nSitemap: " . $gglstmp_url_home . "sitemap.xml|", '', $file_content );
						file_put_contents( $url_robot, $file_content );
					}
				} else {
					$error = __( 'Cannot edit "robot.txt". Check your permissions', 'google-sitemap-plugin' );
				}
			}
			if ( false === get_option( 'gglstmp_robots' ) )
				add_option( 'gglstmp_robots', 0 );
			else
				update_option( 'gglstmp_robots', 0 );
		
			return $default_options;
		}


		public function display_custom_messages( $save_results ) {
			/*=============================== Creating sitemap file ====================================*/
			if ( $this->is_multisite ) {
				$xml_file = 'sitemap_' . preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", "_", str_replace( 'http://', '', str_replace( 'https://', '', site_url() ) ) ) . '.xml';
				$xml_url = site_url( '/' ) . $xml_file;
				$site_id = get_current_blog_id();
				$xml_is_deleted_from_network = isset( $gglstmp_network_options['removed_sitemaps'] ) ? in_array( $site_id, $gglstmp_network_options['removed_sitemaps'] ) : false;
			} else {
				$xml_file = 'sitemap.xml';
				$xml_url  = site_url( '/' ) . $xml_file;
				$xml_is_deleted_from_network = false;
			}

			if ( file_exists( ABSPATH . $xml_file ) ) {
				$xml_is_created = true;
			} else {
				if ( $xml_is_deleted_from_network ) {
					$xml_is_created = false;
				} else {
					gglstmp_sitemapcreate();
					$xml_is_created = true;
				}
			}

			echo
					$xml_is_created
				?
					'<div class="updated bws-notice inline"><p><strong>' . sprintf( __( "%s is in the site root directory.", 'google-sitemap-plugin' ), '<a href="' . $xml_url . '" target="_blank">' . __( 'The Sitemap file', 'google-sitemap-plugin' ) . '</a>' ) . '</strong></p></div>'
				:
					'<div class="error inline"><p><strong>' . __( 'The Sitemap file for this site has been deleted by network admin.', 'google-sitemap-plugin' ) . '</strong></p></div>';

			if ( $this->is_multisite && ! is_subdomain_install() && count( glob( ABSPATH . "sitemap*.xml" ) ) > 0 && ( ! $this->htaccess_active || $this->htaccess_options['allow_xml'] == 0 ) ) {
				if ( $this->options['sitemap'] && file_exists( $this->options['sitemap']['path'] ) ) {
					$status = gglstmp_check_sitemap( $this->options['sitemap']['loc'] ); 
					if ( '200' != $status['code'] ) { ?>
						<div class="error below-h2">
							<p>
								<strong><?php _e( 'Error', 'google-sitemap-plugin' ); ?>:</strong> <?php 
									printf( __( "Can't access XML files on subsites. Add the following rule %s to your %s file in %s after %s or install, activate and enable %s plugin option to resolve this error.", 'google-sitemap-plugin' ),
										'<code>RewriteRule ([^/]+\.xml)$ $1 [L]</code>',
										'<strong>.htaccess</strong>',
										sprintf( '<strong>"%s"</strong>', ABSPATH ),
										'<strong>"RewriteBase"</strong>',
										'Htaccess'
									); ?>
							</p>
						</div>
					<?php }
				}
			}		
		}
	}
}