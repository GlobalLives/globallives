<div id="user-<?php echo $crew_member->ID; ?>" class="crew-member">
	<a href="/profile/<?php echo $crew_member->user_login; ?>">
	 <div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($crew_member->ID,'thumbnail'); ?>"></div>
    <div class="profile-details">
      <div><strong><?php echo $crew_member->first_name; ?> <?php echo $crew_member->last_name; ?></strong></div>
      <div>
        <?php while (has_sub_field('shoot_position','user_'.$crew_member->ID)) : ?>
          <?php if (get_sub_field('position_name','user_'.$crew_member->ID) !== '') : ?>
            <?php the_sub_field('position_name','user_'.$crew_member->ID); ?><br>
          <?php endif; ?>
        <?php endwhile; ?>
      </div>
    </div>
	</a>
</div>