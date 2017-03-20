<?php

// Case-insensitive regex for whether a path is a static file
$regex_path_static_suffix = "\\.(?:jpe?g|gif|png|css|js|ico|zip|7z|tgz|gz|rar|bz2|do[ct][mx]?|xl[ast][bmx]?|exe|pdf|p[op][ast][mx]?|sld[xm]?|thmx?|txt|tar|midi?|wav|bmp|rtf|avi|mp\\d|mpg|iso|mov|djvu|dmg|flac|r70|mdf|chm|sisx|sis|flv|thm|bin|swf|cert|otf|ttf|eot|svgx?|woff2?|jar|class|log|web[ma]|ogv)";

// Case-insensitive regex for whether a user-agent is a bot (so that we would route these bots to the same bucket for performance purposes)
$regex_is_user_agent_bot = 'bot|facebook|crawler|indexer|feed|flipboard|slurp|re[ea]der|spider|scoutjet|ssowl|baidu|google|news|pubsub|^expo9|^tzogeoagent|jakarta|teoma|scrap(?:[ey]|ing)|siteimprove|nutch|coccoc|wazzup|genieo|thunderstone|Rackspace Monitoring|abonti|MSIE 6';

// Case-insensitive regex for whether a user-agent is logged as a bot visit (with the expectation that this will be billed as a bot visit)
$regex_is_user_agent_logged_as_bot = "bot|facebook|crawler|indexer|feed|flipboard|slurp|re[ea]der|spider|scoutjet|ssowl|baidu|google|news|pubsub|^expo9|^tzogeoagent|jakarta|teoma|scrap(?:[ey]|ing)|siteimprove|nutch|coccoc|wazzup|genieo|thunderstone|Rackspace Monitoring|abonti";

// Case-insensitive regex for whether a user-agent is something we don't cache if it's dynamic content
$regex_is_user_agent_nodyncache = 'acer\ s100|archos5|cupcake|docomo\ ht\-03a|dream|htc\ hero|htc\ magic|htc_dream|htc_magic|incognito|lg\-gw620|liquid\ build|maemo|mot\-mb200|mot\-mb300|nexus\ one|opera\ mini|samsung\-s8000|series60.*webkit|series60/5\.0|sonyericssone10|sonyericssonu20|sonyericssonx10|t\-mobile\ mytouch\ 3g|t\-mobile\ opal|tattoo|webmate|2\.0\ mmp|240x320|alcatel|amoi|asus|au\-mic|audiovox|avantgo|benq|blackberry|blazer|cellphone|danger|ddipocket|docomo|dopod|elaine/3\.0|ericsson|eudoraweb|haier|hiptop|hp\.ipaq|htc|huawei|i\-mobile|iemobile|j\-phone|kddi|konka|kwc|kyocera/wx310k|lg/u990|lge\ vx|midp|midp\-2\.0|mmef20|mobilephone|mot\-v|motorola|netfront|newgen|newt|nintendo\ ds|nintendo\ wii|nitro|nokia|novarra|openweb|opera[\s\.]mobi|palm|panasonic|pantech|pdxgw|philips|\bphone\b|playstation\ portable|portalmmm|proxinet|psp|qtek|sagem|samsung|sanyo|sendo|sharp|sharp\-tq\-gx10|smartphone|softbank|sonyericsson|symbian|symbian\ os|symbianos|toshiba|treo|ts21i\-10|up\.browser|up\.link|vertu|vodafone|willcome|windows[ \.]ce|winwap';

// Case-insensitive regex for whether a user-agent is something we never cache
$regex_is_user_agent_never_cache = '^feedburner|feedblitz';

// Case-insensitive regex for whether a user-agent is in our "mobile" caching group
$regex_is_user_agent_mobile_group = "android|iphone|ipod|itouch";

// Case-insensitive regex for whether a user-agent is in our "iPad" caching group
$regex_is_user_agent_ipad_group = '\bipad\b';

// Case insensitive regex for whether a user-agent is VaultPress
$regex_is_user_agent_vaultpress = 'vaultpress';

// Case-insensitive regex for user agents which we should automatically 403 on
$regex_is_user_agent_403 = "^\W|^Mozilla(?: |/2|/4\.0\+?\\()|^(?:LWP|MSIE|Nut|Omniexpl|Opera/9\.64|pussycat|python|super happy|trackback/|user|website-|winnie)|[\\r\\n]|psyche|\bemail|autoemailspider|core-project|diamond|digger|ecollector|forum|^java[/ ]\\d\\.|Microsoft URL|Missigua|Movable Type|grub|httpproxy|Internet Explorer|isc systems|blogsearch|cherrypicker|maxpoint|casper|boston|feedfinder|cmsworldmap|diavol|dotbot|flicky|ia_archiver|kmccrew|libwww|planetwork|pycurl|skygrid|; Widows|a href|DTS agent|user-agent:|hanzoweb|indy lib|murzillo|\.NET CLR 1\)|POE-Component-Client|turing mach|Ubuntu/9\.25|unspecified\\.mail|webaltbot|wise(?:nut)?bot|Windows NT [45]\.[01];\)|Windows XP 5|WordPress/4\.01|discobot|xedant|typhoeus|Morfeus|PMAFind|revolt|sqlmap|w3af|Nikto|ZmEu|\\\\\\\\";

// Case-insensitive regex for paths which we should automatically 403 on
$regex_is_path_403 = "/\.(?:htaccess|svn|cvs|git|smushit-status|largefs)|\\.(?:as.x)\$|r\\d+shell|/.*\\.sql(\\.gz)?$|/uploads/(?:temp|backupbuddy)_|/wp-content/plugins/tweet-blender/|^/sd_nginx_status|^/_wpeprivate|/php-?(?:my)?-?admin|/pma/?$|^/readme.htm|\\bgoogle\\.com/humans\\.txt|mysql-parallel-dump.*|.*?/__wpe_admin_ajax\\.log\$";

// Case-insensitive regex for query-strings which we should automatically 403 on
$regex_is_qstr_403 = "(?:\\<|%3c).*script.*(?:\\>|%3e)|\\b(?:GLOBALS|_REQUEST|_GET|_POST|_COOKIE)(?:=|\[|\%[0-9A-Z]{0,2})|\\bgoogle\\.com/humans\\.txt";

// Case-insensitive regex for paths which we should automatically 403 on, but ONLY IF the connection is NOT TRUSTED
$regex_is_path_403_for_untrusted = "(!:.*?/uploadify.*(?<!\.js|\.css)\$)";

// Regex for whether a path is a feed
$regex_is_path_feed = "/(?:atom(?:/|\\.xml)?(?:/index.php)?|feed(?:/|\\.xml|/rss2?\b/?|/rdf/?|/podcast/?)?(?:/index\\.php|.+\\.xml)?|index\\.rdf)\$";

// Regex for whether a path corresponds to a protected WordPress admin area
$regex_is_path_wp_admin = "/wp-(?:admin|[^/]+\\.php\$)";

// Regex for whether these args *should* be served to bots, otherwise we will 301 the bot to the path with args stripped away.
$regex_is_qargs_allowed_for_bot = "(?:p=\d+|feed=)";

// Regex for whether a path should *not* be served to bots, in which case it 301s the bot to the home page
$regex_is_path_disallowed_for_bot = ".*?(?:/page/\d\d+|/attachment/|/search/|/members/[^/]+/activity|/activity/p/)";

// Regex for whether a path should *not* be served to bots, in which case it 301s the bot to the home page
$regex_is_last_path_component_disallowed_for_bot = ".*?(?:[a-zA-Z](?<!/page)/\d+)/?\$";

// Regex for determining content-type from paths
$regex_content_type_css  = "(?:\\.css|[\\._/-](?:css|styles?|stylesheets?)\.php)\$";
$regex_content_type_js  = "(?:\\.js|[\\._/-](?:js|javascripts?|scripts?)\.php)\$";
$regex_content_type_html = "(?:\\.html|/|/index\\.php)\$";

// Regex for cookies that prevent us from serving the page cached
$regex_is_cookie_nocache = "\\b(?:(?!wordpress_test_cookie)wordpress_|wp-postpass_|comment_author_|wpengine_no_cache|mp_globalcart_|wlcookie|wishlist|Cart66\\w*SID|ap_id|wc_bundle_pge_id|wm_affid|cart_in_use|eMember_in_use|wpsc_customer_cookie_|BS_SESSION|region_pref|lax_user)";

// Regex for paths which we never cache
$regex_is_path_nocache_prefix = "/(?:store|cart|check-?out)(?:/|\$)";
$regex_is_path_nocache = "\\bwptouch[\\w-]+|$regex_is_path_nocache_prefix|\\.xml(?:\\.gz)?\$";

// Regex for query args which we never cache
$regex_is_qargs_nocache = "\\btheme_view=|\\bwptouch[\\w-]+=|cart66AjaxCart";

// Regex for cacheable wp core files
$regex_core_cacheable = "wp-admin/load-(scripts|styles)\\.php$";

// Regex for paths that should be cached for a long time, and irrespective of user agent.
$timthumb_script_regex = "/(?:php|[tT]im)?[tT]humb(?:nail)?\\.(?:php|bmp)";
$permacache_regex_list = array (
	"$timthumb_script_regex\$",
	"/ima?ge?\\.php\$",
	"[^a-zA-Z0-9](?:css|js|scripts?|style(?:sheet)?s?|j(?:ava)?scripts?)\\.php\$",	// many kinds of js and css PHP scripts look like this
	"/plugins/b?wp-minify/min",
    "/plugins/sidebartabs/styleSidebar_global\\.php\$",
    "/plugins/s2member/s2member-o\\.php\$",
	"/shopp?/images?/\\d+/?",
    "/gradient\\.php\$",
	"^/robots\\.txt\$",
);
$regex_is_path_dynamic_long_cache = join('|',$permacache_regex_list);

// Regex for non-path characters inside HTML/CSS attributes.
$regex_charlist_ends_uri = "'\"\\)";
$regex_uri_segment = "[^$regex_charlist_ends_uri]*";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////	Original names and forms of these regular expressions.
/////	New code should use the variables from above this line because they're better documented and explained.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$static_suffix_regex = "$regex_path_static_suffix\$";
$known_403_regex = $regex_is_path_403;
$feed_regex = $regex_is_path_feed;
$dynamic_long_cachable_regex = $regex_is_path_dynamic_long_cache;

$wordpress_admin_script_regex = "/wp-(admin/.|admin\$|[^/]+\\.php\$)";

$static_dirs_nginx_regex       = "^/(?:wp-content/(?:themes|plugins|uploads|wptouch-data|gallery)|wp-includes|wp-admin)/";
$static_dirs_cdn_regex         = "/(?:wp-content/(?:themes|plugins|uploads|files|wptouch-data|gallery)|wp-includes|wp-admin|files|images|img|css|js|fancybox|assets)/";

// Removed sitemap.xml from this list because WordPress and other plugins can generate automatically.
$static_no_backend_regexs = array (
	"/favicon\\.\\w+\$",				// not generated and often 404s
	"/apple-touch-icon[^/]*\\.png\$",	// not generated and often 404s
	"/crossdomain\\.xml\$",				// not generated and often 404s
	"^/PointRollAds\\.htm",				// ad service which has query string but is in fact static HTML
	"^/doubleclick/DARTIframe\\.html",	// ad service which has query string but is in fact static HTML
	"^/Tagcade/def\\.html",         	// ad service which has query string but is in fact static HTML
//	"^/wp-(?:admin|includes)/(?:css|js|images)/.*\\.(?:jpe?g|gif|png|ico|bmp|css|js)",	// statics from core
	"^/wp-includes/wlwmanifest\\.xml",
	"$static_dirs_nginx_regex.*$static_suffix_regex",		// statics from uploads
);

$known_statics_uri_regex = join( '|', array(
    "/xd_receiver\\.htm\$",
    "/featured-content-gallery/(?:css|scripts)/jd\\.gallery\\.(?:css|js)\\.php\$",
    "/ozh-admin-drop-down-menu/inc/adminmenu\\.css\\.php\$",
        ) );
$known_statics_args_regex = "(?:mcsf_action=main_css|ak_action=aktt_(?:css|js)|sjsl=|cf_action=cfmobi_admin_(?:js|css)|apipp_style=|action=shadowboxjs|s=jquery-comment-preview\\.js)";

$known_short_cache_args_regex = "(?:\\bnggpage=)";

$comped_uri_regex = "(?:robots\\.txt|favicon\\.ico)\$";

$vulnerability_scan_header_regexes = array (
	'http_acunetix_product' => 'acunetix',
	'http_x_scanner'        => 'netsparker'
);

$wpe_replace_siteurl_with_https = '(?<!\<span id="sample-permalink" tabindex="-1"\>)(?<!id="siteurl" value=")(?<!id="home" value=")';

/*
	Test strings for Fancybox regex:

/wp-content/themes/dmn/js/fancybox/jquery.fancybox-1.3.1.css
/wp-content/themes/dmn/fancybox/border.top.png
/foobar/wp-content/themes/dmn/fancybox/border.top.png
/2011/02/my-posts-are-awesome/fancybox/border.top.png

*/
$known_404_regex = "^/nas/wp\\b|^/nginx_status|/undefined/?\$|/plugins/wp-postviews/wp-postviews\\.php|^/cgi-bin|/autodiscover\\.xml|^/www\\.youtube\\.com/";

/*
 * Pattern/replace for removing MyIsam from queries 
 */
$wpe_find_myisam_in_create_regex = '/(.*ENGINE\s*=\s*[\'"]{0,1})MYISAM([\'"]{0,1})/i';
$wpe_replace_myisam_in_create_with_innodb = "$1INNODB$2";

class Patterns
{
	static public function build_http_to_https ($html, $blog_url)
	{
		// Strip the protocol
		$blog_url = preg_replace('(https?://)', '', $blog_url);
		$r = '#([\\s-]?src[\s]*=[\s]*[\'"])http://('.$blog_url.')(/?.*[\'"])#i';
		// Let's skip anything that's in a <textarea>
		$ignore_start = $ignore_end   = 0;
		if ( preg_match( "#<textarea.+?</textarea>#is", $html, $match, PREG_OFFSET_CAPTURE ) ) {
			$ignore_start = $match[0][1];
			$ignore_end   = $ignore_start + strlen( $match[0][0] );
			$html         = self::preg_replace_around( $r, "\$1https://".$blog_url."\$3", $html, $ignore_start, $ignore_end );
		} else {
			$html = preg_replace( $r, "\$1https://".$blog_url."\$3", $html );
		}
        	return $html;
	}

	// Performs a preg_replace(), but ignores the substring between the two ignore ends, exclusive.
	static public function preg_replace_around( $re, $repl, $src, $ignore_start, $ignore_end )
	{
		// trivial cases
		if ( $ignore_start >= $ignore_end )
			return preg_replace( $re, $repl, $src );
		// replace before and after and stitch together
		$a = preg_replace( $re, $repl, substr( $src, 0, $ignore_start ) );
		$b = substr( $src, $ignore_start, $ignore_end - $ignore_start );
		$c = preg_replace( $re, $repl, substr( $src, $ignore_end ) );
		return $a . $b . $c;
	}

	//Return an array of regexes for matching wp-admin
	static public function regex_wp_admin() {
		return array(
			"/wp-admin$",
			"/wp-admin\/(?!admin-ajax)",
			"/(wp-)?signup",
			"/(wp-)?login(\.php)?",
			"/wp-includes",
		);
	}

	/**
	 * Filter out regexs which do not compile (or otherwise annoy preg_match) - *For use with arrays*
	 * @param array $ssl_forced_x regex strings to be checked
	 * @param bool $quiet (Default: false) if false turns output buffering off an displays errors, true buffers outputs to keep unit-tests clean
	 * @return array regex that compiles correctly is returned in an array, if none compiles an empty array is returned
	 */
	static public function filter_noncompiled_regex($ssl_forced_x, $quiet = false) {
		return array_filter($ssl_forced_x, function ($re) use ($quiet) {
			if (false === @preg_match("#$re#", '')) {
				// preg_match says this regex is no good, lets not use it
				if ( $quiet == false ) {
					error_log(__FILE__ . "@" . __LINE__ . " we are not using this bad regex in force ssl: #$re#");
				}
				return false;
			}
			// all clear
			return true;
		});
	}

	/**
	 * Reject paths that have quotes, double quotes, or that end in backslashes - *For use with arrays*
	 * @param array $ssl_forced_x regex strings to be checked
	 * @param bool $quiet (Default: false) if false turns output buffering off an displays errors, true buffers outputs to keep unit-tests clean
	 * @return array regex that is determined safe is returned in the final array, if none of the regex is cleared an empty array is returned
	 */
	static public function reject_dangerous_regex($ssl_forced_x, $quiet = false ) {
		if (is_string($ssl_forced_x)) {
			$ssl_forced_x = array($ssl_forced_x);
		}
		return array_filter($ssl_forced_x, function($re) use ($quiet) {
			if(false !== strpos($re, "'") || false !== strpos($re, '"') || "\\" === substr($re, -1)) {
				if ( $quiet == false ) {
					error_log(__FILE__ . "@" . __LINE__ . " - We are not accepting quotes, double quotes, or strings ending with backslash: #$re#");
				}
				return false;
			}
			// all clear
			return true;
		});
	}

	/**
	 * Given an input for rewrite paths, check if regex compiles
	 * @param string $rewrite_path rewrite string with regex that is checked to see if it compiles
	 * @param bool $quiet (Default: false) if false turns output buffering off an displays errors, true buffers outputs to keep unit-tests clean
	 * @return bool false and error log if the regex doesn't compile or true if all clear
	 */
	static public function check_for_noncompiled_regex_string($rewrite_path, $quiet = false) {
		if (false === @preg_match("#$rewrite_path#", '')) {
			// preg_match says this regex is no good, lets not use it
			if ( $quiet == false ) {
				error_log(__FILE__ . "@" . __LINE__ . " we are not using this bad regex in rewrites: #$rewrite_path#");
			}
			return false;
		}
		// all clear
		return true;
	}

	/**
	 * Given a rewrite path, return false and error log if the regex looks dangerous at all (uses quotes, double quotes, or that end in backslashes) or return true if all clear
	 * @param string $rewrite_path rewrite string with regex that is checked to see if it has dangerous characters in it
	 * @param bool $quiet (Default: false) if false turns output buffering off an displays errors, true buffers outputs to keep unit-tests clean
	 */
	static public function reject_dangerous_regex_string($rewrite_path, $quiet = false) {
		if(false !== strpos($rewrite_path, "'") || false !== strpos($rewrite_path, '"') || "\\" === substr($rewrite_path, -1)) {
			if ( $quiet == false ) {
				error_log(__FILE__ . "@" . __LINE__ . " - We are not accepting quotes, double quotes, or strings ending with backslash: #$rewrite_path#");
			}
			return false;
		}
		// all clear
		return true;
	}

	/**
	 * Checks for noncompiling or dangerous regex
	 * @param array $rewrite rewrite arguments to be evaluated for dangerous or noncompiling regex
	 * @param string $domain the domain argument passed in the rewrite rules
	 * @param string $pattern regex pattern passed in the rewrite rules
	 * @param string $argmatch optional regex arg passed in rewrite rules
	 * @param string $replacement rewrite rule's replacement arg
	 * @param bool $quiet (Default: false) if false turns output buffering off and displays errors, true buffers outputs to keep unit-tests clean
	 * @return bool $skip if true, the regex didn't compile or dangerous regex was found and this rewrite will be skipped, if true the regex was safe
	 */
	static public function is_scary_regex($rewrite, $domain, $pattern, $argmatch, $replacement, $quiet = false) {
		$filterees = array($domain, $pattern, $argmatch);
		$skip = false;
		foreach ($filterees as $key){
			if (! isset($rewrite[$key])) {
			        continue;
			}
			if (! Patterns::check_for_noncompiled_regex_string($rewrite[$key], $quiet)) {
				$skip = true;
			}
			if (! Patterns::reject_dangerous_regex_string($rewrite[$key], $quiet)) {
				$skip = true;
			}
		}
		if (! Patterns::reject_dangerous_regex_string($rewrite[$replacement], $quiet)) {
			$skip = true;
		}
		return $skip;
	}
}

// Given a regular expression which matches the path-part of a URL, returns a regular expression
// which matches the entire path + qargs part.
function get_uri_regex_from_path_regex( $re )
{
	$prefix = "^[^\\?]*?";
	if ( $re[0] == '#' ) $re = $re[0] . $prefix . substr($re,1);
	else $re = $prefix . $re;
	return str_replace("\$","(?:\\?|\$)",$re);
}

// Either add or remove protocol/domain parts from timthumb-style src-path references
function ec_modify_timthumb_src_urls( $html, $domain, $add_domain )
{
	global $timthumb_script_regex;

	if ( $add_domain ) {
        $html = preg_replace(
                "#\\b(src=[\"'](https?)://[^\"']+$timthumb_script_regex\\?src=)(?!http)/?#i", "\$1\$2://$domain/", $html
        );
	} else {
		$re_domain = preg_quote($domain);
        $html = preg_replace(
                "#\\b(src=[\"']https?://[^\"']+$timthumb_script_regex\\?src=)https?://{$re_domain}/#i", "\$1/", $html
        );
	}
	return $html;
}

// Given a map of domains -> CDN domains, returns a list of URL replacement rules which map those
// using our standard regexs for what things are supposed to go on a CDN.
// @param $map_domain_cdn mapping of domain -> CDN domain for transformations
function ec_get_cdn_replacement_rules( $map_domain_cdn )
{
	global $static_dirs_cdn_regex, $regex_is_path_dynamic_long_cache, $regex_path_static_suffix;
	global $regex_charlist_ends_uri, $regex_uri_segment, $known_statics_args_regex;

	// Trivial cases
	if ( empty($map_domain_cdn) ) return array();
	// Regex set
	$cdn_regexs = array(
		"#" . get_uri_regex_from_path_regex("^/[^/${regex_charlist_ends_uri}]+${regex_path_static_suffix}\$") . "#i",		// any static in the root
		"#${static_dirs_cdn_regex}.+${regex_path_static_suffix}#i",		// static in a known-static location
		"#" . get_uri_regex_from_path_regex($regex_is_path_dynamic_long_cache) . "#",	// known-dynamic-cacheable paths
		"#\\?.*${known_statics_args_regex}#",		// known-dynamic-cacheable query args
	);
	$rules = array();
	foreach ( $map_domain_cdn as $src_domain => $cdn_domain ) {
		foreach ( $cdn_regexs as $uri_re ) {
			$rules[] = array (
				'src_domain' => $src_domain,
				'src_uri' => $uri_re,
				'dst_domain' => $cdn_domain,
			);
		}
	}
	return $rules;
}

// Converting the site-config 'cdn_regexs' array into CDN replacements
function ec_add_cdn_replacement_rules_from_cdn_regexs( &$rules, $cdn_regexs, $src_domain, $cdn_domain )
{
	if ( empty($cdn_regexs) ) return;		// common case when config isn't there
	foreach ( $cdn_regexs as $re ) {
		$rules[] = array (
			'src_domain' => $src_domain,
			'src_uri' => '#' . $re . '#',
			'dst_domain' => $cdn_domain,
		);
	}
}

// Replaces URLs in a block of HTML, adhereing to a variety of rules
// @param $rules array of replacement rules as a 'src_domain' of domain to find, 'src_uri' to match the absolute path portion, 'dst_domain' to specify the
//			new domain to replace with, 'dst_prefix' as an optional path prefix to pre-pend to the path.
// @param $default_domain if non-null, any absolute paths should be considered this domain for the purposes of rule-application.
function ec_url_replacements( $html, $rules, $default_domain = null, $is_ssl = false )
{
	global $regex_charlist_ends_uri, $regex_uri_segment;
	// Trivial cases
	if ( ! $html || strlen($html) < 5 ) return $html;
	if ( empty($rules) ) return $html;

	// Find all the things which are URLs at all
	$re_start = "\\b(?:(?:src|href|value|data)\\s*=|\\burl\\s*)[\\('\"\\s]+";
	if ( ! preg_match_all( "#${re_start}((?:https?:)?/?/)([^/${regex_charlist_ends_uri}]+)(${regex_uri_segment})#i", $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
		return $html;
//print_r($matches);

	// Process each thing we need to replace, determining the thing to replace with.
	$pieces = array();
	$next_offset = 0;
	foreach ( $matches as $m ) {

		// Load info
		$offset = $m[1][1];
		$end = $m[3][1] + strlen($m[3][0]);
		$protocol = $m[1][0];
		$domain = $m[2][0];
		$path = $m[3][0];

		// If this is an absolute path: If we have a default domain use it, otherwise skip this
		// because we can't resolve it against any rules.
		if ( $protocol == "/" ) {
			if ( $default_domain ) {
				$path = '/' . $domain . $path;	// the path is composed of what is normally the hostname and the path
				$domain = $default_domain;		// set the domain
			}
			else
				continue;
		}
//print("[$protocol][$domain][$path]\n");
		// Protocol could be missing or just / or // at this point.
		if (strlen($protocol) < 6) {
			$protocol = $is_ssl ? 'https://' : 'http://';
		}

		// If the linked resource is https, but the page is not, don't CDN. Doing so would result in
		// the resource being served unsecured (or broken: https://help.wpengine.com/tickets/174180).
		// This is somewhat suboptimal, but it is safe. We are not handling the case where an insecure
		// page has a secure resource *and* the site has secure CDN enabled.
		if ((! $is_ssl) && ('https://' == $protocol)) {
			continue;
		}

		// Run rules to find a replacement, if any
		$replacement = null;
		foreach ( $rules as &$r ) {
			if ( strcasecmp($r['src_domain'],$domain) == 0 && ( !isset($r['src_uri']) || preg_match($r['src_uri'],$path) ) ) {
				// Rule matches!
				$replacement = "$protocol" . $r['dst_domain'] . (isset($r['dst_prefix']) ? $r['dst_prefix'] : "") . $path;
				break;
			}
		}

		// If there's a replacement, accumulate the various strings.
		if ( $replacement ) {
			if ( $offset > $next_offset )
				$pieces[] = substr($html,$next_offset,$offset-$next_offset);
			$pieces[] = $replacement;
			$next_offset = $end;
		}
	}

	// Trivial case
	if ( ! $next_offset ) return $html;

	// Put all the pieces together.
	$pieces[] = substr($html,$next_offset);			// accumulate the last piece
//print_r($pieces);
	$html = join("",$pieces);

	// Finished
	return $html;
}

// Given a PHP backtrace array, finds the first element which is NOT part of a core routine,
// then returns the trace from that point forward.  If $backtrace is null, uses the current
// backtrace from the time of this call.
function ec_get_non_core_backtrace( $backtrace = null )
{
	// Make sure we have a backtrace of some kind
	if ( ! $backtrace ) {
	    // Save lots of memory by not populating the function arguments.
	    // We did see large (>500k) memory consumption by this function on site "edd."
	    // Protected against values not being defined, because some are added in later versions of PHP.
	    $backtrace_options = defined('DEBUG_BACKTRACE_IGNORE_ARGS') ? DEBUG_BACKTRACE_IGNORE_ARGS : 0;
		$backtrace = debug_backtrace( $backtrace_options );
	}

	// Scan for things not in core and not in our own plugin
	for( $k=0 ; $k<count($backtrace) ; $k++ ) {
		if ( ! isset($backtrace[$k]["file"]) ) continue;
		if ( preg_match("#/(?:wp-(?:admin|includes)/|wp-(?:load|blog-header)\\.php|mu-plugins\\.php|wpengine-common/|db\\.php)#", $backtrace[$k]["file"]) )
			continue;
		break;
	}
	return array_slice( $backtrace, $k, 5 );
}
