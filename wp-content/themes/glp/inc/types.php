<?php

/*	==========================================================================
	Posts
	========================================================================== */

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'small', 300, 200, true );

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
		if ( $clips = get_field('clips',$participant_id) ) {
			foreach( $clips as $clip ) {
				$clip_tags += get_the_terms( $clip->ID, 'post_tag' );
			}
			return $clip_tags;
		} else {
			return false;
		}
	}
	
	function get_participant_crew_members( $participant_id ) {
		$crew_members = get_users(array(
			'meta_query' => array(array(
					'key' => 'shoots',
					'value' => '"' . $participant_id . '"',
					'compare' => 'LIKE'
			))
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
			return get_post($participant_id);
		}
	}

/*	==========================================================================
	Clips
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
			'exclude_from_search' => true,
			'supports' => array( 'title', 'thumbnail', 'comments', 'page-attributes' ),
			'menu_position' => 5,
			'has_archive' => false,
			'taxonomies' => array('clip_tag')
		));
	}
	function get_clip_participant( $clip_id ) {
		$participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => -1 ));
		$parent_participants = array();
		foreach( $participants as $participant ) {
			if ( $clips = get_field('clips',$participant->ID) ) {
				foreach( $clips as $clip ) {
					if ($clip->ID == $clip_id) { $parent_participants[] = $participant->ID; }
				}
			}
			if ( $summary_video = get_field('summary_video',$participant->ID) ) {
				if ($summary_video->ID == $clip_id) { $parent_participants[] = $participant->ID; }
			}
		}
		return $parent_participants[0];
	}
	function get_next_clip( $clip_id ) {
		$next_clip_id = '';
		$participant_id = get_clip_participant( $clip_id );
		$next_clip_position = 0;

		if ($participant_id) {
			$clips = get_field('clips',$participant_id);
			if (is_array($clips)) {
				$clip_index = array_search($clip, $clips);
				$next_clip_position = $clip_index++;
			}
			$next_clip = $clips[$next_clip_position];
			$next_clip_id = $next_clip->ID;
		}
		return $next_clip_id;
	}
	function the_next_clip( $clip_id ) {
		echo get_next_clip( $clip_id );
	}
	
/*	==========================================================================
	Users
	========================================================================== */

	add_action('init', 'set_profile_base');
	function set_profile_base() {
		global $wp_rewrite;
		$wp_rewrite->author_base = 'profile';
		$wp_rewrite->flush_rules();
	}

	function get_profile_thumbnail_url( $profile_id, $thumbnail_size = 'thumbnail' ) {
		$thumbnail = wp_get_attachment_image_src( get_field('avatar','user_'.$profile_id), $thumbnail_size );
		if ($thumbnail) {
			return $thumbnail[0];		
		} else {
			return get_bloginfo('template_directory') . '/img/logo-coda.png';
		}
	}
	function the_profile_thumbnail_url( $profile_id, $thumbnail_size = 'thumbnail' ) {
		echo get_profile_thumbnail_url( $profile_id, $thumbnail_size );
	}	

	function get_profile_activities( $user_id ) {
		$activities = array();
		$user = get_userdata( $user_id );

		// First get comments made by the user
		$comments = get_comments(array( 'user_id' => $user_id, 'status' => 'approve' ));
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

		// Add mentions
		$mentions = get_comments(array( 'status' => 'approve' ));
		foreach ($mentions as $mention) {
			if ( strpos($mention->comment_content, '@'.$user->user_login) === 0 ) {
				$activity = array(
					'activity_type' => 'mention',
					'activity_description' => __('mentioned this user on','glp') . ' <span class="activity-post">'.get_the_title($mention->comment_post_ID).'</span>',
					'activity_user' => $mention->user_id,
					'activity_content' => $mention->comment_content,
					'activity_timestamp' => strtotime($mention->comment_date)
				);
				$activities[] = $activity;
			}
		}

		// Add queue, favorites, bookmarks

		// Sort activities by timestamp before returning
		usort($activities, 'profile_activity_compare');
		return $activities;
	}
	function profile_activity_compare($a, $b) {
		return $b['activity_timestamp'] - $a['activity_timestamp'];
	}
	function active_profile_compare($a, $b) {
		return get_profile_last_active($b->ID) - get_profile_last_active($a->ID);
	}
	
	function get_profile_last_active( $user_id ) {
		$activities = get_profile_activities( $user_id );
		if ($activities) {
			return $activities[0]['activity_timestamp'];
		} else {
			return strtotime(' ');
		}
	}
        
        add_filter('clip_toggle_response', 'clip_toggle_queue_response', 1, 3);
        function clip_toggle_queue_response($response, $toggled_on, $toggle_type) {
            if ( 'queue' != $toggle_type ) return $response;

            if ($toggled_on)
                $response = __('&#45; Queue', 'glp');
            else
                $response = __('&#43; Queue', 'glp');

            return $response;
        }
        
        add_filter('clip_toggle_response', 'clip_toggle_favorite_response', 1, 3);
        function clip_toggle_favorite_response($response, $toggled_on, $toggle_type) {
            if ( 'favorite' != $toggle_type ) return $response;

            if ($toggled_on)
                $response = __('&hearts; Unfavorite', 'glp');
            else
                $response = __('&hearts; Favorite', 'glp');

            return $response;
        }
        
        add_filter('clip_toggle_response', 'clip_toggle_bookmark_response', 1, 3);
        function clip_toggle_bookmark_response($response, $toggled_on, $toggle_type) {
            if ( 'bookmark' != $toggle_type ) return $response;

            if ($toggled_on)
                $response = __('&#45; Remove from Bookmarks', 'glp');
            else
                $response = __('&#43; Add to Bookmarks', 'glp');

            return $response;
        }
        
        add_filter('clip_toggle_list_response', 'clip_toggle_list_response', 1, 3);
        function clip_toggle_list_response($response, $all_queued, $toggle_type) {
            if ( 'queue' != $toggle_type ) return $response;

            if (true === $all_queued)
                $response = __('&#45; All from Queue', 'glp');
            else
                $response = __('&#43; All to Queue', 'glp');

            return $response;
        }

        add_filter( 'clip_toggle_queue_status', 'clip_toggle_queue_status', 1, 3 );
        function clip_toggle_queue_status($response, $clip_id, $user_id) {
            $queued = is_clip_queued($clip_id, $user_id, 'queue');
            $response = apply_filters( 'clip_toggle_response', $response, isset($queued), 'queue' );
            return $response;
        }
        
        add_filter( 'clip_toggle_favorite_status', 'clip_toggle_favorite_status', 1, 3 );
        function clip_toggle_favorite_status($response, $clip_id, $user_id) {
            $queued = is_clip_queued($clip_id, $user_id, 'favorite');
            $response = apply_filters( 'clip_toggle_response', $response, isset($queued), 'favorite' );
            return $response;
        }
        
        add_filter( 'clip_toggle_bookmark_status', 'clip_toggle_bookmark_status', 1, 3 );
        function clip_toggle_bookmark_status($response, $clip_id, $user_id) {
            $queued = is_clip_queued($clip_id, $user_id, 'bookmark');
            $response = apply_filters( 'clip_toggle_response', $response, isset($queued), 'bookmark' );
            return $response;
        }
        
        add_filter( 'clip_toggle_queue_list_status', 'clip_toggle_queue_list_status', 1, 3 );
        function clip_toggle_queue_list_status($response, $user_id) {
            $clips = get_field('clips');
            $response = apply_filters( 'clip_toggle_list_response', $response, is_list_queued($clips, $user_id), 'queue' );
            return $response;
        }
        
        function is_clip_queued($clip_id, $user_id, $toggle_type) {
            $queue = get_field( apply_filters('queue_key', $queue_key, $toggle_type), 'user_'.$user_id );
            // get_field returns array of post objects
            if ($queue) {
                foreach ( $queue as $k => $clip) {
                    if ( $clip_id == $clip->ID )
                        return $k;
                }
            }
        }
        
        function is_list_queued($clip_list, $user_id, $queue_key = 'queue') {
            $queue = get_field( apply_filters('queue_key', $queue_key, $toggle_type), 'user_'.$user_id );
            if ($queue) {
                foreach ($queue as $clip) {
                    $queued = array_search($clip, $clip_list);
                    if ( is_int( $queued ) )  {
                        unset($clip_list[$queued]);
                    }
                }
            }
            
            if ( empty($clip_list) )
                return true;
            else 
                return $clip_list;
        }
        
        // Need to save relationship type fields as an array of post_ids rather than post objects.
        add_filter('clean_queue', 'clean_relationship_type_queue');
        function clean_relationship_type_queue($queue) {
            foreach ($queue as $k => $v) {
                if ( is_object($v) && ('WP_Post' == get_class($v) ) )
                    $queue[$k] = $v->ID;
            }
            return $queue;
        }

        add_filter('queue_key', 'get_queue_key', 1, 2);
        function get_queue_key($queue_key, $toggle_type) {
            switch ($toggle_type) {
                case 'queue':
                    $queue_key = 'field_117';
                    break;
                case 'favorite':
                    $queue_key = 'field_116';
                    break;
                case 'bookmark':
                    $queue_key = 'field_118';
                    break;
            }
            return $queue_key;
        }
        
/*	==========================================================================
	Comments / Tags
	========================================================================== */
        
        $hashtag_regex = "/#\S*\w/i";
        
        if ( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) )  {
            add_filter('get_comment', 'style_hashtags');
            add_filter('the_comments', 'style_hashtags_on_comments_query');
        }
        
        function style_hashtags($comment) {
            global $hashtag_regex;
            preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
            foreach ( $hashtags[0] as $hashtag ) {
                if ( !empty($hashtag) ) {
                    $comment->comment_content = str_replace($hashtag, sprintf('<span class="tag">%s</span>', $hashtag), $comment->comment_content);
                }
            }
            return $comment;
        }
        
        
        function style_hashtags_on_comments_query($comments) {
            foreach ($comments as $k => $comment)
                $comments[$k] = style_hashtags($comment);
            
            return $comments;
        }
        
        add_action('wp_insert_comment', 'parse_hashtags_in_comments', 10, 2);
        function parse_hashtags_in_comments($comment_id, $comment) {
            global $hashtag_regex;
            
            // Restrict this to clips for now
            if ( 'clip' == get_post_type( $comment->comment_post_ID ) ) {
                // Do we have #tags
                preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
                foreach ( $hashtags[0] as $hashtag ) {
                    if ( !empty($hashtag) ) {
                        $clip_tag = wp_insert_term( str_replace('#', '', $hashtag), 'clip_tags' );
                        $clip_tags[] = $clip_tag->error_data['term_exists'] ? $clip_tag->error_data['term_exists'] : $clip_tag['term_id'];
                    }
                }

                if ( !empty($clip_tags) ) {
                    $clip_tags = array_map('intval', $clip_tags);
                    $clip_tags = array_unique( $clip_tags );
                    wp_set_object_terms( $comment->comment_post_ID, $clip_tags, 'clip_tags', true );
                }
            }
        }
        
        function comment_has_hastag($comment) {
            global $hashtag_regex;
            preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
            if ( !empty($hashtags[0]) )
                return true;
            else return false;
        }
        
        function comment_tagged_class($comment) {
            if ( comment_has_hastag($comment) ) echo "tagged";
        }
