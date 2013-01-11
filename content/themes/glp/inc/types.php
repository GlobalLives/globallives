<?php

/*	==========================================================================
	Posts
	========================================================================== */

	add_theme_support( 'post-thumbnails' );

/*	==========================================================================
	Pages
	========================================================================== */

	add_post_type_support( 'page', 'excerpt' );

	add_filter( 'body_class', 'add_page_slug_body_class' );
	function add_page_slug_body_class( $classes ) {
		global $post;
		if ( isset( $post ) && ( $post->post_type == 'page' ) ) {
			$classes[] = $post->post_type . '-' . $post->post_name;
		}
		return $classes;
	}
		
/*	==========================================================================
	Participants
	========================================================================== */

	add_action( 'init', 'create_custom_post_types' );
	function create_custom_post_types() {
   
		# Participants
		register_post_type( 'participant', array(
			'labels' => array(
			    'name'			=> __( 'Participants' ),
			    'singular_name'	=> __( 'Participant' )
			),
			'public' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
			'menu_position' => 5,
			'has_archive' => true,
			'rewrite' => array(
			    'slug'			=> 'participants',
			    'with_front'	=> true
			)
		));
	}
	function get_participant_thumbnail_url( $participant_id ) {
		$participant = get_post( $participant_id );
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($participant->ID, 'thumbnail'));
		return $thumbnail[0];
	}
	function the_participant_thumbnail_url( $participant_id ) {
		echo get_participant_thumbnail_url( $participant_id );
	}

/*	==========================================================================
	Users
	========================================================================== */

	