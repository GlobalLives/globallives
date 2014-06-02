<?php
/**
 * Single Event Meta (Map) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/details.php
 *
 * @package TribeEventsCalendar
 * @since 3.6
 */

$map = apply_filters( 'tribe_event_meta_venue_map', tribe_get_embedded_map() );
if ( empty( $map ) ) return;
?>

<div class="tribe-events-venue-map">
	<?php
	do_action( 'tribe_events_single_meta_map_section_start' );
	echo $map;
	do_action( 'tribe_events_single_meta_map_section_end' );
	?>
</div>