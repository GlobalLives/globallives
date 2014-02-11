<?php
	global $profile, $field_keys;

	$wp_fields = array( # WordPress built-in fields
		'first_name',
		'last_name',
		'description',
		'user_url'
	);
	foreach($wp_fields as $field) {
		if (isset($_POST[$field])) {
			update_user_meta(
				$profile->ID,
				$field,
				$_POST[$field]
			);
		}
	}

	$acf_fields = array( # Advanced Custom Fields
		'user_location',
		'user_occupation',
		'user_skills',
		'user_languages'
	);
	foreach($acf_fields as $field) {
		if (isset($_POST[$field])) {
			update_field(
				$field_keys[$field],
				$_POST[$field],
				'user_'.$profile->ID
			);
		}
	}