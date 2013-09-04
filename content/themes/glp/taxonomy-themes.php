<?php
	$theme = $wp_query->queried_object;
	$participants = get_posts(array('post_type' => 'participant', 'numberposts' => -1, 'tax_query' => array(array('taxonomy' => 'themes', 'field' => 'slug', 'terms' => $theme->slug))));
	$referral_id = $_GET['ref'];
?>

<div class="theme-header container">
	<div class="row">
		<div class="theme-breadcrumb span8"><?php if($referral_id AND $referral = get_post($referral_id)) : ?><a href="<?php echo get_permalink($referral->ID); ?>">&larr;<?php _e('Return to','glp'); ?> <?php echo $referral->post_title; ?></a><?php endif; ?></div>
		<div class="theme-filters span4 text-right">
			<?php if($allthemes = get_terms('themes')) : ?>
			<?php _e('Themes','glp'); ?>
			<select name="theme" id="theme-select">
				<?php foreach( $allthemes as $alltheme ) : ?>
					<option value="<?php echo $alltheme->slug; ?>"<?php if($theme->slug == $alltheme->slug) : ?> selected="selected"<?php endif; ?>><?php echo $alltheme->name; ?></option>
				<?php endforeach; ?>
			</select>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="theme-videos container">
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
				<div class="participant-mini span2"><a href="<?php echo get_permalink($participant->ID); ?>">
					<div class="participant-thumbnail"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'thumbnail' ); ?>"></div>
					<h5 class="participant-name"><?php echo $participant->post_title; ?></h5>
					<p class="participant-location"><?php echo get_field('location',$participant->ID); ?></p>
				</a></div>
			<?php endforeach; ?>
			</div>
		</div>
		<div class="span6">
			<h4><?php _e('Comments'); ?> ([#])</h4>
			<?php
				$wp_query->is_single = true;
				$withcomments = 1;
				comments_template();
				$wp_query->is_single = false;
			?>
		</div>
	</div>
</div>
</div>
