<?php
	$theme = $wp_query->queried_object;
	$participants = get_posts(array('post_type' => 'participant', 'numberposts' => -1, 'tax_query' => array(array('taxonomy' => 'themes', 'field' => 'slug', 'terms' => $theme->slug))));
	$referral_id = $_GET['ref'];
	$download_urls = array();
?>

<div class="theme-header navbar">
	<div class="nav-explore-inner navbar-inner container">
		<ul class="nav">
			<li class="theme-breadcrumb span8"><?php if($referral_id AND $referral = get_post($referral_id)) : ?><a href="<?php echo get_permalink($referral->ID); ?>">&larr;<?php _e('Return to','glp'); ?> <?php echo $referral->post_title; ?></a><?php endif; ?></li>
			<li class="theme-filters span4 pull-right text-right">
				<?php if ($allthemes = get_terms('themes')) : ?>
				<?php _e('Themes','glp'); ?>
				<select name="theme" id="theme-select">
					<?php foreach( $allthemes as $alltheme ) : ?>
					<option value="<?php echo $alltheme->slug; ?>"<?php if ($theme->slug == $alltheme->slug) : ?> selected="selected"<?php endif; ?>><?php echo $alltheme->name; ?></option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
			</li>
		</ul>
	</div>
</div> 

<div class="theme-videos container">
	<div id="home" class="row">
		<?php foreach ( $participants as $participant ) : ?>
			<div class="theme-video">
			<?php
				if ($summary_video = get_field('summary_video',$participant->ID)) {
					query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
					get_template_part('templates/clip','grid');
					wp_reset_query();
					if ($download_url = get_field('download_url')) { $download_urls[] = $download_url; }
				}
			?>
			</div>
		<?php endforeach; ?>
	</div>
	<div id="stage"></div>
	<p class="buttons">
		<?php $uploads = wp_upload_dir(); $zip_filename = '/themes/'.$theme->slug.'.zip'; if (create_zip( $download_urls, $uploads['basedir'].$zip_filename )) : ?><a href="<?php echo $uploads['baseurl'].$zip_filename; ?>" class="btn"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
		<a class="btn btn-play-all"><i class="icon icon-white icon-play"></i> <?php _e('Play all','glp'); ?></a>
	</p>
</div>

<div class="theme-details">
	<div class="theme-details-inner container">
		<h4><?php _e('Featured Participants'); ?></h4>
		<?php
			$total_participants = count($participants);
			$participants_per_row = 6;
			foreach ( $participants as $i => $participant ) : ?>
		<?php if ($i % $participants_per_row == 0) : ?><div class="row"><?php endif; ?>
			<div class="participant-mini span2"><a href="<?php echo get_permalink($participant->ID); ?>">
				<div class="participant-thumbnail"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'thumbnail' ); ?>"></div>
				<h5 class="participant-name"><?php echo $participant->post_title; ?></h5>
				<p class="participant-location"><?php echo get_field('location',$participant->ID); ?></p>
			</a></div>
		<?php if ($i == $total_participants - 1 || $i % $participants_per_row == ($participants_per_row - 1)) : ?></div><?php endif; ?>
		<?php endforeach; ?>
		</div>
	</div>
</div>
</div>
