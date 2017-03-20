<?php
/**
 * A class for throttling back the heartbeat of a WordPress page. This class controls
 * the heartbeat rate and even disables the heartbeat for all pages except post editing
 * pages. 
 */

class WPE_Heartbeat_Throttle {

	/**
	 * Register the actions/hooks that make this throttle work.
	 */
	public function register() {
		add_action( 'init', array( $this, 'check_heartbeat_allowed' ), 1 );
	}

	/**
	 * Check that heartbeat is allowed for this page and deregeister it if not. 
	 */
	public function check_heartbeat_allowed() {
		global $pagenow;

		/**
		 * Filter the pages where heartbeat.js is allowed to load.
		 *
		 * @since 2.1.13
		 *
		 * @param array $heartbeat_allowed_pages Array of pages where the heartbeat.js file is allowed to be loaded.
		 */
		$heartbeat_allowed_pages = apply_filters( 'wpe_heartbeat_allowed_pages', array( 'edit.php', 'post.php', 'post-new.php' ) );

		if ( ! in_array( $pagenow, $heartbeat_allowed_pages ) ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

}
