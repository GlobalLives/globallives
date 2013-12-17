<?php global $participants; ?>
<div id="gridview" class="view"><div class="container">
	<?php foreach ($participants as $participant) : ?>
		<article id="participant-<?php echo $participant->ID; ?>" class="participant-grid<?php echo get_field('proposed',$participant->ID) ? ' hide' : ''; ?>"><a href="<?php echo get_permalink($participant->ID); ?>">
			<div class="participant-meta">
				<h3><?php echo $participant->post_title; ?></h3>
				<p><?php the_field('location',$participant->ID); ?></p>
			</div>
			<img src="<?php the_participant_thumbnail_url( $participant->ID, 'medium' ); ?>">
		</a></article>
	<?php endforeach; ?>
</div></div>