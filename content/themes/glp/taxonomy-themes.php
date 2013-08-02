<?php
	$theme = $wp_query->queried_object;
	$participants = get_posts(array('post_type' => 'participant', 'numberposts' => -1, 'tax_query' => array(array('taxonomy' => 'themes', 'field' => 'slug', 'terms' => $theme->slug))));
?>

<div class="container">
	<div id="home" class="row">
		<?php foreach ( $participants as $participant ) : ?>
			<div class="theme-video">
			<?php
				if ( $summary_video = get_field('summary_video',$participant->ID) ) {
					query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
					get_template_part('templates/clip','grid');
					wp_reset_query();
				}
			?>
			</div>
		<?php endforeach; ?>
	</div>
	<div id="stage"></div>
</div>

<div class="theme-details">
	<div class="theme-details-inner container">
		<div class="row">
		<div class="span6">
			<h4><?php _e('Featured Participants'); ?></h4>
			<div class="row">
			<?php foreach ( $participants as $participant ) : ?>
				<div class="span2">
					<h5><?php echo $participant->post_title; ?></h5>
					<p><?php echo get_field('location',$participant->ID); ?></p>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<div class="span6">
			<h4><?php _e('Comments'); ?> ([#])</h4>
			[COMMENTS]
		</div>
	</div>
</div>
</div>
