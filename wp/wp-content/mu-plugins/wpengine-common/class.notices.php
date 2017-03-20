<?php
/**
 * Notices
 *
 */

if( ! defined('WPE_MESSAGES_INTERVAL') )
	define("WPE_MESSAGES_INTERVAL", '3600');

class Wpe_Notices extends WpeCommon {

    var $notices = null;

    function __construct() {
	$this->notices = NULL;
	$this->hooks();
    }

    function __destruct() {
    }

    function hooks() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );

        if ( is_multisite() ) {
        	add_action('network_admin_notices',array( $this, 'display_notices' ) );
        }

        //ajax hook
        add_action('wp_ajax_remove_notice',array($this,'_do_ajax'));
    }


    // Ensure we have notices loaded from our own API.
    // Does nothing if notices are already loaded.
    function load() {
    		if(function_exists('wp_get_current_user'))
    			$current_user = wp_get_current_user();

				// If already loaded notices from cache or remote and stored in local variable, don't do again.
        if ( $this->notices and is_array($this->notices) ) {
        		$this->notices['read'] = get_user_meta($current_user->ID,'wpe_notices_read',true);
            return $this->notices;
				}

        // Don't make this request when in staging.
        if ( is_wpe_snapshot() )
            return array( );


				$this->go_get_em();
        return $this->notices;
    }

		function go_get_em () {
			$current_user = wp_get_current_user();
			$this->notices = get_option('wpe_notices', array());
			$notices_ttl = get_option('wpe_notices_ttl', false);

			//debugging: forces a check
			if( isset($_REQUEST['debug-notice']) ) {
				delete_option('wpe_notices');
				delete_user_meta($current_user->ID,'wpe_notices_read');
				$notices_ttl = false;
			}

			// If TTL is more then interval from now, it's bogus, so kill it.
			if ( $notices_ttl > (time()+WPE_MESSAGES_INTERVAL) )
				$notices_ttl = false;

			// If have notice from cache and it's within TTL, then do nothing else.
			// If TTL has not expired, leave
			if ( $notices_ttl && $notices_ttl >= time() )
				return;

			// Clear notices we have, and we'll attempt to refresh it from the server
			// Save the notices that we've already read.

			$this->notices['read'] = get_user_meta($current_user->ID,'wpe_notices_read',true);

			$seen = array();
			if ( isset($this->notices['read']) )
				$seen = $this->notices['read'];

			$this->notices['read'] = $seen;

			update_option('wpe_notices', $this->notices);
			$expire = time() + WPE_MESSAGES_INTERVAL;
			update_option('wpe_notices_ttl', $expire);
	}

    /**
     * Print the notices
    **/
    function display_notices() {

	//a hook to allow other parts of the plugin to leaverage this
	do_action('wpe_notices', $this);

        global $current_user;

	//make sure we have notices
        $this->load();

        $this->notices['read'] = get_user_meta($current_user->ID,'wpe_notices_read',true);

	// Leave immediately if nothing to do
        if ( ! is_array( $this->notices ) OR empty($this->notices['messages']) )
            return false;

        foreach ( $this->notices['messages'] as $notice ) {

					//if this message isn't forced then lets validate it's time/date
					//@internal this check allows us to hijack the message feature elsewhere in the plugin
					if($notice['force'] != 1)
					{
						if($notice == -1)
							continue;
						if ( ! isset($notice['starts']) || strtotime($notice['starts']) > time() )
							continue;
						if ( ! isset($notice['ends']) || strtotime($notice['ends']) <= time() )
							continue;
					}

					//check this for all notices
					if ( @in_array($notice['id'],$this->notices['read']) )
						continue;

					switch($notice['type']) {
						case 'normal':
							$notice['icon'] =  WPE_PLUGIN_URL.'/images/window-close.png';
							$this->view('admin/notice',$notice);
							add_action('admin_print_footer_scripts',array($this,'footer_scripts'));
						break;
						case 'sticky':
							$this->view('admin/notice-sticky',$notice);
						break;
						case 'lockdown':
							$this->view('admin/notice-sticky',$notice);
						break;
					}

				}
    }

    function footer_scripts() {
    	?>
    	<script>
    		jQuery.noConflict();
				jQuery('img#dismiss-it').click(function() {
					var notice = jQuery(this).parent().parent();
					var id = notice.attr('title');
					jQuery.post(ajaxurl,{action: 'remove_notice','id': id});
					notice.ajaxStop(function() { notice.fadeOut(); });
				});
    	</script>
    	<?php
    }

	function _do_ajax() {
		extract($_REQUEST);
		$current_user = wp_get_current_user();

		$read = get_user_meta($current_user->ID,'wpe_notices_read',true);

		// If already selected to not see this message, do nothing
		if ( @in_array($id,$read) ) {
			return false;
		}
		$read[] = $id;
		update_user_meta($current_user->ID,'wpe_notices_read', $read);
		return false;
	}
}
