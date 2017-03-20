<?php

if ( !class_exists( 'CPK_WPCSV_Posts_Model' ) ) {

class CPK_WPCSV_Posts_Model {

	private $db;
	private $debugger = NULL;
	private $settings = Array( );

	public function __construct( $settings ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->settings = $settings;
		
	}

	public function update_settings( $settings ) {
		$this->settings = $settings;
	}

	public function set_debugger( $debugger ) {
		if ( is_object( $debugger ) ) {
			$this->debugger = $debugger;
		}
	}

	private function trace( $label, $data ) {
		if ( is_object( $this->debugger ) && method_exists( $this->debugger, 'add' ) ) {
			$this->debugger->add( $label, $data );
		}
	}

	public function build_query( $fields, $post_id_list = NULL ) {

		$post_status_type_filter = $this->get_post_type_status_filter( );

		$post_id_filter = ( isset( $post_id_list ) ) ? "AND ID IN ( " . implode( ',', $post_id_list ) . " )" : '';

		$post_date_filter = $this->get_date_conditions( );

		$sql = "SELECT DISTINCT {$fields} FROM {$this->db->posts} WHERE 1 = 1 {$post_status_type_filter} {$post_id_filter} {$post_date_filter} ORDER BY post_modified DESC";

		$this->trace( 'Post Query', $sql );

		return $sql;
	}

	private function get_post_type_status_filter( ) {

		$excludes = $this->settings['post_type_status_exclude_filter'];
		
		$statements = Array( );

		if ( is_array( $excludes ) && !empty( $excludes ) ) {
			foreach( $excludes as $type => $statuses ) {
				$status_list = "'" . implode( "','", array_keys( $statuses ) ) . "'";
				$statements[] = "( `post_type` != '{$type}' OR `post_status` NOT IN ( {$status_list} ) )";
			} # End foreach
		} # End if	

		return ( $statements ) ? ' AND ' . implode( ' AND ', $statements ) : '';
	}

	public function get_post_type_status_combos( $settings = Array( ), $default = FALSE ) {

		$sql = "SELECT DISTINCT post_type FROM {$this->db->posts}";

		$types = $this->db->get_col( $sql );

		$statuses = $this->get_post_status_list( );
		
		if ( is_array( $types ) && !empty( $types ) ) {
			foreach( $types as $type ) {
				$type_statuses[ $type ] = array_combine( $statuses, array_fill( 0, count( $statuses ), $default ) );
			} # End foreach
		} # End if
		
		if ( is_array( $settings ) && !empty( $settings ) ) {
			foreach( $settings as $post_type => $post_statuses ) {
				if ( is_array( $post_statuses ) && !empty( $post_statuses ) ) {
					foreach( $post_statuses as $post_status => $enabled ) {
						$type_statuses[ $post_type ][ $post_status ] = $enabled;	
					} # End foreach
				} # End if
			} # End foreach
		} # End if

		return $type_statuses;

	}

	private function get_date_conditions( ) {
		if ( !isset( $this->settings['frontend']['start_date'] ) && !isset( $this->settings['frontend']['end_date'] ) ) return '';
		if ( !empty( $this->settings['frontend']['start_date'] ) && empty( $this->settings['frontend']['end_date'] ) ) {
			return " AND post_modified >= DATE( '{$this->settings['frontend']['start_date']}' )";
		}
		if ( empty( $this->settings['frontend']['start_date'] ) && !empty( $this->settings['frontend']['end_date'] ) ) {
			return " AND post_modified <= DATE( '{$this->settings['frontend']['end_date']}' )";
		}
		if ( !empty( $this->settings['frontend']['start_date'] ) && !empty( $this->settings['frontend']['end_date'] ) ) {
			return " AND post_modified >= DATE( '{$this->settings['frontend']['start_date']}' ) AND post_modified <= DATE( '{$this->settings['frontend']['end_date']}' )";
		}

	}

	private function get_post_status_list( ) {
		
		$sql = "SELECT DISTINCT post_status FROM {$this->db->posts}";

		$statuses = $this->db->get_col( $sql );

		$defaults = Array(
			'draft',
			'future',
			'inherit',
			'pending',
			'publish',
			'private',
			'trash'
		);

		$merged = array_merge( $statuses, $defaults );

		$merged = array_unique( $merged );

		sort( $merged );

		return $merged;

	}

	public function get_post_ids( ) {
		$sql = $this->build_query( 'ID,post_modified' );
		$post_ids = Array( );

		# $wpdb uses far too much memory so bypassing...

		if ( function_exists( 'mysqli_connect' ) ) {
			$host_parts = explode( ':', DB_HOST );
			if ( count( $host_parts ) == 2 ) {
				list( $db_host, $db_port ) = $host_parts;
			} else {
				$db_host = $host_parts[0];
				$db_port = 3306;
			}
			$link = mysqli_connect( $db_host, DB_USER, DB_PASSWORD, DB_NAME, (int)$db_port );
			mysqli_set_charset( $link, 'utf8mb4' );
			$results = mysqli_query( $link, $sql );
			if ( $results ) {
				while ( $result = mysqli_fetch_array( $results, MYSQLI_ASSOC ) ) {
					$post_ids[] = (int)$result['ID'];
				} # End while
			}
		} else {
			$results = mysql_query( $sql, $this->db->dbh );
			if ( $results ) {
				while ( $result = mysql_fetch_array( $results, MYSQL_ASSOC ) ) {
					$post_ids[] = (int)$result['ID'];
				} # End while
			}
		}

		return $post_ids;
	}

	public function get_post( $post_id ) {

		$headings = Array( );
		$values = Array( );

		# WP Posts fields
		$result = $this->get_posts( $this->remove_prefix( 'wp_', $this->settings['post_fields'] ), Array( $post_id ) );
		$post_fields = $result[0];
		$post_fields['post_author'] = $this->get_author_name( $post_fields['post_author'] );
		$post_headings = array_keys( $post_fields );
		$post_headings = $this->add_prefix( 'wp_', $post_headings );
		$post_values = array_values( $post_fields );

		# Taxonomy fields
		$tax_fields = $this->get_taxonomy_fields( $post_id );
		$this->trace( 'tax_fields', $tax_fields );
		$tax_headings = array_keys( $tax_fields );
		$tax_headings = $this->add_prefix( 'tx_', $tax_headings );
		$tax_values = array_values( $tax_fields );

		# Thumbnail
		$thumb_field = $this->get_thumbnail( $post_id );
		$thumb_heading = array_keys( $thumb_field );
		$thumb_heading = $this->add_prefix( 'fi_', $thumb_heading );
		$thumb_value = array_values( $thumb_field );

		# Custom fields		
		$custom_fields = $this->get_custom_fields_by_post_id( $post_id, $this->settings['export_hidden_custom_fields'] );
		$cf_headings = array_keys( $custom_fields );
		$cf_headings = $this->add_prefix( 'cf_', $cf_headings );
		$cf_values = array_values( $custom_fields );

		$headings = array_merge( $post_headings, $tax_headings, $thumb_heading, $cf_headings );
		$values = array_merge( $post_values, $tax_values, $thumb_value, $cf_values );
		
		$post = $this->apply_filters( $headings, $values, $this->settings );

		$this->trace( 'Filtered Post', $post );

		return $post;
	}

	public function add_prefix( $prefix, $keys ) {
		
		$prefixed = Array( );
		if ( is_array( $keys ) && !empty( $keys ) ) {
			foreach( $keys as $index => $key ) {
				$prefixed[ $index ] = "{$prefix}{$key}";
			} # End foreach
		} # End if

		return $prefixed;
	}

	public function remove_prefix( $prefix, $keys ) {
		
		$deprefixed = Array( );
		if ( is_array( $keys ) && !empty( $keys ) ) {
			foreach( $keys as $index => $key ) {
				$deprefixed[ $index ] = preg_replace( '/^' . $prefix . '/', '', $key );
			} # End foreach
		} # End if

		$this->trace( 'deprefixed', $deprefixed );

		return $deprefixed;
	}

	public function apply_filters( $headings, $values, $settings ) {
		
		$include_list = $this->include_filter( $headings, $this->settings['include_field_list'], $this->settings['mandatory_fields'] );
		$filter_list = $this->exclude_filter( $include_list, $this->settings['exclude_field_list'], $this->settings['mandatory_fields'] );

		$this->trace( 'headings', $headings );
		$this->trace( 'values', $values );
		$this->trace( 'filter list', $filter_list );
		
		if ( is_array( $filter_list ) && !empty( $filter_list ) ) {
			foreach( $filter_list as $index => $value ) {
				$filtered_headings[] = $headings[ $index ];
				$filtered_values[] = $values[ $index ];
			} # End foreach
		} # End if

		return Array( 
			'headings' => $filtered_headings,
			'values' => $filtered_values
		);
	}

	public function get_thumbnail( $post_id ) {
		
		$thumb_id = get_post_thumbnail_id( $post_id );
		$thumb_src = wp_get_attachment_image_src( $thumb_id, 'full' );
		$thumb_url = $thumb_src[0];
		$upload_dir = wp_upload_dir();
		$thumb_file = preg_replace( '|' . WP_CONTENT_URL . '/' . basename( $upload_dir['baseurl'] ) . '/|', '', $thumb_url );

		return Array( 'thumbnail' => $thumb_file );
	}

	public function get_author_name( $author_id ) {
		# Convert User id to username
		if ( isset( $author_id ) && !empty( $author_id ) ) {
			$user = get_user_by( 'id', $author_id );
			if ( gettype( $user ) == 'object' ) {
				$author_name = $user->get( 'user_login' );
			} else { # Author id invalid, so blank the field.
				$author_name = '';
			}
		}
		return $author_name;
	}

	public function get_taxonomy_list( ) {
		return get_taxonomies( Array( 'public' => TRUE ), 'names' );
	}

	public function get_taxonomy_fields( $post_id ) {
		$taxonomy_values = Array( );
		$taxonomy_list = $this->get_taxonomy_list( );
		
		$this->trace( 'Taxonomy List', $taxonomy_list );

		foreach( $taxonomy_list as $taxonomy ) {
			$taxonomy_values[] = $this->export_taxonomy( wp_get_object_terms( $post_id, $taxonomy ) );
		}
		
		$tax_fields = array_combine( $taxonomy_list, $taxonomy_values );

		return $tax_fields;
	}

	private function export_taxonomy( Array $items ) {

		$output = Array( );
		$items = $this->sort_taxonomy( $items );
		foreach( $items as $i ) {
			$text = "{$i->slug}:{$i->name}";
			if ( $i->parent ) {
				$parent = get_term( $i->parent, $i->taxonomy );
				$text = $parent->slug . '~' . $text;
			}

			$output[] = urldecode( $text );
		}

		return implode( ',', $output );
	}

	private function sort_taxonomy( Array $items ) {
		
		if ( empty( $items ) ) return $items;

		foreach( $items as $item ) {
			$grouped[$item->parent]->children[$item->term_id] = $item;
			$index[$item->term_id] = $item;
		}

		foreach( $grouped as $k => $v ) {
			if ( isset( $index[$k] ) ) {
				$index[$k]->children = $v->children;
				unset( $grouped[$k] );
			}
		}

		return $this->taxonomy_array_flatten( $grouped );
	}

	private function taxonomy_array_flatten( Array $array ) {
		$flat = Array( );
		foreach( $array as $k => $v ) {
			if ( !empty( $v->children ) ) {
				$children = $v->children;
				unset( $v->children );
				if ( isset( $v->slug ) ) $flat[] = $v;
				$flat = array_merge( $flat, $this->taxonomy_array_flatten( $children ) );
			} else {
				$flat[] = $v;
			}
		}
		return $flat;
	}

	public function get_posts( Array $fields, $post_ids = Array( ) ) {
		$field_list = '`' . implode( '`,`', $fields ) . '`';
		$sql = $this->build_query( $field_list, $post_ids );
		$results = $this->db->get_results( $sql, ARRAY_A );
		return (Array)$results;
	}

	public function get_custom_field_list( $export_hidden = TRUE ) {
		$sql = "SELECT DISTINCT meta_key FROM {$this->db->postmeta}";
		if ( !$export_hidden ) $sql .= " WHERE meta_key NOT LIKE '\_%'";
		return $this->db->get_col( $sql );
	}
	
	public function get_custom_fields_by_post_id( $post_id, $export_hidden = TRUE ) {

		$sql = "SELECT DISTINCT meta_key, meta_value FROM {$this->db->postmeta}";
		$sql .= " WHERE post_id = '{$post_id}'";
		if ( !$export_hidden ) $sql .= " AND meta_key NOT LIKE '\_%'";
		$results = $this->db->get_results( $sql, ARRAY_A );
		
		$custom_field_keys = $this->get_custom_field_list( $export_hidden );
		$custom_field_values = array_fill_keys( $custom_field_keys, '' );

		$this->trace( 'Full CF List', $custom_field_values );

		if ( is_array( $results ) && !empty( $results ) ) {
			foreach( $results as $result ) {
				$custom_field_values[ $result['meta_key'] ] = $result['meta_value'];
			} # End foreach
		} # End if

		return $custom_field_values;
	}

	public function get_custom_field_values( $post_id, $field_list, $custom_field_list ) {

		$custom_field_values = Array( );

		$meta_values = $this->get_custom_field_by_post_id( $post_id );

		foreach ( $custom_field_list as $cf ) {
			#if ( !in_array( $cf, $field_list ) ) continue;
			$val = ( isset( $meta_values[ $cf ] ) ) ? $meta_values[ $cf ] : '';
			if ( unserialize( $val ) && function_exists( 'json_encode' ) ) {
				$val = json_encode( unserialize( $val ) );
			}
			$custom_field_values[] = $val;
		}
		return $custom_field_values;
	}


	public function exclude_filter( Array $elements, Array $rules, Array $mandatory_fields ) {

		$filtered_array = Array( );

		if ( is_array( $elements ) && !empty( $elements ) ) {
			foreach( $elements as $key => $val ) {
				if ( in_array( $val, $mandatory_fields ) || !$this->rule_match( $val, $rules ) ) {
					$filtered_array[ $key ] = $val;
				}
			} # End foreach
		} # End if

		return $filtered_array;
	}

	public function include_filter( Array $elements, Array $rules, Array $mandatory_fields ) {

		$filtered_array = Array( );
		
		if ( is_array( $elements ) && !empty( $elements ) ) {
			foreach( $elements as $key => $val ) {
				if ( $this->rule_match( $val, $rules ) || in_array( $val, $mandatory_fields ) ) {
					$filtered_array[ $key ] = $val;
				}
			} # End foreach
		} # End if

		return $filtered_array;

	}
	
	private function rule_match( $value, Array $rules ) {
		
		if ( is_array( $rules ) && !empty( $rules ) ) {
			foreach( $rules as $rule ) {
				if ( $rule == $value ) return TRUE;
				if ( $rule == '*' ) return TRUE; 
				if ( substr( $rule, 0, 1 ) == '*' && preg_match( '/' . substr( $rule, 1 ) . '$/', $value ) ) return TRUE;
				if ( substr( $rule, -1, 1 ) == '*' && preg_match( '/^' . substr( $rule, 0, -1 ) . '/', $value ) ) return TRUE;
			} # End foreach
		} # End if

	}

} # End class CPK_WPCSV_Posts_Model

} # End if
