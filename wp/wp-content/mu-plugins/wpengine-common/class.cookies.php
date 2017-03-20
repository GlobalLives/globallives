<?php

namespace wpe\plugin;

class Cookies {

	/**
	 * Register our class method(s) with the appropriate WordPress hooks.
	 */
	public static function register_hooks() {
		add_action( 'auth_cookie_malformed',    array( __CLASS__, 'purge_browser_cookie' ) );
		add_action( 'auth_cookie_expired',      array( __CLASS__, 'purge_browser_cookie' ) );
		add_action( 'auth_cookie_bad_username', array( __CLASS__, 'purge_browser_cookie' ) );
		add_action( 'auth_cookie_bad_hash',     array( __CLASS__, 'purge_browser_cookie' ) );
	}

	/**
	 * Clear the authentication cookies.
	 *
	 * @param array|string $cookie The current authentication cookie.
	 */
	public static function purge_browser_cookie( $cookie ) {
		// Remove the action to prevent recursion with some plugins (notably s2member)
		remove_action( current_action(), array( __CLASS__, __FUNCTION__ ) );

		/*
		 * Sometimes the cookie is empty because WordPress uses multiple types of auth cookies.
		 * When one of the cookies is empty, we don't want to purge the cookies because other
		 * cookies may have us legitimately logged in *and* empty cookies (e.g., unset) aren't
		 * doing the cache-busting that prompts us to want to purge.
		 */
		if ( empty( $cookie ) ) {
			return;
		}

		wp_clear_auth_cookie();
	}
}
