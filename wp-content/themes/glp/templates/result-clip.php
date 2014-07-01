<?php $clip_id = $clip->ID; ?>
<article id="clip-<?php echo $clip_id; ?>" data-participant="<?php echo $participant_id; ?>" class="result result-clip row-fluid hide">
	<div class="clip-thumbnail span3 offset2">
		<a href="<?php the_clip_url($clip_id); ?>"><img src="<?php the_clip_thumbnail($clip->ID); ?>"></a>
	</div>
	<div class="span7">
		<h5 class="clip-title"><a href="<?php the_clip_url($clip_id); ?>"><?php echo $participant->post_title; ?> <small><?php _e('Part','glp'); ?> <?php the_clip_position($clip_id); ?></small></a></h5>
		<p class="clip-duration"><?php the_field($field_keys['clip_duration'], $clip_id); ?></p>
		<p class="clip-tags"><b><i class="fa fa-tag"></i></b> <?php the_clip_tags($clip_id); ?></p>
	</div>
</article>