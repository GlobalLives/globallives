<?php

if ( ! function_exists( 'tribe_array_merge_recursive' ) ) {
	/**
	 * Recursively merge two arrays preserving keys.
	 *
	 * @link http://php.net/manual/en/function.array-merge-recursive.php#92195
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	function tribe_array_merge_recursive( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = tribe_array_merge_recursive( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}
}

if ( ! function_exists( 'tribe_register_plugin' ) ) {
	/**
	 * Checks if this plugin has permission to run, if not it notifies the admin
	 *
	 * @param string $file_path   Full file path to the base plugin file
	 * @param string $main_class  The Main/base class for this plugin
	 * @param string $version     The version
	 * @param array  $classes_req Any Main class files/tribe plugins required for this to run
	 *
	 * @return bool Indicates if plugin should continue initialization
	 */
	function tribe_register_plugin( $file_path, $main_class, $version, $classes_req = array() ) {
		$tribe_dependency = Tribe__Dependency::instance();
		$should_plugin_run = true;

		// Checks to see if the plugins are active
		if ( ! empty( $classes_req ) && ! $tribe_dependency->has_requisite_plugins( $classes_req ) ) {
			$should_plugin_run = false;

			$tribe_plugins = new Tribe__Plugins();
			$admin_notice  = new Tribe__Admin__Notice__Plugin_Download( $file_path );

			foreach ( $classes_req as $class => $version ) {
				$plugin    = $tribe_plugins->get_plugin_by_class( $class );
				$is_active = $tribe_dependency->is_plugin_version( $class, $version );
				$admin_notice->add_required_plugin( $plugin['short_name'], $plugin['thickbox_url'], $is_active );
			}
		}

		if ( $should_plugin_run ) {
			$tribe_dependency->add_active_plugin( $main_class, $version, $file_path );
		}

		return $should_plugin_run;
	}
}

if ( ! function_exists( 'tribe_append_path' ) ) {
	/**
	 * Append a path fragment to a URL preserving query arguments
	 * and fragments.
	 *
	 * @param string $url A full URL in the `http://example.com/?query=var#frag` format.
	 * @param string $path The path to append to the existing, if any, one., e.g. `/some/path`
	 *
	 * @return mixed|string
	 *
	 * @since 4.3
	 */
	function tribe_append_path( $url, $path ) {
		$path = trim( $path, '/' );

		$query = @parse_url( $url, PHP_URL_QUERY );
		$frag  = @parse_url( $url, PHP_URL_FRAGMENT );

		if ( ! ( empty( $query ) && empty( $frag ) ) ) {
			$url   = str_replace( '?' . $query, '', $url );
			$url   = str_replace( '#' . $frag, '', $url );
			$query = $query ? '?' . $query : '';
			$frag  = $frag ? '#' . $frag : '';
		}

		$url = trailingslashit( esc_url_raw( trailingslashit( $url ) . $path ) );
		$url .= $query . $frag;

		return $url;
	}
}

if ( ! function_exists( 'tribe_exit' ) ) {
	/**
	 * Filterable `die` wrapper.
	 *
	 * @param string $status
	 *
	 * @return void|mixed Depending on the handler this function might return
	 *                    a value or `die` before anything is returned.
	 */
	function tribe_exit( $status = '' ) {
		$handler = 'die';

		/**
		 * Filters the callback to call in place of `die()`.
		 *
		 * @param callable $handler The `die` replacement callback.
		 * @param string   $status  The exit/die status.
		 */
		$handler = apply_filters( 'tribe_exit', $handler, $status );

		// Die and exit are language constructs that cannot be used as callbacks on all PHP runtimes
		if ( 'die' === $handler || 'exit' === $handler ) {
			exit ( $status );
		}

		return call_user_func( $handler, $status );
	}
}

if ( ! function_exists( 'tribe_get_request_var' ) ) {
	/**
	 * Tests to see if the requested variable is set either as a post field or as a URL
	 * param and returns the value if so.
	 *
	 * Post data takes priority over fields passed in the URL query. If the field is not
	 * set then $default (null unless a different value is specified) will be returned.
	 *
	 * The variable being tested for can be an array if you wish to find a nested value.
	 *
	 * @see Tribe__Utils__Array::get()
	 *
	 * @param string|array $var
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	function tribe_get_request_var( $var, $default = null ) {
		$post_var = Tribe__Utils__Array::get( $_POST, $var );

		if ( null !== $post_var ) {
			return $post_var;
		}

		$query_var = Tribe__Utils__Array::get( $_GET, $var );

		if ( null !== $query_var ) {
			return $query_var;
		}

		return $default;
	}
}

if ( ! function_exists( 'tribe_is_truthy' ) ) {
	/**
	 * Determines if the provided value should be regarded as 'true'.
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 */
	function tribe_is_truthy( $var ) {
		if ( is_bool( $var ) ) {
			return $var;
		}

		/**
		 * Provides an opportunity to modify strings that will be
		 * deemed to evaluate to true.
		 *
		 * @param array $truthy_strings
		 */
		$truthy_strings = (array) apply_filters( 'tribe_is_truthy_strings', array(
			'1',
			'enable',
			'enabled',
			'on',
			'y',
			'yes',
			'true',
		) );
		// Makes sure we are dealing with lowercase for testing
		if ( is_string( $var ) ) {
			$var = strtolower( $var );
		}

		// If $var is a string, it is only true if it is contained in the above array
		if ( in_array( $var, $truthy_strings, true ) ) {
			return true;
		}

		// All other strings will be treated as false
		if ( is_string( $var ) ) {
			return false;
		}

		// For other types (ints, floats etc) cast to bool
		return (bool) $var;
	}
}

if ( ! function_exists( 'tribe_normalize_terms_list' ) ) {
	/**
	 * Normalizes a list of terms to a list of fields.
	 *
	 * @param $terms A term or array of terms to normalize.
	 * @param string $taxonomy The terms taxonomy.
	 * @param string $field Teh fields the terms should be normalized to.
	 *
	 * @since 4.5
	 *
	 * @return array An array of the valid normalized terms.
	 */
	function tribe_normalize_terms_list( $terms, $taxonomy, $field = 'term_id' ) {
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		$normalized = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && ! empty( $term->{$field} ) ) {
				$normalized[] = $term->{$field};
			} elseif ( is_numeric( $term ) ) {
				$term = get_term_by( 'id', $term, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$normalized[] = $term->{$field};
				}
			} elseif ( is_string( $term ) ) {
				$term = get_term_by( 'slug', $term, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$normalized[] = $term->{$field};
				}
			} elseif ( is_array( $term ) && ! empty( $term[ $field ] ) ) {
				$normalized[] = $term[ $field ];
			}
		}

		return $normalized;
	}
}