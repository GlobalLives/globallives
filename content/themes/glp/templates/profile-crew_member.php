<?php global $crew_member; ?>
<article id="user-<?php echo $crew_member->ID; ?>" class="span2">
	<a href="/profile/<?php echo $crew_member->user_login; ?>">
	<div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($crew_member->ID,'medium'); ?>"></div>
	<p class="profile-name"><?php echo $crew_member->nickname; ?></p>
	</a>
</article>