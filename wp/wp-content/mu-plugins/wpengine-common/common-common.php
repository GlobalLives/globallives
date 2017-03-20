<?php

if ( ! function_exists( 'is_wpe_snapshot' ) )
{
	// True if we're running inside a staging-area snapshot in WPEngines
	function is_wpe_snapshot()
	{
		return isset($_SERVER["IS_WPE_SNAPSHOT"]) ? $_SERVER["IS_WPE_SNAPSHOT"] : false;
	}
}

if ( ! function_exists( 'wpe_el' ) )
{

// True if we're running inside the WPEngines hosted environment
function is_wpe()
{
	return getenv('IS_WPE');
}

// Gets the site name if we're running in an WPE environment, e.g. "asmartbear" or "kip"
function wpe_site()
{
	return PWP_NAME;
}

// Return a key if it appears in an array, else a default value
function wpe_el( $array, $key, $default = FALSE )
{
	if ( ! array_key_exists( $key, $array ) ) return $default;
	return $array[$key];
}

function wpe_param( $key, $default = FALSE )
{
	return wpe_el( $_REQUEST, $key, $default );
}

// Format number of bytes for a human
function wpe_format_bytes($bytes, $precision = 1) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    $bytes /= pow(1024, $pow); 
   
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
}

// Make versions look the same with same number or parts: 3.5 becomes 3.5.0-0-0 to match 3.5-alpha-11111
function normalize_version($v)
{
    // Split any after dash
    $the_split = explode('-', $v);
    $front = explode('.',$the_split[0]);
    // First part has 3 parts
    // Back part has 2 parts
    $x = $front[0].".".$front[1].".". (@$front[2] ?: 0) .".". (@$the_split[1] ?: 0) .".". (@$the_split[2] ?: 0);
    return strtolower($x);
}

// compares production and staging versions to see if staging version is greater than or equal to production
function is_staging_gte($b, $a) // is a greater than b?
{
    if ( $a === $b ) {
        return true;
    }

    $a = normalize_version($a);
    $b = normalize_version($b);

    $split_a = explode('.', $a);
    $split_b = explode('.', $b);

    foreach ( $split_a as $i => $part_a ) {
        $cmp = strcmp($part_a, $split_b[$i]);
        if ( 0 === $cmp ) {
            continue;
        }
        // Weird thing to do to handle numeric (0,1) considered higher version than alpha, beta, rc.
        if ( is_numeric($part_a) && !is_numeric($split_b[$i]) ) {
            return true;
        }
        if ( !is_numeric($part_a) && is_numeric($split_b[$i]) ) {
            return false;
        }
        if ( 0 < $cmp ) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

// Capture plug-in logic in a class.
class WpePlugin_common
{
	protected $options;

	public function get_plugin_title()
	{
		return "WPEngine Plugin";
	}

	public function get_default_options()
	{
		return array (
		//	'snapshot_path' => dirname(__FILE__) . '/snapshots',
		);
	}

	public function get_plugin_token()
	{
		return preg_replace( "!\\W!", "_", $this->get_plugin_title() );
	}

	public function __construct()
	{
		$this->options = FALSE;

		// Hook Wordpress actions and call back object methods.
		// Subclasses can override methods instead of worrying about more Wordpress hooks.
		add_action('init', array($this, 'wp_hook_init'));
	}

	// If the given path is inside the Wordpress installation, returns the full URI to this file.
	// Otherwise returns FALSE.
	public function get_uri_to_local_file( $path )
	{
		$prefix_len = strlen(ABSPATH);
		if ( substr($path,0,$prefix_len) == ABSPATH )
		{
			$base = get_option('siteurl');
			$path = substr($path,$prefix_len);
			if ( substr($path,0,1) != '/' && substr($base,-1) != '/' )
				$base .= '/';
			return $base . $path;
		}
		return FALSE;
	}

	// Called by Wordpress at plugin-initialization time
	public function wp_hook_init()
	{
		if (is_admin())
		{
			$plugin_name = $this->get_plugin_token();

			# Moved to actual plugin
			add_action('admin_menu',array($this,'wp_hook_admin_menu'));
			add_filter("plugin_action_links_$plugin_name", array($this,'wp_hook_add_settings_link'));
		}
	}

	// Called by Wordpress to add our menu item to the WP admin screen - Extended in child class
	public function wp_hook_admin_menu()
	{
		add_options_page($this->get_plugin_title()." Options", $this->get_plugin_title(), 'manage_options', dirname(__FILE__).'/admin.php');
	}

	// Called by Wordpress to add links to the plugins admin page
	public function wp_hook_add_settings_link($links)
	{
		$settings = '<a href="options-general.php?page='.basename(dirname(__FILE__)).'/admin.php">Settings</a>'; 
		array_unshift($links,$settings);
		return $links;
	}

	// Load all plugin options (caches after first load)
	public function get_options()
	{
		// Return cached values if available
		if ( $this->options )
			return $this->options;

		// List of all options with their default values
		$option_defaults = $this->get_default_options();

		// Load options from database
		$this->options = array();
		foreach ( $option_defaults as $key => $default_value )
		{
			$this->options[$key] = get_option( $key, $default_value );
		}
		return $this->options;
	}

	// Gets an option by name (options are cached after first load, so this is efficient)
	public function get_option( $name )
	{
		$options = $this->get_options();
		return @$options[$name];
	}

	// Persists options to the database; returns TRUE if all options were written successfully.
	public function set_option( $name, $value )
	{
		// Make sure options are loaded and updated in memory.
		// If the option value isn't different, don't update Wordpress.
		$this->get_options();
		if ( isset( $this->options[ $name ] ) && $this->options[ $name ] == $value ) {
			return TRUE;		// no error
		}
		$this->options[ $name ] = $value;

		// Set the option value in Wordpress
		add_option( $name, $value, FALSE, 'no' );		// add option if not already exist
		update_option( $name, $value );					// update option value if does exist

		return TRUE;
	}

	// Overwrites all options to match the default ones, as when the plugin is first installed
	public function restore_default_options()
	{
		foreach ( $this->get_default_options() as $key => $value )
			$this->set_option( $key, $value );
	}

	// Validates that the given set of options is a valid configuration.
	// If validation fails, a human-readable error message is returned, otherwise FALSE.
	public function validate_options( $options )
	{
		return FALSE;
	}

	// Attempt to increase all the execution limits we can find.
	protected function increase_php_limits()
	{
                        // Don't abort script if the client connection is lost/closed
                        @ignore_user_abort( true );
                        
                        // 2 hour execution time limits
                        @ini_set( 'default_socket_timeout', 60 * 60 * 2 );
                        @set_time_limit( 60 * 60 * 2 );
                        
                        // Increase the memory limit
                        $current_memory_limit = trim( @ini_get( 'memory_limit' ) );
                        
                        if ( preg_match( '/(\d+)(\w*)/', $current_memory_limit, $matches ) ) {
                                $current_memory_limit = $matches[1];
                                $unit = $matches[2];
                                
                                // Up memory limit if currently lower than 256M
                                if ( 'g' !== strtolower( $unit ) ) {
                                        if ( ( $current_memory_limit < 256 ) || ( 'm' !== strtolower( $unit ) ) )
                                                @ini_set('memory_limit', '256M');
                                }
                        }
                        else {
                                // Couldn't determine current limit, set to 256M to be safe
                                @ini_set('memory_limit', '256M');
                        }
	}
	
	function user_has_access( $user_id, $roles ) {
		$has_access = false;
		$i = 0;
		while( $has_access === false AND $i < count($roles) ) {
			$has_access = user_can($user_id, $roles[$i]);
			$i++;
		}
		return $has_access;
	}
}
