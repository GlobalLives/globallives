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
			
				<p>None yet.</p>

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