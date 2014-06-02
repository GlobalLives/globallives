<?php

// @TODO two functions that do the exact same thing
if ( !function_exists('tribe_get_single_ical_link') ) {
	/**
	 * iCal Link (Single)
	 *
	 * Returns an ical feed for a single event. Must be used in the loop.
	 *
	 * @return string URL for ical for single event.
	 * @since 3.6
	 */
	function tribe_get_single_ical_link() {
		$output = TribeiCal::get_ical_link();
		return apply_filters( 'tribe_get_ical_link', $output );
	}
}

if ( !function_exists('tribe_get_ical_link') ) {
	/**
	 * iCal Link
	 *
	 * Returns a sitewide ical link
	 *
	 * @return string URL for ical dump.
	 * @since 3.6
	 */
	function tribe_get_ical_link() {
		$output = TribeiCal::get_ical_link();
		return apply_filters( 'tribe_get_ical_link', $output );
	}

}

if ( ! function_exists( 'tribe_get_gcal_link' ) ) {
	/**
	 * Google Calendar Link
	 *
	 * Returns an "add to Google Calendar link for a single event. Must be used in the loop
	 *
	 * @param int $postId (optional)
	 *
	 * @return string URL for google calendar.
	 * @since 2.0
	 */
	function tribe_get_gcal_link( $postId = null ) {
		$postId    = TribeEvents::postIdHelper( $postId );
		$tribe_ecp = TribeEvents::instance();
		$output    = esc_url( $tribe_ecp->googleCalendarLink( $postId ) );

		return apply_filters( 'tribe_get_gcal_link', $output );
	}
}


