<?php
	global $current_user, $field_keys; $user_id = $current_user->ID;
	$user_firstname = $current_user->first_name ? $current_user->first_name : explode(' ', $current_user->display_name, 2)[0];
	$user_lastname = $current_user->last_name ? $current_user->last_name : explode(' ', $current_user->display_name, 2)[1];
	$user_url = $current_user->user_url;
	$user_occupation = get_user_meta($user_id, 'occupation', true);
	$user_location = get_user_meta($user_id, 'location', true);
	$user_description = get_user_meta($user_id, 'description', true);
	$user_connections = get_field($field_keys['user_connections'], 'user_'.$user_id);
?>
	<div class="row-fluid">
		<div class="span6">
			<p><input type="text" name="first_name" value="<?php echo $user_firstname; ?>" placeholder="<?php _e('First Name','glp'); ?>" required></p>
			<p><input type="text" name="last_name" value="<?php echo $user_lastname; ?>"placeholder="<?php _e('Last Name','glp'); ?>" required></p>
			<p><input type="text" name="user_occupation" value="<?php echo $user_occupation; ?>" placeholder="<?php _e('Occupation','glp'); ?>" required></p>
			<p><input type="text" name="user_location" id="user_location" value="<?php echo $user_location; ?>" placeholder="<?php _e('Location','glp'); ?>" required></p>
			<p><?php _e('A short bio (optional)','glp'); ?></p>
			<textarea name="description" id="user_description" maxlength="500"><?php echo $user_description; ?></textarea>
		</div>
		<div class="span6">
			<img class="user_thumbnail" src="<?php the_profile_thumbnail_url($user_id, 'medium'); ?>">
			<input type="file" name="user_avatar" id="user_avatar">
		</div>
	</div>
	<hr>
	<div class="user_connections row-fluid">
		<p><?php _e('Connect your social networks (optional)','glp'); ?></p>
		<div class="span6"><br>
			<i class="fa fa-twitter span1"></i>
			<span class="span6 text-right">twitter.com/ </span>
			<input name="user_connections[0][twitter]" class="span5" type="text" placeholder="username" value="<?php echo $user_connections[0]['twitter']; ?>">

			<i class="fa fa-facebook span1"></i>
			<span class="span6 text-right">facebook.com/ </span>
			<input name="user_connections[0][facebook]" class="span5" type="text" placeholder="username" value="<?php echo $user_connections[0]['facebook']; ?>">

			<i class="fa fa-google-plus span1"></i>
			<span class="span6 text-right">plus.google.com/ </span>
			<input name="user_connections[0][google_plus]" class="span5" type="text" placeholder="username" value="<?php echo $user_connections[0]['google_plus']; ?>">
		</div>
		<div class="span5"><br>
			<i class="fa fa-youtube-play span1"></i>
			<span class="span6 text-right">youtube.com/user/ </span>
			<input name="user_connections[0][youtube]" class="span5" type="text" placeholder="username" value="<?php echo $user_connections[0]['youtube']; ?>">

			<i class="fa fa-instagram span1"></i>
			<span class="span6 text-right">instagram.com/ </span>
			<input name="user_connections[0][instagram]" class="span5" type="text" placeholder="username" value="<?php echo $user_connections[0]['instagram']; ?>">

			<i class="fa fa-link span2"></i>
			<input name="user_url" class="span10" type="text" placeholder="<?php _e('Your Website','glp'); ?>" value="<?php echo $user_url; ?>">
		</div>
	</div>