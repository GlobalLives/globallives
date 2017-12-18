<?php global $profile, $current_user, $field_keys; ?>
<article id="user-<?php echo $profile->ID; ?>" class="container">
	<header class="row">
		<div class="profile-header span9 offset3">
			<div class="profile-header-inner">
				<?php if ($current_user->ID == $profile->ID) : ?><a class="edit-profile" href="/profile?mode=edit"><?php _e('Edit','glp'); ?> <i class="icon icon-white icon-edit"></i></a><?php endif; ?>
				<h1 class="profile-name"><?php echo $profile->first_name; ?> <?php echo $profile->last_name; ?></h1>
				<p class="profile-location">
					<?php if ($user_occupation = get_field($field_keys['user_occupation'],'user_'.$profile->ID)) : ?><b><?php echo $user_occupation; ?></b><?php endif; ?>
					<?php if ($user_location = get_field($field_keys['user_location'],'user_'.$profile->ID)) : ?> <?php _e('in','glp'); ?> <b><?php echo $user_location; ?></b><?php endif; ?>
				</p>
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

				<?php if (get_field($field_keys['user_skills'],'user_'.$profile->ID)) : ?>
				<ul>
					<li><b><?php _e('Volunteer Skills','glp'); ?></b></li>
				<?php while (has_sub_field($field_keys['user_skills'],'user_'.$profile->ID)) : ?>
					<?php if (get_sub_field('skill_name')) : ?><li><span class="skill_name"><?php the_sub_field('skill_name'); ?></span> <span class="skill_level"><?php $skill_level = get_sub_field('skill_level'); for ($i = 0; $i < $skill_level; $i++) :?>&bull;<?php endfor; ?></span></li><?php endif; ?>
				<?php endwhile; ?>
				</ul>
				<?php endif; ?>

				<?php if (get_field($field_keys['user_languages'],'user_'.$profile->ID)) : ?>
				<hr>
				<p>
					<b><?php _e('Languages Spoken','glp'); ?></b><br>
				<?php while (has_sub_field($field_keys['user_languages'],'user_'.$profile->ID)) : ?>
					<?php if (get_sub_field('language_name')) : ?><span class="skill_name"><?php the_sub_field('language_name'); ?></span> <span class="skill_level"><?php $language_level = get_sub_field('language_level'); for ($i = 0; $i < $language_level; $i++) :?>&bull;<?php endfor; ?></span><br><?php endif; ?>
				<?php endwhile; ?>
				</p>
				<?php endif; ?>

				<?php if (get_field($field_keys['user_contact'],'user_'.$profile->ID)) : ?>
				<hr>
				<p>
					<b><?php _e('Contact Information','glp'); ?></b><br>
				<?php while (has_sub_field($field_keys['user_contact'],'user_'.$profile->ID)) : ?>
					<?php if (get_sub_field('contact_information') !== '') : ?>
					<i class="fa fa-<?php echo strtolower(get_sub_field('contact_channel')); ?>"></i>
					<?php the_sub_field('contact_information'); ?><br>
					<?php endif; ?>
				<?php endwhile; ?>
				</p>
				<?php endif; ?>

			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('Bio','glp'); ?></h4>
				<p><?php echo $profile->description; ?></p>
				<?php if ($profile->user_url) : ?>
					<h4><?php _e('Website','glp'); ?></h4>
					<p><?php echo '<a target="_blank" href="'.$profile->user_url.'">'.$profile->user_url.'</a>'; ?></p>
				<?php endif; ?>
				<?php if ($collaborators = get_profile_collaborators($profile->ID)) : ?>
					<div class="profile-collaborators">
					<h4><?php _e('Collaborators','glp'); ?> <small>(<?php echo count($collaborators)-1; ?>)</small></h4>
					<?php foreach ($collaborators as $crew_member) : ?>
						<?php if($profile->ID!=$crew_member->ID) { ?>
							<?php include(locate_template('templates/profile-crew_member.php')); ?>
						<?php } ?>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php 
				/* Reintroducing Profile Activity Statistics
				<p class="profile-activity-buttons">
					<span class="span1"><?php _e('Activity','glp'); ?></span>
					<a class="" href=""><i class="fa fa-video-camera"></i> Shoots</a>
					<a class="" href=""><i class="fa fa-comment"></i> Comments</a>
					<a class="" href=""><i class="fa fa-tag"></i> Tags</a>
					<a class="" href=""><i class="fa fa-twitter"></i> @ Mentions</a>
					<a class="" href=""><i class="fa fa-bookmark"></i> Bookmarks</a>
					<a class="" href=""><i class="fa fa-heart"></i> Favorites</a>
				</p>
				<ul class="profile-activity">
				<?php foreach( get_profile_activities( $profile->ID ) as $activity ) : $activity_user = get_userdata( $activity['activity_user'] ); ?>
					<li class="activity <?php echo $activity['activity_type']; ?> row">
						<div class="activity-thumbnail span1"><img src="<?php the_profile_thumbnail_url($activity['activity_user']); ?>"></div>
						<div class="activity-meta span7">
							<i class="fa fa-<?php echo $activity['activity_icon']; ?>"></i>
							<span class="activity-username"><?php the_fullname($activity_user->ID); ?></span> 
							<?php echo $activity['activity_description']; ?> 
							<?php echo human_time_diff( $activity['activity_timestamp'], current_time('timestamp') ); ?> ago.
						</div>
						<div class="activity-content span6"><?php echo $activity['activity_content']; ?></div>
					</li>
				<?php endforeach; ?>
				</ul> */
				?>
			</div>
		</div>
	</div>
</article>