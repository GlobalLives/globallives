<?php
	global $current_user; $user_id = $current_user->ID;
	$user_firstname = $current_user->first_name ? $current_user->first_name : explode(' ', $current_user->display_name, 2)[0];
	$user_lastname = $current_user->last_name ? $current_user->last_name : explode(' ', $current_user->display_name, 2)[1];
	$user_occupation = get_user_meta($user_id, 'occupation', true);
	$user_location = get_user_meta($user_id, 'location', true);
	$user_description = get_user_meta($user_id, 'description', true);
?>
	<div class="row-fluid">
		<div class="span6">
			<p><input type="text" name="first_name" value="<?php echo $user_firstname; ?>" placeholder="<?php _e('First Name','glp'); ?>" required></label></p>
			<p><input type="text" name="last_name" value="<?php echo $user_lastname; ?>"placeholder="<?php _e('Last Name','glp'); ?>" required></label></p>
			<p><input type="text" name="user_occupation" value="<?php echo $user_occupation; ?>" placeholder="<?php _e('Occupation','glp'); ?>" required></label></p>
			<p><input type="text" name="user_location" id="user_location" value="<?php echo $user_location; ?>" placeholder="<?php _e('Location','glp'); ?>" required></label></p>
			<p><?php _e('A short bio (optional)','glp'); ?></p>
			<textarea name="description" id="user_description" maxlength="140"><?php echo $user_description; ?></textarea>
		</div>
		<div class="span6">
			<img class="user_thumbnail" src="<?php the_profile_thumbnail_url($user_id, 'medium'); ?>">
			<input type="file" name="user_avatar" id="user_avatar">
		</div>
	</div>