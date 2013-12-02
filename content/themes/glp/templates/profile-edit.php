<?php global $profile; ?>
<form action="/profile" method="post" id="user-<?php echo $profile->ID; ?>" class="profile-edit-form container">
	<header class="row">
		<div class="profile-header span9 offset3">
			<div class="profile-header-inner">
				<button class="edit-profile btn" type="submit"><i class="icon icon-white icon-ok"></i> <?php _e('Save edits','glp'); ?></button>
				<input class="profile-location" name="location" placeholder="<?php _e('Location','glp'); ?>" value="<?php the_field('location','user_'.$profile->ID); ?>" >
				<input class="profile-name" name="nickname" placeholder="<?php _e('Name','glp'); ?>" value="<?php echo $profile->nickname; ?>">
				<div class="profile-username">@<?php echo $profile->user_login; ?></div>
			</div>
		</div>
	</header>

	<div class="profile-container row">
		<div class="profile-sidebar span3">
			<div class="profile-siderbar-inner">
				<div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($profile->ID,'medium'); ?>"></div>
				<hr>
				<p><b><?php _e('Interested in','glp'); ?>:</b><br>
				<?php $interests = get_field_object('interests','user_'.$profile->ID); $values = get_field('interests','user_'.$profile->ID); foreach ($interests['choices'] as $interest) : ?>
					<label class="checkbox" for="<?php echo $interest; ?>"><input name="interests[]" type="checkbox" value="<?php echo $interest; ?>"<?php if(in_array($interest,$values)) { echo ' checked'; } ?>> <?php echo $interest; ?></label>
				<?php endforeach; ?>
				</p>
				<hr>
				<p><b><?php _e('Expertise','glp'); ?>:</b><br>
				<?php $expertises = get_field_object('expertise','user_'.$profile->ID); $values = get_field('expertise','user_'.$profile->ID); foreach ($expertises['choices'] as $expertise) : ?>
					<label class="checkbox" for="<?php echo $expertise; ?>"><input name="expertise[]" type="checkbox" value="<?php echo $expertise; ?>"<?php if(in_array($expertise,$values)) { echo ' checked'; } ?>> <?php echo $expertise; ?></label>
				<?php endforeach; ?>
				</p>
				<hr>
			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('About','glp'); ?></h4>
				<textarea name="bio" placeholder="<?php _e('Bio','glp'); ?>" class="profile-bio"><?php the_field('bio','user_'.$profile->ID); ?></textarea>
				<p><b><?php _e("Your website",'glp'); ?>:</b><br><input name="user_url" class="profile-website" value="<?php echo $profile->user_url; ?>"></p>
			</div>
		</div>
	</div>
	<input type="hidden" name="profile-save" value="true">
</form>