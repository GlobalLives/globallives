<div id="user-<?php echo $crew_member->ID; ?>" class="crew-member">
	<a href="/profile/<?php echo $crew_member->user_login; ?>">
	 <div class="profile-thumbnail" style="margin: 0;width: 60px;"><img src="<?php the_profile_thumbnail_url($crew_member->ID,'thumbnail'); ?>"></div>
    <div class="profile-details" style="margin: 0 0 0 10px;width:160px;">
      <div><strong><?php echo $crew_member->first_name; ?> <?php echo $crew_member->last_name; ?></strong></div>
      <div><?php echo get_field($field_keys['user_occupation'],'user_'.$crew_member->ID); ?></div>
    </div>
	</a>
</div>