<?php
	global $profile, $current_user;

	// Save user data if it's being submitted...
	if('create' == $_GET['mode']) {
		$user_firstname		= $_GET['user_firstname'];
		$user_lastname		= $_GET['user_lastname'];
		$user_occupation	= $_GET['user_occupation'];
		$user_location		= $_GET['user_location'];

		wp_update_user(array(
			'ID' => $current_user->ID,
			'first_name' => $user_firstname,
			'last_name' => $user_lastname
		));
		update_field('user_occupation',	$user_occupation,	'user_'.$current_user->ID);
		update_field('user_location',	$user_location,		'user_'.$current_user->ID);
	}
	
	// Check for all the required fields...
	if (!is_profile_created($current_user->ID)) :
?>

<article id="user-<?php echo $profile->ID; ?>" class="container">
	<div class="profile-form row span8 offset2">
		<?php gravity_form('Create a Profile', false, true, false, null, false); ?>
	</div>
</article>

<?php else : ?>

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
			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('About','glp'); ?></h4>
				<p class="profile-bio"><?php echo $profile->description; ?></p>
				<?php if ($profile->user_url) : ?><p class="profile-website"><b><?php echo $profile->nickname; ?><?php _e("'s website",'glp'); ?>:</b><br><?php echo $profile->user_url; ?></p><?php endif; ?>
				<hr>
				<p class="profile-activity-buttons">
					<span class="span2"><?php _e('All Activity','glp'); ?></span>
					<a class="span2" href=""><i class="icon icon-film"></i> Shoots</a>
					<a class="span2" href=""><i class="icon icon-comment"></i> Comments</a>
					<a class="span2" href=""><i class="icon icon-tag"></i> Tags</a>
					<a class="span2" href=""><i class="icon icon-user"></i> Mentions</a>
					<a class="span2" href=""><i class="icon icon-book"></i> Bookmarks</a>
					<a class="span2" href=""><i class="icon icon-heart"></i> Favorites</a>
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
<?php endif; ?>