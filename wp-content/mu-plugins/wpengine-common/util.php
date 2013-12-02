<?php
require_once(dirname(__FILE__)."/patterns.php");

// A regular expression to match the domain of this blog, or match any domain if not given
if ( ! isset( $re_curr_domains ) )
    $re_curr_domains = array( "http://[^/\"'\\)]+" );

$cdn_replacement_regexes = array( );

$has_done_nodomains = false;
foreach ( $re_curr_domains as $re_domain ) {

    // Locate URLs that we can replace with the CDN URL.  Match before and after the text to replace with the
    // root URL, so that '\1${cdn_url}\2' is a proper replacement.  Sets an array of replacements.
    $re_src_stn  = "(\\w=['\"])(";        // href/src starting pattern
    $re_src_st   = "([\\s-](?:src|href|value|data)=['\"])${re_domain}(";  // href/src starting pattern
    $re_src_end  = "['\"])";           // href/src ending pattern
    $re_url_st   = "(\\burl\(\\s*['\"]?)${re_domain}(";   // url() starting pattern
    $re_url_end  = "['\"]?\\s*\))";       // url() ending pattern
    $re_qstr     = "(?:\\?[^'\"\)]*)?";    // optional query string
    $re_path0    = "[^\\?'\"\)]*?";      // path stuff, can be zero chars
    $re_uri_cdn  = str_replace( "\$", $re_qstr, $dynamic_long_cachable_regex );
    $re_qstr_cdn = $known_statics_args_regex;

    $cdn_replacement_regexes[] = "#${re_src_st}${re_path0}${static_dirs_cdn_regex}${re_path0}\\.(?:css|js|png|gif|jpe?g|ico|swf)${re_qstr}${re_src_end}#i";
    $cdn_replacement_regexes[] = "#${re_url_st}${re_path0}${static_dirs_cdn_regex}${re_path0}\\.(?:css|js|png|gif|jpe?g|ico|swf)${re_qstr}${re_url_end}#i";
    $cdn_replacement_regexes[] = "#${re_src_st}${re_path0}(?:${re_uri_cdn})${re_src_end}#i";
    $cdn_replacement_regexes[] = "#${re_url_st}${re_path0}(?:${re_uri_cdn})${re_url_end}#i";
    $cdn_replacement_regexes[] = "#${re_src_st}${re_path0}\\?${re_path0}${re_qstr_cdn}${re_path0}${re_src_end}#i";
    if ( isset( $wpe_cdn_uris ) && is_array( $wpe_cdn_uris ) && count( $wpe_cdn_uris ) > 0 ) {
        foreach ( $wpe_cdn_uris as $re ) {
            $exclude_domain            = substr( $re, 0, 10 ) == '[NODOMAIN]';
            if ( $exclude_domain && $has_done_nodomains )
                continue;
            if ( $exclude_domain )
                $re                        = substr( $re, 10 );
            if ( substr( $re, 0, 1 ) == '^' )  // "starts with" is the default
                $re                        = substr( $re, 1 );   // so don't use that character
            else
                $re                        = $re_path0 . $re; // otherwise put the equivalent of ".*" in front of the path
            if ( substr( $re, -1 ) == '$' )  // "ends with" is the default
                $re                        = substr( $re, 0, -1 );   // so don't use that character
            else
                $re                        = $re . $re_path0 . $re_qstr; // otherwise put ".*" at the end
            $re_start                  = $exclude_domain ? $re_src_stn : $re_src_st;
            $re                        = "#${re_start}${re}${re_src_end}#";
            $cdn_replacement_regexes[] = $re;
        }
        $has_done_nodomains        = TRUE;  // don't repeat the ones sans domain even if we have more domains, else we get extra replacements
    }

    // Content-replacement regexes specifically for the WordPress administration area.
    // Have to be much more careful about what we replace!  But many things are the same for all WordPress installs.
    $cdn_admin_replacement_regexes[] = "#${re_src_st}${re_path0}/wp-(?:admin|includes)/${re_path0}\\.(?:css|js|png|gif|jpe?g)${re_qstr}${re_src_end}#i";
    $cdn_admin_replacement_regexes[] = "#${re_src_st}${re_path0}/wp-admin/load-s(?:cript|tyle)s\\.php${re_qstr}${re_src_end}#i";
}

