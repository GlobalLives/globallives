<?php global $current_user, $field_keys; $user_id = $current_user->ID; ?>
			<p><?php _e('(check all that apply)','glp'); ?></p>
			<div class="row-fluid">
<?php
	$user_sources_obj = get_field_object($field_keys['user_sources'], 'user_' . $user_id);
	$source_cols = 3; $i = 0;
	foreach ($user_sources_obj['choices'] as $user_source) {
		$name = 'user_sources[' . $user_source . ']';
?>
		<?php if ($i % $source_cols == 0) { ?><div class="span4"><?php } ?>
					<label class="checkbox"><input type="checkbox" name="<?php echo $name; ?>"> <?php echo $user_source; ?></label>
		<?php if ($i % $source_cols == ($source_cols - 1) || $i == count($user_sources_obj['choices']) - 1) { ?></div><?php } ?>
<?php
	$i++;
	}
?>
			</div>
			<hr>
			<div class="row-fluid">
				<h5><?php _e('Subscribe to our mailing list! Spread the word!', 'glp'); ?></h5>
				<p><?php _e('Get updates on new films, upcoming events and more. (We send about 6 emails a year.)', 'glp'); ?></p>
				<label class="checkbox"><input name="user_subscribe" type="checkbox"> <?php _e('Yes, Sign me up!', 'glp'); ?></label>
			</div>