<?php
/**
 * Banners on plugin settings page
 * @package Google Sitemap by BestWebSoft
 * @since 3.0.3
 */

/**
 * Wrapper. Show ads for PRO on plugin settings page
 * @param     string     $func        function to call
 * @param     boolean    $show_cross  when it is 'false' ad will be displayed regardless of if other blocks are closed
 * @return    void
 */
if ( ! function_exists( 'gglstmp_pro_block' ) ) {
	function gglstmp_pro_block( $func, $show_cross = true ) {
		global $gglstmp_plugin_info, $wp_version, $gglstmp_settings;
		if ( ! bws_hide_premium_options_check( $gglstmp_settings ) || ! $show_cross ) { ?>
			<div class="bws_pro_version_bloc gglstmp_pro_block <?php echo $func;?>" title="<?php _e( 'This options is available in Pro version of plugin', 'google-sitemap-plugin' ); ?>">
				<div class="bws_pro_version_table_bloc">
					<?php if ( $show_cross ) { ?>
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-sitemap-plugin' ); ?>"></button>
					<?php } ?>
					<div class="bws_table_bg"></div>
					<?php call_user_func( $func ); ?>
				</div>
				<div class="bws_pro_version_tooltip">
					<div class="bws_info"><?php _e( 'Unlock premium options by upgrading to Pro version', 'google-sitemap-plugin' ); ?></div>
					<a class="bws_button" href="http://bestwebsoft.com/products/wordpress/plugins/google-sitemap/?k=28d4cf0b4ab6f56e703f46f60d34d039&pn=83&v=<?php echo $gglstmp_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Sitemap Pro"><?php _e( 'Learn More', 'google-sitemap-plugin' ); ?></a>
				</div>
			</div>
		<?php }
	}
}

/**
 * The content of ad block on the "Settings" tab
 * @param     void
 * @return    void
 */
if ( ! function_exists( 'gglstmp_frequency_block' ) ) {
	function gglstmp_frequency_block() { ?>
		<table class="form-table bws_pro_version">
			<tr valign="top">
				<th><?php _e( 'XML Sitemap "Change Frequency" parameter', 'google-sitemap-plugin' ); ?></th>
				<td>
					<select disabled="disabled">
						<option><?php _e( 'Monthly', 'google-sitemap-plugin' ); ?></option>
					</select><br />
					<span class="bws_info"><?php _e( 'This value is used in the sitemap file and provides general information to search engines. The sitemap itself is generated once and will be re-generated when you create or update any post or page. For more info see', 'google-sitemap-plugin' ); ?>&nbsp;<a href="http://www.sitemaps.org/protocol.html#changefreqdef" style="display: inline-block; position: relative; z-index: 100;" target="_blank"><?php _e( 'here', 'google-sitemap-plugin' ); ?></a>.</span>
				</td>
			</tr>
		</table>
	<?php }
}

/**
 * The content of ad block on the "Extra settings" tab
 * @param     void
 * @return    void
 */
if ( ! function_exists( 'gglstmp_extra_block' ) ) {
	function gglstmp_extra_block() { ?>
		<table class="form-table bws_pro_version">
			<tr valign="top">
				<td colspan="2">
					<?php _e( 'Add post types and taxonomies links to the sitemap', 'google-sitemap-plugin' ); ?>:
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<label>
						<input disabled="disabled" checked="checked" id="gglstmp_jstree_url" type="checkbox" name="gglstmp_jstree_url" value="1" />
						<?php _e( "Show URL for pages", 'google-sitemap-plugin' );?>
					</label>
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<img src="<?php echo plugins_url( 'images/pro_screen_1.png', dirname( __FILE__ ) ); ?>" alt="<?php _e( "Example of site pages' tree", 'google-sitemap-plugin' ); ?>" title="<?php _e( "Example of site pages' tree", 'google-sitemap-plugin' ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">
					<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'google-sitemap-plugin' ); ?>" />
				</td>
			</tr>
		</table>
	<?php }
}

/**
 * The content of ad block on the "Custom links" tab
 * @param     void
 * @return    void
 */
if ( ! function_exists( 'gglstmp_custom_links_block' ) ) {
	function gglstmp_custom_links_block() {
		$date = date_i18n( get_option( 'date_format' ), 1458086400 ); ?>

		<p class="search-box">
			<input type="search" disabled="disabled"/>
			<input class="button" value="<?php _e( 'Search' ); ?>" type="submit" disabled="disabled" />
		</p>
		<div class="alignleft actions bulkactions">
			<select disabled="disabled">
				<option value="-1"><?php _e( 'Bulk Actions' ); ?></option>
			</select>
			<input disabled="disabled" class="button action" value="<?php _e( 'Apply' ); ?>" type="submit">
		</div>

		<table class="wp-list-table widefat fixed striped links">
			<thead>
				<tr>
					<th id="cb" class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox" disabled="disabled" /></th>
					<th scope="col" id="url" class="manage-column column-url column-primary sortable asc">URL</th>
					<th scope="col" id="priority" class="manage-column column-priority sortable desc"><?php _e( 'Priority', 'google-sitemap-plugin' ); ?></th>
					<th scope="col" id="frequency" class="manage-column column-frequency"><?php _e( 'Change Frequency', 'google-sitemap-plugin' ); ?></th>
					<th scope="col" id="date" class="manage-column column-date sortable desc"><?php _e( 'Last Changed', 'google-sitemap-plugin' ); ?></th>
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists="list:link">
				<tr style="overflow: visible;">
					<th scope="row" class="check-column">
						<div class="bws_help_box bws_help_box_left dashicons dashicons-editor-help" style="vertical-align: middle;margin-left: 6px;position:relative;z-index: 2;">
							<div class="bws_hidden_help_text" style="min-width: 200px;">
								<strong><?php _e( "Please note", "google-sitemap-plugin" ); ?>:</strong>
								<?php _e( "All URLs listed in the sitemap.xml must use the same protocol ( HTTP or HTTPS ) and reside on the same host as the sitemap.xml. For more info see", "google-sitemap-plugin" ); ?>&nbsp;<a href="http://www.sitemaps.org/protocol.html#location" target="_blank"><?php _e( "here", "google-sitemap-plugin" ); ?></a>.
							</div>
						</div>
					</th>
					<td class="url column-url has-row-actions column-primary" data-colname="URL"><input type="url" style="width: 100%; box-sizing: border-box;" disabled="disabled" /></td>
					<td class="priority column-priority" data-colname="Priority"><input class="small-text" value="100" type="number" disabled="disabled" />&nbsp;%</td>
					<td class="frequency column-frequency" data-colname="Change Frequency">
						<select disabled="disabled" >
							<option value="always"><?php _e( "Always", "google-sitemap-plugin" ); ?></option>
						</select>
					</td>
					<td class="date column-date" data-colname="Last Changed">
						<input class="button button-primary" value="<?php _e( "Save link", "google-sitemap-plugin" ); ?>" type="submit" disabled="disabled" />
					</td>
				</tr>

				<tr>
					<th scope="row" class="check-column"><input type="checkbox" disabled="disabled" /></th>
					<td class="url column-url has-row-actions column-primary" data-colname="URL">http://example.com/lorem/ipsum/dolor/sit/amet</td>
					<td class="priority column-priority" data-colname="Priority">100&nbsp;%</td>
					<td class="frequency column-frequency" data-colname="Change Frequency"><?php _e( 'Monthly', 'google-sitemap-plugin' ); ?></td>
					<td class="date column-date" data-colname="Last Changed"><?php echo $date; ?></td>
				</tr>

				<tr>
					<th scope="row" class="check-column"><input type="checkbox" disabled="disabled" /></th>
					<td class="url column-url has-row-actions column-primary" data-colname="URL">http://example.com/donec-fringilla</td>
					<td class="priority column-priority" data-colname="Priority">100&nbsp;%</td>
					<td class="frequency column-frequency" data-colname="Change Frequency"><?php _e( 'Monthly', 'google-sitemap-plugin' ); ?></td>
					<td class="date column-date" data-colname="Last Changed"><?php echo $date; ?></td>
				</tr>

				<tr>
					<th scope="row" class="check-column"><input type="checkbox" disabled="disabled" /></th>
					<td class="url column-url has-row-actions column-primary" data-colname="URL">http://example.com/lorem-ipsum</td>
					<td class="priority column-priority" data-colname="Priority">100&nbsp;%</td>
					<td class="frequency column-frequency" data-colname="Change Frequency"><?php _e( 'Monthly', 'google-sitemap-plugin' ); ?></td>
					<td class="date column-date" data-colname="Last Changed"><?php echo $date; ?></td>
				</tr>

				<tr>
					<th scope="row" class="check-column"><input type="checkbox" disabled="disabled" /></th>
					<td class="url column-url has-row-actions column-primary" data-colname="URL">http://example.com/?s_id=123&amp;p_id=2</td>
					<td class="priority column-priority" data-colname="Priority">100&nbsp;%</td>
					<td class="frequency column-frequency" data-colname="Change Frequency"><?php _e( 'Monthly', 'google-sitemap-plugin' ); ?></td>
					<td class="date column-date" data-colname="Last Changed"><?php echo $date; ?></td>
				</tr>
			</tbody>

			<tfoot>
				<tr>
					<th class="manage-column column-cb check-column"><input id="cb-select-all-2" type="checkbox" disabled="disabled" /></th>
					<th scope="col" class="manage-column column-url column-primary sortable asc">URL</th>
					<th scope="col" class="manage-column column-priority sortable desc"><?php _e( 'Priority', 'google-sitemap-plugin' ); ?></th>
					<th scope="col" class="manage-column column-frequency"><?php _e( 'Change Frequency', 'google-sitemap-plugin' ); ?></th>
					<th scope="col" class="manage-column column-date sortable desc"><?php _e( 'Last Changed', 'google-sitemap-plugin' ); ?></th>
				</tr>
			</tfoot>

		</table>
	<?php }
}
