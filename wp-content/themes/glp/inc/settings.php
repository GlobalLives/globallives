<?php
/**
 * Theme Settings (Admin Page)
 */
 
	add_action('admin_menu','theme_settings');
 
	function theme_settings() {
 		add_options_page('Theme','Theme','manage_options','glp_theme_settings','theme_settings_page');
	}
 
	function theme_settings_page() {

		/* Check permissions */
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		/* Declare settings */
		$setting_names = array(
			'show_donate_banner',
			'donate_banner_header',
			'donate_banner_body',
			'show_filter_bar'
			);
		foreach ( $setting_names as $setting ) {
			$setting_values[ $setting ] = get_option( $setting );		
		}

		/* Catch updates */
		if( isset($_POST[ 'action' ]) && $_POST[ 'action' ] == 'update' ) {
			foreach ( $setting_names as $setting ) {
				$setting_values[ $setting ] = $_POST[ $setting ];
				update_option( $setting,$setting_values[ $setting ] );
			}
			echo "<div class=\"updated\"><p><strong>Settings saved.</strong></p></div>";
		}
		
		/* Display panel */
?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Theme Settings</h2>
		
		<p>This panel allows easy access to many options of your theme.</p>
		
		<form name="form1" method="post" action="">
			<input type="hidden" name="action" value="update">

			<h3>Header &amp; Footer</h3>
			
			<blockquote>
			
				<h4>Donate Banner</h4>
				<p>
					<input type="checkbox" name="show_donate_banner" <?php if ($setting_values[ 'show_donate_banner' ]) : ?>checked="checked"<?php endif; ?> /> <label for="show_filter_bar">Show Donate Banner?</label>
				</p>
				<p>
					<label for="donate_banner_header">Donate Banner header</label><br>
					<input type="text" name="donate_banner_header" value="<?php echo $setting_values[ 'donate_banner_header' ]; ?>" />
				</p>
				<p>
					<label for="donate_banner_body">Donate Banner body</label><br>
					<textarea name="donate_banner_body" /><?php echo $setting_values[ 'donate_banner_body' ]; ?></textarea>
				</p>		

			</blockquote>
			
			<h3>Explore Page</h3>

			<blockquote>

				<h4>Map / Grid View</h4>
				<p>
					<input type="checkbox" name="show_filter_bar" <?php if ($setting_values[ 'show_filter_bar' ]) : ?>checked="checked"<?php endif; ?> /> <label for="show_filter_bar">Show filter bar?</label>
				</p>

			</blockquote>
			
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
			</p>

		</form>
	</div>
	
<?php }