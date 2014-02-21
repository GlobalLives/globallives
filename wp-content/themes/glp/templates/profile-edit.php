<?php global $profile, $current_user, $field_keys; ?>
<form action="/profile" method="post" id="user-<?php echo $profile->ID; ?>" class="profile-edit-form container">
	<input type="hidden" name="mode" value="save" />
	<header class="row">
		<div class="profile-header span9 offset3">
			<div class="profile-header-inner">
				<button class="edit-profile btn" type="submit"><?php _e('Save','glp'); ?> <i class="icon icon-white icon-ok"></i></button>
				<h1 class="profile-name">
					<input class="profile-firstname span3" name="first_name" placeholder="<?php _e('First Name','glp'); ?>" value="<?php echo $profile->first_name; ?>">
					<input class="profile-lastname span3" name="last_name" placeholder="<?php _e('Last Name','glp'); ?>" value="<?php echo $profile->last_name; ?>">
				</h1>
				<p class="profile-location">
					<?php /*<select class="profile-occupation span2" name="user_occupation">
					<?php $user_occupation_field = get_field_object($field_keys['user_occupation'],'user_'.$profile->ID); $user_occupation_options = $user_occupation_field['choices']; foreach($user_occupation_options as $user_occupation_option) : ?>
						<option value="<?php echo $user_occupation_option; ?>" <?php if (get_field($field_keys['user_occupation'],'user_'.$profile->ID) == $user_occupation_option) : ?> selected<?php endif; ?>><?php echo $user_occupation_option; ?></option>
					<?php endforeach; ?>
					</select> */ ?>
					<input class="profile-occupation span3" name="user_occupation" placeholder="<?php _e('Occupation','glp'); ?>" value="<?php the_field($field_keys['user_occupation'],'user_'.$profile->ID); ?>" >

					<?php _e(' in ','glp'); ?>
					<input class="profile-location span3" name="user_location" placeholder="<?php _e('Location','glp'); ?>" value="<?php the_field($field_keys['user_location'],'user_'.$profile->ID); ?>" >
				</p>
				<div class="profile-username">@<?php echo $profile->user_login; ?></div>
			</div>
		</div>
	</header>

	<div class="profile-container row">
		<div class="profile-sidebar span3">
			<div class="profile-siderbar-inner">
				<div class="profile-thumbnail"><img src="<?php the_profile_thumbnail_url($profile->ID,'medium'); ?>"></div>
				<hr>

				<p>
					<b><?php _e('Skills','glp'); ?></b><br>
				<?php
					$user_skills = get_field($field_keys['user_skills'],'user_'.$profile->ID);
					$user_skills_obj = get_field_object($field_keys['user_skills'],'user_'.$profile->ID);
					$available_skills = $user_skills_obj['sub_fields'][0]['choices'];
					$max_skills = 5;

					for ($i = 0; $i < $max_skills; $i++) :
				?>
					<div class="span4"><div class="row">
					<select class="span2" name="user_skills[<?php echo $i; ?>][skill_name]">
					<option value=""><?php _e('- select -','glp'); ?></option>
					<?php foreach($available_skills as $skill_name_option) : ?>
						<option value="<?php echo $skill_name_option; ?>" <?php if ($user_skills[$i]['skill_name'] == $skill_name_option) : ?> selected<?php endif; ?>><?php echo $skill_name_option; ?></option>
					<?php endforeach; ?>
					</select>
					<input class="span1" type="number" min="0" max="5" name="user_skills[<?php echo $i; ?>][skill_level]" value="<?php echo $user_skills[$i]['skill_level']; ?>"><br>
					</div></div>
				<?php endfor; ?>
				</p>

				<hr>

				<p>
					<b><?php _e('Languages','glp'); ?></b><br>
				<?php
					$user_languages = get_field($field_keys['user_languages'], 'user_'.$profile->ID);
					$enabled_languages = icl_get_languages('skip_missing=0');
					$max_languages = 5;

					for ($i = 0; $i < $max_languages; $i++ ) :
				?>
					<div class="span4"><div class="row">
					<select class="span2" name="user_languages[<?php echo $i; ?>][language_name]">
					<option value=""><?php _e('- select -','glp'); ?></option>
					<?php foreach($enabled_languages as $lang_name_option) : ?>
						<option value="<?php echo $lang_name_option['native_name']; ?>" <?php if ($user_languages[$i]['language_name'] == $lang_name_option['native_name']) : ?> selected<?php endif; ?>><?php echo $lang_name_option['native_name']; ?></option>
					<?php endforeach; ?>
					</select>
					<input class="span1" type="number" min="0" max="5" name="user_languages[<?php echo $i; ?>][language_level]" value="<?php if ($user_languages[$i]) { echo $user_languages[$i]['language_level']; } ?>">
					</div></div>
				<?php endfor; ?>
				</p>

			</div>
		</div>

		<div class="profile-body span9">
			<div class="profile-body-inner">
				<h4><?php _e('About','glp'); ?></h4>
				<textarea name="description" placeholder="<?php _e('Bio','glp'); ?>" class="profile-bio"><?php echo $profile->description; ?></textarea>
				<p><b><?php _e("Website",'glp'); ?>:</b><br><input name="user_url" class="profile-website" value="<?php echo $profile->user_url; ?>"></p>
			</div>
		</div>
	</div>
</form>