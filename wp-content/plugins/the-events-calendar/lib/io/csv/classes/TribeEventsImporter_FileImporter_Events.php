<?php

/**
 * Class TribeEventsImporter_FileImporter_Events
 */
class TribeEventsImporter_FileImporter_Events extends TribeEventsImporter_FileImporter {

	protected $required_fields = array('event_name', 'event_start_date');

	protected function match_existing_post( array $record ) {
		$start_date = $this->get_event_start_date( $record );
		$end_date = $this->get_event_end_date( $record );
		$query_args = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => $this->get_value_by_key( $record, 'event_name' ),
			'meta_query' => array(
				array(
					'key' => '_EventStartDate',
					'value' => $start_date,
				),
			),
			'fields' => 'ids',
			'posts_per_page' => 1,
		);
		if ( !empty($end_date) ) {
			$query_args['meta_query'][] = array(
				'key' => '_EventEndDate',
				'value' => $end_date,
			);
		}
		add_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10, 2 );
		$matches = get_posts($query_args);
		remove_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10, 2 );
		if ( empty($matches) ) {
			return 0;
		}
		return reset($matches);
	}

	protected function update_post( $post_id, array $record ) {
		$event = $this->build_event_array( $record );
		TribeEventsAPI::updateEvent( $post_id, $event );
	}


	protected function create_post( array $record ) {
		$event = $this->build_event_array( $record );
		$id = TribeEventsAPI::createEvent( $event );
		return $id;
	}

	private function get_event_start_date( array $record ) {
		$start_date = $this->get_value_by_key( $record, 'event_start_date' );
		$start_time = $this->get_value_by_key( $record, 'event_start_time' );
		if ( !empty($start_time) ) {
			$start_date .= ' '.$start_time;
		}
		$start_date = date('Y-m-d H:i:s', strtotime($start_date));
		return $start_date;
	}

	private function get_event_end_date( array $record ) {
		$start_date = $this->get_event_start_date( $record );
		$end_date = $this->get_value_by_key( $record, 'event_end_date' );
		$end_time = $this->get_value_by_key( $record, 'event_end_time' );
		if ( empty($end_date) ) {
			$end_date = $start_date;
		}
		if ( !empty($end_time) ) {
			$end_date .= ' '.$end_time;
		}
		if ( !empty($end_date) ) {
			$end_date = date('Y-m-d H:i:s', strtotime($end_date));
		}
		if ( $end_date < $start_date ) {
			$end_date = $start_date;
		}
		return $end_date;
	}

	private function is_all_day( array $record ) {
		$all_day = FALSE;
		if ( in_array(strtolower($this->get_value_by_key( $record, 'event_all_day' )), array('yes', 'true', '1')) ) {
			$all_day = TRUE;
		}
		return $all_day;
	}

	private function build_event_array( array $record ) {
		$start_date = strtotime($this->get_event_start_date($record));
		$end_date = strtotime($this->get_event_end_date($record));
		$all_day = $this->is_all_day( $record ) ? 'yes' : NULL;

		$event = array(
			'post_type' => TribeEvents::POSTTYPE,
			'post_title' => $this->get_value_by_key( $record, 'event_name' ),
			'post_status' => 'publish',
			'post_content' => $this->get_value_by_key( $record, 'event_description' ),
			'EventStartDate' => date( 'Y-m-d', $start_date ),
			'EventStartHour' => date( 'h', $start_date ),
			'EventStartMinute' => date( 'i', $start_date ),
			'EventStartMeridian' => date( 'a', $start_date ),
			'EventEndDate' => date( 'Y-m-d', $end_date ),
			'EventEndHour' => date( 'h', $end_date ),
			'EventEndMinute' => date( 'i', $end_date ),
			'EventEndMeridian' => date( 'a', $end_date ),
			'EventShowMapLink' => $this->get_value_by_key( $record, 'event_show_map_link' ),
			'EventShowMap' => $this->get_value_by_key( $record, 'event_show_map' ),
			'EventCost' => $this->get_value_by_key( $record, 'event_cost' ),
			'EventAllDay' => $all_day,
			'EventHideFromUpcoming' => $this->get_value_by_key( $record, 'event_hide' )
		);

		if ( $organizer_id = $this->find_matching_organizer_id($record) ) {
			$event['Organizer'] = array( 'OrganizerID' => $organizer_id );
		}

		if ( $venue_id = $this->find_matching_venue_id($record) ) {
			$event['Venue'] = array( 'VenueID' => $venue_id );
		}

		if ( $cats = $this->get_value_by_key( $record, 'event_category' ) ) {
			$event['tax_input'][TribeEvents::TAXONOMY] = $this->translate_terms_to_ids(explode(',',$cats));
		}

		return $event;

	}

	private function find_matching_organizer_id( $record ) {
		$name = $this->get_value_by_key( $record, 'event_organizer_name' );
		return $this->find_matching_post_id( $name, TribeEvents::ORGANIZER_POST_TYPE );
	}

	private function find_matching_venue_id( $record ) {
		$name = $this->get_value_by_key( $record, 'event_venue_name' );
		return $this->find_matching_post_id( $name, TribeEvents::VENUE_POST_TYPE );
	}

	/**
	 * When passing terms to wp_insert_post(), we're required to have IDs
	 * for hierarchical taxonomies, not strings
	 *
	 * @param array $terms
	 * @return int[]
	 */
	private function translate_terms_to_ids( array $terms ) {
		$term_ids = array();
		// duplicating some code from wp_set_object_terms()
		foreach ( $terms as $term) {
			if ( !strlen(trim($term)) )
				continue;

			if ( !$term_info = term_exists($term, TribeEvents::TAXONOMY) ) {
				// Skip if a non-existent term ID is passed.
				if ( is_int($term) )
					continue;
				$term_info = wp_insert_term($term, TribeEvents::TAXONOMY);
			}
			if ( is_wp_error($term_info) ) {
				continue;
			}
			$term_ids[] = $term_info['term_id'];
		}
		return $term_ids;
	}

}
