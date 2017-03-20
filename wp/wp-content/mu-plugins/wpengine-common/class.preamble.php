<?php

namespace wpe\plugin;

class Preamble {

    public $function_modifier;
    public $no_option_slug;
    public $non_cacheable_options = array();
    public $cached_options = array();
    public $option_gets         = 0;
    public $option_cache_hits   = 0;
    public $option_sets         = 0;
    public $ot_cache;
    public $now_time;
    public $cached_dir;

    public function __construct() {
        $this->no_option_slug = new \stdClass();
    }

    /**
     * @param $modifier iFunctionModifier
     * @param $uri Page requested to build.  Effects certains overridden behavior.
     */
    public function redefine($modifier, $uri) {
        $this->function_modifier = $modifier;
        $do_modify_option_functions = FALSE === strpos($uri, "/wp-admin");

        // Replaces a function with an already-defined function "_wpe_new_$name" with the given string arglist
        if ( $do_modify_option_functions ) {
            $this->redefineGetOption();

            add_action( 'updated_option', '_wpe_do_changed_option' );
            add_action( 'added_option', '_wpe_do_changed_option' );

            $this->redefineGetObjectTermCache();
        }
        $this->redefineWpUploadDir();

        // DEBUG register_shutdown_function(array($this, 'logStats'));
    }

    /**
     * @return String, most likely used for error log tracking
     */
    public function calculateStats() {
        return "OPTIONS: gets/hits/sets: " . $this->option_gets . "/" . $this->option_cache_hits . "/" . $this->option_sets . " (" . number_format( 100.0 * $this->option_cache_hits / max( 1, $this->option_gets ), 0 ) . "% hits)";
    }

    /**
     * Get the calculated stats and write to the error log.
     */
    public function logStats() {
        error_log($this->calculateStats());
    }

    /**
     * Singleton Function
     * @return Class::Preamble
     */
    static public function instance() {
        static $instance;
        if( $instance ) return $instance;
        $instance = new Preamble();
        return $instance;
    }

    /**
     * Do the function modification for 'get_option'
     */
    public function redefineGetOption() {
        $runkit_code = "return \wpe\plugin\_wpe_new_get_option(\$name, \$default);";
        $uopz_code = function($name, $default=false) {
            return _wpe_new_get_option($name, $default);
        };

        $modify_args = array(
            'function' => 'get_option',
            'runkit_delegate_func' => '_wpe_old_get_option',
            'runkit_code' => $runkit_code,
            'runkit_args' => '$name,$default=false',
            'uopz_closure' => $uopz_code
        );
        $this->function_modifier->redefineFunctionSaveOriginal($modify_args);
    }

    /**
     * Circumvent the wp get_option function
     */
    function new_get_option( $name, $default = false ) {
        $this->option_gets++;
        $is_cacheable = ! isset( $this->non_cacheable_options[$name] );
        if ( $is_cacheable && isset( $this->cached_options[$name] ) ) {
            $this->option_cache_hits++;
            $val = $this->cached_options[$name];
        } else {
            // delegate function, if not, call original
            if ($this->function_modifier->_changesCallUserFunc()) {
                $val = _wpe_old_get_option( $name, $this->no_option_slug );
            } else {
                $val = call_user_func_array('get_option', array($name, $this->no_option_slug));
            }
            if ( $is_cacheable )
                $this->cached_options[$name] = $val == $this->no_option_slug ? $default : $val;
        }
        if ( $val == $this->no_option_slug )
            return $default;
        return $val;
    }

    /**
     * Do the function modification for 'get_object_term_cache'
     */
    public function redefineGetObjectTermCache() {
        $runkit_code = "return \wpe\plugin\_wpe_new_get_object_term_cache(\$id, \$taxonomy);";
        $uopz_code = function($id, $taxonomy) {
            return _wpe_new_get_object_term_cache($id, $taxonomy);
        };

        $modify_args = array(
            'function' => 'get_object_term_cache',
            'runkit_delegate_func' => '_wpe_old_get_object_term_cache',
            'runkit_code' => $runkit_code,
            'runkit_args' => '$id, $taxonomy',
            'uopz_closure' => $uopz_code
        );
        $this->function_modifier->redefineFunctionSaveOriginal($modify_args);
    }

    /**
     * Cache object/terms in current memory
     */
    function new_get_object_term_cache( $id, $taxonomy ) {
        if ( ! isset($this->ot_cache) )
            $this->ot_cache = array();
        $key = "$id|$taxonomy";
        $val = @$this->ot_cache[$key];
        if ( ! $val ) {
            if ($this->function_modifier->_changesCallUserFunc()) {
                $val = _wpe_old_get_object_term_cache( $id, $taxonomy );
            } else {
                $val = call_user_func_array('get_object_term_cache', array($id, $taxonomy));
            }
            $this->ot_cache[$key] = $val;
        }
        return $val;
    }

    /**
     * Do the function modification for 'wp_upload_dir'
     */
    public function redefineWpUploadDir() {
        $runkit_code = "return \wpe\plugin\_wpe_new_wp_upload_dir(\$time);";
        $uopz_code = function($time=null) {
            return _wpe_new_wp_upload_dir($time);
        };

        $modify_args = array(
            'function' => 'wp_upload_dir',
            'runkit_delegate_func' => '_wpe_old_wp_upload_dir',
            'runkit_code' => $runkit_code,
            'runkit_args' => '$time=null',
            'uopz_closure' => $uopz_code
        );
        $this->function_modifier->redefineFunctionSaveOriginal($modify_args);
    }

    /**
     * Cache the wp_upload_dir() against normalized times
     */
    function new_wp_upload_dir( $time = null ) {
        if ( ! isset($this->now_time) ) $this->now_time = date('Y/m');
        $requested_datestamp = $time ? $time : $this->now_time;
        $is_cacheable = $requested_datestamp == $this->now_time;
        if ( isset($this->cached_dir) && $is_cacheable )        // cached and good to go?
            return $this->cached_dir;
        // load it from the original API
        // delegate function, if not, call original
        if ($this->function_modifier->_changesCallUserFunc()) {
            $dir = _wpe_old_wp_upload_dir($time);
        } else {
            $dir = call_user_func('wp_upload_dir', $time);
        }
        if ( $is_cacheable )            // if allowed to cache, do it!
            $this->cached_dir = $dir;
        return $dir;            // this is the answer in any case
    }
}

/**
 * Circumvent the wp get_option function
 */
function _wpe_new_get_option( $name, $default = false ) {
    $preamble = Preamble::instance();
    return $preamble->new_get_option($name, $default);
}

/**
 * Purge cache on add_option() or updated_option()
 */
function _wpe_do_changed_option( $option ) {
    $preamble = Preamble::instance();
    $preamble->option_sets++;
    $preamble->cached_options = array();        // if anything is set, clear the entire array.  Seems like too much, but perhaps safer.
}

/**
 * Cache object/terms in current memory
 */
function _wpe_new_get_object_term_cache( $id, $taxonomy ) {
    $preamble = Preamble::instance();
    return $preamble->new_get_object_term_cache($id, $taxonomy);
}

/**
 * Cache the wp_upload_dir() against normalized times
 */
function _wpe_new_wp_upload_dir( $time = null ) {
    $preamble = Preamble::instance();
    return $preamble->new_wp_upload_dir($time);
}

