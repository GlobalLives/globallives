<?php
	global $profile;

	// WordPress built-in fields
	update_user_meta($profile->ID, 'first_name',	$_POST['first_name']);
	update_user_meta($profile->ID, 'last_name',		$_POST['last_name']);
	update_user_meta($profile->ID, 'description',	$_POST['description']);
	update_user_meta($profile->ID, 'user_url',		$_POST['user_url']);
			
	// Advanced Custom Fields						
	update_field( 'user_location',		$_POST['user_location'],	'user_'.$profile->ID );
	update_field( 'user_occupation',	$_POST['user_occupation'],	'user_'.$profile->ID );
	update_field( 'user_skills',		$_POST['user_skills'],		'user_'.$profile->ID );
