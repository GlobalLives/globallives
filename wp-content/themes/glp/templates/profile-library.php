<?php
	global $profile, $field_keys;
	$profile_id = $profile->ID;
	$user_library = get_field($field_keys['user_library'], 'user_'.$profile_id);
	$library_participants = get_library_participants($profile_id);
?>
<div class="library-header">
	<h3><?php _e('My Library', 'glp'); ?></h3>
</div>
<div class="library-container row-fluid">
	<div class="library-participants span8">
<?php
	foreach( $library_participants as $participant ) {
		include(locate_template('templates/library-participant.php'));
	}
?>
	</div>
	<div class="library-filters clip-filters span4">
		<h4><?php _e('Filter Clips','glp'); ?></h4>
		<h5><?php _e('Tags in Your Library','glp'); ?></h5>
		<?php if ( $clip_tags = get_library_clip_tags($profile_id)) : foreach( $clip_tags as $clip_tag ) : ?>
		<a class="active filter" data-tag="<?php echo $clip_tag->name; ?>"><?php echo $clip_tag->name; ?></a>
		<?php endforeach; endif; ?>
		<div><button class="btn span12 clear-filters"><?php _e('Clear Filters','glp'); ?></button></div>
	</div>
</div>