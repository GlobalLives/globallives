<?php

foreach (glob(get_template_directory() . '/inc/*.php') as $filename) {
	require $filename;
}

# Additional Settings

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'remove_admin_bar');