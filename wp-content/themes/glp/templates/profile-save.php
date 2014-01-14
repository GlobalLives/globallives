<?php global $profile;

	// WordPress built-in fields
	update_user_meta($profile->ID, 'first_name',	$_POST['first_name']);
	update_user_meta($profile->ID, 'last_name',		$_POST['last_name']);
	update_user_meta($profile->ID, 'description',	$_POST['description']);
	update_user_meta($profile->ID, 'user_url',		$_POST['user_url']);
			
	// Advanced Custom Fields
	$location_field_key		= 'field_19';
	$bio_field_key			= 'field_27';
	$interests_field_key	= 'field_20';
	$expertise_field_key	= 'field_21';
						
	update_field( 'user_location',	$_POST['location'],		'user_'.$profile->ID );
	update_field( 'bio',			$_POST['bio'],			'user_'.$profile->ID );
	update_field( 'user_interests',	$_POST['interests'],	'user_'.$profile->ID );
	update_field( 'user_expertise',	$_POST['expertise'],	'user_'.$profile->ID );
			 			
?>