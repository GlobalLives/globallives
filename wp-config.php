<?php
# Database Configuration
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {

	# Local
	define( 'WP_LOCAL_DEV', true );
	include( dirname( __FILE__ ) . '/local-config.php' );

} else {

	# WPEngine
	define('DB_NAME','wp_globallives');
	define('DB_USER','globallives');
	define('DB_PASSWORD','u4DSK07fCT1841ZbvK9e');
	define('DB_HOST','127.0.0.1');
	define('DB_HOST_SLAVE','localhost');
	define('DB_CHARSET', 'utf8');
	define('DB_COLLATE', 'utf8_unicode_ci');
	$table_prefix = 'wp_';

}

# Security Salts, Keys, Etc
define('AUTH_KEY',         'iPYpgUiA)#XYBA[>aAka];eDUs!A s9?m.2?fPaLRb0`GKB&; $BZsBsR9#]|r4-');
define('SECURE_AUTH_KEY',  ')He=C|tyx/)t+(uDx-h<2M#!H7svnl8nSh|}Su.vR<,&ctd~#&_y}[ 6pi<W)-+y');
define('LOGGED_IN_KEY',    '+V_v0j|$MFiME]d)%ta*av?*`#Et6np%hUmsn0Y4[7n)zJ}05s48b9n5,::}iQwO');
define('NONCE_KEY',        'EP99b6h*z!,;yYPh51<DQmiiKhqA<Irl~iibb#=Re2[)%/`N{bp+Nk.T $G_P|?I');
define('AUTH_SALT',        'Mo9pj|rE`pNHdiau0l|1NtMy)Iq471pR B@DdW>NF:;_:[](DSWuy>[oPh^Gs =`');
define('SECURE_AUTH_SALT', '86|q`SSIkP%8IMiD:=cPO;8/nu32FZz+bw<,Xqin=(+y!yzq $^SwzjvG&=&=F}+');
define('LOGGED_IN_SALT',   '*U+|/j3[-I;6c;*c-*G%]x8nAhdQo[U]E+<Jnb3F5Tg3SMom@,8YF!`Z]3=uQzqY');
define('NONCE_SALT',       '.cgcr)P0]hqTW<d)+b+bDo7d%o03B.~+wd NH,+lBcog=2^DQ=XR$[l%#N!|g73?');

# Localized Language Stuff
define('WP_CACHE',TRUE);
define('PWP_NAME','globallives');
define('FS_METHOD','direct');
define('FS_CHMOD_DIR',0775);
define('FS_CHMOD_FILE',0664);
define('PWP_ROOT_DIR','/nas/wp');
define('WPE_APIKEY','fd931f7cb005190b6e27f7410b918c9c9b9033da');
define('WPE_FOOTER_HTML',"");
define('WPE_CLUSTER_ID','1546');
define('WPE_CLUSTER_TYPE','pod');
define('WPE_ISP',true);
define('WPE_BPOD',false);
define('WPE_RO_FILESYSTEM',false);
define('WPE_LARGEFS_BUCKET','largefs.wpengine');
define('WPE_CDN_DISABLE_ALLOWED',false);
define('DISALLOW_FILE_EDIT',FALSE);
define('DISALLOW_FILE_MODS',FALSE);
define('DISABLE_WP_CRON',false);
define('WPE_FORCE_SSL_LOGIN',false);
define('FORCE_SSL_LOGIN',false);
/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/
define('WPE_EXTERNAL_URL',false);
define('WP_POST_REVISIONS',3);
define('WP_TURN_OFF_ADMIN_BAR',false);
define('WPE_BETA_TESTER',false);
umask(0002);
$wpe_cdn_uris=array ();
$wpe_no_cdn_uris=array ();
$wpe_content_regexs=array ();
$wpe_all_domains=array (  0 => 'globallives.wpengine.com',  1 => 'globallives.org',  2 => 'www.globallives.org',);
$wpe_varnish_servers=array (  0 => 'pod-1546',);
$wpe_ec_servers=array ();
$wpe_largefs=array ();
$wpe_netdna_domains=array ();
$wpe_netdna_push_domains=array ();
$wpe_domain_mappings=array ();
$memcached_servers=array ();
define('WPE_WHITELABEL','wpengine');
define('WP_AUTO_UPDATE_CORE',false);
define('WPLANG','');

# WP Engine ID
define('PWP_DOMAIN_CONFIG', 'globallives.org' );

# WP Engine Settings






# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}