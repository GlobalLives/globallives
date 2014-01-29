<?php global $profile, $current_user; ?>
<article id="user-<?php echo $profile->ID; ?>" class="container">
	<header class="row">
		<div class="profile-header span9 offset3">
			<div class="profile-header-inner">
				<?php if ($current_user->ID == $profile->ID) : ?><a class="edit-profile" href="/profile?profile-edit=1"><?php _e('Edit','glp'); ?> <i class="icon icon-white icon-edit"></i></a><?php endif; ?>
				<h1 class="profile-name"><?php echo $profile->first_name; ?> <?php echo $profile->last_name; ?></h1>
				<p class="profile-location"><b><?php the_field('user_occupation','user_'.$profile->ID); ?></b> <?php _e('in','glp'); ?> <b><?php the_field('user_location','user_'.$profile->ID); ?></b></p>
				<div class="profile-username">@<?php echo $profile->user_login; ?></div>
			</div>
		</div>
	</header>

	<div class="profile-container row">
		<div class="profile-sidebar span3">
			<div class="profile-sidebar-inner">
				<div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($profile->ID,'medium'); ?>"></div>
				<p><b><?php _e('Member since','glp'); ?>:</b> <?php echo date("F Y", strtotime($profile->user_registered)); ?></p>
				<p><b><?php _e('Last activity','glp'); ?>:</b> <?php echo human_time_diff( get_profile_last_active( $profile->ID ), current_time('timestamp') ); ?> ago.</p>
				<hr>

				<?php if (get_field('user_skills','user_'.$profile->ID)) : ?>
				<p>
					<b><?php _e('Skills','glp'); ?></b><br>
				<?php while (has_sub_field('user_skills','user_'.$profile->ID)) : ?>
					<?php if (get_sub_field('skill_name')) : ?><span class="skill_name"><?php the_sub_field('skill_name'); ?></span> <span class="skill_level"><?php $skill_level = get_sub_field('skill_level'); for ($i = 0; $i < $skill_level; $i++) :?>&bull;<?php endfor; ?></span><br><?php endif; ?>
				<?php endwhile; ?>
				</p>
				<?php endif; ?>

				<?php if (get_field('user_languages','user_'.$profile->ID)) : ?>
				<p>
					<b><?php _e('Languages','glp'); ?></b><br>
				<?php while (has_sub_field('user_languages','user_'.$profile->ID)) : ?>
					<?php if (get_sub_field('language_name')) : ?><span class="skill_name"><?php the_sub_field('language_name'); ?></span> <span class="skill_level"><?php $language_level = get_sub_field('language_level'); for ($i = 0; $i < $language_level; $i++) :?>&bull;<?php endfor; ?></span><br><?php endif; ?>
				<?php endwhile; ?>
				</p>
				<?php endif; ?>

				<?php /*
				<?php if ($interests = get_field('interests','user_'.$profile->ID)) : ?>
				<p><b><?php _e('Interested in','glp'); ?>:</b><br><?php foreach( $interests as $interest ) : ?><li><?php echo $interest; ?></li><?php endforeach; ?></p>
				<hr>
				<?php endif; ?>
				<?php if ($expertises = get_field('expertise','user_'.$profile->ID)) : ?>
				<p><b><?php _e('Expertise','glp'); ?>:</b><br><?php foreach( $expertises as $expertise ) : ?><li><?php echo $expertise; ?></li><?php endforeach; ?></p>
				<hr>
				<?php endif; ?>	
				<?php if ($shoots = get_field('shoots','user_'.$profile->ID)) : ?>
				<p><b><?php _e('Previous shoots','glp'); ?>:</b><br><?php foreach( $shoots as $shoot ) : ?><li>
					<div class="participant-thumbnail"><img src="<?php the_participant_thumbnail_url( $shoot->ID, 'thumbnail' ); ?>"></div>
					<h5 class="participant-title"><?php echo get_the_title($shoot->ID); ?></h5>
					<span class="participant-location"><?php the_field('location',$shoot->ID); ?></span>
				</li><?php endforeach; ?></p>
				<?php endif; ?>
				*/ ?>
			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('About','glp'); ?></h4>
				<p><?php echo $profile->description; ?></p>
			<?php if ($profile->user_url) : ?>
				<h4><?php _e('Website','glp'); ?></h4>
				<p><?php echo $profile->user_url; ?></p>
			<?php endif; ?>
				<hr>
				<p class="profile-activity-buttons">
					<span class="span2"><?php _e('All Activity','glp'); ?></span>
					<a class="span1 text-center" href=""><i class="icon icon-film"></i><br>Shoots</a>
					<a class="span1 text-center" href=""><i class="icon icon-comment"></i><br>Comments</a>
					<a class="span1 text-center" href=""><i class="icon icon-tag"></i><br>Tags</a>
					<a class="span1 text-center" href=""><i class="icon icon-user"></i><br>Mentions</a>
					<a class="span1 text-center" href=""><i class="icon icon-book"></i><br>Bookmarks</a>
					<a class="span1 text-center" href=""><i class="icon icon-heart"></i><br>Favorites</a>
				</p>
				<hr>
				<h4><?php _e('All Recent Activity','glp'); ?></h4>
								
				<ul class="profile-activity">
				<?php foreach( get_profile_activities( $profile->ID ) as $activity ) : $activity_user = get_userdata( $activity['activity_user'] ); ?>
					<li class="activity <?php echo $activity['activity_type']; ?> row">
						<div class="activity-thumbnail span2"><img src="<?php the_profile_thumbnail_url($activity['activity_user']); ?>"></div>
						<div class="activity-meta span6">
							<span class="activity-username">@<?php echo $activity_user->user_login; ?></span> 
							<?php echo $activity['activity_description']; ?> 
							<?php echo human_time_diff( $activity['activity_timestamp'], current_time('timestamp') ); ?> ago.
						</div>
						<div class="activity-content span6"><?php echo $activity['activity_content']; ?></div>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</article>