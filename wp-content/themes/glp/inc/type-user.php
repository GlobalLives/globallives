<?php
	global $field_keys;

/*	==========================================================================
	User
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

	function get_profile_thumbnail_url( $profile_id, $size = 'thumbnail' ) {
		global $field_keys;

		// First try the Profile page
		if ($user_avatar = get_field($field_keys['user_avatar'], 'user_'.$profile_id)) {
			if ($user_avatar['sizes'][$size] != '') { return $user_avatar['sizes'][$size]; }
		}

		// Then try Social Login
		if ($social_login = get_usermeta($profile_id, 'oa_social_login_user_picture')) {
			return $social_login;
		}

		// Then try Gravatar
		if ($get_avatar = get_avatar($profile_id, $size)) {
			preg_match("/src='(.*?)'/i", $get_avatar, $matches);
			return $matches[1];
		}

		// Finally, give the default
		return get_bloginfo('template_directory') . '/img/logo-coda.png';
	}
	function the_profile_thumbnail_url( $profile_id, $size = 'thumbnail' ) {
		echo get_profile_thumbnail_url( $profile_id, $size );
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
		$favorites = get_field($field_keys['user_favorites'],'user_'.$user_id);
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