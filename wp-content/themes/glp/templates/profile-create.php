<?php
	global $current_user, $field_keys;

	// Get form data
	$user_firstname		= $_GET['user_firstname'];
	$user_lastname		= $_GET['user_lastname'];
	$user_occupation	= $_GET['user_occupation'];
	$user_location		= $_GET['user_location'];

	// WordPress built-in fields
	wp_update_user(array(
		'ID' => $current_user->ID,
		'first_name' => $user_firstname,
		'last_name' => $user_lastname
	));
	
	// Advanced Custom Fields
	update_field($field_keys['user_occupation'],	$user_occupation,	'user_' . $current_user->ID);
	update_field($field_keys['user_location'],		$user_location,		'user_' . $current_user->ID);