<?php
/* 
 * Class for API interaction
 *	 
 */
if(!defined('ABSPATH')) { wp_die('No direct access allowed!'); }

if ( ! class_exists('WpeDiskUsage') ) :
class WpeDiskUsage 
{
	const CACHE_TTL = 360000;	// 100 hours
	public function get ()
	{
		global $blog_id;
		$usage = $this->the_data();
		if ( ! $usage || ! is_array($usage) ) return 0;
		if ( isset($usage[$blog_id]['kbytes']) )
			return $usage[$blog_id]['kbytes'];
		else if ( 1 == $blog_id ) // if blog_id not in our data, return the top level number.
			return @$usage[0]['kbytes'];
		else
			return 0;
	}
	private function the_data ()
	{
		$usage = get_site_option('wpe_upload_space_usage');
		$ttl = get_site_option('wpe_upload_space_usage_ttl');

		// If TTL is more then interval from now, it's bogus, so kill it.
		if ( $ttl > (time() + self::CACHE_TTL) )
			$ttl = false;

		// If have from cache and it's within TTL, then do nothing else.
		// If TTL has not expired, leave
		if ( $ttl && $ttl >= time() )
			return $usage;

		// Get the value and save it in the cache
		$usage_remote = $this->from_remote();

		$expire = time() + self::CACHE_TTL;
		// If did not have an initial value
		if ( false === $usage ) {
			add_site_option( 'wpe_upload_space_usage', $usage_remote );
			add_site_option( 'wpe_upload_space_usage_ttl', $expire );
		} else {
			update_site_option( 'wpe_upload_space_usage', $usage_remote );
			update_site_option( 'wpe_upload_space_usage_ttl', $expire );
		}
		return $usage_remote;
	}
	// Pull the data from our API.
	private function from_remote ()
	{
		$data = array();
		$url = 'https://api.wpengine.com/1.2/?method=disk-usage&account_name=' . PWP_NAME . '&wpe_apikey='.WPE_APIKEY.'&blog_id=all';
		$http = new WP_Http;
		$msg  = $http->get( $url );
		if ( ! is_a( $msg, 'WP_Error' ) && isset( $msg['body'] ) ) {
			$data = json_decode( $msg['body'], TRUE );
		}
		return $data;
	}
}
endif;

class WpeAPINotices extends WpeAPI
{
	public $notices;
	function __construct($args = array()) {
		parent::__construct($args);
		$this->notices = array();
	}
	function get() {
		$mine = $this->go(WPE_APIKEY);
		$this->add($mine);
		$cluster = $this->go('C'.WPE_CLUSTER_ID);
		$this->add($cluster);
		$g = $this->go('ALL');
		$this->add($g);
		return $this->notices;
	}
	function go($file) {
		$this->request_uri = 'https://messages.wpengine.s3.amazonaws.com/'.$file;
		$response = @file_get_contents($this->request_uri);
		if ( ! $response )
			return false;
		$response = json_decode( trim($response), true );
		if ( NULL === $response )
			return false;
		return $response;
	}
	function add($b) {
		if ( is_array($b) )
			$this->notices = array_merge($this->notices,$b);
	}
}

class WpeAPI extends Wp_Http {
	
	public $request_uri = 'https://api.wpengine.com/1.2/index.php';
	public $args = array();	
	public $resp = '';
	public $is_error;
	public $timeout = 10;

	function __construct($args = array()) {
		
		
		//set some defaults
		$defaults = array(
			'account_name'=> PWP_NAME,
			'wpe_apikey'=> WPE_APIKEY
		);
		
		$this->args = $defaults;

		//merge args passed to class 
		if(!empty($args)) {
			$this->args = array_merge($this->args,$args);
		}

		add_filter('http_request_timeout',array($this,'get_timeout'));

	}
	
	function get_timeout() {
                return $this->timeout;
        }
	
	function setup_request($method='GET') {
		if(empty($this->args['method'])) {
			return new WP_Error('error',"Please specify a method for this request.");
		} else {
			if( 'GET' == $method ) 
			{
				if(count($this->args) > 0) {
					foreach($this->args as $k=>$v) {
							if(!empty($v))
								$this->request_uri = add_query_arg(array($k=>$v),$this->request_uri);
					}
				}
			} 
		}
		return null;
	}
	
	function get($args = false) {
		$this->setup_request();
		$this->resp = parent::get($this->request_uri,$args);
		return $this;
	}
	
	function post($args = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->request_uri);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->args);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: api.wpengine.com"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$this->resp = curl_exec($ch);
		return $this;
	}
	
	function set_arg($arg,$value) {
		$this->args[$arg] = $value;
		return null;
	}
	
	function get_arg($arg) {
		if(!empty($this->args[$arg])) {
			return $this->args[$arg];
		} else {
			return false;
		}
	}
	
	function message() {
		$array = json_decode($this->resp['body']); 
		return $array->error_msg;
	}
	
	function set_notice($notice = null) {
		if(!empty($notice)) { $this->resp = new WP_Error('error',$notice); }
		if(is_network_admin()) {
			add_action('network_admin_notices',array($this,'render_notice'));
		} else {
			add_action('admin_notices',array($this,'render_notice'));
		}
	}
		
	function render_notice() {
		if(!is_wp_error($this->resp)) {		
			$notice = json_decode($this->resp['body']); 
			if($this->is_error OR $this->is_error() ) {
				$notice = array('code'=> $notice->error_code,'message'=>$notice->error_msg);
			} else {
				$notice = array('code'=>'updated','message'=>$notice->message);
			}?>
			<div id="message" class="<?php echo $notice['code']; ?>"><p><?php echo $notice['message']; ?></p></div>
			<?php
		} else {
			?><div id="message" class="error"><p><?php echo $this->resp->get_error_message(); ?></p></div>
			<?php
		}
	}
	
	function is_error() {
		if(!is_wp_error($this->resp)) {
			$error = $this->resp['body'];
			$error = json_decode($error);
			if(@$error->error_code == 'error') {
				return $error->error_msg;
				$this->is_error = 1;
			} else {
				return false;
			}
		} else {
			return $this->resp->get_error_message();
		}
	}
	
	function __destruct() {
		
	}
	
} 
