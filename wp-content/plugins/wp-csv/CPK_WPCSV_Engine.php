<?php
if ( !class_exists( 'CPK_WPCSV_Engine' ) ) {
	class CPK_WPCSV_Engine {

		private $post_fields = Array( );
		private $stats = Array( );
		private $export_model;
		private $row_index = 0;
		private $debugger = NULL;
		public $settings = Array( );

		const EXPORT_FILE_NAME = 'wpcsv-export';

		public function __construct( $settings ) { // Constructor
			$this->post_fields = $settings['post_fields'];
			$this->mandatory_fields = $settings['mandatory_fields'];
			$this->settings = $settings;
			$this->export_model = new CPK_WPCSV_Export_Queue_Model( );
			$this->posts_model = new CPK_WPCSV_Posts_Model( $this->settings );
			$this->csv = new CPK_WPCSV_CSV( );
			$this->csv->delimiter = $this->settings['delimiter'];
			$this->csv->enclosure = $this->settings['enclosure'];
			$this->csv->encoding = $this->settings['encoding'];
			$this->log = new CPK_WPCSV_Log_Model( );
		}
		
		public function set_debugger( $debugger ) {
			if ( is_object( $debugger ) ) {
				$this->debugger = $debugger;
				$this->posts_model->set_debugger( $this->debugger );
			}
		}

		private function trace( $label, $data ) {
			if ( is_object( $this->debugger ) && method_exists( $this->debugger, 'add' ) ) {
				$this->debugger->add( $label, $data );
			}
		}

		public function prepare( $settings ) {
			$this->settings = $settings;
			$this->posts_model->update_settings( $this->settings );
			$this->export_model->update_settings( $this->settings );

			$this->export_model->empty_table( $this->settings['frontend']['export_id'] );
			$export_file = $this->settings['csv_path'] . '/' . self::EXPORT_FILE_NAME . "-{$this->settings['frontend']['export_id']}.csv";
			if ( file_exists( $export_file ) ) unlink( $export_file );
			$post_ids = $this->posts_model->get_post_ids( );
			
			if ( $post_ids ) {
				$this->export_model->add_post_ids( $post_ids, $this->settings['frontend']['export_id'] );
			}
		}

		public function get_total( $export_id ) {
			$count = $this->export_model->get_count( $export_id );
			$this->trace( 'Get Total', $count );
			return $count;
		}

		public function export( $include_headings = TRUE, $export_id ) {
			
			$start_time = time( );

			$this->trace( 'Settings', $this->settings );

			$post_ids = $this->export_model->get_post_id_list( $this->settings['limit'], $export_id );

			$this->trace( 'Post Id Count', count( $post_ids ) );
			
			$post_array = Array( );
			$post_ids_actual = Array( );

			if ( is_array( $post_ids ) && !empty( $post_ids ) ) {
				foreach( $post_ids as $post_id ) {
					$post = $this->posts_model->get_post( $post_id, $this->settings );
					if ( empty( $post_array ) ) {
						$post_array[] = $post['headings'];
					}
					$post_array[] = $post['values'];
					$post_ids_actual[] = $post_id;
					wp_cache_flush( ); # Experimental
					
					$execution_time = time( ) - $start_time;
					if ( !$this->optimize_resource_usage( $execution_time, count( $post_ids_actual ) ) ) break;
					
					$this->trace( 'post_array', $post_array );
				} # End foreach
			} # End if

			if ( !$include_headings ) unset( $post_array[0] );

			$result = $this->save_export( $post_array, $export_id );

			$this->export_model->mark_done( $post_ids_actual );

			return $result;
		}

		private function optimize_resource_usage( $execution_time, $count ) {
			global $cpk_wpcsv;

			$memory_used = $this->get_memory_usage( );
			$time_used = $this->get_max_execution_time_usage( $execution_time );

			if ( $memory_used > 90 || $time_used > 90 ) {
				$this->settings['limit'] = $count - 1;
				$cpk_wpcsv->set_settings( $this->settings );
				$cpk_wpcsv->save_settings( );
				if ( $time_used > 90 ) {
					$this->log->add_message( __( "Hit 90% of maximum execution time!  Reducing chunk size to: {$this->settings['limit']}", 'wp-csv' ), 'Warning' );
				}
				if ( $memory_used > 90 ) {
					$this->log->add_message( __( "Hit 90% of maximum memory!  Reducing chunk size to: {$this->settings['limit']}", 'wp-csv' ), 'Warning' );
				}
				return FALSE;
			}

			$threshold = floor( $this->settings['limit'] * 0.9 );

			if ( $memory_used < 90 && $time_used < 90 && ( $count > $threshold ) ) {
				$this->settings['limit'] = floor( $this->settings['limit'] * 1.1 );
				$cpk_wpcsv->set_settings( $this->settings );
				$cpk_wpcsv->save_settings( );
			}

			return TRUE;
		}
		
		public function get_max_memory( ) {
			return $this->return_bytes( ini_get( 'memory_limit' ) );
		}

		public function get_max_execution_time( ) {
			return ini_get( 'max_execution_time' );
		}

		public function get_memory_usage( $memory_threshold = 90 ) {
			$peak_memory = memory_get_peak_usage( TRUE );
			$memory_limit = $this->get_max_memory( );
			return round( ( $peak_memory / $memory_limit ) * 100, 2 );
		}

		public function get_max_execution_time_usage( $execution_time ) {
			if ( $execution_time == 0 ) return 0;
			$max_execution_time = $this->get_max_execution_time( );
			if ( !$max_execution_time ) $max_execution_time = 30;
			return ( $execution_time / $max_execution_time ) * 100;
		}

		private function return_bytes( $val ) {
			$val = trim( $val );
			$last = strtolower( $val[strlen($val)-1] );
			switch( $last ) {
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
				$val *= 1024;
				case 'm':
				$val *= 1024;
				case 'k':
				$val *= 1024;
			}

			return $val;
		}

		public function save_export( Array $posts, $export_id ) {
			if ( !empty( $posts ) ) {
				if ( isset( $posts[0][0] ) && $posts[0][0] == 'ID' ) $posts[0][0] = 'id';
				$this->csv->save( $posts, self::EXPORT_FILE_NAME . '-' . $export_id, $this->settings['csv_path'] );
			}
			return count( $posts );
		}

		public function import( $posts ) {

			$start_time = time( );
			$count = 0;
			foreach( $posts as $post ) {

				if ( $this->import_post( $post, FALSE ) ) $count++; # TODO: function always returns true, but should return false for certain error conditions
				$execution_time = time( ) - $start_time;
				if ( !$this->optimize_resource_usage( $execution_time, $count ) ) break;
			}

			return $count;
		}

		private function is_valid_json( $string ) {
			if ( !is_string( $string ) || !function_exists( 'json_decode' ) ) return FALSE;
			return is_object( json_decode( $string ) );
		}

		public function import_post( $post, $perm_delete ) { 

			$p = Array( );
			$cf = Array( );
			$this->row_index++;

			foreach( $post as $key => $val ) {
				$prefix = substr( $key, 0, 3 );
				$suffix = substr( $key, 3 );

				$attach_id = NULL;
				if ( ( in_array( $key, $this->post_fields ) ) || ( in_array( $suffix, $this->posts_model->get_taxonomy_list( ) ) ) ) {
					$p[ $suffix ] = $val;
				} elseif ( $prefix == 'cf_' ) {
					$cf[ $suffix ] = $val;
				} elseif ( $key == 'fi_thumbnail' ) {
					$thumb_file = $val;
				} // End if
			} // End foreach

			$this->trace( 'p', $p );
			$this->trace( 'cf', $cf );

			global $wpdb;
			$posts_table = $wpdb->prefix . 'posts';

			# Pre-import data sanitization

			if ( $p['post_parent'] > 0 ) {
				$post_parent = get_post( $p['post_parent'], ARRAY_A );
				if ( !isset( $post_parent ) || $post_parent['post_type'] != 'page' ) {
					$this->log->add_message( "Post parent id ({$p['post_parent']}) is specified, but a post with that id could not be found.", 'Warning', $p );
				}
			}


			if ( preg_match( '/\//', $p['post_date'] ) ) { # If it has slashes then determine US/English format
				if ( $this->settings['date_format'] == 'US' ) {
					list( $mm, $dd, $the_rest ) = explode( '/', $p['post_date'] );
				} else {
					list( $dd, $mm, $the_rest ) = explode( '/', $p['post_date'] );
				}
				list( $yyyy, $time ) = explode( ' ', $the_rest );
				$p['post_date'] = "{$yyyy}-{$mm}-{$dd} $time";
			}

			$p['post_date'] = date( 'Y-m-d H:i:s', strtotime( $p['post_date'] ) );
			$p['post_date_gmt'] = get_gmt_from_date( $p['post_date'] ); 

			if ( preg_match( '/\//', $p['post_modified'] ) ) { # If it has slashes then determine US/English format
				if ( $this->settings['date_format'] == 'US' ) {
					list( $mm, $dd, $the_rest ) = explode( '/', $p['post_modified'] );
				} else {
					list( $dd, $mm, $the_rest ) = explode( '/', $p['post_modified'] );
				}
				list( $yyyy, $time ) = explode( ' ', $the_rest );
				$p['post_modified'] = "{$yyyy}-{$mm}-{$dd} $time";
			}

			$p['post_modified'] = date( 'Y-m-d H:i:s', strtotime( $p['post_modified'] ) );
			$p['post_modified_gmt'] = get_gmt_from_date( $p['post_modified'] ); 

			# Convert User id to username
			if ( !empty( $p['post_author'] ) ) {
				$user = get_user_by( 'login', $p['post_author'] );
				
				if ( $user ) {
					$p['post_author'] = $user->get( 'ID' );
				} else {
					$this->log->add_message( "The post author ('{$p['post_author']}') could not be found.", 'Warning' );
				}
			}

			// CREATE
			if ( $p['ID'] == "" ) { 

				$id = wp_insert_post( $p );
				$taxonomy_list = $this->posts_model->get_taxonomy_list( );
				foreach( $taxonomy_list as $t ) {
					$this->import_taxonomy( $id, explode( ',', $p[$t] ), $t );
				}

				// wp_insert_post and wp_publish_post don't appear to support publishing to the future, so hack required:
				if ( strtotime( $p['post_date'] ) > current_time( 'timestamp' ) ) {
					$wpdb->update( $posts_table, array( 'post_status' => 'future' ), array( 'ID' => $id ) );
				} elseif ( $p['post_status'] == 'future' ) {
					$wpdb->update( $posts_table, array( 'post_status' => 'publish' ), array( 'ID' => $id ) );
				}

				# Custom fields	
				foreach( $cf as $key => $val ) {
					if ( !is_null( $val ) && $val != '' ) { 
						if ( $this->is_valid_json( $val ) ) {
							$val = json_decode( $val, TRUE );
						}
						add_post_meta( $id, $key, $val, TRUE );
					}
				}

				// Add thumbnail if one can be found
				if ( !empty( $thumb_file ) ) { // Ignore blank thumb_file fields

					$imported = FALSE;

					// Check media library for image
					$sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = '$thumb_file'";
					$attach_id = $wpdb->get_var( $sql );

					if ( empty( $attach_id ) ) { // Not found in media library, check folder
						$imagefile = WP_CONTENT_DIR . '/uploads/' . $thumb_file;
						$imageurl = WP_CONTENT_URL . '/uploads/' . $thumb_file;
						$imported = false;
						if ( is_file( $imagefile ) ) {
							$attach_id = $this->import_image( $imagefile, $imageurl ); // Import image, maybe refactor to use WP media_handle_upload function
							$imported = true;
						}
					}

					if ( isset( $attach_id ) && !empty( $attach_id ) ) { // If image found in media library or folder, add meta data and link to post.
						// Get path to image
						$image_record = get_post( $attach_id, 'ARRAY_A' );
						$guid = $image_record['guid'];
						$filepath = WP_CONTENT_DIR . preg_replace( '/' . addcslashes( WP_CONTENT_URL, '/' ) . '/', '', $guid );
						
						if ( $imported ) {
							// Get meta data
							$image_meta = $this->get_image_metadata( $filepath );

							// Attach meta data
							$this->add_post_image_meta( $attach_id, $id, $filepath, $image_meta );
						}

						// Attach image to post
						update_post_meta( $id, '_thumbnail_id', $attach_id );
					} else { // No image found but thumb specified
						// Error message
					}
				} else { // If the field is empty, then any thumb should be detached from the post
					delete_post_meta( $id, '_thumbnail_id' );					
				}

				# Simulate the useful parts of wp_publish_post without changing post_status
				clean_post_cache( $id );
				$post_object = get_post( $id );
				do_action( 'edit_post', $post_object->ID, $post_object );
				do_action( "save_post_{$post_object->post_type}", $post_object->ID, $post_object, TRUE );
				do_action( 'save_post', $post_object->ID, $post_object, TRUE );

				$action['Insert'] = $id;
			} else {
				$pid = ( $p['ID'] < 0 ) ? $p['ID']*-1 : $p['ID'];
				$post_val = get_post($pid);
				$post_exists = ( !empty( $post_val ) );

				// MODIFY
				if ( $post_exists ) {
					if ( is_numeric( $p['ID'] ) && $p['ID'] >  0 ) {

						wp_update_post( $p );
	
						// wp_update_post and wp_publish_post don't appear to support publishing to the future, so hack required:
						if ( strtotime( $p['post_date'] ) > current_time( 'timestamp' ) ) {
							$wpdb->update( $posts_table, array( 'post_status' => 'future' ), array( 'ID' => $p['ID'] ) );
						} elseif ( $p['post_status'] == 'future' ) {
							$wpdb->update( $posts_table, array( 'post_status' => 'publish' ), array( 'ID' => $id ) );
						}

						$taxonomy_list = $this->posts_model->get_taxonomy_list( );
						foreach( $taxonomy_list as $t ) {
							if ( isset( $p[ $t ] ) ) {
								$this->import_taxonomy( $p['ID'], explode( ',', $p[$t] ), $t );
							}
						}

						foreach( $cf as $key => $val ) {
							if ( !is_null( $val ) && $val != '' ) {
								if ( $this->is_valid_json( $val ) ) {
									$val = json_decode( $val, TRUE );
								}
								update_post_meta( $p['ID'], $key, $val );
							} else {
								delete_post_meta( $p['ID'], $key );
							}
						}

						// Add thumbnail if one can be found
						if ( !empty( $thumb_file ) ) { // Ignore blank thumb_file fields

							$imported = FALSE;

							// Check media library for image
							$sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = '$thumb_file'";
							$attach_id = $wpdb->get_var( $sql );

							if ( empty( $attach_id ) ) { // Not found in media library, check folder
								$imagefile = WP_CONTENT_DIR . '/uploads/' . $thumb_file;
								$imageurl = WP_CONTENT_URL . '/uploads/' . $thumb_file;
								$imported = false;
								if ( is_file( $imagefile ) ) {
									$attach_id = $this->import_image( $imagefile, $imageurl ); // Import image, maybe refactor to use WP media_handle_upload function
									$imported = true;
								}
							}

							if ( isset( $attach_id ) && !empty( $attach_id ) ) { // If image found in media library or folder, add meta data and link to post.
								// Get path to image
								$image_record = get_post( $attach_id, 'ARRAY_A' );
								$guid = $image_record['guid'];
								$filepath = WP_CONTENT_DIR . preg_replace( '/' . addcslashes( WP_CONTENT_URL, '/' ) . '/', '', $guid );

								if ( $imported ) {
									// Get meta data
									$image_meta = $this->get_image_metadata( $filepath );

									// Attach meta data
									$this->add_post_image_meta( $attach_id, $id, $filepath, $image_meta );
								}

								// Attach image to post
								update_post_meta( $p['ID'], '_thumbnail_id', $attach_id );
							} else { // No image found but thumb specified
								// Error message
							}
						} elseif ( isset( $p['fi_thumbnail'] ) ) { // If the field is empty, then any thumb should be detached from the post
							delete_post_meta( $p['ID'], '_thumbnail_id' );					
						}

						$action['Update'] = $pid;
					}
	
					if ( $p['ID'] <  0 ) { // Delete
						$id = $p['ID']*-1; // Unsign integer
					
						wp_delete_post( $id, $perm_delete ); // Move to trash or delete permanently
						$action['Delete'] = $pid;
					}
				} else { // Post ID doesn't exist
					$this->log->add_message( "The post id ('{$p['ID']}') could not be found, so it's impossible to modify this post.  If you're trying to create a new post, then empty the ID field.  If you're trying to delete, then it seems to have already been deleted.", 'Error' );
				}
			}
			wp_cache_flush( ); # Experimental

			return TRUE;
		}

		private function import_taxonomy( $post_id, Array $items, $taxonomy ) {
			$term_ids = Array( );
			foreach( $items as $i ) {
				if ( empty( $i ) ) continue;
				$i = urldecode( $i );
				$split = preg_split( '/(~|:)/', trim( $i ) );
				# Prevent "one, two, " causing problems (last item is a space)
				if ( empty( $split[0] ) ) continue;
				switch( count( $split ) ) {
					case 1:
						list( $name ) = $split;
						$name_found = get_term_by( 'name', $name, $taxonomy );
						if ( $name_found ) {
							# We want 'Water' to just create one slug and then re-use, not one slug for every instance.
							$slug = $name_found->slug;
						} else {
							$slug = $name;
						}
						$parent_id = 0;
						break;
					case 2:
						list( $slug, $name ) = $split;
						$parent_id = 0;
						break;
					case 3: list( $parent_slug, $slug, $name ) = $split;
						$parent = get_term_by( 'slug', $parent_slug, $taxonomy );
						$parent_id = ( $parent->term_id ) ? $parent->term_id : 0;
						break;
					default:
						return false;
				}
				$term = get_term_by( 'slug', $slug, $taxonomy );
				
				if ( $term ) {
					$term = wp_update_term( $term->term_id, $taxonomy, Array( 'slug' => $slug, 'parent' => $parent_id ) );
				} else {
					$term = wp_insert_term( $name, $taxonomy, Array( 'slug' => $slug, 'parent' => $parent_id ) );
				}

				if ( is_wp_error( $term ) ) {
					$this->stats['Error'][] = Array( 'id' => $post_id, 'error_id' => CPK_WPCSV::ERROR_INVALID_TAXONOMY_TERM );
				} else {
					$term_ids[] = (int)$term['term_id'];
				}
			}

			wp_set_object_terms( $post_id, $term_ids, $taxonomy, FALSE );
			# MUST do this to flush the term cache ( didn't seem to work reliably however )
			wp_cache_set( 'last_changed', time( ) - 1800, 'terms' );
			wp_cache_delete( 'all_ids', $taxonomy );
			wp_cache_delete( 'get', $taxonomy );
			delete_option( "{$taxonomy}_children" );
			_get_term_hierarchy( $taxonomy );
		}

		public function add_post_image_meta( $image_id, $post_id, $file, $meta ) {

			// Let WP run inbuilt functions
			if ( !is_wp_error($image_id) ) {
				wp_update_attachment_metadata( $image_id, wp_generate_attachment_metadata( $image_id, $file ) );
			}

			if ( !isset( $meta['caption'] ) ) $meta['caption'] = '';

			// Manually update the image title, content, etc
			$image_data = array(
				'ID' => $image_id,
				'post_title' => $meta['title'],
				'post_content' => $meta['content'],
				'post_excerpt' => $meta['caption'],
				'post_name' => $meta['title']	
			);
			wp_update_post( $image_data );
			
			return; //Disable image meta_data

		}
 
		public function import_image( $file, $url ) {

			// Get mime type - necessary here or wp_get_attachment_meta fails later
			$mimetype = wp_check_filetype($file, null );

			// Construct the attachment array
			$attachment = array(
				'post_mime_type' => $mimetype['type'],
				'guid' => $url,
				'post_parent' => 0,
				'post_title' => 'temp_title',
				'post_content' => 'temp_content'
			);

			// Save the data
			$image_id = wp_insert_attachment($attachment, $file);

			return $image_id;
		}

		public function get_image_metadata( $file ) {

			$temp = wp_check_filetype($file, null );
			$meta['mimetype'] = $temp['type'];
			$meta['title'] = explode( '.', basename( $file ) );
			$meta['title'] = $meta['title'][0];
			$meta['content'] = '';

			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			// use image exif/iptc data for title and caption defaults if possible
			if ( $image_meta = @wp_read_image_metadata($file) ) {
				if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
					$meta['title'] = $image_meta['title'];
				if ( trim( $image_meta['caption'] ) )
					$meta['content'] = $image_meta['caption'];
			}

			// EXIF
			if ( in_array( $meta['mimetype'], Array( 'image/jpg', 'image/jpeg', 'image/tiff' ) ) ) {
				$exif = exif_read_data( $file );
			}
			// IPTC
			$size = getimagesize( $file, $info );
			if ( isset( $info['APP13'] ) ) {
				$iptc = iptcparse( $info['APP13'] );
			}

			if ( isset( $exif['ExifImageWidth'] ) ) {
				$meta['exif_width'] = $exif['ExifImageWidth'];
			} elseif ( isset( $exif['COMPUTED']['Width'] ) ) {
				$meta['exif_width'] = $exif['COMPUTED']['Width'];
			}

			if ( isset( $exif['ExifImageLength'] ) ) {
				$meta['exif_height'] = $exif['ExifImageLength'];
			} elseif ( isset( $exif['COMPUTED']['Height'] ) ) {
				$meta['exif_height'] = $exif['COMPUTED']['Height'];
			}

			
			if ( ( $meta['exif_width'] * 0.9) <= $meta['exif_height'] && ( $meta['exif_width'] * 1.1 ) >= $meta['exif_height'] ) {
				$meta['exif_orientation'] = 'square';
			} elseif ( ( $meta['exif_height'] * 1.9) <= $meta['exif_width'] ) {
				$meta['exif_orientation'] = 'panorama';
			} elseif ( isset( $exif['Orientation'] ) && in_array( $exif['Orientation'], array( 1, 2, 3, 4 ) ) ) {
				$meta['exif_orientation'] = 'landscape';
			} elseif ( isset( $exif['Orientation'] ) &&  in_array( $exif['Orientation'], array( 5, 6, 7, 8 ) ) ) {
				$meta['exif_orientation'] = 'portrait';
			} else {
				$meta['exif_orientation'] = 'landscape';
			}

			if ( isset( $exif['DateTime'] ) ) {
				$meta['exif_created'] = $exif['DateTime'];
			} elseif ( isset( $exif['FileDateTime'] ) ) {
				$meta['exif_created'] = date( 'Y:m:d H:i:s', $exif['FileDateTime'] );
			}

			$meta['iptc_author'] = ( isset( $iptc['2#080'] ) ) ? implode( ',', $iptc['2#080'] ) : '';

			return $meta;
		}

	}
}
