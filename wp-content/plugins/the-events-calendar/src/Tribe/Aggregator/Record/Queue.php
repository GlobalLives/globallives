<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Queue {
	public static $in_progress_key = 'tribe_aggregator_queue_';
	public static $queue_key = 'queue';
	public static $activity_key = 'activity';

	public $record;

	public $is_fetching = false;
	protected $importer;

	/**
	 * @var Tribe__Events__Aggregator__Record__Activity
	 */
	protected $activity;

	/**
	 * Holds the Items that will be processed
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Holds the Items that will be processed next
	 *
	 * @var array
	 */
	public $next = array();

	/**
	 * How many items are going to be processed
	 *
	 * @var int
	 */
	public $total = 0;

	/**
	 * @var Tribe__Events__Aggregator__Record__Queue_Cleaner
	 */
	protected $cleaner;

	/**
	 * Whether any real processing should happen for the queue or not.
	 *
	 * @var bool
	 */
	protected $null_process = false;

	/**
	 * Tribe__Events__Aggregator__Record__Queue constructor.
	 *
	 * @param int|Tribe__Events__Aggregator__Record__Abstract       $record
	 * @param array                                                 $items
	 * @param Tribe__Events__Aggregator__Record__Queue_Cleaner|null $cleaner
	 */
	public function __construct( $record, $items = array(), Tribe__Events__Aggregator__Record__Queue_Cleaner $cleaner = null ) {
		if ( is_numeric( $record ) ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record );
		}

		if ( ! is_object( $record ) || ! in_array( 'Tribe__Events__Aggregator__Record__Abstract', class_parents( $record ) ) ) {
			$this->null_process = true;

			return;
		}

		if ( is_wp_error( $items ) ) {
			$this->null_process = true;

			return;
		}

		$this->cleaner = $cleaner ? $cleaner : new Tribe__Events__Aggregator__Record__Queue_Cleaner();

		$this->cleaner->remove_duplicate_pending_records_for( $record );

		$failed = $this->cleaner->maybe_fail_stalled_record( $record );

		if ( $failed ) {
			$this->null_process = true;

			return;
		}

		$this->record = $record;

		$this->activity();

		if ( ! empty( $items ) ) {
			if ( 'fetch' === $items ) {
				$this->is_fetching = true;
				$this->items = 'fetch';
			} else {
				$this->init_queue( $items );
			}

			$this->save();
		} else {
			$this->load_queue();
		}
		$this->cleaner = $cleaner;
	}

	public function __get( $key ) {
		switch ( $key ) {
			case 'activity':
				return $this->activity();
				break;
		}
	}

	public function init_queue( $items ) {
		if ( 'csv' === $this->record->origin ) {
			$this->record->reset_tracking_options();
			$this->importer = $items;
			$this->total = $this->importer->get_line_count();
			$this->items = array_fill( 0, $this->total, true );
		} else {
			$this->items = $items;

			// Count the Total of items now and stores as the total
			$this->total = count( $this->items );
		}
	}

	public function load_queue() {
		$this->items = $this->record->meta[ self::$queue_key ];

		if ( 'fetch' === $this->items ) {
			$this->is_fetching = true;
		}
	}

	public function activity() {
		if ( empty( $this->activity ) ) {
			if (
				empty( $this->record->meta[ self::$activity_key ] )
				|| ! $this->record->meta[ self::$activity_key ] instanceof Tribe__Events__Aggregator__Record__Activity
			) {
				$this->activity = new Tribe__Events__Aggregator__Record__Activity;
			} else {
				$this->activity = $this->record->meta[ self::$activity_key ];
			}
		}

		return $this->activity;
	}

	/**
	 * Allows us to check if the Events Data has still pending
	 *
	 * @return boolean
	 */
	public function is_fetching() {
		return $this->is_fetching;
	}

	/**
	 * Shortcut to check how many items are going to be processed next
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Shortcut to check if this queue is empty
	 *
	 * @return boolean
	 */
	public function is_empty() {
		return 0 === $this->count();
	}

	/**
	 * Gets the queue's total
	 *
	 * @return int
	 */
	public function get_total() {
		return $this->count() + $this->activity->count( $this->get_queue_type() );
	}

	/**
	 * Saves queue data to relevant meta keys on the post
	 *
	 * @return self
	 */
	public function save() {
		$this->record->update_meta( self::$activity_key, $this->activity );

		if ( empty( $this->items ) ) {
			$this->record->delete_meta( self::$queue_key );
		} else {
			$this->record->update_meta( self::$queue_key, $this->items );
		}

		// If we have a parent also update that
		if ( ! empty( $this->record->post->post_parent ) ) {
			$parent = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->record->post->post_parent );
			if ( isset( $parent->meta[ self::$activity_key ] ) ) {
				$activity = $parent->meta[ self::$activity_key ];

				if ( $activity instanceof Tribe__Events__Aggregator__Record__Activity ) {
					$parent->update_meta( self::$activity_key, $activity->merge( $this->activity ) );
				}
			}
		}

		// Updates the Modified time for the Record Log
		$args = array(
			'ID' => $this->record->post->ID,
			'post_modified' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, current_time( 'timestamp' ) ),
		);

		if ( empty( $this->items ) ) {
			$args['post_status'] = Tribe__Events__Aggregator__Records::$status->success;
		}

		wp_update_post( $args );

		return $this;
	}

	/**
	 * Processes a batch for the queue
	 *
	 * @return self|Tribe__Events__Aggregator__Record__Activity
	 */
	public function process( $batch_size = null ) {
		if ( $this->null_process ) {
			return $this;
		}

		if ( $this->is_fetching() ) {
			$data = $this->record->prep_import_data();

			if (
				'fetch' === $data
				|| ! is_array( $data )
				|| is_wp_error( $data )
			) {
				return $this->activity();
			}

			$this->init_queue( $data );
			$this->save();
		}

		// Every time we are about to process we reset the next var
		$this->next = array();

		if ( ! $batch_size ) {
			$batch_size = apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size );
		}

		for ( $i = 0; $i < $batch_size; $i++ ) {
			if ( 0 === count( $this->items ) ) {
				break;
			}

			// Remove the Event from the Items remaining
			$this->next[] = array_shift( $this->items );
		}

		if ( 'csv' === $this->record->origin ) {
			$activity = $this->record->continue_import();
		} else {
			$activity = $this->record->insert_posts( $this->next );
		}

		$this->activity = $this->activity()->merge( $activity );

		return $this->save();
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage() {
		if ( 0 === $this->count() ) {
			return 0;
		}

		$total     = $this->get_total();
		$processed = $total - $this->count();
		$percent   = ( $processed / $total ) * 100;
		return (int) $percent;
	}

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event:
	 * this can be useful to prevent collisions between cron-based updated and realtime
	 * updates.
	 *
	 * The flag naturally expires after an hour to allow for recovery if for instance
	 * execution hangs half way through the processing of a batch.
	 */
	public function set_in_progress_flag() {
		Tribe__Post_Transient::instance()->set( $this->record->id, self::$in_progress_key, true, HOUR_IN_SECONDS );
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
		Tribe__Post_Transient::instance()->delete( $this->record->id, self::$in_progress_key );
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		Tribe__Post_Transient::instance()->get( $this->record->id, self::$in_progress_key );
	}

	/**
	 * Returns the primary post type the queue is processing
	 *
	 * @return string
	 */
	public function get_queue_type() {
		$item_type = Tribe__Events__Main::POSTTYPE;

		if ( 'csv' === $this->record->origin ) {
			$item_type = $this->record->meta['content_type'];
		}

		return $item_type;
	}
}

