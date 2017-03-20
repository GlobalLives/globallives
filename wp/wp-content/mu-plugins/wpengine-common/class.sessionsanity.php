<?php

namespace wpe\plugin;

/**
 * Enforce some sanity on expiration times in wp_sessions. This is motivated by 
 * a recent bug in the EDD plugin that caused sessions to be expiring in the 
 * year 2058, which is not going to be even close to the right answer. Instead,
 * we're going to cap the session timeout at 30 days.
 */
class SessionSanity {

	public function register_hooks() {
		add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 999999 );
	}

	public function set_expiration_time( $expiration ) {
/**
 * Filter the upper bound on "sane" expriration times. IMPORTANT: This is a relative time (i.e., number of 
 * seconds until expiration) and NOT an absolute time (unixtime of expiration). Keep it small. Sessions are
 * not designed to last forever.
 * 
 * @since 2.1.14
 *
 * @param number $max_expiration maximum number of seconds until the session expires.
 */
		$max_expiration = apply_filters( 'wpe_max_session_expiration', 30 * DAY_IN_SECONDS );
		if ( $expiration > $max_expiration ) {
			error_log( "Invalid session timeout: $expiration. Maximum allowed value: {$max_expiration}" );
			return $max_expiration;
		}

		return $expiration;
	}

}
