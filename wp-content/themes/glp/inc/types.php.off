<?php
	global $field_keys;

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
				if ($this_clip_tags = get_the_terms($clip->ID, 'clip_tags')) {
					$clip_tags += $this_clip_tags;
				}
			}
			return $clip_tags;
		} else {
			return false;
		}
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
			// 'exclude_from_search' => true,
			'supports' => array( 'title', 'thumbnail', 'comments', 'page-attributes' ),
			'menu_position' => 5,
			'has_archive' => false,
			'taxonomies' => array('clip_tags')
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
			$clips = get_field('clips',$participant->ID);
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
	
/*	==========================================================================
	Users
	========================================================================== */

	add_action('init', 'set_profile_base');
	function set_profile_base() {
		global $wp_rewrite;
		$wp_rewrite->author_base = 'profile';
		$wp_rewrite->author_structure = '/' . $wp_rewrite->author_base . '/%author%';
		$wp_rewrite->flush_rules();
	}

	function get_fullname( $user_id ) {
		$user = get_userdata( $user_id );
		return $user->user_firstname . ' ' . $user->user_lastname;
	}
	function the_fullname( $user_id ) {
		echo get_fullname( $user_id );
	}

	function get_profile_thumbnail_url( $profile_id, $thumbnail_size = 'thumbnail' ) {
		global $field_keys;
		$thumbnail = get_field($field_keys['user_avatar'],'user_'.$profile_id);
		if ($thumbnail) {
			return $thumbnail['sizes']['thumbnail'];
		} else {
			return get_bloginfo('template_directory') . '/img/logo-coda.png';
		}
	}
	function the_profile_thumbnail_url( $profile_id, $thumbnail_size = 'thumbnail' ) {
		echo get_profile_thumbnail_url( $profile_id, $thumbnail_size );
	}

	function is_profile_created( $user_id ) {
		global $field_keys;
		$user = get_userdata( $user_id );
		if ( // Check all required fields
			$user->user_firstname &&
			$user->user_lastname &&
			get_field($field_keys['user_occupation'], 'user_'.$user_id) &&
			get_field($field_keys['user_location'], 'user_'.$user_id)
		) {
			return true;
		} else {
			echo "<!-- USER (" . $user_id . ")";
			echo "\nFirst Name: " . $user->user_firstname;
			echo "\nLast Name: " . $user->user_lastname;
			echo "\nOccupation: " . get_field($field_keys['user_occupation'], 'user_'.$user_id);
			echo "\nLocation: " . get_field($field_keys['user_location'], 'user_'.$user_id);
			// print_r($user);
			echo "\n\n -->";
			return false;
		}
	}

	function get_profile_activities( $user_id ) {
		$activities = array();
		$user = get_userdata( $user_id );

		// First get time that the user joined

		$activity = array(
			'activity_type' => 'join',
			'activity_description' => __('joined ','glp'),
			'activity_user' => $user_id,
			'activity_content' => null,
			'activity_timestamp' => strtotime($user->user_registered),
			'activity_icon' => 'user'
		);
		$activities[] = $activity;		

		// First get comments made by the user
		$comments = get_comments(array( 'user_id' => $user_id, 'status' => 'approve' ));
		foreach ($comments as $comment) {
			$activity = array(
				'activity_type' => 'comment',
				'activity_description' => __('commented on','glp') . ' <span class="activity-post">'.get_the_title($comment->comment_post_ID).'</span>',
				'activity_user' => $user_id,
				'activity_content' => $comment->comment_content,
				'activity_timestamp' => strtotime($comment->comment_date),
				'activity_icon' => 'comment'
			);
			$activities[] = $activity;
		}

		// Add mentions
		$mentions = get_comments(array( 'status' => 'approve' ));
		foreach ($mentions as $mention) {
			if ( strpos($mention->comment_content, '@'.$user->user_login) === 0 ) {
				$activity = array(
					'activity_type' => 'mention',
					'activity_description' => __('mentioned','glp') . ' <span class="activity-username">' . get_fullname($user->ID) .' </span>',
					'activity_user' => $mention->user_id,
					'activity_content' => $mention->comment_content,
					'activity_timestamp' => strtotime($mention->comment_date),
					'activity_icon' => 'reply'
				);
				$activities[] = $activity;
			}
		}

		// Add tags
		$tags = get_comments(array( 'status' => 'approve' ));
		foreach ($tags as $tag) {
			if ( strpos($tag->comment_content, '#') === 0 ) {
				$activity = array(
					'activity_type' => 'tag',
					'activity_description' => __('tagged','glp') . ' <span class="activity-post">'.get_the_title($mention->comment_post_ID).'</span>',
					'activity_user' => $mention->user_id,
					'activity_content' => $mention->comment_content,
					'activity_timestamp' => strtotime($mention->comment_date),
					'activity_icon' => 'tag'
				);
				$activities[] = $activity;
			}
		}		

		// Add favorites, bookmarks
		$favorites = get_field('favorites','user_'.$user_id);
		foreach ($favorites as $favorite_id) {
			$favorite = get_post($favorite_id);
			$activity = array(
					'activity_type' => 'favorite',
					'activity_description' => __('favorited','glp') . ' <span class="activity-post">'.get_the_title($favorite->ID).'</span>',
					'activity_user' => $favorite->user_id,
					'activity_content' => null,
					'activity_timestamp' => strtotime($favorite->comment_date),
					'activity_icon' => 'favorite'
				);
		}

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

	function get_profile_collaborators ($profile_id) {
		global $field_keys;

		$collaborators = array();
		$collaborator_ids = array();

		$participants = get_field($field_keys['user_shoots'], 'user_'.$profile_id);
		if ($participants) {

			foreach ($participants as $participant) {
				$collaborators += get_participant_crew_members($participant->ID);
			}
	
			foreach ($collaborators as $collaborator) {
				$collaborator_ids[] = $collaborator->ID;
			}

		}

		if ($collaborator_ids) { $collaborators = get_users(array(
			'include' => array_unique($collaborator_ids),
			'exclude' => $profile_id
		)); }

		return $collaborators;
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
            global $queue_key, $toggle_type;
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
                    $queue_key = 'field_52f17cb980e31';
                    break;
                case 'bookmark':
                    $queue_key = 'field_52f17cf180e32';
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
