<?php
/*
Plugin Name: Memcached Redux
Description: The real Memcached (not Memcache) backend for the WP Object Cache.
Version: 0.1.3
Plugin URI: http://wordpress.org/extend/plugins/memcached/
Author: Scott Taylor - uses code from Ryan Boren, Denis de Bernardy, Matt Martz, Mike Schroder

Install this file to wp-content/object-cache.php
*/

function wpe_oc_active_notice() {
    $class = "error";
    $message = "WARNING: The WPE object caching file has been found on the staging site, and has just been removed. Please be sure to purge the cache on your production site to ensure that there are no issues.";
    echo "<div class=\"$class\"> <p>$message</p> </div>";
}

function wpe_oc_staging_delete() {
    unlink( __FILE__ );
}

if ( isset( $_SERVER["IS_WPE_SNAPSHOT"] ) ) {
    add_action( 'admin_notices', 'wpe_oc_active_notice' );
    add_action( 'admin_init', 'wpe_oc_staging_delete' );
}

if ( !defined( 'WP_CACHE_KEY_SALT' ) ) {
	define( 'WP_CACHE_KEY_SALT', '' );
}

if ( class_exists( 'Memcached' ) ):

function wp_cache_add( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->add( $key, $data, $group, $expire );
}

function wp_cache_incr( $key, $n = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->incr( $key, $n, $group );
}

function wp_cache_decr( $key, $n = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->decr( $key, $n, $group );
}

function wp_cache_close() {
	global $wp_object_cache;

	return $wp_object_cache->close();
}

function wp_cache_delete( $key, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->delete( $key, $group );
}

function wp_cache_flush() {
	global $wp_object_cache;

	return $wp_object_cache->flush();
}

function wp_cache_get( $key, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->get( $key, $group );
}



// XXX The multi methods have not been verified for generational caching. Therefore,
//     we will not use them.

/**
 * $keys_and_groups = array(
 *      array( 'key', 'group' ),
 *      array( 'key', '' ),
 *      array( 'key', 'group' ),
 *      array( 'key' )
 * );
 *
 */
/*
function wp_cache_get_multi( $key_and_groups, $bucket = 'default' ) {
	global $wp_object_cache;

	return $wp_object_cache->get_multi( $key_and_groups, $bucket );
}
*/

/**
 * $items = array(
 *      array( 'key', 'data', 'group' ),
 *      array( 'key', 'data' )
 * );
 *
 */
/*
function wp_cache_set_multi( $items, $expire = 0, $group = 'default' ) {
	global $wp_object_cache;

	return $wp_object_cache->set_multi( $items, $expire = 0, $group = 'default' );
}
*/
function wp_cache_init() {
	global $wp_object_cache;

	$wp_object_cache = new WP_Object_Cache();
}

function wp_cache_replace( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->replace( $key, $data, $group, $expire );
}

function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	if ( defined( 'WP_INSTALLING' ) == false )
		return $wp_object_cache->set( $key, $data, $group, $expire );
	else
		return $wp_object_cache->delete( $key, $group );
}

function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;

	$wp_object_cache->add_global_groups( $groups );
}

function wp_cache_add_non_persistent_groups( $groups ) {
	global $wp_object_cache;

	$wp_object_cache->add_non_persistent_groups( $groups );
}


function wpe_enable_object_cache() {
    global $wp_object_cache;

    $wp_object_cache->set_cache_enabled(true);
}

function wpe_disable_object_cache() {
    global $wp_object_cache;

    $wp_object_cache->set_cache_enabled(false);
}


class WP_Object_Cache {
	var $global_groups = array ('users', 'userlogins', 'usermeta', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'rss');

	var $no_mc_groups = array( 'comment', 'counts' );

	var $autoload_groups = array ('options');

	var $cache = array();
	var $mc = array();
	var $stats = array();
	var $group_ops = array();

	var $cache_enabled = true;
	var $default_expiration = 600;
	var $mcrouter_prefix = '';

	function is_cache_enabled($group) {
		return ($this->cache_enabled or in_array($group, $this->autoload_groups));
	}

	function add( $id, $data, $group = 'default', $expire = 0 ) {
		if ( !$this->is_cache_enabled($group) ) {
			return false;
		}

		$key = $this->key( $id, $group );

		if ( is_object( $data ) )
			$data = clone $data;

		if ( in_array( $group, $this->no_mc_groups ) ) {
			$this->cache[$key] = $data;
			return true;
		} elseif ( isset( $this->cache[$key] ) && $this->cache[$key] !== false ) {
			return false;
		}

		$mc =& $this->get_mc( $group );
		$expire = $this->convert_expire_time($expire);
		$result = $mc->add( $key, $data, $expire );

		if ( false !== $result ) {
			@ ++$this->stats['add'];
			$this->group_ops[$group][] = "add $id";
			$this->cache[$key] = $data;
		}

		return $result;
	}

	function add_global_groups( $groups ) {
		if ( ! is_array( $groups ) )
			$groups = (array) $groups;

		$this->global_groups = array_merge( $this->global_groups, $groups );
		$this->global_groups = array_unique( $this->global_groups );
	}

	function add_non_persistent_groups( $groups ) {
		if ( ! is_array( $groups ) )
			$groups = (array) $groups;

		$this->no_mc_groups = array_merge( $this->no_mc_groups, $groups );
		$this->no_mc_groups = array_unique( $this->no_mc_groups );
	}

	function incr( $id, $n = 1, $group = 'default' ) {
		if ( !$this->is_cache_enabled($group)) {
			return false;
		}

		$key = $this->key( $id, $group );
		$mc =& $this->get_mc( $group );
		$this->cache[ $key ] = $mc->increment( $key, $n );
		return $this->cache[ $key ];
	}

	function decr( $id, $n = 1, $group = 'default' ) {
		$key = $this->key( $id, $group );
		$mc =& $this->get_mc( $group );
		$this->cache[ $key ] = $mc->decrement( $key, $n );
		return $this->cache[ $key ];
	}

	function close() {
		// Silence is Golden.
	}

	function delete( $id, $group = 'default' ) {
		$key = $this->key( $id, $group );

		if ( in_array( $group, $this->no_mc_groups ) ) {
			unset( $this->cache[$key] );
			return true;
		}

		$mc =& $this->get_mc( $group );

		$result = $mc->delete( $key );

		@ ++$this->stats['delete'];
		$this->group_ops[$group][] = "delete $id";

		if ( false !== $result )
			unset( $this->cache[$key] );

		return $result;
	}

	function flush() {
		// Don't flush if multi-blog. -bwd probably ok because of generations
		//if ( function_exists( 'is_site_admin' ) || defined( 'CUSTOM_USER_TABLE' ) && defined( 'CUSTOM_USER_META_TABLE' ) )
		//	return true;
		$ret = true;
		foreach ( $this->mc as $bucket => $mc ) {
			if ($this->reset_generation($bucket) === false) {
				syslog(LOG_WARNING, "[WPE] Memcache generation reset failed for $bucket. Performing full flush.");
				$mc->flush();
				$ret &= $this->reset_generation($bucket);
			}
		}
		return $ret;
	}

	function get( $id, $group = 'default' ) {
		if ( !$this->is_cache_enabled($group) ) {
			return false;
		}
		$key = $this->key( $id, $group );
		$mc =& $this->get_mc( $group );

		if ( isset( $this->cache[$key] ) ) {
			if ( is_object( $this->cache[$key] ) ) {
				$value = clone $this->cache[$key];
			} else {
				$value = $this->cache[$key];
			}
		} else if ( in_array( $group, $this->no_mc_groups ) ) {
			$this->cache[$key] = $value = false;
		} else {
			$value = $mc->get( $key );
			if ( empty( $value ) || ( is_integer( $value ) && -1 == $value ) ) {
				$value = false;
			}
			$this->cache[$key] = $value;
		}

		@ ++$this->stats['get'];
		$this->group_ops[$group][] = "get $id";

		if ( 'checkthedatabaseplease' === $value ) {
			unset( $this->cache[$key] );
			$value = false;
		}

		return $value;
	}

	function get_multi( $keys, $group = 'default' ) {
		$return = array();
		$gets = array();
		foreach ( $keys as $i => $values ) {
			$mc =& $this->get_mc( $group );
			$values = (array) $values;
			if ( empty( $values[1] ) )
				$values[1] = 'default';

			list( $id, $group ) = (array) $values;
			if ( !$this->is_cache_enabled($group) ) {
				continue;
			}
			$key = $this->key( $id, $group );

			if ( isset( $this->cache[$key] ) ) {

				if ( is_object( $this->cache[$key] ) )
					$return[$key] = clone $this->cache[$key];
				else
					$return[$key] = $this->cache[$key];

			} else if ( in_array( $group, $this->no_mc_groups ) ) {
				$return[$key] = false;

			} else {
				$gets[$key] = $key;
			}
		}

		if ( !empty( $gets ) ) {
			$results = $mc->getMulti( $gets, $null, Memcached::GET_PRESERVE_ORDER );
			$joined = array_combine( array_keys( $gets ), array_values( $results ) );
			$return = array_merge( $return, $joined );
		}

		@ ++$this->stats['get_multi'];
		$this->group_ops[$group][] = "get_multi $id";
		$this->cache = array_merge( $this->cache, $return );
		return array_values( $return );
	}

	function key( $key, $group ) {
		if ( empty( $group ) )
			$group = 'default';
		$prefix = $this->prefix_for_group($group);

		$bucket = $this->bucket_for_group($group);
		$generation = $this->get_generation($bucket);
		$key =  preg_replace( '/\s+/', '', "v2:" . WP_CACHE_KEY_SALT . "$prefix$group:$key:$generation");
		return $this->mcrouter_prefix . $key;
	}

	function replace( $id, $data, $group = 'default', $expire = 0 ) {
		if (! $this->is_cache_enabled($group)) {
			return false;
		}

		$key = $this->key( $id, $group );
		$expire = $this->convert_expire_time($expire);
		$mc =& $this->get_mc( $group );

		if ( is_object( $data ) )
			$data = clone $data;

		$result = $mc->replace( $key, $data, $expire );
		if ( false !== $result )
			$this->cache[$key] = $data;
		return $result;
	}

	function set( $id, $data, $group = 'default', $expire = 0 ) {
		$key = $this->key( $id, $group );
		if ( isset( $this->cache[$key] ) && ( 'checkthedatabaseplease' === $this->cache[$key] ) )
			return false;

		if ( is_object( $data) )
			$data = clone $data;

		$this->cache[$key] = $data;

		if ( in_array( $group, $this->no_mc_groups ) )
			return true;

		$expire = $this->convert_expire_time($expire);
		$mc =& $this->get_mc( $group );
		$result = $mc->set( $key, $data, $expire );

		return $result;
	}

	function set_multi( $items, $expire = 0, $group = 'default' ) {
		$sets = array();
		$mc =& $this->get_mc( $group );
		$expire = $this->convert_expire_time($expire);

		foreach ( $items as $i => $item ) {
			if ( empty( $item[2] ) )
				$item[2] = 'default';

			list( $id, $data, $group ) = $item;

			$key = $this->key( $id, $group );
			if ( isset( $this->cache[$key] ) && ( 'checkthedatabaseplease' === $this->cache[$key] ) )
				continue;

			if ( is_object( $data) )
				$data = clone $data;

			$this->cache[$key] = $data;

			if ( in_array( $group, $this->no_mc_groups ) )
				continue;

			$sets[$key] = $data;
		}

		if ( !empty( $sets ) )
			$mc->setMulti( $sets, $expire );
	}

	function colorize_debug_line( $line ) {
		$colors = array(
			'get'   => 'green',
			'set'   => 'purple',
			'add'   => 'blue',
			'delete'=> 'red'
		);

		$cmd = substr( $line, 0, strpos( $line, ' ' ) );

		$cmd2 = "<span style='color:{$colors[$cmd]}'>$cmd</span>";

		return $cmd2 . substr( $line, strlen( $cmd ) ) . "\n";
	}

	function stats() {
		echo "<p>\n";
		foreach ( $this->stats as $stat => $n ) {
			echo "<strong>$stat</strong> $n";
			echo "<br/>\n";
		}
		echo "</p>\n";
		echo "<h3>Memcached:</h3>";
		foreach ( $this->group_ops as $group => $ops ) {
			if ( !isset( $_GET['debug_queries'] ) && 500 < count( $ops ) ) {
				$ops = array_slice( $ops, 0, 500 );
				echo "<big>Too many to show! <a href='" . add_query_arg( 'debug_queries', 'true' ) . "'>Show them anyway</a>.</big>\n";
			}
			echo "<h4>$group commands</h4>";
			echo "<pre>\n";
			$lines = array();
			foreach ( $ops as $op ) {
				$lines[] = $this->colorize_debug_line( $op );
			}
			print_r( $lines );
			echo "</pre>\n";
		}

		if ( !empty( $this->debug ) && $this->debug )
			var_dump( $this->memcache_debug );
	}

	function &get_mc( $group ) {
		if ( isset( $this->mc[$group] ) )
			return $this->mc[$group];
		return $this->mc['default'];
	}

	static public function get_site_cache_key($file_path){
		// this value is used for the object cache generation key
		// get the site name, if not at least get a unique value by creating a md5 sum from the path
		// therefore, this is necessary because either way we will always have a unique value for that site
		// this function is also in object-cache-new.php so update it there if you modify this function
		$patterns = array(
			"#^/nas/wp/www/(?:sites|staging|cluster-(?:[\d]+))/([^/]+)#",
			"#^/nas/content/(?:live|staging)/([^/]+)#"
		);
		foreach ($patterns as $site_pattern) {
			// Check for a matching install
			$result = preg_match($site_pattern, $file_path, $matches);
			if ($result && isset($matches[1])) {
				return $matches[1];
			}
		}
		return md5($file_path);
	}

	function set_cache_enabled($enabled) {
		// Can be used to turn off object caching for a particular request
		$this->cache_enabled = $enabled;
	}

	function __construct() {
		global $memcached_servers;
		global $mcrouter_server;

		// get appropriate bucket
		if ($mcrouter_server && array_key_exists('host', $mcrouter_server) && array_key_exists('port', $mcrouter_server)) {
			// use mcrouter host and port if the global mcrouter is set
			$buckets = array("{$mcrouter_server['host']}:{$mcrouter_server['port']}");
			$this->mcrouter_prefix = '/rep/all/';
		} else {
			if ($mcrouter_server) {
				// we should be using mcrouter, but we can't
				error_log('[wpengine] Pod set to use mcrouter but host and port names are not specified in site config.json. Falling back to no replication.');
			}

			if ( isset($memcached_servers) ) {
				$buckets = $memcached_servers;
			} else {
				$buckets = array('unix:///tmp/memcached.sock');
			}
		}

		reset( $buckets );
		if ( is_int( key( $buckets ) ) )
			$buckets = array( 'default' => $buckets );

		foreach ( $buckets as $bucket => $servers ) {
			$this->mc[$bucket] = new Memcached();

			$instances = array();
			foreach ( $servers as $server ) {
				if ( substr( $server, 0, 5 ) == "unix:" ) {
					$node = substr($server, 7);
					$port = 0;
				} else {
					@list( $node, $port ) = explode( ':', $server );
					if ( empty( $port ) )
						$port = ini_get( 'memcache.default_port' );
					$port = intval( $port );
					if ( !$port )
						$port = 11211;
				}
				$instances[] = array( $node, $port, 1 );
			}
			$this->mc[$bucket]->addServers( $instances );
		}

		global $blog_id, $table_prefix;
		$this->global_prefix = '';
		$this->blog_prefix = '';
		if ( function_exists( 'is_multisite' ) ) {
			$this->global_prefix = ( is_multisite() || defined( 'CUSTOM_USER_TABLE' ) && defined( 'CUSTOM_USER_META_TABLE' ) ) ? '' : $table_prefix;
			$this->blog_prefix = ( is_multisite() ? $blog_id : $table_prefix ) . ':';
		}

		// try to use the blog name but if we can't locate it, at least use something unique
		$customer = WP_Object_Cache::get_site_cache_key(__FILE__);
		$this->customer = $customer;

		// SO: blog prefix must come before any custom prefix
		$this->global_prefix = $this->global_prefix . ':' . $customer;
		$this->blog_prefix = $this->blog_prefix . ':' . $customer;

		$this->cache_hits =& $this->stats['get'];
		$this->cache_misses =& $this->stats['add'];
	}

	private function prefix_for_group($group) {
		if ( false !== array_search($group, $this->global_groups) )
			$prefix = $this->global_prefix;
		else
			$prefix = $this->blog_prefix;

		return $prefix;
	}

	private function generation_key() {
		if (! defined('WPE_OBJECT_CACHE_GENERATION_PREFIX')) {
			define('WPE_OBJECT_CACHE_GENERATION_PREFIX', 'wpe_generation:');
		}
		$key = WPE_OBJECT_CACHE_GENERATION_PREFIX . $this->customer;
		return $key;
	}

	private function reset_generation($bucket) {
		$this->mc[$bucket]->delete($this->generation_key());
		$this->generation[$bucket] = microtime() . rand(0, PHP_INT_MAX);
		return $this->mc[$bucket]->set($this->generation_key(), $this->generation[$bucket]);
	}

	private function get_generation($bucket) {
		if (isset($this->generation[$bucket])) {
			return $this->generation[$bucket];
		}

		// Attempt to load the generation from memcache. If it's not present, then the entire
		// cache for this blog has been invalidated, so reset to a new generation.
		$this->generation[$bucket] = $this->mc[$bucket]->get($this->generation_key());
		if ($this->generation[$bucket] === false) {
			$this->reset_generation($bucket);
		}
		return $this->generation[$bucket];
	}

	private function bucket_for_group($group) {
		if (isset($this->mc[$group])) {
			return $group;
		}
		return 'default';
	}

	private function convert_expire_time($expire) {
		$expire = ($expire == 0) ? $this->default_expiration : $expire;
		# Memcached treats expiration times over 30 days as Unix Time. Because of this, if
		# a user tries to set wp_cache to over 30 days, we need to convert it.
		if ( $expire > 30 * DAY_IN_SECONDS ) {
			$expire = time() + $expire;
		}
		return $expire;
	}
}
else: // No Memcached

	// In 3.7+, we can handle this smoothly
	if ( function_exists( 'wp_using_ext_object_cache' ) ) {
		wp_using_ext_object_cache( false );

	// In earlier versions, there isn't a clean bail-out method.
	} else {
		wp_die( 'Memcached class not available.' );
	}

endif;
