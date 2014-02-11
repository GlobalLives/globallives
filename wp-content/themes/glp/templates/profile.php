<?php
	global $profile, $current_user;

	if (isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	} elseif (isset($_POST['mode'])) {
		$mode = $_POST['mode'];
	} else {
		$mode = false;
	}

	switch($mode) {

		case 'save': // Save existing profile from Edit mode

			get_template_part('templates/profile','save');
			get_template_part('templates/profile','show');
			break;

		case 'edit':

			get_template_part('templates/profile','edit');
			break;

		case 'create': // Create new profile from form

			get_template_part('templates/profile','create');
			// No break, continues to default.

		default:

			if ($profile->ID != $current_user->ID || is_profile_created($profile->ID)) {
				get_template_part('templates/profile','show');
			} else {
				get_template_part('templates/profile','form');
			}
			break;
	}
?>