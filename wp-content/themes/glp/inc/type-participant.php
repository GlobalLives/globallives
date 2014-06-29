<?php
	global $field_keys;

/*	==========================================================================
	Participant
	========================================================================== */

	add_action( 'init', 'create_participant_post_type' );
	function create_participant_post_type() {

   		register_taxonomy( 'series', 'participant', array(
			'label' => __( 'Series' ),
			'rewrite' => array(
				'slug' => 'series',
				'with_front' => false
			)
		));
   		register_taxonomy( 'themes', 'participant', array(
			'label' => __( 'Themes' ),
			'rewrite' => array(
				'slug' => 'themes',
				'with_front' => false
			)
		));

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
			    'with_front'	=> false
			)
		));
	}
	function get_participant_thumbnail_url( $participant_id, $thumbnail_size = 'thumbnail' ) {
		$participant = get_post( $participant_id );
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($participant->ID), $thumbnail_size );
		return $thumbnail[0];
	}
	function the_participant_thumbnail_url( $participant_id, $thumbnail_size = 'thumbnail' ) {
		echo get_participant_thumbnail_url( $participant_id, $thumbnail_size );
	}

	function get_participant_taxonomy_slugs( $participant_id, $taxonomy ) {
		$term_slugs = array();
		if ($participant_terms = get_the_terms( $participant_id, $taxonomy)) {
			foreach ($participant_terms as $term) {
				$term_slugs[] = $term->slug;
			}
		}
		return $term_slugs;
	}

	function get_participant_clip_tags( $participant_id ) {
		$clip_tags = array();
		if ( $clips = get_field($field_keys['participant_clips'],$participant_id) ) {
			foreach( $clips as $clip ) {
				if ($this_clip_tags = get_the_terms($clip->ID, 'clip_tags')) {
					$clip_tags += $this_clip_tags;
				}
			}
			return $clip_tags;
		} else {
			return false;
		}
	}
	function get_participant_themes( $participant_id, $limit = 5 ) {
		global $field_keys;
		$themes = array();
		if ($clips = get_field($field_keys['participant_clips'], $participant_id)) {
			foreach ($clips as $clip) {
				foreach(get_clip_tags($clip->ID) as $clip_tag) {
					if (array_key_exists($clip_tag, $themes)) {
						$themes[$clip_tag] += 1;
					} else {
						$themes[$clip_tag] = 1;
					}
				}
			}
			arsort($themes);
			return array_keys(array_slice($themes, 0, $limit));
		} else {
			return false;
		}
	}
	function the_participant_themes( $participant_id ) {
		$themes = get_participant_themes($participant_id);
		echo implode(', ', $themes);
	}

	function get_participant_crew_members( $participant_id ) {
		global $field_keys;
		$crew_members = get_users(array(
			'meta_query' => array(
				array(
					'key' => 'shoots',
					'compare' => 'LIKE',
					'value' => '"' . $participant_id . '"'
				)
			)
		));
		return $crew_members;
	}

	function get_related_participants( $participant_id, $taxonomy = 'themes' ) {

		$tax_ids = wp_get_post_terms( $participant_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( $tax_ids ) { // Get participants that share taxonomy
			$related_participants = get_posts(array(
				'post_type'			=> 'participant',
				'posts_per_page'	=> -1,
				'tax_query'			=> array(array(
					'taxonomy' 	=> $taxonomy,
					'field'		=> 'id',
					'terms'		=> $tax_ids,
					'operator' 	=> 'IN'
				))
			));
			return $related_participants;
		} else {
			return false;
		}
	}
