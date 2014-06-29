<?php global $clip_index, $clip; setup_postdata($clip); ?>
<article class="participant-clip-listing<?php echo strip_tags(get_the_term_list($clip->ID,'clip_tags',' ',' ')); ?>">
	<div class="row">
		<div class="clip-thumbnail span2" data-clip-id="<?php echo $clip->ID; ?>">
			<img src="<?php the_clip_thumbnail($clip->ID); ?>">
		</div>
		<div class="">
			<h5 class="clip-title"><?php the_title();?> <small>(<?php _e('Part ','glp'); echo $clip_index + 1; ?>)</small></h5>
			<p class="clip-duration"><?php the_field('duration',$clip->ID); ?></p>
			<?php if ($download_url = get_field('download_url',$clip->ID)) : ?><a class="" href="<?php echo $download_url; ?>"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
			<?php $item_id = $clip->ID; include(locate_template('templates/link-queue.php')); ?>
		</div>
	</div>
</article>
