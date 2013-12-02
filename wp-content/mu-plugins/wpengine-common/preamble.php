<?php

if ( extension_loaded( 'runkit' ) ) {

    class WPE_Global {

		public static $no_option_slug;
		public static $replace_options = false;
        public static $non_cacheable_options = array();
        public static $cached_options = array();
        public static $option_gets         = 0;
        public static $option_cache_hits   = 0;
        public static $option_sets         = 0;
        public static $cached_get_home_url = array();
        public static $c_get_home_url      = 0;
        public static $c_get_home_url_hits = 0;
        public static $filter_cache        = array();
        public static $filter_runs       = 0;
        public static $filter_cache_hits = 0;

		public static function static_init() {
			WPE_Global::$no_option_slug = new stdClass();
			WPE_Global::$replace_options = FALSE === strpos($_SERVER['REQUEST_URI'],"/wp-admin");
		}
		
        public static function log_stats() {
            error_log( "OPTIONS: gets/hits/sets: " . WPE_Global::$option_gets . "/" . WPE_Global::$option_cache_hits . "/" . WPE_Global::$option_sets . " (" . number_format( 100.0 * WPE_Global::$option_cache_hits / max( 1, WPE_Global::$option_gets ), 0 ) . "% hits)" );
//		error_log("GET_HOME_URL: gets/hits: ".WPE_Global::$c_get_home_url."/".WPE_Global::$c_get_home_url_hits." (".number_format(100.0*WPE_Global::$c_get_home_url_hits/max(1,WPE_Global::$c_get_home_url),0)."% hits)");
//		error_log("FILTERS: runs/hits: ".WPE_Global::$filter_runs."/".WPE_Global::$filter_cache_hits." (".number_format(100.0*WPE_Global::$filter_cache_hits/max(1,WPE_Global::$filter_runs),0)."% hits)");
        }

        public static function copy( &$obj ) {
            return is_object( $obj ) ? clone $obj : $obj;
        }

    }
	WPE_Global::static_init();

	// Replaces a function with an already-defined function "_wpe_new_$name" with the given string arglist
    function _wpe_replace_function( $name, $args ) {
		if ( function_exists($name) ) {			// is possible that it doesn't!  Then don't emit errors.
	        $call_args = preg_replace( "#=[^,]+#", "", $args );
	        runkit_function_copy( $name, "_wpe_old_$name" );
	        runkit_function_redefine( $name, $args, "return _wpe_new_${name}($call_args);" );
		}
    }

	if ( WPE_Global::$replace_options ) {

	    function _wpe_new_get_option( $name, $default = false ) {
	        WPE_Global::$option_gets++;
	        $is_cacheable = ! isset( WPE_Global::$non_cacheable_options[$name] );
	        if ( $is_cacheable && isset( WPE_Global::$cached_options[$name] ) ) {
	            WPE_Global::$option_cache_hits++;
				$val = WPE_Global::$cached_options[$name];
	        } else {
		        $val = _wpe_old_get_option( $name, WPE_Global::$no_option_slug );
		        if ( $is_cacheable )
		            WPE_Global::$cached_options[$name] = $val == WPE_Global::$no_option_slug ? $default : $val;
			}
			if ( $val == WPE_Global::$no_option_slug )
				return $default;
			return $val;
	    }
	    _wpe_replace_function( 'get_option', '$name,$default=false' );

		// Purge cache on add_option() or updated_option()
	    function _wpe_do_changed_option( $option ) {
	        global $_wpe_cached_options;

	        WPE_Global::$option_sets++;
	        WPE_Global::$cached_options = array();		// if anything is set, clear the entire array.  Seems like too much, but perhaps safer.
	    }
	
	    add_action( 'updated_option', '_wpe_do_changed_option' );
	    add_action( 'added_option', '_wpe_do_changed_option' );
	
		// Cache object/terms in current memory
		function _wpe_new_get_object_term_cache( $id, $taxonomy ) {
			static $ot_cache;
			if ( ! isset($ot_cache) ) $ot_cache = array();
			$key = "$id|$taxonomy";
			$val = @$ot_cache[$key];
			if ( ! $val ) {
				$val = _wpe_old_get_object_term_cache( $id, $taxonomy );
				$ot_cache[$key] = $val;
			}
			return $val;
		}
	    _wpe_replace_function( 'get_object_term_cache', '$id,$taxonomy' );
	
	}

	// Cache the wp_upload_dir() against normalized times
	function _wpe_new_wp_upload_dir( $time = null ) {
		static $now_time;
		static $cached_dir;
		if ( ! isset($now_time) ) $now_time = date('Y/m');
		$requested_datestamp = $time ? $time : $now_time;
		$is_cacheable = $requested_datestamp == $now_time;
		if ( isset($cached_dir) && $is_cacheable )		// cached and good to go?
			return $cached_dir;
		$dir = _wpe_old_wp_upload_dir($time);		// load it from the original API
		if ( $is_cacheable )			// if allowed to cache, do it!
			$cached_dir = $dir;
		return $dir;			// this is the answer in any case
	}
    _wpe_replace_function( 'wp_upload_dir', '$time=null' );


    /*
      function _wpe_new_get_home_url( $blog_id = null, $path = '', $scheme = null )
      {
      WPE_Global::$c_get_home_url++;
      $key = "$blog_id|$path|$scheme";
      if ( isset(WPE_Global::$cached_get_home_url[$key]) ) {
      WPE_Global::$c_get_home_url_hits++;
      return WPE_Global::$cached_get_home_url[$key];
      }
      $val = _wpe_old_get_home_url($blog_id, $path, $scheme);
      WPE_Global::$cached_get_home_url[$key] = $val;
      return $val;
      }
      _wpe_replace_function('get_home_url','$blog_id=null,$path="",$scheme=null');
     */

// Funnel the varargs version into the array-based version
    /*
      runkit_function_redefine('apply_filters','$tag','return apply_filters_ref_array($tag,array_slice(func_get_args(),1));');

      function _wpe_new_apply_filters_ref_array( $tag, $args )
      {
      WPE_Global::$filter_runs++;
      $key = "$tag|".serialize($args);
      if ( isset( WPE_Global::$filter_cache[$key] ) ) {
      WPE_Global::$filter_cache_hits++;
      return WPE_Global::$filter_cache[$key];
      }
      $r = _wpe_old_apply_filters_ref_array($tag,$args);
      WPE_Global::$filter_cache[$key] = WPE_Global::copy($r);
      return $r;
      }
      _wpe_replace_function('apply_filters_ref_array','$tag,$args');
     */

//register_shutdown_function(array('WPE_Global','log_stats'));
}

