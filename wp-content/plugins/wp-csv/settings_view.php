<script type="text/javascript">
jQuery( function( ) {
	jQuery( '#wpcsv-settings-tabs' ).tabs( );
});
</script>
<form method='post'>
<input type='hidden' name='action' value='export'/>

<input class='wpcsv-save-all-button upper' type="submit" value="<?php _e( 'Save All Settings', 'wp-csv' ); ?>" />
<div id='wpcsv-settings-tabs'>
<ul>
	<li><a href='#general'>[ General ]</a></li>
	<li><a href='#filters'>[ Filters ]</a></li>
</ul>
<div id='general'>
<?php if ( isset( $nonce ) ) echo $nonce ?>
<strong class='red'><?php echo $error;?></strong>
<table class='widefat'>
<thead>
<tr><th colspan='2'><strong><?php _e( 'General', 'wp-csv' ); ?></strong></th></tr>
</thead>
<tbody>
<tr><th><?php _e( 'Delimiter', 'wp-csv' ); ?>:</th><td><input name="delimiter" type="text" length="1" value="<?php echo htmlentities($delimiter); ?>"/><span class='description'><strong> <?php _e( "(European users will usually need to change this from ',' to ';')", 'wp-csv' ); ?></strong><span/></td></tr>
<tr><th><?php _e( 'Enclosure', 'wp-csv' ); ?>:</th><td><input name="enclosure" type="text" length="1" value="<?php echo htmlentities($enclosure); ?>"/><span class='description'></td></tr>
<tr><th><?php _e( 'Import Date format', 'wp-csv' ); ?>:</th><td><select name="date_format"><option <?php if ($date_format == 'US' ) echo 'selected';?> value="US">US (MM/DD/YYYY)</option><option <?php if ($date_format == 'English' ) echo 'selected';?> value="English">English (DD/MM/YYYY)</option></select><span class='description'><strong> <?php _e( "(Dates are always exported as 'YYYY-MM-DD HH:MM:SS')", 'wp-csv' ); ?></strong><span/></td></tr></td></tr>
<?php
if ( current_user_can( 'manage_options' ) ):
?>
<tr><th><?php _e( 'Minimum Access Level', 'wp-csv' ); ?>:</th><td><select name="access_level">
<option <?php if ($access_level == 'manage_options' ) echo 'selected';?> value="manage_options">Administrator</option>
<option <?php if ($access_level == 'edit_pages' ) echo 'selected';?> value="edit_pages">Editor</option>
<option <?php if ($access_level == 'publish_posts' ) echo 'selected';?> value="publish_posts">Author</option>
<option <?php if ($access_level == 'edit_posts' ) echo 'selected';?> value="edit_posts">Contributor</option>
<option <?php if ($access_level == 'read' ) echo 'selected';?> value="read">Subscriber</option>
</select></td></tr>
<?php
endif;
?>
<?php 
	$shortcode_checked = ( $frontend_shortcode ) ? 'checked ' : '';
?>
<tr><th><?php _e( "Shortcode Enabled", 'wp-csv' ); ?>:</th><td><input name="frontend_shortcode" type="checkbox" <?php echo $shortcode_checked; ?>/>
<blockquote><i><?php _e( "Only turn this on if you want your site visitors to be able to export posts!  To display the export form in a post or page you can use the shortcode '[wpcsv_export_form]'. It will allow any visitor to export to CSV according to whatever settings you've last saved on this screen (except for debug output).  <strong>Please use this feature with caution!</strong>", 'wp-csv' ); ?></i></blockquote></td></tr>
<?php 
	$debug_checked = ( $debug ) ? 'checked ' : '';
?>
<tr><th><?php _e( "Debug Active", 'wp-csv' ); ?>:</th><td><input name="debug" type="checkbox" <?php echo $debug_checked; ?>/>
<blockquote><i><?php _e( "This may cause extra load and create quite a large trace file.  Only turn on if there's a problem. NOTE: Currently only traces export.", 'wp-csv' ); ?></i></blockquote></td></tr>
</tbody>
</table>
</div>
<div id='filters'>
<table class='widefat'>
<thead>
<tr><th colspan='2'><strong><?php _e( 'Filters', 'wp-csv' ); ?></strong></th></tr>
</thead>
<tbody>
<?php 
	$hidden_checked = ( $export_hidden_custom_fields ) ? 'checked ' : '';
?>
<tr><th><?php _e( "Export 'Hidden' Custom Fields", 'wp-csv' ); ?>:</th><td><input name="export_hidden_custom_fields" type="checkbox" <?php echo $hidden_checked; ?>/></td></tr>
<tr><th><?php _e( 'Post Type and Status Exclude Filter', 'wp-csv' ); ?>:</th>
<td>
<p>Select the post types and post statuses that you want to <strong>EXCLUDE</strong>. Everything else will be exported.</p>
<?php
	echo "<p><a href='#' id='wpcsv-type-status-toggle-all-on'>[ Exclude All ]</a> &nbsp;&nbsp; <a href='#' id='wpcsv-type-status-toggle-all-off'>[ Exclude None ]</a></p>";
	echo $hc->post_type_and_status_filters( $type_status_filters );
?>
</td></tr>
<tr><th><?php _e( "Include Fields", 'wp-csv' ); ?>:</th><td><textarea name="include_field_list" cols="70" rows="5" /><?php echo implode( ',', $include_field_list ); ?></textarea>
<blockquote><i><?php _e( "Control which fields are included in the export file.  You can enter the full field name or a pattern such as '*' (for everything), 'start*' (for fields starting with 'start'), or '*end' (for fields ending with 'end'). Separate field rules with a comma.  NOTE: Some fields are mandatory and will appear no matter what rules you add.  Excluded fields will not appear.", 'wp-csv' ); ?></i></blockquote></td></tr>
<tr><th><?php _e( "Exclude Fields", 'wp-csv' ); ?>:</th><td><textarea name="exclude_field_list" cols="70" rows="5" /><?php echo implode( ',', $exclude_field_list ); ?></textarea>
<blockquote><i><?php _e( "Control which fields are excluded from the export file.  You can enter a pattern such as 'start*' (for fields starting with 'start'), or '*end' (for fields ending with 'end'). NOTE: Some fields are mandatory and will appear no matter what you enter.  Excluded fields take precedence over included fields so you can include 'start*' and then exclude 'start_useless_field'. Separate field rules with a comma.", 'wp-csv' ); ?></i></blockquote></td></tr>
</tbody>
</table>
</div>
</div>
<input class='wpcsv-save-all-button lower' type="submit" value="<?php _e( 'Save All Settings', 'wp-csv' ); ?>" />
</form>
