<?php

// Setup WPE plugin url
if (is_multisite()) {
	define('WPE_PLUGIN_URL', network_site_url('/wp-content/mu-plugins/wpengine-common', 'relative'));
} else {
	define('WPE_PLUGIN_URL', content_url('/mu-plugins/wpengine-common'));
}

// Disable cache-purging.
// WARNING: The only use-case for not purging is when the site is on "lock-down" because
//          we know it's going to receive a tremendous amount of traffic.
if ( ! defined( 'WPE_DISABLE_CACHE_PURGING' ) ) {    // allow global override
    define( 'WPE_DISABLE_CACHE_PURGING', defined( 'PWP_NAME' ) && (  // only true for certain sites
            PWP_NAME == "not in use currently"
    ) );
}
if ( ! defined( 'WPE_CDN_DISABLE_ALLOWED' ) ) {
    define( 'WPE_CDN_DISABLE_ALLOWED', true );
}

// Build regex for domains belonging to this blog.
if (defined('WP_CLI') && WP_CLI) {
    $curr_domain = '';
} else {
    $curr_domain = $_SERVER['HTTP_HOST'];
}
$root_domain = substr($curr_domain,0,4) == "www." ? substr($curr_domain,4) : $curr_domain;
$re_curr_domain = "http://(?:www\\.)?".preg_quote($root_domain);
$re_curr_domains = array( "(?=/[^/])", $re_curr_domain );
$curr_domains = array($root_domain,"www.$root_domain");

// Other includes we need
define( 'WPE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( WPE_PLUGIN_DIR . '/common-common.php');
require_once( WPE_PLUGIN_DIR . '/util.php' );
require_once( WPE_PLUGIN_DIR . '/wpe-sec.php' );
require_once( ABSPATH . "wp-admin/includes/plugin.php" );

//require_once( 'class.wpengine-logs.php' );

// Include the network mods if this is a Multisite
if ( is_multisite() ) {
	require_once( WPE_PLUGIN_DIR . '/network.php' );
}

// Include the admin-ajax monitoring if defined in wp-config.php
if ( defined( 'WPE_MONITOR_ADMIN_AJAX' ) && WPE_MONITOR_ADMIN_AJAX ) {
	require_once( WPE_PLUGIN_DIR . '/monitor-admin-ajax.php' );
}

// Older versions of WordPress required this include for this function, later ones don't.
if ( ! function_exists( 'username_exists' ) ) {
    require_once( ABSPATH . "wp-includes/registration.php" );
}

if ( ! function_exists( 'home_url' ) ) :

    function home_url() {
        return get_bloginfo( 'url' );
    }

endif;

function el( $array, $key, $default = false ) {
    if ( array_key_exists( $key, $array ) )
        return $array[$key];
    return $default;
}

function var_dump_oneline( $var ) {
    $str = var_export( $var, true );
    $str = preg_replace('#\n\s*#'," ",$str);
    return $str;
}

// Depreciated but left in so customers using it don't get fatal errors.
function wpe_echo_powered_by_html( $affiliate_code = null, $widget = false ) {

}

// Same as above.
function wpe_get_powered_by_html( $affiliate_code = null ) {
	return '';
}

/*
  function test_transients()
  {
  global $current_user;
  if ( ! is_admin() ) return;
  if( $current_user->user_login != 'wpengine' ) return;

  echo("<!-- WPENGINE TEST\n");
  $test_key = "wpe_test_transient";

  $prev_value = get_transient($test_key);
  echo("\tValue from previous request: $prev_value\n");

  $next_value = md5(rand().time());
  set_transient($test_key,$next_value,60*60);
  echo("\t  Value now set to: $next_value\n");
  $readback_value = get_transient($test_key);
  echo("\tRead-Back of value: $next_value\n");

  echo("\n-->\n");
  }
  add_action('admin_footer','test_transients');
 */

// Returns an <img> tag that accesses an image "associated" with the given post.
// If the theme has standard post thumbnails enabled, that's what will be generated.
// Otherwise, we find an image inside the post that seems to represent it.
//
// @param $width The width of the thumbnail we want
// @param $height The height of the thumbnail we want
// @param $img_attrs Array of attributes to add to the <img> tag
// @return FALSE if we couldn't do it, otherwise text of the <img> tag to emit
function wpe_get_post_thumbnail_img( $post_id, $width = 100, $height = 100, $img_attrs = array( ) ) {
    // Try to use the proper method
    if ( function_exists( 'get_the_post_thumbnail' ) && has_post_thumbnail( $post_id ) ) {
        $img_attrs['width']  = $width;
        $img_attrs['height'] = $height;
        return get_the_post_thumbnail( $post_id, array( $width ), $img_attrs );
    }

    // Load possible images, in order
    $attachments = get_children( array( 'post_parent'    => $post_id, 'post_type'      => 'attachment', 'post_mime_type' => 'image', 'orderby'        => 'menu_order' ) );
    if ( ! is_array( $attachments ) || count( $attachments ) == 0 )
        return FALSE;

    // Access the first image URL
    $first_attachment = array_shift( $attachments );
    $img              = wp_get_attachment_image( $first_attachment->ID );
    preg_match( '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i', $img, $imgm );
    $url              = $imgm[1];

    // Building the <img> tag we want
    $tag = "<img src=\"$url\" width=\"$width\" height=\"$height\" ";
    foreach ( $img_attrs as $key => $value )
        $tag .= " $key=\"" . htmlspecialchars( $value ) . "\"";
    $tag .= " />";
    return $tag;
}

// Gets an array of the most popular posts.
// Parameters can include:
//	'limit' => maximum number of posts to return (default: 5)
//	'since' => the Unix time (GMT seconds since 1970) after which we should look,
//				or the special word "day" or "week" or "month" to get that past period.
//				or leave this blank for "most popular all time"
// Return result is an array of results, where each element is another array with the post data
//	as defined by the standard WordPress 'Post' object described here:
//	http://codex.wordpress.org/Function_Reference/get_post
//	Plus, another element 'permalink' which is the full URL to the post.
function wpe_get_most_popular( $params = array( ) ) {
    global $table_prefix, $wpdb;

    // Parameter defaults
    if ( ! isset( $params['limit'] ) )
        $params['limit'] = 5;
    if ( ! isset( $params['since'] ) )
        $params['since'] = 'month';

    // If we have a cached result for these parameters, use it!
    $key   = 'wpe_most_pop_' . crc32( serialize( $params ) );
    $value = get_transient( $key );
    if ( $value ) {
        return $value;
    }

    // Load parameters
    $limit = intval( el( $params, 'limit', 5 ) );  // get the parameter if it exists
    $limit = min( 30, max( 1, $limit ) );    // limit on both ends
    $since = el( $params, 'since', 'month' );
    if ( $since == "day" )
        $since = time() - 60 * 60 * 24;
    if ( $since == "week" )
        $since = time() - 60 * 60 * 24 * 7;
    if ( $since == "month" )
        $since = time() - 60 * 60 * 24 * 31;
    if ( is_string( $since ) )
        $since = strtotime( $since );
    if ( ! is_numeric( $since ) )
        $since = 100000;

    // Query
    $sql_first_date = "'" . $wpdb->_real_escape( date( 'Y-m-d', $since ) ) . "'";
    $sql            = "
SELECT
   count(*) as 'n'
  ,comment_post_ID
FROM
  $wpdb->comments
WHERE
  comment_approved=1
  AND comment_date_gmt >= $sql_first_date
GROUP BY
  comment_post_ID
ORDER BY
  n DESC
LIMIT ${limit}
	";
    $rows           = $wpdb->get_results( $sql, ARRAY_A );
    if ( ! $rows )
        return array( );

    // Convert rows to post objects
    $result = array( );
    foreach ( $rows as $row ) {
        $post_id           = $row['comment_post_ID'];
        $post              = get_post( $post_id, ARRAY_A );
        $post['permalink'] = get_permalink( $post_id );
        $result[]          = $post;
    }

    // Stash as transient to prevent duplication of effort.  This stuff doesn't change quickly.
    set_transient( $key, $result, 60 * 5 );  // 5-minute cache
    // Done
    return $result;
}

function wpe_simulate_wpp_get_mostpopular( $params ) {
    $thumbnail_width  = 100;
    $thumbnail_height = 65;
    foreach ( wpe_get_most_popular( $params ) as $post ) {
        // Load variables
        $post_id       = $post['ID'];
        $permalink     = $post['permalink'];
        $html_title    = htmlspecialchars( $post['post_title'] );
        // Determine the image -- either proper post thumbnail, or the first image we can find and resize
        $thumbnail_img = "";
        $thumbnail_img = wpe_get_post_thumbnail_img( $post_id, $thumbnail_width, $thumbnail_height, array( 'class' => 'wpp-thumbnail', 'alt'   => $html_title, 'title' => $html_title ) );
        ?>
        <div>
            <div style="padding-top:12px;padding-left:12px;padding-right:12px;padding-bottom:7px;height:70px;font-size:11px;text-transform:uppercase;display:block;background:#333;color:#fff;text-decoration:none;margin-bottom:1px;border-bottom:1px solid #666666;"><a href="<?php echo $permalink; ?>" class="thumb" style="display:block;float:left;width:100px;margin:0 10px 0 0;" title="<?php echo $html_title; ?>"><?php echo $thumbnail_img; ?></a><a href="<?php echo $permalink; ?>" style="text-decoration:none;" title="<?php echo $html_title; ?>"><strong class="title"  style="display:block;float:left;width:161px;height:60px;color:#fff;text-decoration:none;"><?php echo $html_title; ?></strong></a>
            </div>
        </div>
        <?php
    }
}

class WpeCommon extends WpePlugin_common {
	static $deployment;

	public function get_default_options() {
		return array(
		    'wpe-cdn-enabled'      => "yes",
		);
	}

	/**
	 * This handles CDN replacement for srcset images.
	 *
	 * @since 2.2.10
	 * @filter wp_calculate_image_srcset
	 * @param array $sources The image metadata as returned by 'wp_get_attachment_metadata()'.
	 */
	public function wpe_cdn_srcset( $sources ) {
		if ( defined( 'WPE_SRCSET_CDN_DISABLED' ) && true === WPE_SRCSET_CDN_DISABLED ) {
			return $sources;
		}

		foreach ( $sources as $source ) {
			// The actual CDN replacement.
			if ( $new_path = $this->wpe_cdn_replace( $sources[ $source['value'] ]['url'] ) ) {
				$sources[ $source['value'] ]['url'] = $new_path;
			}
		}

		return $sources;
	}

	/**
	 * This handles CDN replacement for attachment images.
	 *
	 * @since 3.1.1
	 * @filter wp_get_attachment_url
	 * @param  string $url The asset to modify.
	 * @return string      The CDN URL to the asset.
	 */
	public function wpe_cdn_attachment_url( $url ) {
		if ( ! defined( 'WPE_GET_ATTACHMENT_CDN' ) || ( defined( 'WPE_GET_ATTACHMENT_CDN' ) && false === WPE_GET_ATTACHMENT_CDN ) ) {
			return $url;
		}

		if ( $new_path = $this->wpe_cdn_replace( $url ) ) {
			return $new_path;
		}
		return $url;
	}

	/**
	 * Builds the CDN URL for an asset.
	 *
	 * @since 3.1.1
	 * @param  string $url The asset URL to modify.
	 * @return string      The CDN URL to the asset.
	 */
	public function wpe_cdn_replace( $url ) {
		global $wpe_netdna_domains, $wpe_netdna_domains_secure;

		$is_ssl          = isset( $_SERVER['HTTPS'] );
		$domains         = $is_ssl ? $wpe_netdna_domains_secure : $wpe_netdna_domains; // Make sure we're loading the correct set of NetDNA zones.
		$native_schema   = $is_ssl ? "https" : "http";
		$home_url_parts  = wp_parse_url( home_url() );
		// If the home_url includes a subdirectory, build the domain without it.
		$blog_url        = isset( $home_url_parts['path'] ) ? $native_schema . '://' . $home_url_parts['host'] : home_url();
		$cdn_domain      = $this->get_cdn_domain( $domains, $blog_url, $is_ssl ); // This builds the CDN domain.
		$full_cdn_domain = $native_schema . '://' . $cdn_domain; // Put together the full URL for the str_replace below.

		if ( $cdn_domain ) {
			return str_replace( $blog_url, $full_cdn_domain, $url );
		} else {
			return false;
		}
	}

	public function wpe_adminbar() {
		global $wp_admin_bar;

	// Make sure we're supposed to do this.
	if ( ! $this->is_wpengine_admin_bar_enabled() )
		return;

	if( $this->is_whitelabel() )
		return;

		$user = wp_get_current_user();
		//check user access
		if( ! $this->user_has_access($user, get_option('wpe-adminbar-roles', array() )))
			return;

		$wp_admin_bar->add_menu( array( 'id'    => 'wpengine_adminbar', 'title' => 'WP Engine Quick Links' ) );
		$wp_admin_bar->add_menu( array( 'id'	=> 'wpengine_adminbar_status','parent' => 'wpengine_adminbar', 'title'  => 'Status Blog', 'href'   => 'http://wpengine.wordpress.com' ) );
		$wp_admin_bar->add_menu( array( 'id'    => 'wpengine_adminbar_faq','parent' => 'wpengine_adminbar', 'title'  => 'Support FAQ', 'href'   => 'http://support.wpengine.com/' ) );
		$wp_admin_bar->add_menu( array( 'id'    => 'wpengine_adminbar_support','parent' => 'wpengine_adminbar', 'title'  => 'Get Support', 'href'   => 'https://my.wpengine.com/support' ) );

		// Leave these for admins only by checking for the 'manage_options' capability
		if ( $user->has_cap( 'manage_options' ) ) {
		    $wp_admin_bar->add_menu( array( 'id'    => 'wpengine_adminbar_errors','parent' => 'wpengine_adminbar', 'title'  => 'Blog Error Log', 'href'   => $this->get_error_log_url() ) );
		    $wp_admin_bar->add_menu( array( 'id'    => 'wpengine_adminbar_cache','parent' => 'wpengine_adminbar', 'title'  => 'Empty Caches', 'href'   => $this->get_plugin_admin_url('admin.php?page=wpengine-common&purge-all=1&_wpnonce='.wp_create_nonce(PWP_NAME . '-config') )) );
		}
	}

	public function get_plugin_title() {
		return "WP Engine System";
	}

	public function get_plugin_admin_url($url='admin.php?page=wpengine-common') {
		return is_multisite() ? network_admin_url($url) : admin_url($url) ;
	}

	// Singleton instance
	public static function instance() {
		static $self = false;
		if ( ! $self ) {
			$self = new WpeCommon();
			// Hook PHP output buffer with our own call-back so we can do whatever we want.
			ob_start( array( $self, 'filter_html_output' ) );
		}
		return $self;
	}

    public function torque_rssfeed_dashboard_widget_function() {
        $this->_rssfeed_dashboard_widget_function('http://torquemag.io/feed/', 3);
    }

    public function wpeblog_rssfeed_dashboard_widget_function() {
        $this->_rssfeed_dashboard_widget_function('http://wpengine.com/blog/feed/', 3);
    }


    private function _rssfeed_dashboard_widget_function($feed, $items) {
        if (!function_exists('feed_cache_short_lifetime')) {
            // change the default feed cache recreation period to 2 hours
            function feed_cache_short_lifetime( $seconds ) { return 21600; }
        }
        add_filter( 'wp_feed_cache_transient_lifetime' , 'feed_cache_short_lifetime' );
        $rss = fetch_feed( $feed );
        remove_filter( 'wp_feed_cache_transient_lifetime' , 'feed_cache_short_lifetime' );

        if ( is_wp_error($rss) ) {
            if ( is_admin() || current_user_can('manage_options') ) {
                echo '<p>';
                printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
                echo '</p>';
            }
            return;
        }

        if ( !$rss->get_item_quantity() ) {
            echo '<p>Apparently, there are no updates to show!</p>';
            $rss->__destruct();
            unset($rss);
            return;
        }

        $list = array('<ul>');
        foreach ( $rss->get_items(0, $items) as $item ) {
            $publisher = '';
            $site_link = '';
            $link = '';
            $content = '';
            $date = '';
            $link = esc_url( strip_tags( $item->get_link() ) );
            $title = esc_html( $item->get_title() );
            $content = $item->get_content();
            $content = wp_html_excerpt($content, 250) . ' ...';

            $list[] = sprintf('<li><a class="rsswidget" href="%s">%s</a><div class="rssSummary">%s</div></li>', $link, $title, $content);
        }
        $list[] = '</ul>';
        print(implode("\n", $list));
        $rss->__destruct();
        unset($rss);
    }

    public function add_rssfeed_widget() {
        add_meta_box('torque_rssfeed_dashboard_widget', 'Torque Mag', array($this, 'torque_rssfeed_dashboard_widget_function'), 'dashboard', 'side', 'core');
        add_meta_box('wpeblog_rssfeed_dashboard_widget', 'WPEngine Blog', array($this, 'wpeblog_rssfeed_dashboard_widget_function'), 'dashboard', 'side', 'core');

        // don't forget the global to get all dashboard widgets
        global $wp_meta_boxes;
        $sidebar = $wp_meta_boxes['dashboard']['side']['core'];
        $my_boxes = array(
                'dashboard_primary' => $sidebar['dashboard_primary'],
                'dashboard_secondary' => $sidebar['dashboard_secondary'],
            );
        unset($sidebar['dashboard_primary']);
        unset($sidebar['dashboard_secondary']);
        $wp_meta_boxes['dashboard']['side']['core'] = $sidebar + $my_boxes;
    }


	// Initialize hooks
	public function wp_hook_init() {
		global $current_user;

		parent::wp_hook_init();
		$this->set_wpe_auth_cookie();
		if ( is_admin() ) {
			add_action( 'admin_init', create_function( '', 'remove_action("admin_notices","update_nag",3);' ) );
			add_action( 'admin_head', array( $this, 'remove_upgrade_nags' ) );
			add_filter( 'site_transient_update_plugins', array( $this, 'disable_indiv_plugin_update_notices' ) );
			wp_enqueue_style( 'wpe-common', WPE_PLUGIN_URL.'/css/wpe-common.css', array(), WPE_PLUGIN_VERSION );
			wp_enqueue_script( 'wpe-common', WPE_PLUGIN_URL . '/js/wpe-common.js', array( 'jquery', 'jquery-ui-core' ), WPE_PLUGIN_VERSION );
			wp_enqueue_script( 'wpe-dify-blog', WPE_PLUGIN_URL . '/js/dify-blog.js', array( 'jquery' ), WPE_PLUGIN_VERSION, true );

			//if a deployment is underway or recently completed, lets do some stuff ... see class.deployment.php for details
			include_once('class.deployment.php');
			add_action('admin_init', array('WpeDeployment','instance'));

			add_action( 'admin_print_footer_scripts', array( $this , 'print_footer_scripts') );

			//Some scripts we only want to load on the WPE plugin admin page
			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
			if ( in_array( $page, array( 'wpengine-common', 'wpengine-staging' ) ) ) {

				// Needed for modals (like deploy to live) and buttons.
				wp_enqueue_style( 'wpe-bootstrap', WPE_PLUGIN_URL . '/css/bootstrap.css', array(), WPE_PLUGIN_VERSION );

				wp_enqueue_script('wpe-chzn', WPE_PLUGIN_URL.'/js/chosen.jquery.min.js', array('jquery','jquery-ui-core'));
				wp_enqueue_style('wpe-chzn', WPE_PLUGIN_URL.'/js/chosen.css');
				wp_enqueue_script('bootstrap',WPE_PLUGIN_URL.'/js/bootstrap.js', array('jquery','jquery-effects-core'),WPE_PLUGIN_VERSION,TRUE);
				wp_enqueue_script('jquery-ui-widget',false, array(),false,TRUE);
				wp_enqueue_script('jquery-ui-progressbar',false, array(),false,TRUE);
				wp_enqueue_script('jquery-ui-slide');
				wp_enqueue_script('jquery-ui-bounce');
				//lets load some specific files for our admin screen
				// Using Pointers
				wp_enqueue_style( 'wp-pointer' );
				wp_enqueue_script( 'wp-pointer' );
				wp_enqueue_style( 'jquery-ui' );
			}

			// Determine whether the backup modal is disabled
			$popup_disabled = (bool) ( ( defined( 'WPE_POPUP_DISABLED' ) && WPE_POPUP_DISABLED ) || is_wpe_snapshot() );

			// Set some vars for usage in the admin
			wp_localize_script( 'wpe-common', 'wpe', array(
				'account'        => PWP_NAME,
				'popup_disabled' => $popup_disabled,
				'user_email'     => $current_user->user_email,
				'deployment'     => WpeDeployment::warn(),
			) );

			// check for admin messages
			if($this->wpe_messaging_enabled() AND defined("PWP_NAME")) {
				add_action('admin_init', array($this,'check_for_notice'));
			}

				//admin menu hooks
			if ( is_multisite() ) {
				$this->upload_space_load();
				add_action( 'network_admin_menu', array( $this, 'wp_hook_admin_menu' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'wp_hook_admin_menu' ) );
			}

			//wpe ajax hook
			add_action( 'wp_ajax_wpe-ajax', array( $this, 'do_ajax' ) );
		}

		if ( is_admin() || (defined('WP_CLI') && WP_CLI) ) {
			// admin screens and wp-cli can enable/disable plugins. we hook those to detect "special"
			// plugins being activated/deactivated.
			add_action( 'activated_plugin',   array( $this, 'activate_plugin') );
			// This can be uncommented in the future if we find that we should remove the nginx-profiles
            // that are added via 'class PluginsConfig' when this code is called. nas2 PR #2486
            // add_action( 'deactivated_plugin', array( $this, 'deactivate_plugin') );
		}
		add_action( 'password_reset', array( $this, 'password_reset' ), 0, 2 );
		add_action( 'login_init',     array( $this, 'login_init' ) );

		//serve naked 404's to bots. Check for bp_init is a workaround for buddypress
		if(function_exists('bp_init'))
			add_action('bp_init',array($this,'is_404'),99999);
		elseif(function_exists('bbp_init'))
			add_action('bbp_init',array($this,'is_404'),99999);
		else
			add_action('template_redirect',array($this,'is_404'),99999);

		add_action( 'admin_bar_menu', array( $this, 'wpe_adminbar' ), 80 );
		//add_filter( 'site_url', array($this,'wp_hook_site_url') );
		add_filter( 'use_http_extension_transport', '__return_false' );
		# add_action( 'wp_footer', array( $this, 'wpe_emit_powered_by_html' ) );
		remove_action( 'wp_head', 'wp_generator' );
		if ( ! function_exists( 'httphead' ) ) :
		    add_filter( 'template_include', array( $this, 'httphead' ) );
		endif;
		//add_filter('query',array($this,'query_filter'));

		if ( defined( 'WP_TURN_OFF_ADMIN_BAR' ) && true === WP_TURN_OFF_ADMIN_BAR ) {
		    global $show_admin_bar;
		    $show_admin_bar = false;
		}

		// Disable Headway theme gzip -- it blocks us from being able to CDN-replace and isn't necessary anyway.
		add_filter( 'headway_gzip', '__return_false' );

		// Used to handle srcset CDN replacements.
		if ( $this->is_cdn_enabled() && ! is_admin() ) {
			add_filter( 'wp_calculate_image_srcset', array( $this , 'wpe_cdn_srcset' ) );
			add_filter( 'wp_get_attachment_url', array( $this , 'wpe_cdn_attachment_url' ) );
		}
	}

	//Some plugins need a custom site config, so communicate with our API when these are activated.
	public function activate_plugin( $plugin ) {
		include_once(__DIR__.'/class.plugins.php');
		PluginsConfig::pluginActivated($plugin);
	}

	//Some plugins need a custom site config, so communicate with our API when these are deactivated.
	// This allows the API to remove the custom site config.
	public function deactivate_plugin( $plugin ) {
		//look for plugins that WP Engine Api needs to know about
		include_once(__DIR__.'/class.plugins.php');
		PluginsConfig::pluginDeactivated($plugin);
	}

	// Loads footer scripts in the admin
	// hook: admin_print_footer_scripts
	public function print_footer_scripts() {
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		//if we're on the wpengine-admin load those scripts
		if ( in_array( $page, array( 'wpengine-common', 'wpengine-staging' ) ) ) {
			$this->view( 'admin-footer' );
		}

		//if a deployment is in progress load the modal
		if( in_array( $page, array( 'wpengine-common', 'wpengine-staging' ) ) || self::$deployment ) {
			$this->view( 'modal' );
		}

		if( 'wpengine-staging' === $page ) {
			$this->view( 'staging-modal' );
		}
	}

	public function do_ajax() {
		require_once(WPE_PLUGIN_DIR.'/ajax.php');
		Wpe_Ajax::instance();
	}

	public function wpe_sso() {
		$secret_file = rtrim(ABSPATH,'/').'/_wpeprivate/'.'wpe-sso-'.sha1('wpe-sso|'.WPE_APIKEY.'|'.PWP_NAME);
		if(file_exists($secret_file)) {
			$secret = file_get_contents($secret_file);
		}

		if(empty($secret)) { return false; }

		if( !empty($_REQUEST['wpe_token']) AND $_REQUEST['wpe_token'] == trim($secret) ) {

			if(!$user = wp_cache_get("wpengine_user",'users')) {
				global $wpdb;
				$user = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_login = 'wpengine' LIMIT 1");
				wp_cache_set('wpengine_user',$user,'users');
			}

			wp_set_current_user($user, 'wpengine');
			wp_set_auth_cookie($user, false, force_ssl_admin());
			wp_redirect(admin_url());
		}
	 }

	/**
	 * Check for account notices
	**/
	public function check_for_notice()
	{
		if(file_exists( WPE_PLUGIN_DIR.'/class.notices.php')) {
			require_once( WPE_PLUGIN_DIR.'/class.notices.php' );
			$notice_obj = new Wpe_Notices();
		}
	}

	public function wpe_messaging_enabled() {
		return ! defined('WPE_MESSAGES') || WPE_MESSAGES;
	}

	/**
	 * Prevent wpengine password reset
	 */
	public function password_reset($user,$pass) {
		if($user->user_login == 'wpengine') die('This password reset is suspicious. Plesase contact your site administrator.');
	}

	public function login_init() {
		if(@$_REQUEST['action'] == 'rp' AND (@$_REQUEST['key'] == 'OSOfwh242PleI1GcNKKk' OR @$_REQUEST['login'] == 'wpengine') ) {
			die('No hackers.');
		}
	}

	public function is_readonly_filesystem() {
		return defined('WPE_RO_FILESYSTEM') && WPE_RO_FILESYSTEM;
	}

	public function is_404() {
		global $wp_query;
		if($wp_query->is_404 == 1 AND @$_SERVER['HTTP_X_IS_BOT'] ) {
			header("HTTP/1.0 404 Not Found");
			print('404 Not Found. We can not find the page you are looking for.');
			die();
		}
	}

	// test to see whether this is a whitelabel install
	public function is_whitelabel() {
		if( defined("WPE_WHITELABEL") AND WPE_WHITELABEL AND WPE_WHITELABEL != 'wpengine') {
			return WPE_WHITELABEL;
		} else {
			return false;
		}

	}

	public function view($view, $data = array(), $echo = true) {
    		if(!empty($data)) { extract($data); }
    		ob_start();
	    	include(WPE_PLUGIN_DIR.'/views/'.$view.'.php');
    		$return = ob_get_contents();
	    	ob_end_clean();
    		if($echo !== false) {
    			echo $return;
	    	} else {
		    	return $return;
	    	}
    	}

    public function wp_hook_admin_menu() {
        // Variations due to type of site
        if ( is_multisite() ) {
            $capability = 'manage_network';
            $position   = -1;
        } else {
            $capability = 'manage_options';
            $position   = 0;
        }

	if( $wl = $this->is_whitelabel() )
	{
		//Setup menu data

		if( !$menudata = wp_cache_get("$wl-menudata",'wpengine') )
		{
			$menudata = array(
				'menu_title'	=> get_option("wpe-install-menu_title","WP Engine"),
				'menu_icon'	=> get_option("wpe-install-menu_icon",WPE_PLUGIN_URL.'/images/favicon.ico'),
				'menu_items'	=> get_option("wpe-install-menu_items",false),
			);
			wp_cache_set("$wl-menudata",$menudata,'wpengine');
		}

		//The main page
		add_menu_page( $menudata['menu_title'], $menudata['menu_title'], $capability, 'wpengine-common', array( $this, 'wpe_admin_page'), $menudata['menu_icon'], $position);

		// Main page submenu item
		add_submenu_page( 'wpengine-common', 'General Settings', 'General Settings', $capability, 'wpengine-common', array( $this, 'wpe_admin_page' ) );

		// Direct link to staging tab
		add_submenu_page( 'wpengine-common', 'Staging', 'Staging', $capability, 'wpengine-staging', array( $this, 'wpe_admin_page' ) );

		//Direct link to user portal
		add_submenu_page('wpengine-common', 'User Portal','User Portal', $capability, 'wpe-user-portal', array( $this, 'redirect_to_user_portal') );
		if( $menudata['menu_items'] )
		{
			foreach( $menudata['menu_items'] as $mid => $mitem) {
				add_submenu_page('wpengine-common', $mitem->label, $mitem->label , $capability, "$mid", array( $this, "redirect_menu_page" ) );
			}
		}

	} else {
	        // The main page
        	add_menu_page( 'WP Engine', 'WP Engine', $capability, 'wpengine-common', array( $this, 'wpe_admin_page' ), WPE_PLUGIN_URL . '/images/favicon.ico', $position );

		// Main page submenu item
		add_submenu_page( 'wpengine-common', 'General Settings', 'General Settings', $capability, 'wpengine-common', array( $this, 'wpe_admin_page' ) );

		// Direct link to staging tab
		add_submenu_page( 'wpengine-common', 'Staging', 'Staging', $capability, 'wpengine-staging', array( $this, 'wpe_admin_page' ) );

		// Direct link to user portal
		add_submenu_page( 'wpengine-common', 'User Portal', 'User Portal', $capability, 'wpe-user-portal', array( $this, 'redirect_to_user_portal' ) );

		// Direct link to Zendesk
		add_submenu_page( 'wpengine-common', 'Support System', 'Support System', $capability, 'wpe-support-portal', array( $this, 'redirect_to_portal' ) );
	}
    }

	// Gets site dirsize value from our API and store it in a transient
	public function upload_space_load() {
		$upload_dirs = wp_upload_dir();
        $dir = $upload_dirs['basedir'];
		$key     = 'dirsize_cache';
		$dirsize = get_transient( $key );

		// If don't have value, go get it.
		if ( ! is_array( $dirsize ) || ! isset( $dirsize[$dir]['size'] ) ) {
		    $size = FALSE;
		    if( !is_wpe_snapshot() ) {
    			include_once WPE_PLUGIN_DIR.'/class-wpeapi.php';
    			$usage = new WPE_Disk_Usage();
    			$size = 1024 * $usage->get();
		    }
		    $dirsize[$dir]['size'] = $size;
		}

		set_transient( $key, $dirsize, 3600 );
	}

	public function wp_hook_site_url( $url, $path, $orig_scheme, $blog_id ) {
        	if ( defined( 'PWP_NAME' ) && PWP_NAME == "balsamiqmain" ) {
            		return preg_replace( '#^https?://[^/]+/(wp-login\.php|wp-admin)\b#', 'http://balsamiqmain.wpengine.com/\1', $url );
        	}
        	return $url;
	}

	public function redirect_to_user_portal() {
        	if ( empty( $_GET['page'] ) && $_GET['page'] )
			return false;

		if( $this->is_whitelabel() ) {
			$link = wp_cache_get('wpe-install-userportal','wpengine');
			if( !$link ) {
				$link = get_option('wpe-install-userportal');
				wp_cache_set( 'wpe-install-userportal',$link, 'wpengine');
			}
		} else {
			$link = "http://my.wpengine.com";
		}

		wp_redirect( $link );
		exit;
	}

	/**
	 * Redirect to the Support section of the User Portal.
	 *
	 * Mainly this is used for customers who need to submit a ticket. It redirects them to the
	 * appropriate location in the Customer Portal
	 *
	 * @since 2.0.51
	 */
	public function redirect_to_portal() {
        	if ( empty( $_GET['page'] ) && $_GET['page'] )
        		return false;

		wp_redirect( 'https://my.wpengine.com/support?from=wp-admin' );
		exit;
	}

	public function redirect_menu_page() {
		if ( empty( $_GET['page'] ) && $_GET['page'] )
			return false;

		if( $this->is_whitelabel() ) {
			$wl = WPE_WHITELABEL;
			$menudata = wp_cache_get("$wl-menudata",'wpengine');
			if( !empty( $menudata['menu_items']->{$_GET['page']} ) )
			{
				wp_redirect($menudata['menu_items']->{$_GET['page']->target});
			}
		}
    	}

	// Emits our admin page into the output stream.
	public function wpe_admin_page() {
        	// Keep this code separate for complexity.
		include(dirname( __FILE__ ) . "/admin-ui.php");
    	}

	public function get_access_log_url( $which ) {
        	return "/".PWP_NAME."/".md5(WPE_APIKEY)."/logs/${which}.log";
    	}

	public function get_error_log_url( $production = true ) {
        	global $wpengine_platform_config;
        	$method = $production ? 'errors-site' : 'errors-staging-site';
        	return $wpengine_platform_config['locations']['api1'] . "/?method=$method&account_name=" . PWP_NAME . "&wpe_apikey=" . WPE_APIKEY;
	}

    	public function get_customer_record ( ) {
        	global $wpengine_platform_config;
        	$url = $wpengine_platform_config['locations']['api1'] . "/?method=customer-record&account_name=" . PWP_NAME . "&wpe_apikey=" . WPE_APIKEY;
        	$url = $wpengine_platform_config['locations']['api1'] . "/?method=customer-record&account_name=" . PWP_NAME . "&wpe_apikey=" . WPE_APIKEY;
		$http = new WP_Http;
		$msg  = $http->get( $url );
        	if ( is_a( $msg, 'WP_Error' ) )
	        	return false;
		if ( ! isset( $msg['body'] ) )
        		return false;
		$data = json_decode( $msg['body'], true );
		return $data;
    	}

	// If not already set, and we're an administrator, set the WP Engine authentication cookie.
	public function set_wpe_auth_cookie() {
		$wpe_cookie = 'wpe-auth';

		// If not-authenticated, delete our cookie in case it exists.
		if ( ! wp_get_current_user() || ! current_user_can('edit_pages') ) {
			if ( isset($_COOKIE[$wpe_cookie]) )			// normally isn't set, so this optimization happens a lot
				setcookie($wpe_cookie,'',time()-1000000,'/');
			return;
		}

		// Authenticated, so set the cookie properly.  No need if it's already set properly.
		$cookie_value = md5('wpe_auth_salty_dog|'.WPE_APIKEY);
		if ( ! isset( $_COOKIE[$wpe_cookie] ) || $_COOKIE[$wpe_cookie] != $cookie_value )
			setcookie($wpe_cookie,$cookie_value,0,'/','',force_ssl_admin());

	}

	public function disable_indiv_plugin_update_notices( $value ) {
        	$plugins_to_disable_notices_for = array();
        	$basename = '';
        	foreach ( $plugins_to_disable_notices_for as $plugin )
            		$basename = plugin_basename( $plugin );
        	if ( isset( $value->response[@$basename] ) )
            		unset( $value->response[$basename] );
        	return $value;
    	}

	// Filter on all WordPress SQL queries
	public function query_filter( $sql ) {
		// Ordering by the non-GMT version isn't indexed and always returns the same results as ordering by GMT.
		$new_sql = preg_replace( "#\\bORDER BY (\\w+_(?:posts\\.post|comments\\.comment)_date)\\b(\\s+(?:A|DE)SC\\b)?#", "ORDER BY \$1_gmt\$2, \$1\$2", $sql );
		//if($new_sql != $sql) error_log("[[[$sql]]] -> [[[$new_sql]]]");
		return $new_sql;
	}

    // Stuff we run as often as WordPress will allow
    public function do_frequently() {
        global $wpdb;

        print("WPEngine Frequent Periodic Tasks: Start\n" );

        // Check for old wp-cron items that aren't clearing out.  Has to be older than a certain
        // threshold because that means we've done several wp-cron attempts and it's not clearing.
        // Also only clear certain known problematic things which might be gumming up other non-problematic
        // things, e.g. Disqus was doing this as a known issue on Nicekicks.
        if ( true ) {
            $now                       = time();
            $problematic_wp_cron_hooks = array( 'dsq_sync_post', 'dsq_sync_forum' );  // bad types
            $problematic_wp_cron_age_secs = 60 * 60 * 2;  // when older than this, delete the entries.
            $too_old                      = $now - $problematic_wp_cron_age_secs; // if scheduled timestamp older than this, it needs to be nuked.
            $crons                        = _get_cron_array();
            if ( ! empty( $crons ) ) {
                print("\tLoaded crons array, contains " . count( $crons ) . " entries.\n" );
                $changed_cron = FALSE;  // did we make any changes?
                foreach ( $crons as $timestamp => $cron ) {
                    if ( $timestamp < $too_old ) {  // ancient!
                        foreach ( $problematic_wp_cron_hooks as $hook ) { // only nuke these
                            if ( isset( $crons[$timestamp][$hook] ) ) {
                                $changed_cron = true;
                                print("\tRemoved old cron: $hook: $timestamp: age=" . ($now - $timestamp) . " s\n" );
                                unset( $crons[$timestamp][$hook] );
                            }
                        }
                        if ( empty( $crons[$timestamp] ) ) {  // any timestamp with no hooks can always be deleted
                            $changed_cron = true;
                            unset( $crons[$timestamp] );
                        }
                    }
                }
                if ( $changed_cron ) {  // don't re-write cron unless something actually changed, otherwise *very* inefficient!
                    print("\tRe-writing crons array, now contains " . count( $crons ) . " entries.\n" );
                    _set_cron_array( $crons );
                }
            }
        }

        // Check for "future" posts (i.e. scheduled) which missed the schedule.  This happens on high-traffic
        // sites when in the middle of a cron job because there's just a single-shot cron event for that post.
        $sql      = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s AND post_date_gmt < UTC_TIMESTAMP()", 'future' );
        $post_ids = $wpdb->get_col( $sql );
        foreach ( $post_ids as $post_id ) {
            print("\tFIXING: Post ID $post_id was scheduled but was missed. Publishing now...\n" );
            wp_publish_post( $post_id );
            print("\t\tFIXED.\n" );
        }

        print("Finished.\n" );
    }

    // Our own method for filtering the HTML output by WordPress, post-processing everything else on the page.
    public function filter_html_output( $html ) {
        if (defined('WP_CLI') && WP_CLI) {
            return $html;
        }
        global $wpengine_platform_config;
        global $wpe_ssl_admin, $timthumb_script_regex, $wpe_netdna_domains, $wpe_netdna_domains_secure;
        $wpe_netdna_push_domains = $wpengine_platform_config['netdna_push_domains'];
        $wpe_no_cdn_uris = $wpengine_platform_config['no_cdn_uris'];
        global $cdn_on_known_alias, $wp_object_cache, $re_curr_domain, $curr_domains, $curr_domain, $wpe_largefs;
        global $wpe_cdn_uris, $wpe_replace_siteurl_with_https;

        $uri       = $_SERVER['REQUEST_URI'];
        $http_host = $_SERVER['HTTP_HOST'];

        // If this is the staging area, don't apply the filter
        if ( is_wpe_snapshot() )
            return $html;

        // For AJAX requests that return serialized data
        if ( is_serialized($html) ) {
            return $html;
        }

        // A general tool for disabling this filter, which can be implemented anywhere in PHP.
        if ( defined( 'WPE_NO_HTML_FILTER' ) && WPE_NO_HTML_FILTER )
            return $html;

        // Non-trivial
        if ( strlen( $html ) < 100 ) {
            //error_log("not enough content to filter: ".strlen($html)." chars.");
            return $html;
        }

        // If basic WordPress subsystems aren't set up, none of this can work anyway.
        if ( ! isset( $wp_object_cache ) || ! $wp_object_cache || ! is_object( $wp_object_cache ) )
            return $html;

        // If this isn't textual content, don't do any filtering.
        $is_html = false;
        foreach ( headers_list() as $header )
            if ( preg_match( "#^content-type:\\s*text/#i", $header ) ) {
                $is_html = true;
                break;
            }
        if ( ! $is_html )
            return $html;

        // If this isn't a GET or POST, don't do anything.
        $method = strtoupper( $_SERVER['REQUEST_METHOD'] );
        switch ( $method ) {
            case 'GET' :
            case 'POST' :
                break;
            default :
                return $html;
        }

        // Don't do at all for some blogs
        // Remove the link to download WP3.3
//		$html = preg_replace('#/wp-admin/(network/)?update-core.php">Get Version 3.3#','">',$html);
//		$html = preg_replace('#update-core.php\?action=do-core-upgrade#','',$html);
        $is_admin      = is_admin();
        $is_admin_page = preg_match( "#/wp-(?:admin/|login\\.php)#", $uri );
        $blog_url      = home_url();
        $re_blog_url   = preg_quote( $blog_url );
		$uses_largefs  = isset($wpe_largefs) && ! empty($wpe_largefs);
        $is_ssl        = @$_SERVER['HTTPS'];
        if ( preg_match( '/^[oO][fF]{2}$/', $is_ssl ) )
            $is_ssl        = false;  // have seen this!
        $native_schema = $is_ssl ? "https" : "http";

        // Determine the CDN, if any
        if ($is_ssl) {
            $domains = $wpe_netdna_domains_secure;
        } else {
            $domains = $wpe_netdna_domains;
        }
        $cdn_domain = $this->get_cdn_domain( $domains, $blog_url, $is_ssl );

        // Should we actually use the CDN?  If it's currently disabled, then no, even if we know
        // better, because this is probably due to a designer wanting to iterate without caching.
        $cdn_enabled = $this->is_cdn_enabled();  // until we know otherwise

	//error_log("enabled: ".($cdn_enabled?'y':'n')."; domain=$cdn_domain; uri=$uri");
        // If it's an aliased MU domain, it might not appear to be enabled by W3TC, but
        // because it was explicitly listed, it should be enabled, so do that here.
        if ( $cdn_on_known_alias && $cdn_domain ) {
            $cdn_enabled = true;
        }

        // Some paths might reject CDN completely -- if so, don't do CDN replacements.
        // In fact, UNDO any that were done by W3TC!
        $undo_cdn = false;
        foreach ( $wpe_no_cdn_uris as $re ) {
            if ( preg_match( '#' . $re . '#', $uri ) ) {
                $cdn_enabled = false;
                $undo_cdn    = true;
                break;
            }
        }
	//error_log("ssl=".($is_ssl?'yes':'no').", uri=$uri, cdn=".($cdn_enabled?'yes':'no').", undo=".($undo_cdn?'yes':'no'));
        // Possible undo existing CDN replacements
        if ( $undo_cdn && $cdn_domain ) {
            $re   = "#\\bhttps?://" . preg_quote( $cdn_domain ) . "/#";
            $repl = "$native_schema://$http_host/";
            $html = preg_replace( $re, $repl, $html );
        }

        // Find TimThumb-style references that include entire URLs in the source and replace with relative paths only.
		// Do NOT do this if we're using LargeFS because the files are likely not on disk and need to use domains.
		if ( ! $is_admin ) {
			$html = ec_modify_timthumb_src_urls( $html,
				($uses_largefs && $cdn_domain && $cdn_enabled) ? $cdn_domain : $http_host,		// pull from the "external" CDN domain to trick TimThumb
				$uses_largefs
			);
		}

        // Only replace if the CDN is also enabled, unless this is the admin screens in which case we can
        // always use it because it's only for safe, versioned system files.
	if ( $cdn_domain && $cdn_enabled && ! $is_admin ) {  // XXX: DISABLED FOR ADMINS BECAUSE OF THESITEWHICHWILLNOTBENAMED.COM -- USE OUR OWN CDN TO FIX!
		$map_domain_cdn = array();
		foreach( $curr_domains as $domain )
			$map_domain_cdn[$domain] = $cdn_domain;
		$rules = array();
		// Start with site-specific rules
		ec_add_cdn_replacement_rules_from_cdn_regexs( $rules, $wpe_cdn_uris, $http_host, $cdn_domain );
		// If any LargeFS paths use 301 behavior, we also might as well just direct those directly
		// to S3 so we don't have to serve them at all.
		if ( isset($wpe_largefs) && count($wpe_largefs) > 0 ) {
			foreach ( $wpe_largefs as $lfs ) {
				if ( el($lfs,'redirect',false) ) {
					$rules[] = array (
						'src_domain' => $http_host,
						'src_uri' => '#^'.preg_quote($lfs['path']).'#',
						'dst_domain' => "s3.amazonaws.com",
						'dst_prefix' => "/" . WPE_LARGEFS_BUCKET . "/" . PWP_NAME,
					);
				}
			}
		}
		// If there are CDN push-zones, apply those before general CDN paths. This is not supported
		// for HTTPS environments.
		if ( isset( $wpe_netdna_push_domains ) && ! $is_ssl ) {
			foreach ( $wpe_netdna_push_domains as $re => $zone ) {
				$rules[] = array (
					'src_domain' => $http_host,
					'src_uri' => '#'.$re.'#',
					'dst_domain' => "${zone}push.wpengine.netdna-cdn.com",
				);
			}
		}
		$rules = array_merge($rules,ec_get_cdn_replacement_rules( $map_domain_cdn ));	// standard CDN replacements
		$html = ec_url_replacements( $html, $rules, $curr_domain, $is_ssl );
        }

        // Run site-specific content replacements
		$content_regexs = $this->get_regex_html_post_process();
        if ( ! $is_admin && is_array( $content_regexs ) && count( $content_regexs ) > 0 ) {
            foreach ( $content_regexs as $re => $repl ) {  // TODO: Can do in one pass with keys/values
                $html = preg_replace( $re, $repl, $html );
            }
        }

		// Replacements for malware and other general stuff
		$html = preg_replace( "#<iframe\s*src\s*=\s*[\"'][^\"']*?/feed/xml.php.*?</iframe>#", "", $html );

        // If in admin area and requires SSL, force those URLs.
        // However, do NOT make those replacements inside post content itself.
        if ( $is_admin_page && $wpe_ssl_admin ) {
            // Find URLs inside post content and "hide" them so the mass replacement skips them.
            $ignore_start = $ignore_end   = 0;
            if ( preg_match( "#<textarea.+?</textarea>#is", $html, $match, PREG_OFFSET_CAPTURE ) ) {
                $ignore_start = $match[0][1];
                $ignore_end   = $ignore_start + strlen( $match[0][0] );
            }
            $new_blog_url = preg_replace( "#\\bhttp://#", "https://", $blog_url );
            if ( $new_blog_url != $blog_url )  // handle trivial case
                $html         = Patterns::preg_replace_around(
                    "#$wpe_replace_siteurl_with_https\\b$re_blog_url#", $new_blog_url, $html, $ignore_start, $ignore_end
                );
        }

        // Change any remaining http -> https.
        if ( $is_ssl ) {
            $html = $this->build_http_to_https($html);
        }

        // If we have an external blog URL, rewrite local URLs.
        // But not in the admin area; always go to the backend for that.
        if ( isset( $_SERVER['HTTP_X_WPE_REWRITE'] ) )
            $external_url = $_SERVER['HTTP_X_WPE_REWRITE'];
        elseif ( defined( 'WPE_EXTERNAL_URL' ) && WPE_EXTERNAL_URL )
            $external_url = WPE_EXTERNAL_URL;
        else
            $external_url = null;
        if ( $external_url && ! $is_admin_page ) {
            $burl         = $this->get_url_to_replace( $blog_url );
            $external_url = $this->get_url_to_replace( $external_url );
            $replacements = array( // make multiple domain-based replacements
                $burl            => $external_url, // the obvious one
                urlencode( $burl ) => urlencode( $external_url ), // "like" buttons and similar often url-encode the target URL for a GET request
            );
            foreach ( $replacements as $burl => $repl ) {
                // Replace the entire URL
                $re       = preg_quote( $burl );
                $html     = preg_replace( "#${re}#", $repl, $html );
                // When there's a subdirectory in the external URL, absolute paths without the scheme/domain portion
                // of the URL also need to be replaced.
                $ext      = parse_url( $repl ); // explode URL to parts
                $ext_path = $ext['path'];  // path only
                if ( strlen( $ext_path ) >= 2 ) {  // non-trivial path
                    // regex of things that look like references to absolute paths in tags,
                    $re   = "#(\\w+=['\"])(/[^'\"]+)(['\"])#";
                    $repl = "\$1${repl}\$2\$3";
                    $html = preg_replace( $re, $repl, $html );
                }
            } // replacement loop
        }

        // Finished.
        return apply_filters('wpe_filtered_output',$html);
    }

    // str-replace the html on non-https pages to https.
    public function build_http_to_https ($html)
    {
        $blog_url       = home_url();
	return Patterns::build_http_to_https($html,$blog_url);
    }

    public static function get_url_to_replace( $url ) {
        if ( substr( $url, -1 ) == '/' )
            $url = substr( $url, 0, -1 );
        return $url;
    }

    public function snapshot_to_staging() {
        global $wpengine_platform_config;

        $http = new WP_Http;
        $url  = $wpengine_platform_config['locations']['api1'] . '/?method=staging&account_name=' . PWP_NAME . '&wpe_apikey=' . WPE_APIKEY;
        $msg  = $http->get( $url );
        if ( is_a( $msg, 'WP_Error' ) ) {
            // Usually means request timed-out. Do what want here.
						return "Recreating the staging area failed!  Please contact support for assistance.";
        }
//		if (isset($msg['body'])) return $msg['body'];
        return '';
    }

    // Returns structured status information block, especially useful for displaying to a human.
    public function get_staging_status() {
        global $wpengine_platform_config;
        $sldomain = get_option('wpe-install-domain_mask', $wpengine_platform_config['locations']['domain_base']);
        $r = array( );
        $staging_dir        = PWP_ROOT_DIR . "/www/staging/" . PWP_NAME;
        $staging_touch_file = "${staging_dir}/last-mod";
        $r['staging_url']   = "http://" . PWP_NAME . ".staging.$sldomain";
        $have_snapshot      = is_dir( $staging_dir ) && file_exists( $staging_touch_file );
        $r['have_snapshot'] = $have_snapshot;
        if ( $have_snapshot ) {
            $r['status'] = @file_get_contents( $staging_touch_file );
            if ( ! $r['status'] ) {   // backwards-compatibility for when we just "touched" the file when done.
                $r['status']   = "Live and ready";
                $r['is_ready'] = true;
            } else {
		$r_json = json_decode($r['status'], true);
		if (is_array($r_json)){
			$r['status'] = "";
			// we have a json status, look for 'non-ready's
			foreach ($r_json as $key => $value) {
				if ("Ready!" != $value['text']) {
					// append any non-readies to the status
					$r['status'] = $r['status'].' '.$value['text'];
				}
			}
			if ("" == $r['status']){
				// we never set, so there must be a ready
				$r['status'] = 'Ready!';
			}

		}  // else leave r['status'] as it is, it's probably the old 'just text' format
                $r['is_ready']    = $r['status'] == "Ready!";
            }
            $r['last_update'] = filemtime( $staging_touch_file );
            $staging_version_file = $staging_dir . "/wp-includes/version.php";
            $r['version'] = $this->get_wp_version($staging_version_file);
        }
        return $r;
    }

    // Returns structured information about the site as we know it
    public function get_site_info() {
        global $wpengine_platform_config;
        static $cached_site_info = null;
        $base = $wpengine_platform_config['locations']['domain_base'];
        if ( ! $cached_site_info ) {
            $r                = new stdClass;
            $r->name = PWP_NAME;
            $r->cluster = WPE_CLUSTER_ID;
            $r->is_pod = defined( 'WPE_ISP' ) ? WPE_ISP : FALSE;
            $r->lbmaster = @constant( "WPE_LBMASTER_IP" ) ?: FALSE;
            if( !$r->lbmaster ) {
                $r->lbmaster = $r->is_pod ? "pod-" . $r->cluster . ".$base" : "lbmaster-" . $r->cluster . ".$base";
            }

            $r->public_ip = gethostbyname($r->lbmaster);
            $r->sftp_host = ($r->is_pod ? $r->name : "sftp".$r->cluster) . ".$base";
            $r->sftp_ip = ($r->is_pod ? gethostbyname("pod-{$r->cluster}") : gethostbyname($r->sftp_host));
            $r->sftp_port = defined('WPE_SFTP_PORT') ? WPE_SFTP_PORT : 22;
            $cached_site_info = $r;
        }
        return $cached_site_info;
    }

    // Blowup the space-separated list of domains and ips into an associative array
    public function env_get_dedicated_ips($dedicated_ip_pairs) {
        $env_ip_pairs = array();
        foreach(explode(' ', $dedicated_ip_pairs) as $index_num => $ip_pair) {
            $stuckpairs = explode("=", $ip_pair);
            if (count($stuckpairs) === 2) {
                $env_ip_pairs[$stuckpairs[0]] = $stuckpairs[1];
            }
        }
        return $env_ip_pairs;
    }

    public function remove_upgrade_nags() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('#dashboard_right_now a.button').css('display','none');
            });

        </script>
        <?php
    }


    // Called on init to replace PHP's notion of source IP address with the proxied value from nginx
    public function real_ip() {
        $this->process_internal_command();
    }

    public function purge_object_cache() {
        global $wp_object_cache;
        // Check for valid cache. Sometimes this is broken -- we don't know why! -- and it crashes when we flush.
        // If there's no cache, we don't need to flush anyway.
        if ( $wp_object_cache && is_object( $wp_object_cache ) ) {
            try {
                wp_cache_flush();
            } catch ( Exception $ex ) {
                echo("\tWarning: error flushing WordPress object cache: " . $ex->getMessage() . "\n");
                // but, continue.  Probably not that important anyway.
            }
        }
    }

    // Is the CDN enabled?
    public function is_cdn_enabled() {
		if ( WPE_CDN_DISABLE_ALLOWED ) {
	        $val = get_site_option( 'wpe-cdn-enabled', null );
	        if ( $val == "disabled" )
	            return false;
		}
        return true;
    }

    // Sets CDN to be enabled or disabled.
    public function set_cdn_enabled( $state ) {
        $this->set_site_option( 'wpe-cdn-enabled', $state ? "yes" : "disabled"  );
    }

    // Is ORDER BY RAND() enabled?
    public function is_rand_enabled() {
		if ( ! isset($this->_rand_enabled) ) {
       		$this->_rand_enabled = get_site_option( 'wpe-rand-enabled', false );
		}
		return $this->_rand_enabled;
    }

    // Sets ORDER BY RAND() to be enabled or disabled.
    public function set_rand_enabled( $state ) {
		$state = !! $state;		// normalize to boolean
        $this->set_site_option( 'wpe-rand-enabled', $state );
		$this->_rand_enabled = $state;
    }

    // Is the object cache enabled?
    public function is_object_cache_enabled() {
        global $memcached_servers;
	if ( ! defined('WP_CACHE') ) return false;
	if ( ! WP_CACHE ) return false;
	if ( 0 == count($memcached_servers) ) return false;
	$path = WP_CONTENT_DIR . "/object-cache.php";
	return file_exists($path);
    }

    // Sets object cache to be enabled or disabled.
    public function set_object_cache_enabled( $state ) {
		$path = WP_CONTENT_DIR . "/object-cache.php";
		if ( $state ) {
			copy(dirname(__FILE__)."/object-cache.php",$path);		// copy our version into place
		} else {
			unlink($path);			// remove the object cache file
		}
    }

	public function is_wpengine_admin_bar_enabled() {
		return get_site_option( 'wpengine_admin_bar_enabled', 1 );
	}

	public function set_wpengine_admin_bar_enabled( $enabled ) {
		return $this->set_site_option( 'wpengine_admin_bar_enabled', $enabled );
	}

    /**
     * Check if WP Engine news feed is enabled on the WordPress dashboard and WPE plugin.
     * For multisites, this checks if this option is set at a network level, and this setting will apply for all subsites.
     * For a non-multisite, this defaults to check the site's options table.
     * @return int Returns 1 if the news feed is enabled, and 0 if disabled. If option not found, defaults to 1.
     */
    public function is_wpengine_news_feed_enabled() {
        return get_site_option( 'wpengine_news_feed_enabled', 1 );
    }

    /**
     * Set the option to enable/disable the WP Engine news feed in the WordPress dashboard and WPE plugin
     * For multisites, the option is set at a network level, and the setting will apply for all subsites.
     * For a non-multisite, this defaults to set the option in the site's options table.
     * @param int 1 if the news feed is enabled; 0 if the news feed is disabled
     * @return bool true if the option was successfully added or updated
     */
    public function set_wpengine_news_feed_enabled($enabled) {
        return $this->set_site_option( 'wpengine_news_feed_enabled', $enabled );
    }

	public function get_regex_html_post_process()
	{
		global $wpengine_platform_config;
		$wpe_content_regexs = $wpengine_platform_config['content_regexs'];
        $x = get_site_option( 'regex_html_post_process', null );
		if ( $x == null && isset($wpe_content_regexs) ) {
			$x = $wpe_content_regexs;
		}
		if ( $x ) return $x;
		return array();
	}

	public function set_regex_html_post_process( $arry )
	{
		$this->set_site_option( 'regex_html_post_process', $arry );
	}

	public function get_regex_html_post_process_text()
	{
		$a = $this->get_regex_html_post_process();
		$str = "";
		foreach ( $a as $re => $repl ) {
			$str .= "$re => $repl\n";
		}
		return $str;
	}

	public function set_regex_html_post_process_text( $txt )
	{
		$a = array();
		foreach ( preg_split("#\r?\n#",$txt) as $line ) {
			$parts = explode("=>",$line,2);
			if ( count($parts) == 0 ) continue;
			$re = trim($parts[0]);
			if ( ! $re ) continue;
			if ( FALSE === preg_match($re,"") ) return "This is an invalid PHP regular expression: $re";
			$repl = "";
			if ( count($parts) == 2 ) $repl = trim($parts[1]);
			$a[$re] = $repl;
		}
		$this->set_regex_html_post_process($a);
		return TRUE;
	}

	// Given text which represents one regular expression per line, returns an array
	// of regular expressions NOT including the regular PHP expression wrapper AND validates
	// those expressions.  If any are invalid, returns a human-readable string error message,
	// otherwise returns the array, which can be empty.
	public function convert_text_regexes_to_regexs( $re_lines ) {
		$results = array();
		$line_no = 0;
		if ( $re_lines ) foreach ( preg_split('/\r?\n/',$re_lines) as $re ) {
			$line_no++;
			$re = trim($re);
			if ( ! $re ) continue;
			$regex = "#${re}#";
			if ( @preg_match($regex,"") === FALSE ) {
				return "Invalid regular expression on line $line_no: $re";
			}
			$results[] = $re;
		}
		return $results;
	}

	private function set_site_option($name,$value) {
        add_site_option( $name, $value  );
        update_site_option( $name, $value  );
	}

    // Determines the domain name of the CDN for this site, if any.
    // If it does have a configured CDN (either NetDNA zone or FQDN), returns the FQDN (e.g. "asmartbear.wpengine.netdna-cdn.com")
    // If there is a non-trivial NetDNA config for this client, but this particular blog has no config, returns FALSE.
    // If there is no NetDNA config for this client at all, returns null.
    // So: Just to determine whether or not there's a CDN, can treat the return value as boolean, but to distinguish
    // between "actively has no CDN" and "don't have an opinion about CDN config," use the exact return value.
    public function get_cdn_domain( $netdna_config, $blog_url, $is_ssl ) {
        global $wpengine_platform_config;
        $wpe_domain_mappings = $wpengine_platform_config['domain_mappings'];
        global $cdn_on_known_alias;

        // NetDNA CDN configuration.  It's possible we have a CDN configuration and this doesn't
        // know it; in that case the configuration is handled manually.  But if we do know the
        // configuration for certain, we can enforce that here.
        $cdn_on_known_alias = false;  // until we know otherwise
        if ( ! $netdna_config || ! is_array( $netdna_config ) || 0 == count( $netdna_config ) )
            return null;  // no opinion
        $blog_domain        = parse_url( $blog_url, PHP_URL_HOST );
        if ( isset( $wpe_domain_mappings[$blog_domain] ) ) { // if this domain is an alias, resolve the alias first
            $cdn_on_known_alias = true;
            $blog_domain        = $wpe_domain_mappings[$blog_domain];
        }
	if ($is_ssl) {
            $zone_base = "-wpengine.netdna-ssl.com";
        } else {
            $zone_base = ".wpengine.netdna-cdn.com";
        }
        foreach ( $netdna_config as $zone => $domain ) {
            // Newer netdna array format
            if ( is_array( $domain ) ) {
                // If this is the url we're looking for.
                if ( 0 == strcasecmp( $blog_domain, $domain['match'] ) ) {
                    if ( isset( $domain['custom'] ) )
                        return $domain['custom'];
                    if ( isset( $domain['zone'] ) )
                        return $domain['zone'] . $zone_base; // build FQDN from NetDNA zone
                }
            } else {
                if ( strcasecmp( $blog_domain, $domain ) == 0 ) {
                    if ( strpos( $zone, "." ) ) // already is FQDN?
                        return $zone;
                    return $zone . $zone_base; // build FQDN from NetDNA zone
                }
            }
        }
        return false; // this site has none, but others do.
    }

    // Ensure we have certain database tables and process content therein.
    // Returns FALSE on error, TRUE if we succeeded.
    public function ensure_database() {

        return;

        global $wpdb, $table_prefix;

        // Ensure our posts table exists
        $tables    = $wpdb->get_col( "SHOW TABLES" );
        $wpe_posts = $table_prefix . "wpe_posts";
        if ( ! in_array( $wpe_posts, $tables ) ) {
            $wpdb->show_errors( TRUE );
            $result = $wpdb->query( "
				CREATE TABLE $wpe_posts (
					post_id BIGINT(20) UNSIGNED NOT NULL,
					last_modified DATETIME,
					post_permalink VARCHAR(255),
					PRIMARY KEY (post_id)
				) engine=InnoDB, DEFAULT CHARSET=utf8
			" );
            if ( $result === FALSE ) {
                echo("ERROR: Unable to create $wpe_posts: $wpdb->last_result");
                return FALSE;
            }
        }

        // Any new posts not in our table, add to our table now
        $wpdb->query( "
			INSERT INTO $wpe_posts
				( post_id )
			SELECT posts.ID
			FROM $wpdb->posts LEFT OUTER JOIN $wpe_posts ON ( $wpdb->posts.ID = $wpe_posts.post_id )
			WHERE $wpe_posts.post_id IS NULL
			AND $wpdb->posts.post_status='publish'
		" );

        // Query for posts that need updating.
        // Limit so we don't choke on massive blogs.
        // Order by most recent so if we limit we're getting the "most important" ones first.
        $update_ids = $wpdb->get_col( "
			SELECT wp.post_id
			FROM $wpdb->posts p JOIN $wpe_posts wp ON ( p.ID = wp.post_id )
			WHERE
				wp.last_modified IS NULL OR
				wp.post_permalink IS NULL OR
				wp.last_modified < p.post_modified_gmt
			ORDER BY wp.post_id DESC
			LIMIT 3
		" );
        foreach ( $update_ids as $post_id ) {
            $post_id = intval( $post_id );
            echo("\tupdating $post_id\n");
            $post    = get_post( $post_id );
            echo("\tgot the post: $post\n");
            $wpdb->update( $wpe_posts, array(
                'last_modified'  => $post->post_date_gmt,
                'post_permalink' => get_permalink( $post_id ),
                    ), array( 'post_id' => $post_id ) );
            echo("\tupdated\n");
        }

        return true;
    }

    // Called peridoically internally to ensure the right plugins are loaded etc.
    public function ensure_standard_settings() {
        global $wpdb, $memcached_servers;
        global $wpengine_platform_config;

        // Compute some values
        $blog_url = home_url();
        $sitename = "unknown: " . __FILE__;
        if ( preg_match( "#^/nas/wp/www/[^/]+/([^/]+)/#", __FILE__, $match ) )
            $sitename = $match[1];
        echo( "Ensuring: $sitename - $blog_url\n" );
        add_filter( 'http_request_timeout', function() {
                    return 30;
                }, 1 );  // some sites take FOREVER
        $http = new WP_Http;
        $url  = $wpengine_platform_config['locations']['api1'] . '/?method=site&account_name=' . PWP_NAME . '&wpe_apikey=' . WPE_APIKEY;
        $msg  = $http->get( $url );
        if ( ! $msg || is_a( $msg, 'WP_Error' ) || ! isset( $msg['body'] ) ) {
            echo("### FAIL: Couldn't load site configuration! (from " . __FILE__ . ")\n");
            echo($url . "\n");
            echo("Server Response:\n");
            var_export( $msg );
            return;
        }
        $config = json_decode( $msg['body'], TRUE );
        if ( ! $config || ! is_array( $config ) ) {
            echo("### FAIL: Couldn't decode site configuration! (from " . __FILE__ . ")\n");
            echo($url . "\n");
            echo("Server Response:\n");
            var_export( $msg );
            return;
        }
        $is_pod          = WPE_CLUSTER_TYPE == "pod";
        $cluster_id      = WPE_CLUSTER_ID;
        $is_bpod         = defined( 'WPE_BPOD' ) && WPE_BPOD;
        $lbmaster        = $is_pod ? "localhost" : "lbmaster-$cluster_id";
        $dbmaster        = $is_pod ? "localhost" : "dbmaster-$cluster_id";
        $is_high_traffic = el( $config, 'high_traffic', false ) || $is_pod;
        $all_varnish     = true;  // not having this has bit us before, and having it for small sites is fine.
        // If site has hyper db in place, turn of w3tc dbcache
        $hyperdb         = el( $config, 'hyperdb' );
        if ( $is_pod )
            $hyperdb         = false;
        if ( $hyperdb )
            $dbcache_enabled = false;
        else
            $dbcache_enabled = true;

        // List of user-agent patterns where we shouldn't use the page cache.
        $no_cache_user_agents = array(
            "X-WPE-Rewrite", // used to create a caching namespace for proxy-redirected blogs.
        );

        // List of cookie-patterns where we shouldn't use the page cache.
        $no_cache_cookies = array(
            "wptouch_switch_cookie", // used by WPTouch when it's in a dynamic mode.
        );

        $is_nfs             = ! $is_pod;
        $allow_file_locking = ! $is_nfs;

        // Should we allow W3TC Page Cache?
        // If we're sending all traffic to Varnish, then no it's just overlap.
        $pgcache_enabled = ! $all_varnish;

        // Should we allow W3TC Object Cache?
        // It takes a *lot* more memory in memcached, but can significantly speed up a site.
        if ( isset( $config['use_object_cache'] ) )
            $allowed_objectcache = $config['use_object_cache'];
        else
            $allowed_objectcache = ( $is_high_traffic || $is_pod ) && ! $is_bpod;  // used to be slow on a pod, but now with unix sockets for memcached it's fast!


// Should we cache database queries for logged-in users?
        // Normally no, but for high-admin sites it does help and might be worth the risk.
        $cache_database_for_logged_in = $is_high_traffic || $allowed_objectcache || el( $config, 'cache_database_for_logged_in', false );
        if ( $is_pod )
            $cache_database_for_logged_in = false;

        // How long to allow files to be cached before they're considered "stale" and should
        // be reaped by a cron task.  This is extra slow on NFS, so keep it short so we do
        // not have to sift through too many files.  Maybe this should be in memcached!
        $file_cache_seconds = $is_nfs ? 600 : 60 * 60;
        $memcached_file_ttl = $is_pod ? 24 * 60 * 60 : 60 * 60 * 2;  // if in memcached we can leave around far longer than on disk
        // Which server should be used for database-related or file-related memcached?
        $file_memcached_server = $is_pod ? "unix:///tmp/memcached.sock" : ( $cluster_id == 1 ? "localhost:11211" : "$dbmaster:11211" );
        $db_memcached_server   = $file_memcached_server;
        $obj_memcached_server  = $file_memcached_server;

        // Should we use the memcached-based file cache or the disk-based?  Disk-based means
        // flushing only affects one blog, but memory-based is much faster to process.
        $pgcache_in_memcached  = $is_pod ? false : true;
        $pgcache_cache_queries = $pgcache_in_memcached;

        // NetDNA CDN zone for this site
        // $cdn_domain = $this->get_cdn_domain( el( $config, 'netdna', array( ) ), $blog_url );

        // Ensure WPEngine standard account.
        echo( "Ensuring the WPEngine account...\n" );
        $wpe_user_id = username_exists( 'wpengine' );  // get existing ID
        $wpe_user    = array(
            'user_login'    => 'wpengine',
            'user_pass'     => md5( mt_rand() . mt_rand() . mt_rand() . mt_rand() . time() . gethostname() . WPE_APIKEY ), // random password; we'll set it properly next
            'user_email'    => 'bitbucket@wpengine.com',
            'user_url'      => 'http://wpengine.com',
            'role'          => 'administrator',
            'user_nicename' => 'wpengine',
            'description'   => 'This is the "wpengine" admin user that our staff uses to gain access to your admin area to provide support and troubleshooting. It can only be accessed by a button in our secure log that auto generates a password and dumps that password after the staff member has logged in. We have taken extreme measures to ensure that our own user is not going to be misused to harm any of our clients sites.'
        );

	if ( ! $wpe_user_id ) {
		$wpe_user_id = wp_insert_user( $wpe_user );  // creates; returns new user ID
        } else {
		$wpe_user['ID'] = $wpe_user_id;
		wp_update_user( $wpe_user );
	}

	if ( $wpe_user_id ) {  // could be we tried to create it but failed; then don't run this code
		// Set the request variable because some plugins keyed on it during profile_update hook.
		$_REQUEST['user_id'] = $wpe_user_id;

            // Impersonate as the wpengine user.
            if ( function_exists( 'wp_set_current_user' ) ) {
                wp_set_current_user( $wpe_user_id );
            }

        }

        // Make Multisite wpengine admin a Super Admin
        if ( $wpe_user_id && function_exists( 'is_multisite' ) && is_multisite() ) {
            require_once( ABSPATH . '/wp-admin/includes/ms.php');
            grant_super_admin( $wpe_user_id );
        }

        // Empty caches
        echo( "Purging object cache...\n" );
        $this->purge_object_cache();

        // Deactivate plugins we don't support.
        echo( "Deactivating plugins: hello, migration, cachers...\n" );
        $plugins = array(
            "hello.php", // stupid
            "wpengine-migrate/plugin.php", // unnecessary, but commonly there
            "wp-file-cache/file-cache.php", // cache we don't use
            "wp-super-cache/wp-cache.php", // cache we don't use
            "hyper-cache/plugin.php", // cache we don't use
            "db-cache-reloaded/db-module.php", // cache we don't use
            "db-cache-reloaded/db-cache-reloaded.php", // cache we don't use
            "no-revisions/norevisions.php", // unneeded; we do this via wp-config.php
            "wp-phpmyadmin/wp-phpmyadmin.php", // blacklisted for security issues
            "wpengine-common/plugin.php", // moved to a must-use plugin.
        );
        $plugins[] = "w3-total-cache/w3-total-cache.php";
        if ( false == el( $config, 'profiler' ) )
            $plugins[] = "wpe-profiler/wpe-profiler.php";

        // Check if the plugin is active.  If so, deactivate it and turn on the other plugin
        $required_plugins = array( );

        // If plugin is activated, turn it off and turn on the other one instead.
	$disable_sitemap = false;
        // This plugin is not in the repo anymore, but that doesn't mean that people won't bring it over from another host.
        if ( is_plugin_active( 'google-xml-sitemaps-with-multisite-support/sitemap.php' ) ) {
		echo( "Turning off 'google-xml-sitemaps-with-multisite-support/sitemap.php' " );
		$plugins[] = 'google-xml-sitemaps-with-multisite-support/sitemap.php';
		$disable_sitemap = true;
	}
	if ( $disable_sitemap ) {
		echo( " ... Turning on 'bwp-google-xml-sitemaps/bwp-simple-gxs.php'\n" );

            // BWP sitemap installer uses wp_rewrite, but when this function runs, it's not instantiated yet in wp-settings.php
            global $wp_rewrite;
            if ( NULL == $wp_rewrite ) {
                $wp_rewrite  = new WP_Rewrite();
            }
            // Remove sitemap files
            $remove_file = array( 'sitemap.xml', 'sitemap.xml.gz' );
            foreach ( $remove_file as $file ) {
                if ( file_exists( ABSPATH . "/$file" ) ) {
                    echo "Remove $file\n";
                    unlink( ABSPATH . "/$file" );
                }
            }
        }

        deactivate_plugins( $plugins );

	//look for plugins that WP Engine Api needs to know about
	include_once(__DIR__.'/class.plugins.php');
	PluginsConfig::sniff();

        // Activate all the plugins we require.  If already activated this won't do anything.
        if ( el( $config, 'profiler' ) )
            $required_plugins[] = "wpe-profiler/wpe-profiler.php";
        foreach ( $required_plugins as $path ) {
            echo( "Activating $path...\n" );
            $result = activate_plugin( $path );
            if ( $result )
                die( "Error activating $path: $result\n" );
        }

        // Display info about function modifier
        $this->_check_function_modifier($config);

	// If first-run, do some extra config
       	if( wpe_param( 'first-run' ) )
		$this->ensure_account_user();

        // Clean-up
        echo( "Purging object cache...\n" );
        $this->purge_object_cache();

        echo("Done!\n");
    }

    public function _check_function_modifier($config=array())
    {
        try {
            $factory = new \wpe\function_modifier\FunctionModifierFactory();
            $modifier = $factory->getFunctionModifier();
            $have_modifier = true;
        } catch (\Exception $e) {
            $have_modifier = false;
        }
        $have_preamble = el( $config, 'use_preamble' );
        echo("Have function modifier: " . ($have_modifier ? "yes" : "no") . "; Have preamble: " . ($have_preamble ? "yes" : "no") . "\n");
    }

    // Ensure standard account.
    // @param wp-cmd=ensure-user
    public function ensure_account_user()
    {
        $sitename = PWP_NAME;
        echo( "Ensuring user: " . $sitename . "\n" );

        // The email address for the user
        $data = $this->get_customer_record();
        $email = @$data['email'];

        $user_id = username_exists( $sitename );  // get existing ID
        $user = array(
            'user_login'    => $sitename,
            'user_url'      => home_url(),
            'role'          => 'administrator',
            'user_nicename' => $sitename,
            'user_pass'     => md5(rand() . time() . rand()),
            'user_email'    => $email,
        );

        if ( ! $user_id ) {
            $user_id = wp_insert_user( $user );  // creates; returns new user ID
        } else {
            $user['ID'] = $user_id;
            wp_update_user( $user );  // update!
        }

        // Make Multisite admin a Super Admin
        if ( $user_id && function_exists( 'is_multisite' ) && is_multisite() ) {
            require_once( ABSPATH . '/wp-admin/includes/ms.php');
            grant_super_admin( $user_id );
        }

    } // ensure user

    public function from_allowed_ip()
    {
        global $wpe_special_ips;
	// Special IPs are ones found in the wp-config.php that are populated by the WPE application.
	// Check if the requestor is in the network and allowed to perform the request.
        if ( is_array($wpe_special_ips) && in_array($_SERVER['REMOTE_ADDR'], $wpe_special_ips) ) {
                return true;
        }
        if ( $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ||
		substr( $_SERVER['REMOTE_ADDR'], 0, 9 ) == '127.0.0.1' ||
                substr( $_SERVER['REMOTE_ADDR'], 0, 11 ) == '67.210.230.' ||
                substr( $_SERVER['REMOTE_ADDR'], 0, 3 ) == '10.' ||			// private subnet always OK (e.g. Amazon)
                substr( $_SERVER['REMOTE_ADDR'], 0, 12 ) == '216.151.212.' ||		// serverbeach external net
                substr( $_SERVER['REMOTE_ADDR'], 0, 7 ) == '172.16.' ||			// serverbeach internal net
                $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']			// if request originated on same host as this script is run.
        ) {
		return true;
	} else {
		return false;
	}
    }

    public function process_internal_command() {

        // Ensure this is an internal command; process normally otherwise
        $cmd = wpe_param( 'wp-cmd' );
        if ( ! $cmd ) {
            return;    // without a command, it's not an internal request
	}
	if ( ! $this->from_allowed_ip() ) {
            print("Ignoring request from non-local host: " . $_SERVER['REMOTE_ADDR'] . " to " . $_SERVER['SERVER_ADDR'] . "\n" );
            exit( 0 );  // local requests only -- security! Meaning our public IP address or localhost
        }
        @ob_get_clean();
        error_reporting( -1 );
        header( "Content-Type: text/plain" );  // just in case we're viewing inside a browser, but typically is commandline
        define( 'WPE_NO_HTML_FILTER', TRUE );

        // Execute command
        switch ( $cmd ) {
            	case 'ping':
                	header( "Content-Type: text/plain" );
                	header( "X-WPE-Host: " . gethostname() . " " . $_SERVER['SERVER_ADDR'] );
                	print( "pong\n" );
                	break;
            	case 'ensure':
                	header( "Content-Type: text/plain" );
                	$this->ensure_standard_settings();
                	break;
            	case 'ensure-user':
                	header( "Content-Type: text/plain" );
                	$this->ensure_account_user();
                	break;
            	case 'nada':
                	return;  // ignore, just to get into some other page
		case 'cron':
                	header( "Content-Type: text/plain" );
              		$this->do_frequently();
                	break;
 		case 'refresh-notices':
                	delete_option('wpe_notices_ttl');
                	delete_transient('wpe_notices_ttl');
                	wp_cache_delete('wpe_notices_ttl','transient');
        	       	break;
		case 'sso':
			$key = $_POST['key'];
			if( sha1('wpe-sso|'.WPE_APIKEY.'|'.PWP_NAME) == $key )	{
					global $wpdb;
					$token = sha1($key. mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand() );
					set_transient('wpe_sso',$token,60);
					echo $token;
			}
			break;
		case 'purge-all-caches':
			ob_start();
			WpeCommon::purge_memcached();
			WpeCommon::clear_maxcdn_cache();
			WpeCommon::purge_varnish_cache();  // refresh our own cache (after CDN purge, in case that needed to clear before we access new content)
			$this->purge_object_cache();
			$errors = ob_get_contents();
			ob_end_clean();
			header( "Content-Type: text/plain" );
		       	header( "X-WPE-Host: " . gethostname() . " " . $_SERVER['SERVER_ADDR'] );
		        print("All Caches were purged!");
	            break;
            	case 'purge-varnish-cache':
			WpeCommon::purge_varnish_cache();
			print("Varnish cache was purged! ");
	            break;
            	case 'purge-object-cache':
			WpeCommon::purge_memcached();
			print( "Object cache was purged! " );
	            break;
            	default:
                	die( "ERROR: unknown command: `$cmd`\n" );
        }

        // Stop processing
        exit( 0 );
    }

    public static function clear_maxcdn_cache() {
        if ( WPE_DISABLE_CACHE_PURGING )
            return false;

        global $wpe_netdna_domains, $wpe_netdna_domains_secure;
        global $wpengine_platform_config;

        $url = sprintf( "%s/install/%s/cdn/purge", $wpengine_platform_config['locations']['api2'], PWP_NAME );
        $args = array(
            'timeout' => 3,
            'blocking' => true,
            'headers' => array (
                'wpengine-key' => WPE_APIKEY,
            ),
        );
        $resp = wp_remote_post( $url, $args );

        if ( is_wp_error( $resp ) ) {
            error_log( 'something went wrong while purging WP Engine CDN: ' . $resp->get_error_message() );
            return false;
        }

        return true;
    }

    public static function purge_memcached() {
        global $wpe_varnish_servers;

        // Purge the cache of the servers we are connected with.
        wp_cache_flush();

	// If we only want to run here
        if ( ! empty( $_REQUEST['solo'] ) ) {
            return;
        }

        // Purge other servers in our world. Remove ourselves from the list
        $blog_domain = parse_url( home_url(), PHP_URL_HOST );
        $other_servers = array_diff( $wpe_varnish_servers, array( '127.0.0.1', gethostname() ) );
        foreach ( $other_servers as $other_server ) {
		// Tell others to purge object cached.  Tell them to only purge themselves.
                $res = WpeCommon::http_request_async( "GET", $other_server, 80, $_SERVER['HTTP_HOST'], '/?wp-cmd=purge-object-cache&solo=1', array(), 0 );
        }
    }

	// Function for hooks which might pass some arguments, but we want no arguments to the Varnish-cache
	// routine so that the entire Vanish cache for this domain is purged.
	public static function purge_varnish_cache_all() {
		WpeCommon::purge_varnish_cache();
	}

    public static function purge_varnish_cache( $post_id = null, $force = false ) {
        global $wpengine_platform_config;
        global $wpe_varnish_servers;
        global $wpdb;
        $wpe_all_domains = $wpengine_platform_config['all_domains'];
        if (!$wpe_all_domains){
            $wpe_all_domains = array();
        }
        static $purge_counter;

        // Globally disabled?
        if ( defined( 'WPE_DISABLE_CACHE_PURGING' ) && WPE_DISABLE_CACHE_PURGING ) {
            return false;
        }

        // Autosaving never updates published content.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        // If we've purged "enough" times, stop already.
        if ( isset($purge_counter) && $purge_counter > 2 && ! $force ) {
            return false;
        }

        $blog_url       = home_url();
        $blog_url_parts = @parse_url( $blog_url );
        $blog_domain    = $blog_url_parts['host'];

        // If purging everything, $paths and $abspaths are empty.
        $abspaths = array();    // paths to purge exactly; no prefix or suffixes.
        $paths = array();       // path regular expressions to purge.
        $purge_thing = false;
        if ( $post_id && $post_id > 1 && !! ( $post = get_post( $post_id ) ) ) {
            // Certain post types aren't cached so we shouldn't purge
            if ( $post->post_type == 'attachment' || $post->post_type == 'revision' ) {
                return;
            }

            // If the post isn't published, we don't need to purge (draft, scheduled, deleted)
            if ( $post->post_status != 'publish' ) {
                //error_log("purgebail: ".$post->post_status);
                return;
            }

            //error_log("micropurge: $post_id");
            // Determine the set of paths to purge.  If there's no post_id, purge all. Otherwise name the paths.
            $purge_domains = array( $blog_domain );
            $blog_path = WpeCommon::get_path_trailing_slash( @$blog_url_parts['path'] );
            if ( $blog_path == '/' ) {
                $blog_path_prefix = "";
            } else {
                $tpath            = substr( $blog_path, 0, -1 );
                $blog_path_prefix = $tpath . ".*";
            }

            // Always purge the post's own URI, along with anything similar
            $post_parts = parse_url( get_permalink( $post_id ) );
            $post_uri   = rtrim($post_parts['path'],'/')."(.*)";
            if ( ! empty( $post_parts['query'] ) ) {
                $post_uri .= "?" . $post_parts['query'];
            }
            $paths[]    = $post_uri;

            // Purge the v2 WP REST API endpoints
            if ( 'post' === $post->post_type ) {
                $paths[] = '/wp-json/wp/v2/posts';
            }
            else if ( 'page' === $post->post_type ) {
                $paths[] = '/wp-json/wp/v2/pages';
            }

            // Purge the categories & tags this post belongs to
            if ( defined('WPE_PURGE_CATS_ON_PUB') ) {
                foreach ( wp_get_post_categories( $post_id ) as $cat_id ) {
                    $cat     = get_category( $cat_id );
                    $slug    = $cat->slug;
                    $paths[] = "$blog_path_prefix/$slug/";
                }
                foreach ( wp_get_post_tags( $post_id ) as $tag ) {
                    $slug    = $tag->slug;
                    $paths[] = "$blog_path_prefix/$slug/";
                }
            }

            // Purge main pages if we're there.  Can't know for sure, so approximate by saying
            // if it's more than 7 days old it's either not there or has been there for so
            // long that it doesn't matter.
            if ( time() - strtotime( $post->post_date_gmt ) < 60 * 60 * 24 * 7 ) {
                if ( $post->post_type != 'shop_order' ) {
                    $paths[]     = "^${blog_path}\$";
                    $paths[]     = "/feed";
                }
            }
            $purge_thing = $post_id;
        } else {
            $paths[]     = ".*";  // full blog purge
            $purge_thing = true;
            $purge_domains = $wpe_all_domains;
            if ( isset($wpdb->dmtable) ) {
                $rows = $wpdb->get_results( "SELECT domain FROM {$wpdb->dmtable}" );
                foreach ( $rows as $row ) {
                    $purge_domains[] = strtolower($row->domain);
                }
                $purge_domains = array_unique($purge_domains);
            }
        }

        // add on blog domains for this blog
        // this will ensure we are purging in all proper locations
        $purge_domains = array_unique(array_merge($purge_domains, self::get_blog_domains()));

        if ( ! count( $paths ) ) {
            return;  // short-circuit if there's nothing to do.
        }
        // else:
        $paths       = array_unique( $paths );  // allow the code above to be sloppy

        /**
         * The paths to purge in Varnish.
         *
         * @param array $paths   The paths to be purged.
         * @param int   $post_id The requested post_id to purge if one was passed.
         */
        $paths = apply_filters( 'wpe_purge_varnish_cache_paths', $paths, $post_id );

        // At this point, we know we're going to purge, so let's bump the purge counter. This will prevent us from
        // over-purging on a given request.
        // BWD: I'm not sure this is necessary and may actually be harmful, depending on how many updates to a given
        // post can happen within a single request.
        if ( ! isset( $purge_counter ) ) {
            $purge_counter = 1;
        } else {
            $purge_counter++;
        }

        // Purge Varnish cache.
        if ( WPE_CLUSTER_TYPE == "pod" ) {
            $wpe_varnish_servers = array( "localhost" );
        }
        // Ordinarily, the $wpe_varnish_servers are set during apply. Just in case, let's figure out a fallback plan.
        else if ( ! isset( $wpe_varnish_servers ) ) {
            if ( ! defined( 'WPE_CLUSTER_ID' ) || ! WPE_CLUSTER_ID ) {
                $lbmaster            = "lbmaster";
            } else if ( WPE_CLUSTER_ID >= 4 ) {
                $lbmaster            = "localhost"; // so the current user sees the purge
            } else {
                $lbmaster            = "lbmaster-" . WPE_CLUSTER_ID;
            }
            $wpe_varnish_servers = array( $lbmaster );
        }

        // Debugging
        if ( false ) {
            $msg_key = rand();
            $msg     = "Varnishes # $msg_key:\n" . var_export( $wpe_varnish_servers, true ) . "\nDomains:\n" . var_export( $purge_domains, true ) . "\nPaths:\n" . var_export( $paths, true );
            //error_log( "PURGE: $msg" );
        }

        if ( $paths ) {
            $paths_regex = '(' . join( '|', $paths ) . ')';
        }
        if ( $abspaths ) {
            $abspaths = array_map( 'preg_quote', $abspaths );
            $abspaths_regex = '^(' . join( '|', $abspaths ) . ')$';
        }
        if ($paths && $abspaths) {
            $path_regex = "(($abspaths_regex)|($paths_regex))";
        } else if ($paths) {
            $path_regex = $paths_regex;
        } else if ($abspaths) {
            $path_regex = $abspaths_regex;
        } else {
            $path_regex = '.*';
        }

        // Convert the purge domains to PCRE-escaped strings and join them together in an alternation -- purging in
        // blocks of 100 domains. Varnish can't handle an unlimited number. Ordinarily, this will still be one chunk,
        // but for some massive sites, there can be more than 100 domains mapped, unfortunately.
        $hostname = $purge_domains[0];
        $purge_domains = array_map( 'preg_quote', $purge_domains );
        $purge_domain_chunks = array_chunk( $purge_domains, 100 );
        foreach ($purge_domain_chunks as $chunk) {
            $purge_domain_regex = '^(' . join( '|', $chunk ) . ')$';
            // Tell Varnish.
            foreach ( $wpe_varnish_servers as $varnish ) {
                $headers = array( 'X-Purge-Path' => $path_regex, 'X-Purge-Host' => $purge_domain_regex );
                $ok = WpeCommon::http_request_async( 'PURGE', $varnish, 9002, $hostname, '/', $headers, 0 );
                if ( ! $ok ) {
                    error_log("purge_varnish_cache() failed for: ($varnish) #$purge_domain_regex# #$path_regex#");
                }
            }
        }
        return true;
    }

    /**
     * Look up domains that should be purged
     */
    public static function get_blog_domains()
    {
        global $blog_id;
        global $wpdb;

        $blog_domains = array();
        $table_name = $wpdb->base_prefix . 'domain_mapping';
        try {
            if (null != $blog_id) {
                if (is_multisite() && $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                    $rows = $wpdb->get_results("SELECT domain FROM $table_name where blog_id=$blog_id");
                    foreach ($rows as $row) {
                        $blog_domains[] = strtolower($row->domain);
                    }
                    $blog_domains = array_unique($blog_domains);
                }
            }
        } catch(Exception $e){
            // there was a problem accessing the wp_domain_mapping table
            // since we cannot access any rows with domains, return an empty array
            error_log("There was a problem accessing the domain table: {$e->getMessage()}");
            return array();
        }
        return $blog_domains;
    }

    /**
     * Creates an HTTP request to a remote host, but doesn't wait for the result.
     * @param method HTTP method, e.g. "GET" or "POST"
     * @param domain server to hit, e.g. "api.foobar.com"
     * @param port e.g. 80
     * @param hostname string to use for the "Host" header on the target machine (null to copy $domain)
     * @param url e.g. "/v2/do_thing?apikey=1234"
     * @param wait_ms time to wait for the request to get going, since it will be destroyed when the connection ends
     * @return true on "success". (No errors detected.)
     */
    public static function http_request_async( $method, $domain, $port, $hostname, $uri, $extra_headers = array( ), $wait_ms = 100 ) {
        //don't do anything is on staging
        if (is_wpe_snapshot()) {
            return false;
        }

        // Construct the URL so that we can invoke the request with cURL.
        $protocol = 'http';
        if ( 443 == $port ) {
            $protocol = 'https';
        }
        $url = "$protocol://$domain:$port$uri";

        // Callers might not specify hostname if it doesn't differ from $domain:
        if (!$hostname) {
            $hostname = $domain;
        }

        $headers = array("Host: $hostname");
        foreach ($extra_headers as $k => $v) {
            $headers[] = "$k: $v";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($wait_ms > 0) {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $wait_ms);
        }
        $result = curl_exec($ch);
        if ($wait_ms > 0) {
            // Don't care what the result is in this case...
            curl_close($ch);
            return true;
        }
        // else:
        if (false === $result) {
            curl_close($ch);
            return false;
        }
        // else:
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (400 <= $httpCode) {
            return false;
        }
        // else:
        return true;
    }

    public function ssl_login_filter( $login_url, $redirect = '' ) {
        return preg_replace(
                        "#\\bhttp(://|%3A%2F%2F)#", "https\$1", $login_url
        );
    }

    public function httphead( $template ) {
        if ( $_SERVER['REQUEST_METHOD'] == 'HEAD' )
            return false;

        return $template;
    }

    private static function get_path_trailing_slash( $path ) {
        if ( substr( $path, -1 ) != '/' )
            return $path . '/';
        return $path;
    }

    public function get_wp_version($version_file) {
        // Checking the current site version
        if ( ! file_exists( $version_file ) ) {
            // couldn't find version file
            return false;
        }

	    //parse version file for version information
        $version = preg_find( "#\\\$wp_version\\s*=\\s*['\"]([^'\"]+)['\"];#ms", file_get_contents($version_file) );

        return $version;
    }


}

/**
 * sets the comment cookie lifetime to 3 minutes instead of one year
 *
 * @return int comment cookie expiration time in seconds from now
 * @author SO
 **/
function set_lower_comment_cookie_lifetime($content) {
	return 180;
}
add_filter('comment_cookie_lifetime', 'set_lower_comment_cookie_lifetime');

// Create an instance to get all our hooks installed
$wpe_common = WpeCommon::instance();

add_action( 'plugins_loaded', array( $wpe_common, 'real_ip' ) );
$wpe_ssl_admin = defined( 'WPE_FORCE_SSL_LOGIN' ) && WPE_FORCE_SSL_LOGIN;
if ( $wpe_ssl_admin ) {
    add_filter( 'login_url', array( $wpe_common, 'ssl_login_filter' ) );
}

/*
Purge Varnish for a specific post and related URLs when something happens to it.
NOTE: Please do not add `clean_post_cache` hook back in here. In the event that revisions are on, this can cause
an entire site's page caching to purge due to the shuffling that happens when at the revision limit.
*/
foreach ( array( 'trashed_post', 'delete_post', 'edit_post', 'publish_page', 'publish_post', 'save_post' ) as $hook )
    add_action( $hook, array( $wpe_common, 'purge_varnish_cache' ) );

// Purge Varnish for the entire domain
foreach ( array( 'bp_blogs_new_blog' ) as $hook )
    add_action( $hook, array( $wpe_common, 'purge_varnish_cache_all' ) );

// Purge database cache when something happens that doesn't use the WordPress API to purge it properly
foreach ( array('signup_finished','bp_core_clear_cache','bp_blogs_new_blog') as $hook )
	add_action( $hook, array( $wpe_common, 'purge_memcached' ) );

// Add missing functions from other plugins
if ( ! function_exists( 'apc_clear_cache' ) ) {

    function apc_clear_cache() { /* do nothing; APC is not supported */
    }

}

//single sign on
if(isset($_REQUEST['wpe_token'])) {
	setcookie('wpengine_no_cache',$_GET['wpe_token'],60);
	add_action('wp',array($wpe_common,'wpe_sso'));
}


///////////////////////////////////////////////
// Control the query
///////////////////////////////////////////////
add_filter('query', 'wpe_filter_query',999);
function wpe_filter_query( $sql ) {
	global $wpe_common;

	// Disallow ORDER BY RAND().  It trashes large sites.  Several plugins do it.
	if ( isset($wpe_common) && strpos($sql,"ORDER BY RAND()") && ! $wpe_common->is_rand_enabled() ) {
		$sql = str_replace("ORDER BY RAND()","ORDER BY 1",$sql);
	}

	// Add debugging information to the query so it shows up in MySQL logs
	if ( !empty( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
		// Build the strings we want
		$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$bt = ec_get_non_core_backtrace();
		if ( count($bt) > 0 )
			$stack = $bt[0]["file"] . ":" . $bt[0]["line"];
		else
			$stack = "N/A";
		// Build the comment and escape special characters
		$comment = "From [$url] in [$stack]";
		$comment = str_replace( '*', '-*-', $comment );
		// Append to query
		$sql .= ' /* ' . $comment . ' */';
	}

	// Finished.
	return $sql;
}

/*
 * Start an output buffer to cache a chuck of the theme.
 * @package wpengine-common
 * @param string $key Unique key to identify chunk in the cache
 * @param string $group Cache group indentifier
 * @param int $ttl time to pass before cache expires
 * @todo move to separate file or class
 *
 */

function wpe_static_start($key,$group,$ttl) {
        global $wpe_statics;
        //setup empty array
        if(!$wpe_statics) $wpe_statics = array();

        if(!$output = wp_cache_get($key,$group)) {
                echo '<!--wpereader-->';
                ob_start();
                $wpe_statics[$key] = array('group'=>$group,'ttl'=>$ttl);
        } else {
                echo $output;
        }
}

/*
 * End the output buffer and cache the object
 * @package wpengine-common
 * @param string $key Unique key to identify chunk in the cache. This should match the preceding instance of wpe_static_start()
 * @todo move to separate file or class
 *
 */

function wpe_static_end($key) {
        global $wpe_statics;

        if(!empty($wpe_statics[$key])) {
                echo '<!--delivered-->';
                $output = ob_get_contents();
                ob_end_clean();
                wp_cache_set($key,$output,@$wpe_statics[$key]['group'],@$wpe_statics[$key]['ttl']);
                echo $output;
        } else {
                return;
        }
}

//define("WPE_DB_DEBUG",true);
if(defined('WPE_DB_DEBUG') AND @WPE_DB_DEBUG != false) {
	include_once(dirname(__FILE__).'/db.php');
}

// Force the blog to be private when viewing the staging site.
if( is_wpe_snapshot() ) {
    add_action( 'pre_option_blog_public', '__return_zero' );
}

// Finds the first occurrance of the given pattern in the subject and returns the match.
// If there is a grouping element, returns just the content of the group, otherwise returns
// the entire match.
// If the pattern doesn't match, returns FALSE.
function preg_find( $pattern, $subject )
{
    if ( ! preg_match( $pattern, $subject, $match ) )
        return FALSE;
    if ( count($match) == 1 )       // no group; return the entire match
        return $match[0];
    return $match[1];       // return first group
}

class WPE_Query_Governator
{
    // this was 1024, but the logs were so so so noisey
    const QUERY_LENGTH_WARN = 16384;

    const QUERY_LENGTH_MAX = 16384;

    /**
     * The single instance of this class.
     *
     * @var WPE_Query_Governator
     */
    protected static $instance = null;

    /**
     * Get the single instance of this class.
     *
     * @return WPE_Query_Governator The instance of this class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        add_filter( 'query', array( $this,'check_and_govern' ) );
    }

    public function check_and_govern( $query )
    {
        if ( defined( 'WPE_GOVERNOR' ) && ! WPE_GOVERNOR ) {
            return $query;
        }
        $query_length = strlen($query);

        if ( self::QUERY_LENGTH_WARN > $query_length || ! preg_match( '#^\s*select#i', $query ) ) {
            return $query;
        }

        // Get backtrace
        $backtrace = ec_get_non_core_backtrace();

        // Add backtrace info if we got anything
        $backtrace_info = empty( $backtrace ) ? '' : sprintf( ' in %s:%s', $backtrace[0]['file'], $backtrace[0]['line'] );

        // Log the error
        $log_this_message = sprintf( '%s QUERY (%d characters long generated%s): %s', ( $query_length > self::QUERY_LENGTH_MAX ? 'KILLED' : 'LONG' ), $query_length, $backtrace_info, $query );
        error_log( $log_this_message );

        if ( $query_length > self::QUERY_LENGTH_MAX ) {
            return '';
        }
        return $query;
    }
}
WPE_Query_Governator::get_instance();

/*
 * Make failed logins return `403` so either `badboyz` or `fail2ban`
 * can pick them up and ban frequent offenders.
 *
 */
function wpe_login_failed_403() {

	// Don't 403 when the login comes through an Ajax request
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	status_header( 403 );
}
add_action( 'wp_login_failed', 'wpe_login_failed_403' );

add_filter( 'heartbeat_send', 'wpe_heartbeat_settings', 10, 2 );
/**
 * Adjust the heartbeat interval
 *
 * Our new default interval is 60 seconds, but this can be overridden with the
 * WPE_HEARTBEAT_INTERVAL constant.
 *
 * "There's a big difference between mostly dead and all dead.
 *  Mostly dead is slightly alive." - Miracle Max
 *
 * @param array|object $response The Heartbeat response object or array.
 * @param string $screen_id The screen id.
 */
function wpe_heartbeat_settings( $response, $screen_id ) {
	if ( ! defined( 'WPE_HEARTBEAT_INTERVAL' ) ) {
		define( 'WPE_HEARTBEAT_INTERVAL', 60 );
	}

	// Ensure we're working with an array, not an object
	$response = (array) $response;

	// Change the hearbeat interval if it is numeric and less than our value, OR if it's NOT numeric (e.g. "fast")
	$change_interval = (
		( is_numeric( $_POST['interval'] ) && $_POST['interval'] <= WPE_HEARTBEAT_INTERVAL )
		|| ! is_numeric( $_POST['interval'] )
	);

	if ( $change_interval ) {
		$response['heartbeat_interval'] = WPE_HEARTBEAT_INTERVAL;
	}

	return $response;
}

require_once( dirname( __FILE__ ) . '/class.powered-by-widget.php' );

// Register wpe_powered_by_widget widget.
function wpe_register_powered_by_widget() {
	register_widget( 'WPE_Powered_By_Widget' );
}
add_action( 'widgets_init', 'wpe_register_powered_by_widget' );

/**
 * Logic to support the old way to unregister widgets. Our customers could be
 * hiding our widget using wp_unregister_sidebar_widget and the new widget would
 * still show up. To prevent this we need to hook wp_unregister_sidebar_widget and
 * hide our widget when it's called.
 */
function wpe_unregister_powered_by_widget ( $id ) {
	// Old widget ID.
	if ( 'wpe_widget_powered_by' !== $id ) {
		return;
	}

	// Unregister the new widget.
	unregister_widget( 'WPE_Powered_By_Widget' );
}
add_action( 'wp_unregister_sidebar_widget', 'wpe_unregister_powered_by_widget' );

require_once(__DIR__."/dify-widget.php");

require_once( dirname( __FILE__ ) . '/class.filterplugin.php' );

function wpe_hook_plugin_api_response() {
	new \wpe\plugin\FilterPlugin( 'content-performance', 'https://s3.amazonaws.com/wpengine-wordpress-plugins/content-performance/production/manifest.json' );
}

add_action( 'admin_init', 'wpe_hook_plugin_api_response' );

// Prevent weird problems with logging in due to Object Caching
// example: password has been changed, but Object Cache still holds old password, and therefore prevents login
if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
    add_filter( 'wp_authenticate_user', 'wpe_refresh_user' );
    function wpe_refresh_user( $user ) {
        wp_cache_delete( $user->user_login, 'userlogins' );
        return get_user_by( 'login', $user->user_login );
    }
}
