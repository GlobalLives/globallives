<?php global $crew_member; ?>
<article id="user-<?php echo $crew_member->ID; ?>" class="span2">
	<a href="/profile/<?php echo $crew_member->user_login; ?>">
	<div class="profile-thumbnail"><?php echo get_avatar( $crew_member->ID, 300 ); ?></div>
	<p class="profile-name"><?php echo $crew_member->nickname; ?></p>
	</a>
</article>