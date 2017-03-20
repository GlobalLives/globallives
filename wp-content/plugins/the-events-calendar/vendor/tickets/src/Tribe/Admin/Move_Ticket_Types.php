<?php


/**
 * Class Tribe__Tickets__Admin__Move_Ticket_Types
 *
 * Handles moving ticket types from a post to another.
 */
class Tribe__Tickets__Admin__Move_Ticket_Types extends Tribe__Tickets__Admin__Move_Tickets {
	protected $dialog_name = 'move_ticket_types';

	public function setup() {
		add_action( 'admin_init', array( $this, 'dialog' ) );
		add_action( 'wp_ajax_move_ticket_types_post_list', array( $this, 'update_post_choices' ) );
		add_action( 'wp_ajax_move_ticket_type', array( $this, 'move_ticket_type_requests' ) );
		add_action( 'tribe_tickets_ticket_type_moved', array( $this, 'notify_event_attendees' ), 100, 3 );
		add_action( 'tribe_events_tickets_metabox_advanced', array( $this, 'expose_ticket_history' ), 100 );
		add_filter( 'tribe_tickets_move_tickets_template_vars', array( $this, 'move_tickets_dialog_vars' ) );
		add_filter( 'tribe_tickets_move_tickets_script_data', array( $this, 'move_tickets_dialog_data' ) );
	}

	public function move_tickets_dialog_vars( array $vars ) {
		if ( ! $this->is_move_tickets_dialog() ) {
			return $vars;
		}

		return array_merge( $vars, array(
			'title'    => __( 'Move Ticket Types', 'event-tickets' ),
			'mode'     => 'ticket_type_only',
		) );
	}

	public function move_tickets_dialog_data( array $data ) {
		if ( ! $this->is_move_tickets_dialog() ) {
			return $data;
		}

		return array_merge( $data, array(
			'ticket_type_id' => absint( $_GET[ 'ticket_type_id' ] ),
			'src_post_id'    => absint( $_GET[ 'post' ] ),
			'mode'           => 'ticket_type_only',
		) );
	}

	/**
	 * Returns an updated list of post choices.
	 */
	public function update_post_choices() {
		if ( ! wp_verify_nonce( $_POST['check' ], 'move_ticket_type' ) ) {
			wp_send_json_error();
		}

		$args = wp_parse_args( $_POST, array(
			'post_type'    => '',
			'search_terms' => '',
			'ignore'       => '',
		) );

		wp_send_json_success( array( 'posts' =>  $this->get_possible_matches( $args ) ) );
	}

	/**
	 * Returns a list of post types for which tickets are currently enabled.
	 *
	 * The list is expressed as an array in the following format:
	 *
	 *     [ 'slug' => 'name', ... ]
	 *
	 * @return array
	 */
	protected function get_post_types_list() {
		$types_list = array( 'all' => __( 'All supported types', 'tribe-tickets' ) );

		foreach ( Tribe__Tickets__Main::instance()->post_types() as $type ) {
			$pto = get_post_type_object( $type );
			$types_list[ $type ] = $pto->label;
		}

		return $types_list;
	}

	/**
	 * Listens out for ajax requests to move a ticket type to a new post.
	 */
	public function move_ticket_type_requests() {
		$args = wp_parse_args( $_POST, array(
			'check'          => '',
			'ticket_type_id' => 0,
			'target_post_id' => 0,
			'src_post_id'    => 0,
		) );

		if ( ! wp_verify_nonce( $args['check' ], 'move_tickets' ) ) {
			wp_send_json_error();
		}

		$ticket_type_id = absint( $args[ 'ticket_type_id' ] );
		$destination_id = absint( $args[ 'target_post_id' ] );

		if ( ! $ticket_type_id || ! $destination_id ) {
			wp_send_json_error( array(
				'message' => __( 'Ticket type could not be moved: the ticket type or destination post was invalid.', 'event-tickets' )
			) );
		}

		if ( ! $this->move_ticket_type( $ticket_type_id, $destination_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Ticket type could not be moved: unexpected failure during reassignment.', 'event-tickets' )
			) );
		}

		wp_send_json_success( array(
			'message' => sprintf(
				'<p>' . __( 'Ticket type %1$s for %2$s was successfully moved to %3$s. All previously sold tickets of this type have been transferred to %3$s. Please adjust stock manually as needed. %1$s ticket holders have received an email notifying them of the change. You may now close this window!', 'event-tickets' ) . '</p>',
				'<a href="' . esc_url( get_admin_url( null, '/post.php?post=' . $ticket_type_id . '&action=edit' ) ) . '" target="_blank">' . get_the_title( $ticket_type_id ) . '</a>',
				'<a href="' . esc_url( get_admin_url( null, '/post.php?post=' . $src_post_id . '&action=edit' ) ) . '" target="_blank">' . get_the_title( $src_post_id ) . '</a>',
				'<a href="' . esc_url( get_admin_url( null, '/post.php?post=' . $destination_id . '&action=edit' ) ) . '" target="_blank">' . get_the_title( $destination_id ) . '</a>'
			),
			'remove_ticket_type' => $ticket_type_id,
		) );
	}

	/**
	 * Moves a ticket type to a new post.
	 *
	 * For example, a VIP ticket type may be attached to an event called "Tuesday Race Day",
	 * however if this event is rained out it may be desirable to move the VIP ticket type
	 * to a new "Thursday Race Day" event instead.
	 *
	 * Moving the ticket type, rather than recreating it, can be useful when ticket orders
	 * have already been placed.
	 *
	 * @param int $ticket_type_id
	 * @param int $destination_post_id
	 * @param int $instigator_id (optional) the user who initiated or requested the change
	 *
	 * @return bool
	 */
	public function move_ticket_type( $ticket_type_id, $destination_post_id, $instigator_id = null ) {
		if ( null === $instigator_id ) {
			$instigator_id = get_current_user_id();
		}

		$ticket_type = Tribe__Tickets__Tickets::load_ticket_object( $ticket_type_id );

		if ( null === $ticket_type ) {
			return false;
		}

		$provider = $ticket_type->get_provider();
		$event_key = $provider->get_event_key();

		/**
		 * Fires immediately before a ticket type is moved.
		 *
		 * @param int $ticket_type_id
		 * @param int $destination_post_id
		 * @param int $instigator_id
		 */
		do_action( 'tribe_tickets_ticket_type_before_move', $ticket_type_id, $destination_post_id, $instigator_id );

		$src_post_id = get_post_meta( $ticket_type_id, $event_key, true );
		$success = update_post_meta( $ticket_type_id, $event_key, $destination_post_id );

		if ( ! $success ) {
			return false;
		}

		$history_message = sprintf(
			__( 'Ticket type was moved to <a href="%1$s" target="_blank">%2$s</a> from <a href="%3$s" target="_blank">%4$s</a>', 'event-tickets' ),
			get_permalink( $destination_post_id ),
			get_the_title( $destination_post_id ),
			get_permalink( $src_post_id ),
			get_the_title( $src_post_id )
		);

		$history_data = array(
			'src_event_id' => $src_post_id,
			'tgt_event_it' => $destination_post_id,
		);

		Tribe__Post_History::load( $ticket_type_id )->add_entry( $history_message, $history_data );

		/**
		 * Fires when a ticket type is relocated from one post to another.
		 *
		 * @param int $ticket_type_id       the ticket type which has been moved
		 * @param int $destination_post_id  the post to which the ticket type has been moved
		 * @param int $src_post_id          the post which previously hosted the ticket type
		 * @param int $instigator_id        the user who initiated the change
		 */
		do_action( 'tribe_tickets_ticket_type_moved', $ticket_type_id, $destination_post_id, $src_post_id, $instigator_id );

		return true;
	}

	/**
	 * Notify attendees who purchased tickets of this type that it has been
	 * reassigned to a different post/event.
	 *
	 * @param int $ticket_type_id
	 * @param int $new_post_id
	 * @param int $original_post_id
	 */
	public function notify_event_attendees( $ticket_type_id, $new_post_id, $original_post_id ) {
		$to_notify = array();

		// Build a list of email addresses we want to send notifications of the change to
		foreach ( Tribe__Tickets__Tickets::get_event_attendees( $new_post_id ) as $attendee ) {
			// We're not interested in attendees who were already attending this event
			if ( (int) $ticket_type_id !== (int) $attendee[ 'product_id' ] ) {
				continue;
			}

			// Skip if an email address isn't available
			if ( ! isset( $attendee[ 'purchaser_email' ] ) ) {
				continue;
			}

			if ( ! isset( $to_notify[ $attendee[ 'purchaser_email' ] ] ) ) {
				$to_notify[ $attendee[ 'purchaser_email' ] ] = 1;
			} else {
				$to_notify[ $attendee[ 'purchaser_email' ] ]++;
			}
		}

		// Dispatch the emails
		foreach ( $to_notify as $email_addr => $num_tickets ) {
			/**
			 * Sets the moved ticket type email address.
			 *
			 * @param string $email_addr
			 */
			$to = apply_filters( 'tribe_tickets_ticket_type_moved_email_recipient', $email_addr );

			/**
			 * Sets any attachments for the moved ticket type email address.
			 *
			 * @param array $attachments
			 */
			$attachments = apply_filters( 'tribe_tickets_ticket_type_moved_email_attachments', array() );

			/**
			 * Sets the HTML for the moved ticket type email.
			 *
			 * @param string $html
			 */
			$content = apply_filters( 'tribe_tickets_ticket_type_moved_email_content',
				$this->generate_email_content( $ticket_type_id, $original_post_id, $new_post_id, $num_tickets )
			);

			/**
			 * Sets any headers for the moved ticket type email.
			 *
			 * @param array $headers
			 */
			$headers = apply_filters( 'tribe_tickets_ticket_type_moved_email_headers',
				array( 'Content-type: text/html' )
			);

			/**
			 * Sets the subject line for the moved ticket type email.
			 *
			 * @param string $subject
			 */
			$subject = apply_filters( 'tribe_tickets_ticket_type_moved_email_subject',
				sprintf( __( 'Changes to your tickets from %s', 'event-tickets' ), get_bloginfo( 'name' ) )
			);

			wp_mail( $to, $subject, $content, $headers, $attachments );
		}
	}

	/**
	 * @param int    $tgt_ticket_type_id
	 * @param int    $src_event_id
	 * @param int    $tgt_event_id
	 * @param int    $num_tickets
	 *
	 * @return string
	 */
	protected function generate_email_content( $tgt_ticket_type_id, $src_event_id, $tgt_event_id, $num_tickets ) {
		$vars = array(
			'original_event_id'   => $src_event_id,
			'original_event_name' => get_the_title( $src_event_id ),
			'new_event_id'        => $tgt_event_id,
			'new_event_name'      => get_the_title( $tgt_event_id ),
			'ticket_type_id'      => $tgt_ticket_type_id,
			'ticket_type_name'    => get_the_title( $tgt_ticket_type_id ),
			'num_tickets'         => $num_tickets
		);

		return tribe_tickets_get_template_part( 'tickets/email-ticket-type-moved', null, $vars, false );
	}

	/**
	 * Prints out the audit trail/post history for the current ticket, if
	 * available.
	 */
	public function expose_ticket_history() {
		// This will only be available during edit requests for existing tickets
		if ( ! isset( $_POST[ 'ticket_id' ] ) ) {
			return;
		}

		$ticket_id = absint( $_POST[ 'ticket_id' ] );
		$ticket_object = Tribe__Tickets__Tickets::load_ticket_object( $ticket_id );

		if ( ! $ticket_object || ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		/**
		 * Allows the display of a ticket type's post history ("audit trail")
		 * within the ticket editor to be turned on or off.
		 *
		 * @param bool $show_history
		 */
		if ( ! apply_filters( 'tribe_tickets_show_ticket_type_history_in_ticket_editor', true ) ) {
			return;
		}

		// $provider is needed to form the correct table row classes (otherwise the
		// history section will not show/hide appropriately)
		$provider = $ticket_object->provider_class;
		$history = Tribe__Post_History::load( $ticket_id );

		if ( ! $history->has_entries() ) {
			return;
		}

		include EVENT_TICKETS_DIR . '/src/admin-views/ticket-type-history.php';
	}
}