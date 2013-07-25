<?php

// ** MySQL settings - You can get this info from your web host ** //

if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {

	define( 'WP_LOCAL_DEV', true );
	include( dirname( __FILE__ ) . '/local-config.php' );

} else {

	define('DB_NAME', 'database_name_here');
	define('DB_USER', 'username_here');
	define('DB_PASSWORD', 'password_here');
	define('DB_HOST', 'localhost');
}
	
	define('DB_CHARSET', 'utf8');
	define('DB_COLLATE', '');

	define('GOOGLE_ANALYTICS_ID', 'UA-2159509-3');

// ** Custom "content" directory ** //

define('WP_CONTENT_DIR', dirname( __FILE__ ) . '/content');
define('WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content');
define('WP_HOME', 'http://' . $_SERVER['SERVER_NAME']);
define('WP_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/wp');

// ** File permissions ** //

define('FS_METHOD', 'direct');

/** Authentication Unique Keys and Salts.
 *  @link https://api.wordpress.org/secret-key/1.1/salt/
 */

define('AUTH_KEY',         '4JD[j4M~>:Hd2r0faZ#.VT|wf;A-Foq5Q$>T<W>%X#C}GBC27-z/^_NPS^N9&liG');
define('SECURE_AUTH_KEY',  'C&v[<z&;$V8`^G`jn~Db_xI%jvL.33](6^X6&+<p`kH-?;DO.8+DHOJO=o[?/`o9');
define('LOGGED_IN_KEY',    'e|e84*#!Ws_p_~+Tb=H_W];UQx%&@V ,SIeK+5C`TZLM$q^+6Xg)I5@quk&9WR>@');
define('NONCE_KEY',        'H/Ng>Ta6M<FjR[;v|0+filW04sEN<zg[B7j.R~0@aU&VKk]Ml%+EAJF;0VF)Ef?^');
define('AUTH_SALT',        'dvo,LMchl4_;8SC&muY| Oyc,*j2e3bmum3u[hvuj==uI513`upB>&7-+|+kS-w+');
define('SECURE_AUTH_SALT', 'Y2a!l|4n!+6.gfDe-rbJ5^.h^KwdaN]M25gWd3r}wM9x|R|clhQR:A3hP:vn%Yf`');
define('LOGGED_IN_SALT',   ' `56qxFE !_pVJthN h:K-VJ1kL;i#$-+$EA_N>,7jX3-~-tL:5Lb[v3+FA Ean_');
define('NONCE_SALT',       'k4 REb36zMdOy4|o>vKF7~G2VX-imiYOP9*4+[([I*!1#7(a-;gWz/e*L#pc{>U2');

$table_prefix  = 'wp_';

define('WPLANG', '');
// define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');