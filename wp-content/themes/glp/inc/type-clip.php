<?php
	global $field_keys;

/*	==========================================================================
	Clip
	========================================================================== */

	add_action( 'init', 'create_clip_post_type' );
	function create_clip_post_type() {
   
		register_taxonomy( 'clip_tags', 'clip', array(
			'label' => __( 'Clip Tags' ),
			'rewrite' => array( 'slug' => 'clip-tag' )
		));
   
		register_post_type( 'clip', array(
			'labels' => array(
			    'name'			=> __( 'Clips' ),
			    'singular_name'	=> __( 'Clip' )
			),
			'public' => true,
			// 'exclude_from_search' => true,
			'supports' => array( 'title', 'thumbnail', 'comments', 'page-attributes' ),
			'menu_position' => 5,
			'has_archive' => false,
			'taxonomies' => array('clip_tags'),
			'menu_icon' => 'dashicons-video-alt'
		));
	}
	function get_clip_participant( $clip_id ) {
		$participant = get_posts(array(
            'post_type' => 'participant',
            'posts_per_page' => 1,
            'meta_query' => array(
            	'relation' => 'OR',
            	array(
	                'key' => 'clips',
    	            'value' => '"'.$clip_id.'"',
        	        'compare' => 'LIKE'
            	),
            	array(
            		'key' => 'summary_video',
            		'value' => '"'.$clip_id.'"',
            		'compare' => 'LIKE'
            	)
            )
        ));

		return $participant[0];
	}
	function get_next_clip( $clip_id ) {
		$clip = get_post($clip_id);
		$participant = get_clip_participant( $clip_id );
		$next_clip_id = '';
		$clip_index = 0;

		if ($participant) {
			$clips = get_field($field_keys['participant_clips'],$participant->ID);
			$clip_index = array_search($clip, $clips);
			$next_clip = $clips[$clip_index + 1];
			$next_clip_id = $next_clip->ID;
		}
		return $next_clip_id;
	}
	function the_next_clip( $clip_id ) {
		echo get_next_clip( $clip_id );
	}

	function get_clip_seconds($clip_id) {
		global $field_keys;
		$duration = get_field($field_keys['clip_duration'], $clip_id);
		$seconds = array_slice(explode(':', $duration),-1);
		return sprintf('%02d', $seconds[0]);
	}
	function the_clip_seconds($clip_id) {
		echo get_clip_seconds($clip_id);
	}

	function get_clip_minutes($clip_id) {
		global $field_keys;
		$duration = get_field($field_keys['clip_duration'], $clip_id);
		$minutes = array_slice(explode(':', $duration),-2,1);
		return sprintf('%d', $minutes[0]);
	}
	function the_clip_minutes($clip_id) {
		echo get_clip_minutes($clip_id);
	}

	function get_clip_thumbnail($clip_id) {
		global $field_keys;
		$youtube_id = get_field($field_keys['clip_youtube_id'],$clip_id);
		return "http://img.youtube.com/vi/" . $youtube_id . "/0.jpg";
	}
	function the_clip_thumbnail($clip_id) {
		echo get_clip_thumbnail($clip_id);
	}
