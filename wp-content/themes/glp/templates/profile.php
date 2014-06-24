<?php
	global $profile, $current_user;

	$mode = isset($_POST['mode']) ? $_POST['mode'] : false;
	switch($mode) {
		case 'save': // Save existing profile from Edit mode

			get_template_part('templates/profile', 'save');
			get_template_part('templates/profile', 'show');
			break;

		default: // No mode

			if ($current_user->ID != $profile->ID || is_profile_created($profile->ID)) {
				get_template_part('templates/profile', 'show');
			} else {
				get_template_part('templates/profile', 'create');
			}
	}
?>