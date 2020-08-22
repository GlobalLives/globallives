<?php global $participants, $field_keys; ?>
<div id="gridview" class="view">
	<div class="container">
	<?php foreach ($participants as $participant) : ?>
		<article id="participant-<?php echo $participant->ID; ?>" class="participant-grid"><a href="<?php echo get_permalink($participant->ID); ?>">
			<div class="participant-meta">
				<h3><?php echo $participant->post_title; ?></h3>
				<p>
					<?php the_field($field_keys['participant_occupation'],$participant->ID); ?>
					<?php _e('in','glp'); ?>
					<?php the_field($field_keys['participant_location'],$participant->ID); ?>
				</p>
				<p>
					<?php if ($themes = get_the_terms($participant->ID,'themes')) : ?>
					<b><?php _e('Themes: ','glp'); ?></b>
					<?php $theme_names = array(); foreach($themes as $theme) { $theme_names[] = $theme->name; }; echo implode(', ', $theme_names); ?>
					<? endif; ?>
				</p>
			</div>
			<img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>" class="thumbnail">
		</a></article>
	<?php endforeach; ?>
</div>
</div>