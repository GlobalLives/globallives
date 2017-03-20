<?php
/**
 * Event API - can be used by other plugins to manipulate events
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__API' ) ) {
	class Tribe__Events__API {
		public static $modified_field_key = '_tribe_modified_fields';

		public static $valid_venue_keys = array(
			'Venue',
			'Address',
			'City',
			'Province',
			'State',
			'StateProvince',
			'Province',
			'Zip',
			'Phone',
		);

		public static $valid_organizer_keys = array(
			'Organizer',
			'Phone',
			'Email',
			'Website',
		);

		/**
		 * Create a new event
		 *
		 * @param array $args The post args.
		 *
		 * @return int|WP_Error The created event ID or a WP_Error object if it fails.
		 */
		public static function createEvent( $args ) {

			$args['post_type'] = Tribe__Events__Main::POSTTYPE;
			$event_id          = wp_insert_post( $args, true );

			if ( ! is_wp_error( $event_id ) ) {
				self::saveEventMeta( $event_id, $args, get_post( $event_id ) );
			}

			return $event_id;
		}

		/**
		 * Update an existing event
		 *
		 * @param int   $event_id The event ID to update.
		 * @param array $args    The post args.
		 *
		 * @return false|int The event ID.
		 */
		public static function updateEvent( $event_id, $args ) {
			$post = get_post( $event_id );
			$args['ID'] = $event_id;
			$args['post_type'] = Tribe__Events__Main::POSTTYPE;

			if ( wp_update_post( $args ) ) {
				self::saveEventMeta( $event_id, $args, $post );
			}

			return $event_id;
		}

		/**
		 * Delete an event - all instances if recurring
		 *
		 * @param int  $eventId      The Event ID to delete.
		 * @param bool $force_delete Same as the WP parameter.
		 *
		 * @return mixed
		 */
		public static function deleteEvent( $eventId, $force_delete = false ) {
			return wp_delete_post( $eventId, $force_delete );
		}

		/**
		 * Used by createEvent and updateEvent - saves all the various event meta
		 *
		 * @param int   $event_id The event ID we are modifying meta for.
		 * @param array $data     The meta fields we want saved.
		 * @param       WP_Post   The event itself.
		 *
		 */
		public static function saveEventMeta( $event_id, $data, $event = null ) {
			$tec = Tribe__Events__Main::instance();

			$data = self::prepare_event_date_meta( $event_id, $data );

			$now = current_time( 'timestamp' );

			$post_meta = self::get_and_flatten_event_meta( $event_id );

			if ( empty( $post_meta[ self::$modified_field_key ] ) ) {
				$modified = array();
			} else {
				$modified = $post_meta[ self::$modified_field_key ];
			}

			if ( empty( $data['EventHideFromUpcoming'] ) ) {
				delete_metadata( 'post', $event_id, '_EventHideFromUpcoming' );
			}

			update_metadata( 'post', $event_id, '_EventShowMapLink', isset( $data['venue']['EventShowMapLink'] ) );
			update_metadata( 'post', $event_id, '_EventShowMap', isset( $data['venue']['EventShowMap'] ) );

			if ( isset( $data['post_status'] ) ) {
				$post_status = $data['post_status'];
			} else {
				$post_status = get_post_status( $event_id );
			}

			// Handle the submission of linked post type posts (like venue and organizer)
			Tribe__Events__Linked_Posts::instance()->handle_submission( $event_id, $data );

			// Ordinarily there is a single cost value for each event, but addons (ie, ticketing plugins) may need
			// to record a number of different price points for the same event
			$event_cost = isset( $data['EventCost'] ) ? (array) $data['EventCost'] : array();
			$data['EventCost'] = (array) apply_filters( 'tribe_events_event_costs', $event_cost, $event_id );

			if ( isset( $data['FeaturedImage'] ) && ! empty( $data['FeaturedImage'] ) ) {
				update_metadata( 'post', $event_id, '_thumbnail_id', $data['FeaturedImage'] );
				unset( $data['FeaturedImage'] );
			}

			if ( isset( $data['EventAllDay'] ) && 'yes' === $data['EventAllDay'] ) {
				$data['EventDuration'] = null;
			}

			do_action( 'tribe_events_event_save', $event_id );

			//update meta fields
			foreach ( $tec->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $data[ $htmlElement ] ) && $tag != Tribe__Events__Main::EVENTSERROROPT ) {
					if ( is_string( $data[ $htmlElement ] ) ) {
						$data[ $htmlElement ] = filter_var( $data[ $htmlElement ], FILTER_SANITIZE_STRING );
					}
					// Fields with multiple values per key
					if ( is_array( $data[ $htmlElement ] ) ) {
						delete_metadata( 'post', $event_id, $tag );
						foreach ( $data[ $htmlElement ] as $value ) {
							add_metadata( 'post', $event_id, $tag, $value );
						}
					}
					// Fields with a single value per key
					else {
						update_metadata( 'post', $event_id, $tag, $data[ $htmlElement ] );
					}

					if ( self::is_meta_value_changed( $tag, $data, $post_meta ) ) {
						$modified[ $tag ] = $now;
					}
				}
			}

			// Set sticky state for calendar view.
			if ( $event instanceof WP_Post ) {
				if ( isset( $data['EventShowInCalendar'] ) && $data['EventShowInCalendar'] == 'yes' && $event->menu_order != '-1' ) {
					$update_event = array(
						'ID'         => $event_id,
						'menu_order' => '-1',
					);
					wp_update_post( $update_event );
				} elseif ( ( ! isset( $data['EventShowInCalendar'] ) || $data['EventShowInCalendar'] != 'yes' ) && $event->menu_order == '-1' ) {
					$update_event = array(
						'ID'         => $event_id,
						'menu_order' => '0',
					);
					wp_update_post( $update_event );
				}
			}

			// Set featured status
			empty( $data['feature_event'] )
				? tribe( 'tec.featured_events' )->unfeature( $event_id )
				: tribe( 'tec.featured_events' )->feature( $event_id );

			$fields_to_check_for_changes = array(
				'_EventShowInCalendar',
				'_thumbnail_id',
			);

			foreach ( $fields_to_check_for_changes as $field ) {
				if ( ! self::is_meta_value_changed( $field, $data, $post_meta ) ) {
					continue;
				}

				$modified[ $field ] = $now;
			}

			if ( $modified ) {
				update_post_meta( $event_id, self::$modified_field_key, $modified );
			}

			do_action( 'tribe_events_update_meta', $event_id, $data );
		}

		/**
		 * Determines if a meta value has been changed
		 *
		 * @param string $field Field to compare against
		 * @param array $new New data
		 * @param array $old Old post data
		 *
		 * @return boolean
		 */
		public static function is_meta_value_changed( $field, $new, $old ) {
			if ( 0 === strpos( $field, '_' ) ) {
				$field = ltrim( $field, '_' );
			}

			$prefixed_field = "_{$field}";

			if ( isset( $new[ $field ] ) && ! isset( $old[ $prefixed_field ] ) && ! empty( $new[ $field ] ) ) {
				return true;
			}

			if ( ! isset( $new[ $field ] ) && isset( $old[ $prefixed_field ] ) ) {
				return true;
			} elseif ( ! isset( $new[ $field ] ) ) {
				// if the new field isn't set and the old field isn't set, there's no change
				return false;
			}

			$data_value = $new[ $field ];

			if ( is_array( $data_value ) && ! count( $data_value ) ) {
				$data_value = null;
			}

			if ( ! isset( $old[ $prefixed_field ] ) || $data_value !== $old[ $prefixed_field ] ) {
				return true;
			}

			return false;
		}

		/**
		 * Determines if a post value has been changed
		 *
		 * @param string $field Field to compare against
		 * @param array $new New data
		 * @param array $old WP_Post pre-update
		 *
		 * @return boolean
		 */
		public static function is_post_value_changed( $field, $new, $old ) {
			if ( ! is_object( $new ) ) {
				$new = (object) $new;
			}

			if ( ! is_object( $old ) ) {
				$old = (object) $old;
			}

			if ( ! isset( $new->$field ) ) {
				return false;
			}

			if ( isset( $new->$field ) && ! isset( $old->$field ) ) {
				return true;
			}

			if ( $new->$field !== $old->$field ) {
				return true;
			}

			return false;
		}

		/**
		 * given a set of meta data, prepare date data if it exists
		 *
		 * @param $data array Associative array of event meta data
		 *
		 * @return array
		 */
		protected static function prepare_event_date_meta( $event_id, $data ) {
			$date_provided = false;

			if ( isset( $data['EventAllDay'] ) ) {
				if ( Tribe__Date_Utils::is_all_day( $data['EventAllDay'] ) ) {
					$data['EventAllDay'] = 'yes';
				} else {
					$data['EventAllDay'] = 'no';
				}
			}

			$datepicker_format = Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

			if ( isset( $data['EventStartDate'] ) ) {
				$data['EventStartDate'] = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $data['EventStartDate'] );
			}

			if ( isset( $data['EventEndDate'] ) ) {
				$data['EventEndDate'] = Tribe__Date_Utils::datetime_from_format( $datepicker_format, $data['EventEndDate'] );
			}

			if ( isset( $data['EventAllDay'] ) && 'yes' === $data['EventAllDay'] ) {
				$date_provided = true;
				$data['EventStartDate'] = tribe_beginning_of_day( $data['EventStartDate'] );
				$data['EventEndDate']   = tribe_end_of_day( $data['EventEndDate'] );
			} elseif ( isset( $data['EventStartDate'] ) && isset( $data['EventEndDate'] ) ) {
				$date_provided = true;
				delete_post_meta( $event_id, '_EventAllDay' );

				// EventStartTime will always be 24h Format
				if ( isset( $data['EventStartTime'] ) ) {
					$start_date_string = "{$data['EventStartDate']} {$data['EventStartTime']}";
				} else {
					$start_date_string = "{$data['EventStartDate']} {$data['EventStartHour']}:{$data['EventStartMinute']}:00";
					if ( isset( $data['EventStartMeridian'] ) ) {
						$start_date_string .= " {$data['EventStartMeridian']}";
					}
				}

				// EventEndTime will always be 24h Format
				if ( isset( $data['EventEndTime'] ) ) {
					$end_date_string = "{$data['EventEndDate']} {$data['EventEndTime']}";
				} else {
					$end_date_string = "{$data['EventEndDate']} {$data['EventEndHour']}:{$data['EventEndMinute']}:00";
					if ( isset( $data['EventEndMeridian'] ) ) {
						$end_date_string .= " {$data['EventEndMeridian']}";
					}
				}

				$data['EventStartDate'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $start_date_string ) );
				$data['EventEndDate'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $end_date_string ) );
			}

			if ( ! $date_provided ) {
				$data['EventStartDate'] = get_post_meta( $event_id, '_EventStartDate', true );
				$data['EventEndDate'] = get_post_meta( $event_id, '_EventEndDate', true );
				return $data;
			}

			// If a specific timezone was not specified, default to the sitewide timezone
			if ( ! isset( $data['EventTimezone'] ) ) {
				$data['EventTimezone'] = Tribe__Events__Timezones::wp_timezone_string();
			}

			// Additionally store datetimes in UTC
			if ( empty( $data['EventStartDateUTC'] ) ) {
				$data['EventStartDateUTC'] = Tribe__Events__Timezones::to_utc( $data['EventStartDate'], $data['EventTimezone'] );
			}

			if ( empty( $data['EventEndDateUTC'] ) ) {
				$data['EventEndDateUTC']   = Tribe__Events__Timezones::to_utc( $data['EventEndDate'], $data['EventTimezone'] );
			}

			if ( empty( $data['EventTimezoneAbbr'] ) ) {
				$data['EventTimezoneAbbr'] = Tribe__Events__Timezones::abbr( $data['EventStartDate'], $data['EventTimezone'] );
			}

			// sanity check that start date < end date
			$start_timestamp = strtotime( $data['EventStartDate'] );
			$end_timestamp   = strtotime( $data['EventEndDate'] );

			if ( $start_timestamp > $end_timestamp ) {
				$data['EventEndDate'] = $data['EventStartDate'];
			}

			$data['EventDuration'] = strtotime( $data['EventEndDate'] ) - $start_timestamp;

			return $data;
		}//end prepare_event_date_meta

		/**
		 * Triggers an update of the cost meta data (min <-> max) for an event.
		 *
		 * This is primarily for internal use where an addon needs to update the cost meta
		 * data for an event (but no other meta fields). To actually add to or modify the
		 * range of cost values the tribe_events_event_costs filter hook should be
		 * leveraged.
		 *
		 * @param $event_id
		 */
		public static function update_event_cost( $event_id ) {
			// Loads current event costs, on construct
			// Tribe__Events__Tickets__Tickets->get_ticket_prices() adds them to this filter
			$event_cost = (array) apply_filters( 'tribe_events_event_costs', array(), $event_id );

			// Kill the old cost meta data
			delete_post_meta( $event_id, '_EventCost' );

			// Add fresh entries for each of the new values
			foreach ( $event_cost as $cost ) {
				add_post_meta( $event_id, '_EventCost', $cost );
			}
		}

		/**
		 * Saves the event organizer information passed via an event
		 *
		 * @param array   $data        The organizer data.
		 * @param WP_Post $post        The post.
		 * @param string  $post_status The intended post status.
		 *
		 * @return mixed
		 */
		private static function saveEventOrganizer( $data, $post = null, $post_status = 'publish' ) {
			$organzier_id = ! empty( $data['OrganizerID'] ) ? $data['OrganizerID'] : null;
			return Tribe__Events__Organizer::instance()->save( $organizer_id, $data, Tribe__Events__Organizer::POSTTYPE, $post_status );
		}

		/**
		 * Saves the event venue information passed via an event
		 *
		 * @param array   $data        The venue data.
		 * @param WP_Post $post        The venue object.
		 * @param string  $post_status The intended post status.
		 *
		 * @return mixed.
		 */
		private static function saveEventVenue( $data, $post = null, $post_status = 'publish' ) {
			$venue_id = ! empty( $data['VenueID'] ) ? $data['VenueID'] : null;

			return Tribe__Events__Venue::instance()->save( $venue_id, $data, Tribe__Events__Venue::POSTTYPE, $post_status );
		}

		/**
		 * Creates a new organizer
		 *
		 * @param array  $data        The organizer data.
		 * @param string $post_status the intended post status.
		 *
		 * @return mixed
		 */
		public static function createOrganizer( $data, $post_status = 'publish' ) {
			return Tribe__Events__Organizer::instance()->create( $data, $post_status );
		}

		/**
		 * Check to see if any organizer data set
		 *
		 * @param array $data the organizer data.
		 *
		 * @return bool If there is ANY organizer data set, return true.
		 */
		private static function someOrganizerDataSet( $data ) {
			return Tribe__Events__Organizer::instance()->has_organizer_data( $data );
		}

		/**
		 * Deletes an organizer
		 *
		 * @param int  $organizer_id  The organizer ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 */
		public static function deleteOrganizer( $organizer_id, $force_delete = false ) {
			return Tribe__Events__Organizer::instance()->delete( $organizer_id, $force_delete );
		}

		/**
		 * Updates an organizer
		 *
		 * @param int   $organizer_id The organizer ID to update.
		 * @param array $data        The organizer data.
		 *
		 */
		public static function updateOrganizer( $organizer_id, $data ) {
			return Tribe__Events__Organizer::instance()->update( $organizer_id, $data );
		}

		/**
		 * Saves organizer meta
		 *
		 * @param int   $organizer_id The organizer ID.
		 * @param array $data        The organizer data.
		 *
		 */
		private static function saveOrganizerMeta( $organizer_id, $data ) {
			return Tribe__Events__Organizer::instance()->save_meta( $organizer_id, $data );
		}

		/**
		 * Creates a new venue
		 *
		 * @param array  $data        The venue data.
		 * @param string $post_status the intended post status.
		 *
		 * @return mixed
		 */
		public static function createVenue( $data, $post_status = 'publish' ) {
			return Tribe__Events__Venue::instance()->create( $data, $post_status );
		}

		/**
		 * Check to see if any venue data set
		 *
		 * @param array $data the venue data.
		 *
		 * @return bool If there is ANY venue data set, return true.
		 */
		private static function someVenueDataSet( $data ) {
			return Tribe__Events__Venue::instance()->has_venue_data( $data );
		}

		/**
		 * Updates an venue
		 *
		 * @param int   $venue_id The venue ID to update.
		 * @param array $data    The venue data.
		 *
		 */
		public static function updateVenue( $venue_id, $data ) {
			return Tribe__Events__Venue::instance()->update( $venue_id, $data );
		}

		/**
		 * Deletes a venue
		 *
		 * @param int  $venue_id      The venue ID to delete.
		 * @param bool $force_delete Same as WP param.
		 *
		 */
		public static function deleteVenue( $venue_id, $force_delete = false ) {
			return Tribe__Events__Venue::instance()->delete( $venue_id, $force_delete );
		}

		/**
		 * Saves venue meta
		 *
		 * @param int   $venue_id The venue ID.
		 * @param array $data    The venue data.
		 *
		 */
		private static function saveVenueMeta( $venue_id, $data ) {
			return Tribe__Events__Venue::instance()->save_meta( $venue_id, $data );
		}

		/**
		 * Gets all post meta and flattens it out a bit
		 *
		 * @param int $event_id Post ID for event
		 *
		 * @return array
		 */
		public static function get_and_flatten_event_meta( $event_id ) {
			$temp_post_meta = get_post_meta( $event_id );
			$post_meta = array();
			foreach ( (array) $temp_post_meta as $key => $value ) {
				if ( 1 === count( $value ) ) {
					$post_meta[ $key ] = maybe_unserialize( reset( $value ) );
				} else {
					$post_meta[ $key ] = maybe_unserialize( $value );
				}
			}

			return $post_meta;
		}
	}
}
