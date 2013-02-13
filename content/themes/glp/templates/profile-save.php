<?php global $profile;

	// WordPress built-in fields
	update_user_meta( $profile->ID,	'nickname',		$_POST['nickname'] );
	update_user_meta( $profile->ID,	'description',	$_POST['bio'] );
	update_user_meta( $profile->ID,	'user_url',		$_POST['user_url'] );
			
	// Advanced Custom Fields
	$location_field_key		= 'field_19';
	$bio_field_key			= 'field_27';
	$interests_field_key	= 'field_20';
	$expertise_field_key	= 'field_21';
						
	update_field( $location_field_key,	$_POST['location'],		'user_'.$profile->ID );
	update_field( $bio_field_key,		$_POST['bio'],			'user_'.$profile->ID );
	update_field( $interests_field_key,	$_POST['interests'],	'user_'.$profile->ID );
	update_field( $expertise_field_key,	$_POST['expertise'],	'user_'.$profile->ID );
			 			
?>