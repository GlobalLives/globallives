<?php

if ( ! function_exists( 'tribe_is_day' ) ) {
	/**
	 * Single Day Test
	 *
	 * Returns true if the query is set for single day, false otherwise
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_day() {
		$tribe_ecp = TribeEvents::instance();
		$is_day    = ( $tribe_ecp->displaying == 'day' ) ? true : false;

		return apply_filters( 'tribe_is_day', $is_day );
	}

}

if ( ! function_exists( 'tribe_get_day_link' ) ) {
	/**
	 * Link Event Day
	 *
	 * @param string $date
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_day_link( $date = null ) {
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_day_link', $tribe_ecp->getLink('day', $date), $date);
	}
}

if ( ! function_exists( 'tribe_get_linked_day' ) ) {
	/**
	 * Day View Link
	 *
	 * Get a link to day view
	 *
	 * @param string $date
	 * @param string $day
	 * @return string HTML linked date
	 * @since 2.0
	 */
	function tribe_get_linked_day($date, $day) {
		$return = '';
		$return .= "<a href='" . tribe_get_day_link($date) . "'>";
		$return .= $day;
		$return .= "</a>";
		return apply_filters('tribe_get_linked_day', $return);
	}
}

if ( ! function_exists( 'tribe_the_day_link' ) ) {
	/**
	 * Output an html link to a day
	 *
	 * @param string $date 'previous day', 'next day', 'yesterday', 'tomorrow', or any date string that strtotime() can parse
	 * @param string $text text for the link
	 * @return void
	 * @since 3.0
	 **/
	function tribe_the_day_link( $date = null, $text = null ) {
		try {
			if ( is_null( $text ) ) {
				$text = tribe_get_the_day_link_label($date);
			}
			$date = tribe_get_the_day_link_date( $date );

			$link = tribe_get_day_link($date);

			$html = '<a href="'. $link .'" data-day="'. $date .'" rel="prev">'.$text.'</a>';
		} catch ( OverflowException $e ) {
			$html = '';
		}

		echo apply_filters( 'tribe_the_day_link', $html );
	}
}

if ( ! function_exists( 'tribe_get_the_day_link_label' ) ) {
	/**
	 * Get the label for the day navigation link
	 *
	 * @param string $date_description
	 *
	 * @return string
	 * @since 3.1.1
	 */
	function tribe_get_the_day_link_label( $date_description ) {
		switch ( strtolower( $date_description ) ) {
			case null :
				return __( 'Today', 'tribe-events-calendar-pro' );
			case 'previous day' :
				return __( '<span>&laquo;</span> Previous Day', 'tribe-events-calendar-pro' );
			case 'next day' :
				return __( 'Next Day <span>&raquo;</span>', 'tribe-events-calendar-pro' );
			case 'yesterday' :
				return __( 'Yesterday', 'tribe-events-calendar-pro' );
			case 'tomorrow' :
				return __( 'Tomorrow', 'tribe-events-calendar-pro' );
			default :
				return date_i18n( 'Y-m-d', strtotime( $date_description ) );
		}
	}
}

if ( ! function_exists( 'tribe_get_the_day_link_date' ) ) {
	/**
	 * Get the date for the day navigation link.
	 *
	 * @param string $date_description
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_the_day_link_date( $date_description ) {
		if ( is_null($date_description) ) {
			return TribeEventsPro::instance()->todaySlug;
		}
		if ( $date_description == 'previous day' ) {
			return tribe_get_previous_day_date(get_query_var('start_date'));
		}
		if ( $date_description == 'next day' ) {
			return tribe_get_next_day_date(get_query_var('start_date'));
		}
		return date('Y-m-d', strtotime($date_description) );
	}
}

if ( ! function_exists( 'tribe_get_next_day_date' ) ) {
	/**
	 * Get the next day's date
	 *
	 * @param string $start_date
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_next_day_date( $start_date ) {
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($start_date)) > '2037-12-30' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}
		$date = Date('Y-m-d', strtotime($start_date . " +1 day") );
		return $date;
	}
}

if ( ! function_exists( 'tribe_get_previous_day_date' ) ) {
	/**
	 * Get the previous day's date
	 *
	 * @param string $start_date
	 * @return string
	 * @since 3.1.1
	 * @throws OverflowException
	 */
	function tribe_get_previous_day_date( $start_date ) {
		if ( PHP_INT_SIZE <= 4 ) {
			if ( date('Y-m-d', strtotime($start_date)) < '1902-01-02' ) {
				throw new OverflowException(__('Date out of range.', 'tribe-events-calendar-pro'));
			}
		}
		$date = Date('Y-m-d', strtotime($start_date . " -1 day") );
		return $date;
	}
}
