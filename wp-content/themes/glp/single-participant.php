<?php global $field_keys; while (have_posts()) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('participant-detail'); ?> data-participant_id="<?php the_ID(); ?>">

	<?php if ($participants = get_related_participants(get_the_ID())) : ?>
	<div class="participant-detail-map">	
		<?php get_template_part('templates/view','map'); ?>
		<?php if ($themes = get_the_terms(get_the_ID(),'themes')) { get_template_part('templates/nav','themes'); } ?>

		<div class="handle">
			<div class="handle-inner container">
				<h5><?php _e('Discover'); ?> <?php echo $post->post_title; ?>'s <?php _e('shared themes'); ?></h5>
				<a class="btn btn-inverse"><i class="icon icon-globe"></i>
					<span><?php _e('Show map'); ?> &#9652;</span>
					<span class="hide"><?php _e('Collapse map'); ?> &#9662;</span>
				</a>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div id="stage" class="participant-detail-video container">
		<?php
			if ($_GET['clip_id']) {
				$clip_id = $_GET['clip_id'];
			} elseif ($summary_video = get_field($field_keys['participant_summary_video'], get_the_ID())) {
				$clip_id = $summary_video[0]->ID;
			}
			if ($clip_id) {
				query_posts(array( 'post_type' => 'clip', 'p' => $clip_id, 'posts_per_page' => 1 ));
				get_template_part('templates/clip','stage');
				wp_reset_query();
			}
		?>
	</div>
	
	<div class="participant-detail-content">
		<div class="container">
			<div class="row">
	
				<div class="span6">
					<header>
						<h2 class="participant-title"><span class="participant-name"><?php the_title(); ?> </span> &mdash; <span class="participant-location"><?php the_field($field_keys['participant_location'], get_the_ID()); ?></span></h2>
					</header>
					<div class="participant-meta row">
						<div class="span3">
							<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field($field_keys['participant_occupation'], get_the_ID()); ?><br>
							<?php if ($dob = get_field($field_keys['participant_dob'], get_the_ID())) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
						</div>
						<div class="span3">
							<b><?php _e('Religion','glp'); ?>:</b> <?php the_field($field_keys['participant_religion'], get_the_ID()); ?><br>
							<b><?php _e('Income','glp'); ?>:</b> <?php 
							$incomes = get_field_object($field_keys['participant_income']); $income = get_field($field_keys['participant_income'], get_the_ID()); echo $incomes['choices'][$income]; ?>
						</div>
					</div>
					<div class="participant-content">
						<?php the_content(); ?>
					</div>
					<?php if ( $crew_members = get_participant_crew_members( get_the_ID() )) : ?>
					<div class="participant-crew row">
						<h3 class="span6">Crew Members</h3>
						<?php foreach ( $crew_members as $crew_member ) : ?>
							<?php include(locate_template('templates/profile-crew_member.php')); ?>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="span6"><div class="row">
					<div class="participant-clips span4">
						<h3><?php _e('Footage','glp'); ?> (<?php $clips = get_field($field_keys['participant_clips'], get_the_ID()); echo count($clips); ?>)</h3>
						<div class="participant-clips-scrollbox">
						<?php if ($clips) : ?>
							<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
							<a class="btn-toggle-all" data-list-id="<?php the_ID(); ?>" data-user-id="<?php echo $current_user->ID; ?>"><?php echo apply_filters('clip_toggle_queue_list_status', $text, $current_user->ID); ?></a>
							<?php endif; ?>
						<?php foreach( $clips as $clip_index => $clip ) : ?>
								<?php get_template_part('templates/clip','listing'); ?>
						<?php endforeach; else : ?>
								<p class="alert alert-error"><?php _e('No clips for this participant.','glp'); ?></p>
						<?php endif; ?>
						</div>
					</div>
					<div class="span2"><div class="participant-filter-clips">
						<h4><?php _e('Filter Clips','glp'); ?></h4>
						<h5><?php _e('By Popular Tags','glp'); ?></h5>
						<?php if ( $clip_tags = get_participant_clip_tags( get_the_ID() )) : foreach( $clip_tags as $clip_tag ) : ?>
						<a class="active filter" data-tag="<?php echo $clip_tag->name; ?>"><?php echo $clip_tag->name; ?></a>
						<?php endforeach; endif; ?>
					</div></div>
				</div></div>
		
			</div>
		</div>
	</div>
</article>
<?php endwhile; ?>