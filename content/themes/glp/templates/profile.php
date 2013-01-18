<?php global $profile; ?>
<article id="user-<?php echo $profile->ID; ?>" class="container">
	<header class="row">
		<div class="profile-header span9 offset3">
			<div class="profile-header-inner">
				<h2 class="profile-location"><?php the_field('location','user_'.$profile->ID); ?></h2>
				<h1 class="profile-name"><?php echo $profile->display_name; ?></h1>
				<div class="profile-username">@<?php echo $profile->user_login; ?></div>
			</div>
		</div>
	</header>

	<div class="profile-container row">
		<div class="profile-sidebar span3">
			<div class="profile-siderbar-inner">
				<div class="profile-thumbnail"><?php echo get_avatar( $profile->ID, 300 ); ?></div>
				<p><b><?php _e('Member since','glp'); ?>:</b> <?php echo date("F Y", strtotime($profile->user_registered)); ?></p>
				<p><b><?php _e('Last activity','glp'); ?>:</b> <?php echo '?'; ?></p>
				<hr>
				<?php if ($interests = get_field('interests','user_'.$profile->ID)) : ?>
				<p><b><?php _e('Interested in','glp'); ?>:</b><br><?php foreach( $interests as $interest ) : ?><li><?php echo $interest; ?></li><?php endforeach; ?></p>
				<hr>
				<?php endif; ?>
				<?php if ($expertises = get_field('expertise','user_'.$profile->ID)) : ?>
				<p><b><?php _e('Expertise','glp'); ?>:</b><br><?php foreach( $expertises as $expertise ) : ?><li><?php echo $expertise; ?></li><?php endforeach; ?></p>
				<hr>
				<?php endif; ?>	
				<p><b><?php _e('Previous shoots','glp'); ?>:</b><br></p>
			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('About','glp'); ?></h4>
				<p class="profile-bio"><?php echo $profile->description; ?></p>
				<?php if ($profile->user_url) : ?><p class="profile-website"><b><?php echo $profile->display_name; ?><?php _e("'s website",'glp'); ?>:</b><br><?php echo $profile->user_url; ?></p><?php endif; ?>
				<hr>
				<p class="profile-activity-buttons">
					<span class="span2"><?php _e('All Activity','glp'); ?></span>
					<a class="span2" href=""><i class="icon icon-comment"></i> Comments</a>
					<a class="span2" href=""><i class="icon icon-user"></i> Mentions</a>
					<a class="span2" href=""><i class="icon icon-list"></i> Queue</a>
				</p>
				<hr>
				<h4><?php _e('All Recent Activity','glp'); ?></h4>
				<ul class="profile-activity">
				<?php foreach( get_profile_activities( $profile->ID ) as $activity ) : $activity_user = get_userdata( $activity['activity_user'] ); ?>
					<li class="activity <?php echo $activity['activity_type']; ?> row">
						<div class="activity-thumbnail span2"><?php echo get_avatar($activity['activity_user']); ?></div>
						<div class="activity-meta">
							<span class="activity-username">@<?php echo $activity_user->user_login; ?></span> 
							<?php echo $activity['activity_description']; ?> 
							<?php echo human_time_diff( $activity['activity_timestamp'], current_time('timestamp') ); ?> ago.
						</div>
						<div class="activity-content"><?php echo $activity['activity_content']; ?></div>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</article>