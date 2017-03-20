<?php

namespace wpe\plugin;

class FilterPlugin
{
	/**
	 * The plugin remote update path.
	 * @var string
	 */
	private $update_path;
	/**
	 * Plugin name
	 * @var string
	 */
	private $slug;

	/**
	 * @param string $slug
	 * @param string $update_path
	 */
	public function __construct( $slug, $update_path ) {
		$this->slug = $slug;
		$this->update_path = $update_path;

		// Define the alternative response for plugin description check.
		add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );
	}

	/**
	 * Add our self-hosted description to the filter.
	 *
	 * @param boolean $false
	 * @param array $action
	 * @param object $arg
	 * @return bool|object
	 */
	public function check_info( $false, $action, $arg ) {
		if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$response = $this->get_remote();
			if ( $response ) {
				$response->sections = (array) $response->sections;
				return $response;
			}
		}

		return false;
	}

	/**
	 * Return the remote version from a JSON manifest.
	 *
	 * @return object The decoded JSON file.
	 */
	public function get_remote() {

		// Make the GET request.
		$request = apply_filters( 'wpe_filter_plugin_update_response', wp_remote_get( $this->update_path ) );
		// Check if response is valid.
		if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			return json_decode( $request['body'] );
		}

		return false;
	}
}
