<?PHP
/*
Plugin Name: Stop Spammers Spam Control
Plugin URI: http://wordpress.org/plugins/stop-spammer-registrations-plugin/
Description: The Stop Spammers Plugin blocks spammers from leaving comments or logging in. Protects sites from robot registrations and malicious attacks.
Version: 6.15
Author: Keith P. Graham

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
// networking requires a couple of globals

define('KPG_SS_VERSION', '6.15');
define( 'KPG_SS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KPG_SS_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
define( 'KPG_SS_PLUGIN_DATA', plugin_dir_path( __FILE__ ).'data/' );
$kpg_check_sempahore=false; 


if (!defined('ABSPATH')) exit;

// hook the init event to start work.

add_action('init','kpg_ss_init',0); 
// dummy filters for addons
add_filter('kpg_ss_addons_allow','kpg_ss_addons_d',0);
add_filter('kpg_ss_addons_deny','kpg_ss_addons_d',0);
add_filter('kpg_ss_addons_get','kpg_ss_addons_d',0);
// done - the reset will be done in the init event
/*******************************************************************
How it works:
1) Network blog MU installation
	if networked switch then install network filters for options 
		all options will redirect to Blog #1 options so no need to setup for each blog.
	else 
		normal install
2) Case when user is logged in.
	setup User and comment actions
	if networked:
		setup right-now and options to install only on Network Admin page.
	else 
		install hooks and filters for right-now options screens
3) When user is not logged in
	hook template redirect
	hook init
4) on template redirect check for bad requests and 404s on exploit items
5) on init check for POST or GET 
6) on post gather post variables and check for spam, logins or exploits
7) on get check for access blocking
8) on deny
	update counters
	update cache
	update log
	present rejection screen - could contain email request form or captcha
9) on email request form send email to admin requesting Allow List.
10 on captcha success add to good cache, remove from bad cache, update counters, log success.

*/

function kpg_ss_init() {
	remove_action('init','kpg_ss_init');
	add_filter( 'pre_user_login', 'kpg_ss_user_reg_filter', 1, 1 );
	// incompatible with a jetpack submit
	if ($_POST!=null&&array_key_exists('jetpack_protect_num',$_POST)) return;
	// emember trying to log in - disable plugin for emember logins.
	if (function_exists('wp_emember_is_member_logged_in')) { 
		// only emember function I could find after 30 econds of googling.
		if (!empty($_POST)&&array_key_exists('login_pwd',$_POST)) return;
	}

	// set up the akismet hit
	add_action('akismet_spam_caught','kpg_ss_log_akismet'); //hook akismet spam
	$muswitch='N';
	
	

	if (function_exists('is_multisite') && is_multisite()) {
		$muswitch='Y';
		// check the muswitch option
		$muswitch='Y';
		switch_to_blog(1);
		// get the mu option
		$muswitch=get_option('kpg_muswitch');
		if (empty($muswitch)) $muswitch='Y'; // by default we operate in network mode with blog(1) being the main.
		if ($muswitch!='N') $muswitch='Y'; 
		restore_current_blog();
		if ($muswitch=='Y') {
			// install the hooks for options
			define('KPG_SS_MU', $muswitch);
			kpg_sp_require('includes/ss-mu-options.php');
			kpg_ssp_global_setup();
		}
	} else {
		define('KPG_SS_MU', $muswitch);
	}
	

	if (function_exists('is_user_logged_in')) { 
		// check to see if we need to hook the settings
		// load the settings if logged in
		if(is_user_logged_in()) {
			remove_filter( 'pre_user_login', 'kpg_ss_user_reg_filter', 1);
			if(current_user_can('manage_options')) {
				kpg_sp_require('includes/ss-admin-options.php');
				return;
			}
		}
	}	
	// user is not logged in. We can do checks.
	// add the new user hooks
	global $wp_version;
	if(!version_compare($wp_version, "3.1", "<")) { // only in newer versions
		add_action('user_register', 'kpg_new_user_ip');
		add_action('wp_login', 'kpg_log_user_ip', 10, 2);
	}
	// don't do anything else if the emember is logged in
	if (function_exists('wp_emember_is_member_logged_in')) { 
		if (wp_emember_is_member_logged_in()) return;
	}
    // can we check for $_GET registrations?
	if (isset($_POST) && !empty($_POST)) {
		// see if we are returning from a deny
		if (array_key_exists('kpg_deny',$_POST)&&array_key_exists('kn',$_POST )) {
			//deny form hit
			$knonce=$_POST['kn'];
			if (!empty($knonce)&&wp_verify_nonce($knonce,'kpg_stopspam_deny')) {
				//call the checker program
				sfs_errorsonoff();
				$options=kpg_ss_get_options();
				$stats=kpg_ss_get_stats();
				$post=get_post_variables();
				be_load('kpg_ss_challenge',kpg_get_ip(),$stats,$options,$post);
				// if we come back we continue as normal
				sfs_errorsonoff('off');
				return; // 
			}
			
		}
		// need to check that we are not Allow Listed.
		// don' check if ip is google, etc
		// check to see if we are doing a post with values
		$post=get_post_variables();
		if (!empty($post['email']) || !empty($post['author'])|| !empty($post['comment'])) { // must be a login or a comment which require minimum stuff 
			//remove_filter( 'pre_user_login', kpg_ss_user_reg_filter, 1);
			//sfs_debug_msg('email or author '.print_r($post,true));
			$reason=kpg_ss_check_white();
			if($reason!==false) {
				//sfs_debug_msg("return from white $reason");
				return;	
			}
			//sfs_debug_msg('past white ');
			kpg_ss_check_post(); // on POST check if we need to stop comments or logins
		} else {
			//sfs_debug_msg('no email or author '.print_r($post,true));
		}
	} else {
		// this is a get - check for get addons
		$addons=array();
		$addons=apply_filters('kpg_ss_addons_get',$addons);
		// these are the allow before addons
		// returns array 
		//[0]=class location,[1]=class name (also used as counter),[2]=addon name,
		//[3]=addon author, [4]=addon description
		if (!empty($addons)&&is_array($addons)) {
			foreach($addons as $add) {
				if (!empty($add)&&is_array($add)) {
					$options=kpg_ss_get_options();
					$stats=kpg_ss_get_stats();
					$post=get_post_variables();
					$reason=be_load($add,kpg_get_ip(),$stats,$options);
					if ($reason!==false) {
						// need to log a passed hit on post here.
						remove_filter( 'pre_user_login', kpg_ss_user_reg_filter, 1);
						kpg_ss_log_bad(kpg_get_ip(),$reason,$add[1],$add);					
						return;
					}
				}
			}
		}
	}
	add_action( 'template_redirect', 'kpg_ss_check_404s' );// check missed hits for robots scanning for exploits.
	add_action('kpg_stop_spam_caught','kpg_caught_action',10,2); // hook stop spam  - for testing 
	add_action('kpg_stop_spam_OK','kpg_stop_spam_OK',10,2); // hook stop spam - for testing 

}
// start of loadable functions

function kpg_sp_require($file) {
	require_once($file);
}




/************************************************************
*  function kpg_sfs_check_admin()
* Checks to see if the current admin can login
*************************************************************/
function kpg_sfs_check_admin() {
	kpg_sfs_reg_add_user_to_allowlist(); // also saves options
}
function kpg_sfs_reg_add_user_to_allowlist() {
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	return be_load('kpg_ss_addtoallowlist',kpg_get_ip(),$stats,$options);
}
function kpg_ss_set_stats(&$stats,$addon=array()) {
	// this sets the stats
	if (empty($addon)||!is_array($addon)) {
		// need to know if the spam count has changed
		if ($stats['spcount']==0 || empty($stats['spdate'])) {
			$stats['spdate']=date('Y/m/d',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		}
		if ($stats['spmcount']==0 || empty($stats['spmdate'])) {
			$stats['spmdate']=date('Y/m/d',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		}
	} else {
		// update addon stats
		// addon stats are kept in addonstats array in stats
		$addonstats=array();
		if (array_key_exists('addonstats',$stats)) {
			$addonstats=$stats['addonstats'];
		}
		$addstats=array();
		if (array_key_exists($addon[1],$addonstats)) {
			$addstats=$addonstats[$addon[1]];
		} else {
			$addstats=array(0,$addon);
		}
		$addstats[0]++;
		$addonstats[$addon[1]]=$addstats;
		$stats['addonstats']=$addonstats;
	}
	// other checks? - I might start compressing this, since it can get large.
	update_option('kpg_stop_sp_reg_stats',$stats);
}
function kpg_ss_get_now() {
	// to use a safe date everywhere.
	if (function_exists('date_default_timezone_set')) {
		date_default_timezone_set ('UTC'); // WP is a UTC base date. if default tz is not set this fixes it.
	}
	$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));

}
function kpg_ss_get_stats() {
	$stats=get_option('kpg_stop_sp_reg_stats');
	if (!empty($stats) && is_array($stats) &&array_key_exists('version',$stats) && $stats['version']==KPG_SS_VERSION) return $stats;
	return be_load('kpg_ss_get_stats','');
}
function kpg_ss_get_options() {
	$options=get_option('kpg_stop_sp_reg_options');
	$st=array();
	if (!empty($options) && is_array($options) &&array_key_exists('version',$options) && $options['version']==KPG_SS_VERSION) return $options;
	return be_load('kpg_ss_get_options','');
}
function kpg_ss_set_options($options) {
	update_option('kpg_stop_sp_reg_options',$options);
}
function kpg_get_ip() {
	$ip=$_SERVER['REMOTE_ADDR'];
	return $ip;
}
function kpg_ss_admin_menu() {
	if (!function_exists('kpg_ss_admin_menu_l')) kpg_sp_require('settings/settings.php');
	sfs_errorsonoff();
	kpg_ss_admin_menu_l();
	sfs_errorsonoff('off');
}
function kpg_ss_check_site_get() {
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	sfs_errorsonoff();
	$ret=be_load('kpg_ss_check_site_get',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff('off');
	return $ret;
}
function kpg_ss_check_post() {
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	$ret=be_load('kpg_ss_check_post',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff('off');
	return $ret;
}
function kpg_ss_check_404s() { // check for exploits on 404s
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$ret=be_load('kpg_ss_check_404s',kpg_get_ip(),$stats,$options);
	sfs_errorsonoff('off');
	return $ret;

}
function kpg_ss_log_bad($ip,$reason,$chk,$addon=array()) {
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	$post['reason']=$reason;
	$post['chk']=$chk;
	$post['addon']=$addon;
	return be_load('kpg_ss_log_bad',kpg_get_ip(),$stats,$options,$post);
}

function kpg_ss_log_akismet() {
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	if ($options['chkakismet']!='Y') return false;
	// check white lists first
	$reason=kpg_ss_check_white();
	if ($reason!==false) return;
	// not on allow lists
	$post=get_post_variables();
	$post['reason']='from Akismet';
	$post['chk']='chkakismet';
	$ansa= be_load('kpg_ss_log_bad',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff('off');
	return $ansa;
}
function kpg_ss_log_good($ip,$reason,$chk,$addon=array()) {
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	$post['reason']=$reason;
	$post['chk']=$chk;
	$post['addon']=$addon;
	return be_load('kpg_ss_log_good',kpg_get_ip(),$stats,$options,$post);
}
function kpg_ss_check_white() { 
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	$ansa= be_load('kpg_ss_check_white',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff('off');
	return $ansa;
	
}
function kpg_ss_check_white_block() { // ??
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	$post=get_post_variables();
	$post['block']=true;
	$ansa= be_load('kpg_ss_check_white',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff('off');
	return $ansa;
	
}

function be_load($file,$ip,&$stats=array(),&$options=array(),&$post=array()) {
	// all classes have a process() method.
	// all classes have the same name as the file being loaded
	// only executes the file if there is an option set with value 'Y' for the name.
	if (empty($file)) {
		return false;
	}
	// load the be_module if does not exist
	if (!class_exists('be_module')) {
		require_once('classes/be_module.class.php');
	} 
	//if ($ip==null) $ip=kpg_get_ip();
	// for some loads we use an absolute path
	// if it is an addon, it has the absolute path to the be_module
	if (is_array($file)) { // addons pass their array
		// this is an absolute location so load it directly

		if (!file_exists($file[0])) {
			sfs_debug_msg('not found '.print_r($add,true));
			return false;
		}
		//require_once($file[0]);
		// this loads a be_module class
		$class=new $file[1]();
		$result=$class->process($ip,$stats,$options,$post);
		$class=null;
		unset($class); // doesn't do anything
		//memory_get_usage(true); // force a garage collection
		return $result;
	}
	
	$ppath=plugin_dir_path( __FILE__ ).'classes/';
	$fd=$ppath.$file.'.php';
	$fd=str_replace("/",DIRECTORY_SEPARATOR,$fd); // windows fix
	if (!file_exists($fd)) {
		//echo "<br><br>Missing $file $fd<br><br>";
		$ppath=plugin_dir_path( __FILE__ ).'modules/';
		$fd=$ppath.$file.'.php';
		$fd=str_replace("/",DIRECTORY_SEPARATOR,$fd); // windows fix
	}
	if (!file_exists($fd)) {
		$ppath=plugin_dir_path( __FILE__ ).'modules/countries/';
		$fd=$ppath.$file.'.php';
		$fd=str_replace("/",DIRECTORY_SEPARATOR,$fd); // windows fix
	}
	if (!file_exists($fd)) {
		echo "<br><br>Missing $file $fd<br><br>";
		return false;
	}
	require_once($fd);
	// this loads a be_module class
	//sfs_debug_msg("loading $fd");
	$class=new $file();
	$result=$class->process($ip,$stats,$options,$post);
	$class=null;
	unset($class); //does nothing - take out
	return $result;
}
// this should be moved to a dynamic load, perhaps. It is one of the most common things

function get_post_variables() {
	// for wordpress and other login and comment programs.
	// need to find: login password comment author email
	// copied from stop spammers plugin
	// made generic so it also checks "head" and "get" (as well as cookies
	$p=$_POST;
	$ansa=array(
	'email'=>'',
	'author'=>'',
	'pwd'=>'',
	'comment'=>'',
	'subject'=>'',
	'url'=>''
	);
	if (empty($p) || !is_array($p)) {
		return $ansa;
	}
	$search=array(
	'email'=>array('user_email','email','address'), // 'input_' = woo forms
	'author'=>array('author','name','user_login','signup_for','log','user','name','_id'),
	'pwd'=>array('psw','pwd','pass','secret'),
	'comment'=>array('comment','message','body','excerpt'),
	'subject'=>array('subj','topic'),
	'url'=>array('url','blog_name','blogname')
	);
	$emfound=false;
	// rewrite this
	foreach ($search as $var=>$sa) {
		foreach ($sa as $srch) {
			foreach($p as $pkey=>$pval) {
				// see if the things in $srch live in post
				if (stripos($pkey,$srch)!==false) {
					// got a hit
					if (is_array($pval)) $pval=print_r($pval,true);
					$ansa[$var]=$pval;
					break;
				}
			}
			if (!empty($ansa[$var])) break;
		}
		if (empty($ansa[$var]) && $var=='email' ) {  // empty email
			// did not get a hit so we need to try again and look for something that looks like an email
 			foreach($p as $pkey=>$pval) {
				if (stripos($pkey,'input_')) {
					// might have an email
					if (is_array($pval)) $pval=print_r($pval,true);
					if(strpos($pval,'@')!==false&&strrpos($pval,'.')>strpos($pval,'@')) {
						// close enough
						$ansa[$var]=$pval;
						break;
					}
				}
			}
		} 
	}
	
	
/*	
	
	foreach ($search as $var=>$sa) {
		foreach ($sa as $srch) {
			foreach($p as $pkey=>$pval) {
				if (is_string($pval)&&!is_array($pval)) { // woo commerce fix - overkill.
					if (strpos($pkey,$srch)!==false) {
						if ($var=='email'&&strpos($pval,'@')!==false&&strrpos($pval,'.')>strpos($pval,'@')) { // only valid with @ before last dot sign and .
							$ansa[$var]=$pval;
							$emfound=true;
							break;
						} else { // no @ sign - save for now, hope for better
							if (empty($ansa[$var])) $ansa[$var]=$pval;
						}
					}
					if ($var!='email' && $emfound) break; // keep checking email even if we have one - look for better
				}
			}
		}
	}
	*/
	// sanitize input - some of this is stored in history and needs to be cleaned up
	foreach($ansa as $key=>$value) {
		// clean the variables even more
		$ansa[$key]=sanitize_text_field($value); // really clean gets rid of high value characters
	}
	if (strlen($ansa['email'])>80) $ansa['email']=substr($ansa['email'],0,77).'...';
	if (strlen($ansa['author'])>80) $ansa['author']=substr($ansa['author'],0,77).'...';
	if (strlen($ansa['pwd'])>32) $ansa['pwd']=substr($ansa['pwd'],0,29).'...';
	if (strlen($ansa['comment'])>999) $ansa['comment']=substr($ansa['comment'],0,996).'...';
	if (strlen($ansa['subject'])>80) $ansa['subject']=substr($ansa['subject'],0,77).'...';
	if (strlen($ansa['url'])>80) $ansa['url']=substr($ansa['url'],0,77).'...';
	//print_r($ansa);
	//exit;
	return $ansa;
}
function kpg_ss_addons_d($config=array()) {
	// dummy function for testing
	return $config;
}
function kpg_caught_action($ip='',$post=array()) {
	// this is hit on spam detect for addons. Added this for a template for testing - not needed.
	// $post has all the standardized post variables plus reason and the chk that found the problem.
	// good addon would be a plugin to manage an sql table where this stuff is stored.
}
function kpg_stop_spam_OK($ip='',$post=array()) {
	// dummy function for testing
	// unreports spam
}

function really_clean($s) {
	// try to get all non 7-bit things out of the string
	if (empty($s)) return '';
	$ss=array_slice(unpack("c*", "\0".$s), 1);
	if (empty($ss)) return $s;
	$s='';
	for ($j=0;$j<count($ss);$j++) {
		if ($ss[$j]<127&&$ss[$j]>31) $s.=pack('C',$ss[$j]);
	}
	return $s;
}
function load_be_module() {
	if (!class_exists('be_module')) {
		require_once('classes/be_module.class.php');
	} 
}
function kpg_new_user_ip($user_id) {
	$x=$_SERVER['REQUEST_URI'];
	$ip=kpg_get_ip();
	//sfs_debug_msg("Checking reg filter login $x (kpg_user_ip)=".$ip.", method=".$_SERVER['REQUEST_METHOD'].", request=".print_r($_REQUEST,true));
    // check to see if the user is OK
	// add the users ip to new users
	update_user_meta($user_id, 'signup_ip', $ip);
}
function kpg_sfs_ip_column_head($column_headers) {
	$column_headers['signup_ip'] = 'User IP';
	return $column_headers;

}
function kpg_log_user_ip($user_login="", $user="") {
	if (empty($user)) return;
	if (empty($user_login)) return;
	// add the users ip to new users
	if (!isset($user->ID)) return;
	$user_id=$user->ID;
	//$ip=kpg_get_ip();
	$ip=$_SERVER['REMOTE_ADDR'];
	$oldip=get_user_meta($user_id,  'signup_ip', true );
	if (empty($oldip) || $ip!=$oldip) {
		update_user_meta($user_id, 'signup_ip', $ip);
	}
}

/***********************************

$user_email = apply_filters( 'user_registration_email', $user_email );

I am going to start checking this filter for registrations.
add_filter( 'user_registration_email', kpg_ss_user_reg_filter, 1, 1 );

***********************************/

function kpg_ss_user_reg_filter($user_login) {
	// the plugin should be all initialized
	// check the ip, etc.
	sfs_errorsonoff();
	$options=kpg_ss_get_options();
	$stats=kpg_ss_get_stats();
	
	// fake out the post variables
	$post=get_post_variables();
	$post['author']=$user_login;
	$post['addon']='chkRegister'; // no really an addon - but may be moved out when working.

	if ($options['filterregistrations']!='Y') {
		remove_filter( 'pre_user_login', kpg_ss_user_reg_filter, 1);
		sfs_errorsonoff('off');
		return $user_login;
	}
	// if the suspect is already in the bad cache he does not get a second chance?
	// prevents looping	
	$reason=be_load('chkbcache',kpg_get_ip(),$stats,$options,$post);
	sfs_errorsonoff();
	if ($reason!==false) {
		$rejectmessage=$options['rejectmessage'];
		$post['reason']='Failed Registration: bad cache';
		$host['chk']='chkbcache';
		$ansa= be_load('kpg_ss_log_bad',kpg_get_ip(),$stats,$options,$post);
		wp_die("$rejectmessage","Login Access Denied",array('response' => 403));
		exit();
	}
	// check the white list
	$reason=kpg_ss_check_white();
	sfs_errorsonoff();
	if ($reason!==false) {
		$post['reason']='passed registration:'.$reason;
		$ansa= be_load('kpg_ss_log_good',kpg_get_ip(),$stats,$options,$post);
		sfs_errorsonoff('off');
		return $user_login;
    }
	// check the black list
	//sfs_debug_msg("Checking black list on registration: /r/n".print_r($post,true));
	$ret=be_load('kpg_ss_check_post',kpg_get_ip(),$stats,$options,$post);
	$post['reason']='Passed Registration '.$ret;
	$ansa=be_load('kpg_ss_log_good',kpg_get_ip(),$stats,$options,$post);
	
	return $user_login;
}


require_once('includes/stop-spam-utils.php');


?>