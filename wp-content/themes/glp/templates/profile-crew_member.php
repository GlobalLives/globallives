<div id="user-<?php echo $crew_member->ID; ?>" class="crew-member span1">
	<a href="/profile/<?php echo $crew_member->user_login; ?>">
	<div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($crew_member->ID,'thumbnail'); ?>"></div>
	</a>
</div>