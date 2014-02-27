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
		'user_languages',
		'user_contact'
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

	if ($_FILES) {
		foreach ($_FILES as $file => $array) {
	        $attachment_id = insert_attachment($file, $profile->ID);
    	    update_field($field_keys['user_avatar'], $attachment_id, 'user_'.$profile->ID);
        }
    }

    // Helper Functions

	function insert_attachment ($file_handler, $post_id, $setthumb = 'false') {
		if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		$attachment_id = media_handle_upload( $file_handler, $post_id );

		if ($setthumb) update_post_meta($post_id, '_thumbnail_id', $attach_id);
		return $attachment_id;
	}