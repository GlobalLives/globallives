<?php
	global $field_keys;

/*	==========================================================================
	Post
	========================================================================== */

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'small', 300, 200, true );

	function get_featured_image_src( $post_id, $size = 'thumbnail' ) {
		if ($featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size)) {
			return $featured_image[0];
		} else {
			return '';
		}
	}
	function the_featured_image_src( $post_id, $size = 'thumbnail' ) {
		echo get_featured_image_src( $post_id, $size );
	}