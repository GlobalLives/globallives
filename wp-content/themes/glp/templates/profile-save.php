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
			update_user_meta($profile->ID, $field, $_POST[$field]);
		}
	}

	$acf_fields = array( # Advanced Custom Fields
		'user_location',
		'user_occupation',
		'user_skills',
		'user_contact',
		'user_sources',
		'user_subscribe'
	);
	foreach($acf_fields as $field) {
		if (isset($_POST[$field])) {
			update_field($field_keys[$field], $_POST[$field], 'user_'.$profile->ID);
		}
	}
	if (isset($_POST['user_languages'])) {
		$submitted_languages = $_POST['user_languages'];
		$half = count($submitted_languages) / 2;
		$user_languages = array();
		for ($i = 0; $i < $half; $i++) {
			$user_languages[$i] = array(
				'language_name' => $submitted_languages[$i]['language_name'],
				'language_level' => $submitted_languages[$i + $half]['language_level']
			);
		}
		update_field($field_keys['user_languages'], $user_languages, 'user_'.$profile->ID);
	}

	if ($_FILES["file"]["error"] != 0) {
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