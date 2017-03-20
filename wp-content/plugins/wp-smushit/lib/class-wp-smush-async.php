<?php
/**
 * @package WP Smush
 * @subpackage Admin
 * @since 2.5
 *
 * @author Umesh Kumar <umesh@incsub.com>
 *
 * @copyright (c) 2016, Incsub (http://incsub.com)
 */
require_once 'wp-async-task-smush.php';

if ( ! class_exists( 'WpSmushAsync' ) ) {

	class WpSmushAsync extends WP_Async_Task_Smush {

		protected $argument_count = 2;
		protected $priority = 12;
		/**
		 * Whenever a attachment metadata is generated
		 * Had to be hooked on generate and not update, else it goes in infinite loop
		 *
		 * @var string
		 */
		protected $action = 'wp_generate_attachment_metadata';

		/**
		 * Prepare data for the asynchronous request
		 *
		 * @throws Exception If for any reason the request should not happen
		 *
		 * @param array $data An array of data sent to the hook
		 *
		 * @return array
		 */
		protected function prepare_data( $data ) {
			//We don't have the data, bail out
			if ( empty( $data ) ) {
				return $data;
			}

			//Return a associative array
			$image_meta             = array();
			$image_meta['metadata'] = ! empty( $data[0] ) ? $data[0] : '';
			$image_meta['id']       = ! empty( $data[1] ) ? $data[1] : '';

			return $image_meta;
		}

		/**
		 * Run the async task action
		 * @todo: See if auto smush is enabled or not
		 * @todo: Check if async is enabled or not
		 */
		protected function run_action() {

			$metadata = ! empty( $_POST['metadata'] ) ? $_POST['metadata'] : '';
			$id       = ! empty( $_POST['id'] ) ? $_POST['id'] : '';

			//Get metadata from $_POST
			if ( ! empty( $metadata ) && wp_attachment_is_image( $id ) ) {
				// Allow the Asynchronous task to run
				do_action( "wp_async_$this->action", $id );
			}
		}

	}

	class WpSmushEditorAsync extends WP_Async_Task_Smush {

		protected $argument_count = 2;
		protected $priority = 12;
		/**
		 * Whenever a attachment metadata is generated
		 * Had to be hooked on generate and not update, else it goes in infinite loop
		 *
		 * @var string
		 */
		protected $action = 'wp_save_image_editor_file';

		/**
		 * Prepare data for the asynchronous request
		 *
		 * @throws Exception If for any reason the request should not happen
		 *
		 * @param array $data An array of data sent to the hook
		 *
		 * @return array
		 */
		protected function prepare_data( $data ) {
			//Store the post data in $data variable
			if ( ! empty( $data ) ) {
				$data = array_merge( $data, $_POST );
			}

			//Store the image path
			$data['filepath'] = !empty( $data[1] ) ? $data[1] : '';
			$data['wp-action'] = !empty( $data['action'] ) ? $data['action'] : '';
			unset( $data['action'], $data[1] );

			return $data;
		}

		/**
		 * Run the async task action
		 * @todo: Add a check for image
		 * @todo: See if auto smush is enabled or not
		 * @todo: Check if async is enabled or not
		 */
		protected function run_action() {

			if ( isset( $_POST['wp-action'], $_POST['do'], $_POST['postid'] )
			     && 'image-editor' === $_POST['wp-action']
			     && check_ajax_referer( 'image_editor-' . $_POST['postid'] )
			     && 'open' != $_POST['do']
			) {
				$postid = ! empty( $_POST['postid'] ) ? $_POST['postid'] : '';
				// Allow the Asynchronous task to run
				do_action( "wp_async_$this->action", $postid, $_POST );
			}
		}

	}
}