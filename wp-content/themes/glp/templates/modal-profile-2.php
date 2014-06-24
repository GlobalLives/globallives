<?php global $current_user, $field_keys; $user_id = $current_user->ID; ?>
<div class="row-fluid">
<?php
	$user_skills = get_field($field_keys['user_skills'], 'user_' . $user_id);
	$user_skills_obj = get_field_object($field_keys['user_skills'], 'user_' . $user_id);
	foreach ($user_skills_obj['sub_fields'] as $skill_category) {
?>
				<div class="span4">
					<h5><?php echo $skill_category['label']; ?></h5>
<?php
		foreach ($skill_category['choices'] as $skill) {
			$name = 'user_skills[0][' . $skill_category['name'] . '][]';
			$checked = in_array($skill, $user_skills[0][$skill_category['name']]);
?>
					<label class="checkbox"><input type="checkbox" name="<?php echo $name; ?>" value="<?php echo $skill; ?>"<?php if ($checked) : ?> checked<?php endif; ?>> <?php echo $skill; ?></label>
<?php
		}
?>
				</div>
<?php
	}
?>
			</div>
			<hr>
			<div class="row-fluid">
				<div class="span6">
					<h5><?php _e('Translation', 'glp'); ?></h5>
					<div id="available-languages">
<?php
	$user_languages = get_field($field_keys['user_languages'], 'user_' . $user_id);
	foreach ($user_languages as $user_language) {
		$name = $user_language['language_name'];
?>
						<label class="checkbox"><input type="checkbox" name="user_languages[][language_name]" value="<?php echo $name; ?>" checked> <?php echo $name; ?></label>
<?php
	}
	$languages = icl_get_languages('skip_missing=0');
	foreach ($languages as $language) {
		$name = $language['translated_name'];
		$has = false;
		foreach($user_languages as $user_language) {
			if ($user_language['language_name'] == $name) { $has = true; }
		}
		if (!$has) {
?>
						<label class="checkbox"><input type="checkbox" name="user_languages[][language_name]" value="<?php echo $name; ?>"> <?php echo $name; ?></label>
<?php
		}
	}
?>
					</div>
					<div class="input-append">
						<input type="text" id="add-language" placeholder="<?php _e('Add another', 'glp'); ?>">
						<button type="button" id="add-language-btn" class="btn" ><?php _e('Add', 'glp'); ?></button>
					</div>

				</div>
				<div class="span6">
					<p><?php _e('I speak: (indicate level of proficiency)', 'glp'); ?></p>
					<div id="spoken-languages">
<?php
	foreach($user_languages as $user_language) {
		$name = $user_language['language_name'];
		$level = $user_language['language_level'];
		$slug = preg_replace('~[^-\w]+~', '-', strtolower($name));
?>
						<label class="select inline" id="<?php echo $slug; ?>"><?php echo $name ?>
						<select name="user_languages[][language_level]">
							<option value="n"<?php if ($level == 'n') : ?> selected<?php endif; ?>>N - Native</option>
							<option value="5"<?php if ($level == '5') : ?> selected<?php endif; ?>>5 - Professional</option>
							<option value="4"<?php if ($level == '4') : ?> selected<?php endif; ?>>4 - Near Native</option>
							<option value="3"<?php if ($level == '3') : ?> selected<?php endif; ?>>3 - Advanced</option>
							<option value="2"<?php if ($level == '2') : ?> selected<?php endif; ?>>2 - Intermediate</option>
							<option value="1"<?php if ($level == '1') : ?> selected<?php endif; ?>>1 - Basic</option>
						</select>
						</label>
<?php
	}
?>
					</div>
				</div>
			</div>