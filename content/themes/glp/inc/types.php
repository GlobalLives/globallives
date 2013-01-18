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

	add_action( 'init', 'create_participant_post_type' );
	function create_participant_post_type() {
   
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
	
	function get_participant_clip_tags( $participant_id ) {
		$clip_tags = array();
		$clips = get_field('clips',$participant_id);
		foreach( $clips as $clip ) {
			$clip_tags += get_the_terms($clip->ID, 'post_tag');
		}
		return $clip_tags;
	}

/*	==========================================================================
	Clips
	========================================================================== */

	add_action( 'init', 'create_clip_post_type' );
	function create_clip_post_type() {
   
		register_post_type( 'clip', array(
			'labels' => array(
			    'name'			=> __( 'Clips' ),
			    'singular_name'	=> __( 'Clip' )
			),
			'public' => true,
			'supports' => array( 'title', 'thumbnail', 'comments', 'page-attributes' ),
			'menu_position' => 5,
			'has_archive' => false,
			'taxonomies' => array('post_tag')
		));
	}
	
/*	==========================================================================
	Users
	========================================================================== */

	add_action('init', 'set_profile_base');
	function set_profile_base() {
		global $wp_rewrite;
		$wp_rewrite->author_base = 'profile';
	}

	function get_profile_activities( $user_id ) {
		$activities = array();
		// First get comments made by the user
		$comments = get_comments(array( 'user_id' => $user_id ));
		foreach ($comments as $comment) {
			$activity = array(
				'activity_type' => 'comment',
				'activity_description' => __('wrote a comment on','glp') . ' <span class="activity-post">'.get_the_title($comment->comment_post_ID).'</span>',
				'activity_user' => $user_id,
				'activity_content' => $comment->comment_content,
				'activity_timestamp' => strtotime($comment->comment_date)
			);
			$activities[] = $activity;
		}
		return $activities;
	}