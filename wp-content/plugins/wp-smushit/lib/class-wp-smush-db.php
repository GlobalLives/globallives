<?php
/**
 * @package WP Smush
 * @subpackage Admin
 * @version 2.3
 *
 * @author Umesh Kumar <umesh@incsub.com>
 *
 * @copyright (c) 2016, Incsub (http://incsub.com)
 */
if ( ! class_exists( 'WpSmushDB' ) ) {

	/**
	 * Class WpSmushStats
	 */
	class WpSmushDB {
		function __construct() {
			//Update resize savings
			add_action( 'wp_smush_image_resized', array( $this, 'resize_savings' ) );
			//Update Conversion savings
			add_action( 'wp_smush_png_jpg_converted', array( $this, 'conversion_savings' ) );
		}

		/**
		 * Filter the Posts object as per mime type
		 *
		 * @param $posts Object of Posts
		 *
		 * @return mixed array of post ids
		 *
		 */
		function filter_by_mime( $posts ) {
			global $wpsmushit_admin;
			if ( empty( $posts ) ) {
				return $posts;
			}
			foreach ( $posts as $post_k => $post ) {
				if ( ! isset( $post->post_mime_type ) || ! in_array( $post->post_mime_type, $wpsmushit_admin->mime_types ) ) {
					unset( $posts[ $post_k ] );
				} else {
					$posts[ $post_k ] = $post->ID;
				}
			}

			return $posts;
		}

		/**
		 * Fetch all the unsmushed attachments
		 *
		 * @return array $attachments
		 */
		function get_unsmushed_attachments() {
			global $wpsmushit_admin, $wpsmush_db;

			if ( ! isset( $_REQUEST['ids'] ) ) {

				/** Do not fetch more than this, any time
				Localizing all rows at once increases the page load and slows down everything */
				$r_limit = apply_filters( 'wp_smush_max_rows', 5000 );

				//Check if we can get the unsmushed attachments from the other two variables
				if ( ! empty( $wpsmushit_admin->attachments ) && ! empty( $wpsmushit_admin->smushed_attachments ) ) {
					$unsmushed_posts = array_diff( $wpsmushit_admin->attachments, $wpsmushit_admin->smushed_attachments );
					$unsmushed_posts = ! empty( $unsmushed_posts ) && is_array( $unsmushed_posts ) ? array_slice( $unsmushed_posts, 0, $r_limit ) : array();

					return $unsmushed_posts;
				} else {
					$limit = $wpsmushit_admin->query_limit();

					$get_posts       = true;
					$unsmushed_posts = array();
					$args            = array(
						'fields'                 => array( 'ids', 'post_mime_type' ),
						'post_type'              => 'attachment',
						'post_status'            => 'any',
						'orderby'                => 'ID',
						'order'                  => 'DESC',
						'posts_per_page'         => $limit,
						'offset'                 => 0,
						'meta_query'             => array(
							array(
								'key'     => 'wp-smpro-smush-data',
								'compare' => 'NOT EXISTS'
							)
						),
						'update_post_term_cache' => false,
						'no_found_rows'          => true,
					);
					//Loop Over to get all the attachments
					while ( $get_posts ) {

						//Remove the Filters added by WP Media Folder
						$wpsmush_db->remove_filters();

						$query = new WP_Query( $args );

						if ( ! empty( $query->post_count ) && sizeof( $query->posts ) > 0 ) {

							//Get a filtered list of post ids
							$posts = $wpsmush_db->filter_by_mime( $query->posts );

							//Merge the results
							$unsmushed_posts = array_merge( $unsmushed_posts, $posts );

							//Update the offset
							$args['offset'] += $limit;
						} else {
							//If we didn't get any posts from query, set $get_posts to false
							$get_posts = false;
						}

						//If we already got enough posts
						if ( count( $unsmushed_posts ) >= $r_limit ) {
							$get_posts = false;
						} else if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count <= $args['offset'] ) {
							//If total Count is set, and it is alread lesser than offset, don't query
							$get_posts = false;
						}
					}
				}
			} else {
				return array_map( 'intval', explode( ',', $_REQUEST['ids'] ) );
			}

			return $unsmushed_posts;
		}

		/**
		 * Total Image count
		 *
		 * @param bool $force_update
		 *
		 * @return bool|int|mixed
		 */
		function total_count( $force_update = false ) {
			global $wpsmushit_admin, $wpdb;

			//Retrieve from Cache
			if ( ! $force_update && $count = wp_cache_get( 'total_count', 'wp-smush' ) ) {
				return $count;
			}

			$posts  = array();
			$offset = 0;
			$limit  = $wpsmushit_admin->query_limit();

			$mime  = implode( "', '", $wpsmushit_admin->mime_types );
			$query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_mime_type IN ('$mime') LIMIT %d, %d";
			//Remove the Filters added by WP Media Folder
			$this->remove_filters();

			$get_posts = true;

			while ( $get_posts ) {
				$results = $wpdb->get_col( $wpdb->prepare( $query, $offset, $limit ) );
				if ( ! empty( $results ) && is_array( $results ) && sizeof( $results ) > 0 ) {

					//Get a filtered list of post ids
					$posts = array_merge( $posts, $results );

					//Update the offset
					$offset += $limit;
				} else {
					//If we didn't get any posts from query, set $get_posts to false
					$get_posts = false;
				}
			}

			//If we have got the Query result
			if ( ! empty( $posts ) && is_array( $posts ) ) {
				wp_cache_add( 'smush_attachments', $posts, 'wp-smush' );
			}

			//Set Attachment ids, and total count
			$wpsmushit_admin->attachments = $posts;
			$wpsmushit_admin->total_count = ! empty( $posts ) && is_array( $posts ) ? sizeof( $posts ) : 0;

			// send the count
			return $posts;
		}

		/**
		 * Optimised images count
		 *
		 * @param bool $return_ids
		 *
		 * @return array|int
		 */
		function smushed_count( $return_ids = false ) {
			global $wpsmushit_admin;

			//Don't query again, if the variable is already set
			if ( ! $return_ids && ! empty( $wpsmushit_admin->smushed_count ) && $wpsmushit_admin->smushed_count > 0 ) {
				return $wpsmushit_admin->smushed_count;
			}

			$query = array(
				'fields'         => array( 'ids', 'post_mime_type' ),
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'order'          => 'ASC',
				'posts_per_page' => - 1,
				'meta_key'       => 'wp-smpro-smush-data',
				'no_found_rows'  => true
			);

			//Remove the Filters added by WP Media Folder
			$this->remove_filters();

			$results = new WP_Query( $query );

			if ( ! is_wp_error( $results ) && $results->post_count > 0 ) {

				$posts = $this->filter_by_mime( $results->posts );

				if ( ! $return_ids ) {
					//return Post Count
					return count( $posts );
				} else {
					//Return post ids
					return $posts;
				}
			} else {
				return false;
			}
		}

		/**
		 * Returns/Updates the number of images Super Smushed
		 *
		 * @param string $type media/nextgen, Type of images to get/set the super smushed count for
		 *
		 * @param array $attachments Optional, By default Media attachments will be fetched
		 *
		 * @return array|mixed|void
		 *
		 * @todo: Refactor Method, Separate Media Library and Nextgen, moreover nextgen functioanlity is broken
		 *
		 */
		function super_smushed_count( $type = 'media', $attachments = array() ) {

			if ( 'media' == $type ) {
				$count = $this->get_super_smushed_attachments();
			} else {
				$key = 'wp-smush-super_smushed_nextgen';

				//Flag to check if we need to re-evaluate the count
				$revaluate = false;

				$super_smushed = get_option( $key, false );

				//Check if need to revalidate
				if ( ! $super_smushed || empty( $super_smushed ) || empty( $super_smushed['ids'] ) ) {

					$super_smushed = array(
						'ids' => array()
					);

					$revaluate = true;
				} else {
					$last_checked = $super_smushed['timestamp'];

					$diff = $last_checked - current_time( 'timestamp' );

					//Difference in hour
					$diff_h = $diff / 3600;

					//if last checked was more than 1 hours.
					if ( $diff_h > 1 ) {
						$revaluate = true;
					}
				}
				//Do not Revaluate stats if nextgen attachments are not provided
				if ( 'nextgen' == $type && empty( $attachments ) && $revaluate ) {
					$revaluate = false;
				}

				//Need to scan all the image
				if ( $revaluate ) {
					//Get all the Smushed attachments ids
					//Note: Wrong Method called, it'll fetch media images and not NextGen images
					//Should be $attachments, in place of $super_smushed_images
					$super_smushed_images = $this->get_super_smushed_attachments( true );

					if ( ! empty( $super_smushed_images ) && is_array( $super_smushed_images ) ) {
						//Iterate over all the attachments to check if it's already there in list, else add it
						foreach ( $super_smushed_images as $id ) {
							if ( ! in_array( $id, $super_smushed['ids'] ) ) {
								$super_smushed['ids'][] = $id;
							}
						}
					}

					$super_smushed['timestamp'] = current_time( 'timestamp' );

					update_option( $key, $super_smushed );
				}

				$count = ! empty( $super_smushed['ids'] ) ? count( $super_smushed['ids'] ) : 0;
			}

			return $count;
		}

		/**
		 * Updates the Meta for existing smushed images and retrieves the count of Super Smushed images
		 *
		 * @param bool $return_ids Whether to return ids or just the count
		 *
		 * @return array|int Super Smushed images Id / Count of Super Smushed images
		 */
		function get_super_smushed_attachments( $return_ids = false ) {

			global $wpsmushit_admin;

			//Get all the attachments with wp-smush-lossy
			$limit         = $wpsmushit_admin->query_limit();
			$get_posts     = true;
			$super_smushed = array();
			$args          = array(
				'fields'                 => array( 'ids', 'post_mime_type' ),
				'post_type'              => 'attachment',
				'post_status'            => 'any',
				'orderby'                => 'ID',
				'order'                  => 'DESC',
				'posts_per_page'         => $limit,
				'offset'                 => 0,
				'meta_query'             => array(
					array(
						'key'   => 'wp-smush-lossy',
						'value' => 1
					)
				),
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			);
			//Loop Over to get all the attachments
			while ( $get_posts ) {

				//Remove the Filters added by WP Media Folder
				$this->remove_filters();

				$query = new WP_Query( $args );

				if ( ! empty( $query->post_count ) && sizeof( $query->posts ) > 0 ) {
					$posts = $this->filter_by_mime( $query->posts );
					//Merge the results
					$super_smushed = array_merge( $super_smushed, $posts );

					//Update the offset
					$args['offset'] += $limit;
				} else {
					//If we didn't get any posts from query, set $get_posts to false
					$get_posts = false;
				}
				//If total Count is set, and it is alread lesser than offset, don't query
				if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count <= $args['offset'] ) {
					$get_posts = false;
				}
			}

			return $return_ids ? $super_smushed : count( $super_smushed );
		}

		/**
		 * Remove any pre_get_posts_filters added by WP Media Folder plugin
		 */
		function remove_filters() {
			//remove any filters added b WP media Folder plugin to get the all attachments
			if ( class_exists( 'Wp_Media_Folder' ) ) {
				global $wp_media_folder;
				if ( is_object( $wp_media_folder ) ) {
					remove_filter( 'pre_get_posts', array( $wp_media_folder, 'wpmf_pre_get_posts1' ) );
					remove_filter( 'pre_get_posts', array( $wp_media_folder, 'wpmf_pre_get_posts' ), 0, 1 );
				}
			}
			global $wpml_query_filter;
			//If WPML is not installed, return
			if ( ! is_object( $wpml_query_filter ) ) {
				return;
			}

			//Remove language filter and let all the images be smushed at once
			if ( has_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) ) ) {
				remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
				remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
			}
		}

		/**
		 * Get the savings from image resizing, And force update if set to true
		 *
		 * @param bool $force_update , Whether to Re-Calculate all the stats or not
		 *
		 * @param bool $format Format the Bytes in readable format
		 *
		 * @return array|bool|mixed|string Array of {
		 *      'bytes',
		 *      'before_size',
		 *      'after_size'
		 * }
		 *
		 */
		function resize_savings( $force_update = true, $format = false ) {
			$savings = '';

			if ( ! $force_update ) {
				$savings = wp_cache_get( WP_SMUSH_PREFIX . 'resize_savings', 'wp-smush' );
			}

			global $wpsmushit_admin;

			//If nothing in cache, Calculate it
			if ( empty( $savings ) || $force_update ) {
				$savings = array(
					'bytes'       => 0,
					'size_before' => 0,
					'size_after'  => 0,
				);

				$limit      = $wpsmushit_admin->query_limit();
				$offset     = 0;
				$query_next = true;
				global $wpdb;

				while ( $query_next ) {

					$resize_data = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key=%s LIMIT $offset, $limit", WP_SMUSH_PREFIX . 'resize_savings' ) );

					if ( ! empty( $resize_data ) ) {
						foreach ( $resize_data as $data ) {

							if ( ! empty( $data ) ) {
								$meta = maybe_unserialize( $data );
								if ( ! empty( $meta ) && ! empty( $meta['bytes'] ) ) {
									$savings['bytes'] += $meta['bytes'];
									$savings['size_before'] += $meta['size_before'];
									$savings['size_after'] += $meta['size_after'];
								}
							}
						}
					}
					//Update the offset
					$offset += $limit;

					//Compare the Offset value to total images
					if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count < $offset ) {
						$query_next = false;
					} elseif ( ! $resize_data ) {
						//If we didn' got any results
						$query_next = false;
					}
				}

				if ( $format ) {
					$savings['bytes'] = size_format( $savings['bytes'], 1 );
				}

				wp_cache_set( WP_SMUSH_PREFIX . 'resize_savings', $savings, 'wp-smush' );
			}

			return $savings;
		}

		/**
		 * Return/Update PNG -> JPG Conversion savings
		 *
		 * @param bool $force_update Whether to force update the conversion savings or not
		 * @param bool $format Optionally return formatted savings
		 *
		 * @return array Savings
		 */
		function conversion_savings( $force_update = true, $format = false ) {
			$savings = '';

			if ( ! $force_update ) {
				$savings = wp_cache_get( WP_SMUSH_PREFIX . 'pngjpg_savings', 'wp-smush' );
			}
			//If nothing in cache, Calculate it
			if ( empty( $savings ) || $force_update ) {
				global $wpsmushit_admin;
				$savings = array(
					'bytes'       => 0,
					'size_before' => 0,
					'size_after'  => 0,
				);

				$limit      = $wpsmushit_admin->query_limit();
				$offset     = 0;
				$query_next = true;
				global $wpdb;

				while ( $query_next ) {

					$conversion_savings = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key=%s LIMIT $offset, $limit", WP_SMUSH_PREFIX . 'pngjpg_savings' ) );

					if ( ! empty( $conversion_savings ) ) {
						foreach ( $conversion_savings as $data ) {

							if ( ! empty( $data ) ) {
								$meta = maybe_unserialize( $data );

								if ( is_array( $meta ) ) {
									foreach ( $meta as $size ) {
										if ( ! empty( $size ) && is_array( $size ) ) {
											$savings['bytes'] += $size['bytes'];
											$savings['size_before'] += $size['size_before'];
											$savings['size_after'] += $size['size_after'];
										}
									}
								}
							}
						}
					}
					//Update the offset
					$offset += $limit;

					//Compare the Offset value to total images
					if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count < $offset ) {
						$query_next = false;
					} elseif ( ! $conversion_savings ) {
						//If we didn' got any results
						$query_next = false;
					}
				}

				if ( $format ) {
					$savings['bytes'] = size_format( $savings['bytes'], 1 );
				}

				wp_cache_set( WP_SMUSH_PREFIX . 'pngjpg_savings', $savings, 'wp-smush' );
			}

			return $savings;
		}

		/**
		 * Get all the resized images
		 *
		 * @return array Array of post ids of all the resized images
		 *
		 */
		function resize_images() {
			global $wpsmushit_admin;
			$limit          = $wpsmushit_admin->query_limit();
			$get_posts      = true;
			$resized_images = array();
			$args           = array(
				'fields'                 => array( 'ids', 'post_mime_type' ),
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'orderby'                => 'ID',
				'order'                  => 'DESC',
				'posts_per_page'         => $limit,
				'offset'                 => 0,
				'meta_key'               => WP_SMUSH_PREFIX . 'resize_savings',
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			);
			//Loop Over to get all the attachments
			while ( $get_posts ) {

				//Remove the Filters added by WP Media Folder
				$this->remove_filters();

				$query = new WP_Query( $args );

				if ( ! empty( $query->post_count ) && sizeof( $query->posts ) > 0 ) {

					$posts = $this->filter_by_mime( $query->posts );

					//Merge the results
					$resized_images = array_merge( $resized_images, $posts );

					//Update the offset
					$args['offset'] += $limit;
				} else {
					//If we didn't get any posts from query, set $get_posts to false
					$get_posts = false;
				}

				//If total Count is set, and it is alread lesser than offset, don't query
				if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count < $args['offset'] ) {
					$get_posts = false;
				}
			}

			return $resized_images;
		}

		/**
		 * Get all the PNGJPG Converted images
		 *
		 * @return array Array of post ids of all the converted images
		 *
		 */
		function converted_images() {
			global $wpsmushit_admin;
			$limit            = $wpsmushit_admin->query_limit();
			$get_posts        = true;
			$converted_images = array();
			$args             = array(
				'fields'                 => array( 'ids', 'post_mime_type' ),
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'orderby'                => 'ID',
				'order'                  => 'DESC',
				'posts_per_page'         => $limit,
				'offset'                 => 0,
				'meta_key'               => WP_SMUSH_PREFIX . 'pngjpg_savings',
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			);
			//Loop Over to get all the attachments
			while ( $get_posts ) {

				//Remove the Filters added by WP Media Folder
				$this->remove_filters();

				$query = new WP_Query( $args );

				if ( ! empty( $query->post_count ) && sizeof( $query->posts ) > 0 ) {

					//Filter Posts by mime types
					$posts = $this->filter_by_mime( $query->posts );

					//Merge the results
					$converted_images = array_merge( $converted_images, $posts );

					//Update the offset
					$args['offset'] += $limit;
				} else {
					//If we didn't get any posts from query, set $get_posts to false
					$get_posts = false;
				}

				//If total Count is set, and it is alread lesser than offset, don't query
				if ( ! empty( $wpsmushit_admin->total_count ) && $wpsmushit_admin->total_count < $args['offset'] ) {
					$get_posts = false;
				}
			}

			return $converted_images;
		}

		/**
		 * Returns the ids and meta which are losslessly compressed
		 *
		 * Called only if the meta key isn't updated for old images, else it is not used
		 *
		 * @return array
		 */
		function get_lossy_attachments( $attachments = '', $return_count = true ) {

			$lossy_attachments = array();
			$count             = 0;

			if ( empty( $attachments ) ) {
				//Fetch all the smushed attachment ids
				$attachments = $this->smushed_count( true );
			}

			//If we dont' have any attachments
			if ( empty( $attachments ) || 0 == count( $attachments ) ) {
				return 0;
			}

			//Check if image is lossless or lossy
			foreach ( $attachments as $attachment ) {

				//Check meta for lossy value
				$smush_data = ! empty( $attachment->smush_data ) ? maybe_unserialize( $attachment->smush_data ) : '';

				//For Nextgen Gallery images
				if ( empty( $smush_data ) && is_array( $attachment ) && ! empty( $attachment['wp_smush'] ) ) {
					$smush_data = ! empty( $attachment['wp_smush'] ) ? $attachment['wp_smush'] : '';
				}

				//Return if not smushed
				if ( empty( $smush_data ) ) {
					continue;
				}

				//if stats not set or lossy is not set for attachment, return
				if ( empty( $smush_data['stats'] ) || ! isset( $smush_data['stats']['lossy'] ) ) {
					continue;
				}

				//Add to array if lossy is not 1
				if ( 1 == $smush_data['stats']['lossy'] ) {
					$count ++;
					if ( ! empty( $attachment->attachment_id ) ) {
						$lossy_attachments[] = $attachment->attachment_id;
					} elseif ( is_array( $attachment ) && ! empty( $attachment['pid'] ) ) {
						$lossy_attachments[] = $attachment['pid'];
					}
				}
			}
			unset( $attachments );

			if ( $return_count ) {
				return $count;
			}

			return $lossy_attachments;
		}

		/**
		 * Get the attachment ids with Upfront images
		 *
		 * @param array $skip_ids
		 *
		 * @return array|bool
		 */
		function get_upfront_images( $skip_ids = array() ) {

			$query = array(
				'fields'         => array( 'ids', 'post_mime_type' ),
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'order'          => 'ASC',
				'posts_per_page' => - 1,
				'meta_key'       => 'upfront_used_image_sizes',
				'no_found_rows'  => true
			);

			//Skip all the ids which are already in resmush list
			if ( ! empty( $skip_ids ) && is_array( $skip_ids ) ) {
				$query['post__not_in'] = $skip_ids;
			}

			$results = new WP_Query( $query );

			if ( ! is_wp_error( $results ) && $results->post_count > 0 ) {
				$posts = $this->filter_by_mime( $results->posts );

				return $posts;
			} else {
				return false;
			}
		}
	}

	/**
	 * Initialise class
	 */
	global $wpsmush_db;
	$wpsmush_db = new WpSmushDB();
}