<div class="tab-content">
	<?php while (have_posts()) : the_post(); ?>
	<article id="content_glp" <?php post_class('front-page content-pane active'); ?>>
		<div class="page-content"><?php the_content(); ?></div>
	</article>
	<?php endwhile; ?>
	<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 20)); ?>
	<?php foreach ($participants as $participant) : ?>
		<?php $current_wp_post = get_post($participant->ID) ?>
		<article id="content_<?php echo $participant->ID; ?>" class="content-pane">
			
			<div class="page-content">
				<h2><?php echo $participant->post_title; ?> &mdash; <span class="participant-location"><?php the_field($field_keys['participant_location'], $participant->ID); ?></span></h2>
				<div class="participant-meta row">
					<div class="span4 tinyDetails">

						<img src="<?php the_field('map_image',$participant->ID); ?>" alt="<?php echo $participant->post_title; ?>" />
						
						<div class="row">
							<div class="span2">
								<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field($field_keys['participant_occupation'], $participant->ID); ?><br>
								<?php if (
								$dob = get_field($field_keys['participant_dob'], $participant->ID)) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
								<br>
								<br>
							</div><!-- .span2 -->
							<div class="span2">
								<b><?php _e('Religion','glp'); ?>:</b> <?php the_field($field_keys['participant_religion'], $participant->ID); ?><br>
								<b><?php _e('Income','glp'); ?>:</b> <?php 
								$incomes = get_field_object($field_keys['participant_income']); $income = get_field($field_keys['participant_income'], $participant->ID); echo $incomes['choices'][$income]; ?>
								<br>
								<br>
							</div><!-- .span2 -->
							<div class="span4">
								<p><?php echo $participant->post_content; ?></p>
								<a href="<?php echo get_permalink($participant->ID); ?>" class="btn btn-inverse"><i class="icon icon-play"></i> Full Story</a>
								<br>
								<br>
							</div><!-- .span4 -->
						</div><!-- .row -->
					</div><!-- .tinyDetails -->

					<div class="span8" id="video_<?php echo $participant->ID; ?>">
					<?php //loaded by ajax
						if ( $summary_video = get_field($field_keys['participant_summary_video'],$participant->ID) ) {
							query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
							get_template_part('templates/clip','summary');
							wp_reset_query();
						}
					?>
					</div><!-- .span8 -->
				</div><!-- .participant-meta -->
			</div>
		</article>

	<?php endforeach; ?>
	<div id="explore">
		<h4><?php _e('Explore the Collection','glp'); ?></h4>
		<a href="/explore/#gridview" class="btn btn-inverse"><i class="icon icon-th-large"></i> <?php _e('Grid View','glp'); ?></a>
		<a href="/explore/#mapview" class="btn btn-inverse"><i class="icon icon-globe"></i> <?php _e('Map View','glp'); ?></a>
	</div>
</div>