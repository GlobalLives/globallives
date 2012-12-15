<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */


// ** MySQL settings - You can get this info from your web host ** //

if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {

	define( 'WP_LOCAL_DEV', true );
	include( dirname( __FILE__ ) . '/local-config.php' );

} else {

	/** The name of the database for WordPress */
	define('DB_NAME', 'database_name_here');
	
	/** MySQL database username */
	define('DB_USER', 'username_here');
	
	/** MySQL database password */
	define('DB_PASSWORD', 'password_here');
	
	/** MySQL hostname */
	define('DB_HOST', 'localhost');
}
	
	/** Database Charset to use in creating database tables. */
	define('DB_CHARSET', 'utf8');
	
	/** The Database Collate type. Don't change this if in doubt. */
	define('DB_COLLATE', '');

/**
 * Custom Content Directory.
 *
 * Themes, plugins, and uploads are now in /content because they can't be in /wp (since it's a submodule)
 */
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );

define('WP_HOME', 'http://' . $_SERVER['SERVER_NAME']);
define('WP_SITEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/wp');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '4JD[j4M~>:Hd2r0faZ#.VT|wf;A-Foq5Q$>T<W>%X#C}GBC27-z/^_NPS^N9&liG');
define('SECURE_AUTH_KEY',  'C&v[<z&;$V8`^G`jn~Db_xI%jvL.33](6^X6&+<p`kH-?;DO.8+DHOJO=o[?/`o9');
define('LOGGED_IN_KEY',    'e|e84*#!Ws_p_~+Tb=H_W];UQx%&@V ,SIeK+5C`TZLM$q^+6Xg)I5@quk&9WR>@');
define('NONCE_KEY',        'H/Ng>Ta6M<FjR[;v|0+filW04sEN<zg[B7j.R~0@aU&VKk]Ml%+EAJF;0VF)Ef?^');
define('AUTH_SALT',        'dvo,LMchl4_;8SC&muY| Oyc,*j2e3bmum3u[hvuj==uI513`upB>&7-+|+kS-w+');
define('SECURE_AUTH_SALT', 'Y2a!l|4n!+6.gfDe-rbJ5^.h^KwdaN]M25gWd3r}wM9x|R|clhQR:A3hP:vn%Yf`');
define('LOGGED_IN_SALT',   ' `56qxFE !_pVJthN h:K-VJ1kL;i#$-+$EA_N>,7jX3-~-tL:5Lb[v3+FA Ean_');
define('NONCE_SALT',       'k4 REb36zMdOy4|o>vKF7~G2VX-imiYOP9*4+[([I*!1#7(a-;gWz/e*L#pc{>U2');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
