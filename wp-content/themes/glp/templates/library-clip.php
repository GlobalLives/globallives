<?php
	$clip_id = $clip->ID;
	$participant = get_clip_participant($clip_id);
?>
<article class="library-clip<?php echo strip_tags(get_the_term_list($clip->ID,'clip_tags',' ',' ')); ?>">
	<div class="row">
		<div class="clip-thumbnail span4" data-clip-id="<?php echo $clip->ID; ?>">
			<a href="<?php the_clip_url($clip_id); ?>"><img src="<?php the_clip_thumbnail($clip->ID); ?>"></a>
		</div>
		<div class="">
			<h5 class="clip-title"><?php echo $participant->post_title; ?> <small><?php _e('Part','glp'); ?> <?php the_clip_position($clip_id); ?></small></h5>
			<p class="clip-duration"><?php the_field('duration', $clip_id); ?></p>
			<p class="clip-tags"><?php _e('Tags:','glp'); ?> <?php the_clip_tags($clip_id); ?></p>
			<?php if ($download_url = get_field('download_url',$clip_id)) : ?><a class="" href="<?php echo $download_url; ?>"><i class="icon icon-white icon-new-window"></i> Get Files</a><?php endif; ?>

			<?php if ($can_edit) : ?><a class="btn-toggle" data-toggle-type="library" data-user-id="<?php echo $profile_id; ?>" data-clip-id="<?php echo $clip_id; ?>"><?php echo apply_filters('clip_toggle_queue_status', $text, $clip_id, $profile_id); ?></a><?php endif; ?>

			<?php #$item_id = $clip_id; include(locate_template('templates/link-queue.php')); ?>
		</div>
	</div>
</article>
