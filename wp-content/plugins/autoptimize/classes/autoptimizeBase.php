<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class autoptimizeBase
{
	protected $content = '';
	
	public function __construct($content)
	{
			$this->content = $content;
			//Best place to catch errors
	}
	
	//Reads the page and collects tags
	abstract public function read($justhead);
	
	//Joins and optimizes collected things
	abstract public function minify();
	
	//Caches the things
	abstract public function cache();
	
	//Returns the content
	abstract public function getcontent();
	
	//Converts an URL to a full path
	protected function getpath($url) {
	if (strpos($url,'//')===0) {
		$url = "http:".$url;
	} else if ((strpos($url,'//')===false) && (strpos($url,parse_url(AUTOPTIMIZE_WP_SITE_URL,PHP_URL_HOST))===false)) {
			$url = AUTOPTIMIZE_WP_SITE_URL.$url;
		}
        $path = str_replace(AUTOPTIMIZE_WP_ROOT_URL,'',$url);
	    if(preg_match('#^((https?|ftp):)?//#i',$path)) {
            /** External script/css (adsense, etc) */
       		return false;
       	}
        $path = str_replace('//','/',WP_ROOT_DIR.$path);
        return $path;
	}
	
	// logger
	protected function ao_logger($logmsg) {
		$logfile=WP_CONTENT_DIR.'/ao_log.txt';
		$logmsg.="\n";
		file_put_contents($logfile,$logmsg,FILE_APPEND);
	}

	// hide everything between noptimize-comment tags
	protected function hide_noptimize($noptimize_in) {
		if ( preg_match( '/<!--\s?noptimize\s?-->/', $noptimize_in ) ) { 
			$noptimize_out = preg_replace_callback(
				'#<!--\s?noptimize\s?-->.*?<!--\s?/\s?noptimize\s?-->#is',
				create_function(
					'$matches',
					'return "%%NOPTIMIZE%%".base64_encode($matches[0])."%%NOPTIMIZE%%";'
				),
				$noptimize_in
			);
		} else {
			$noptimize_out = $noptimize_in;
		}
		return $noptimize_out;
	}
	
	// unhide noptimize-tags
	protected function restore_noptimize($noptimize_in) {
		if ( strpos( $noptimize_in, '%%NOPTIMIZE%%' ) !== false ) { 
			$noptimize_out = preg_replace_callback(
				'#%%NOPTIMIZE%%(.*?)%%NOPTIMIZE%%#is',
				create_function(
					'$matches',
					'return stripslashes(base64_decode($matches[1]));'
				),
				$noptimize_in
			);
		} else {
			$noptimize_out = $noptimize_in;
		}
		return $noptimize_out;
	}

	protected function hide_iehacks($iehacks_in) {
		if ( strpos( $iehacks_in, '<!--[if' ) !== false ) { 
			$iehacks_out = preg_replace_callback(
				'#<!--\[if.*?\[endif\]-->#is',
				create_function(
					'$matches',
					'return "%%IEHACK%%".base64_encode($matches[0])."%%IEHACK%%";'
				),
				$iehacks_in
			);
		} else {
			$iehacks_out = $iehacks_in;
		}
		return $iehacks_out;
	}

	protected function restore_iehacks($iehacks_in) {
		if ( strpos( $iehacks_in, '%%IEHACK%%' ) !== false ) { 
			$iehacks_out = preg_replace_callback(
				'#%%IEHACK%%(.*?)%%IEHACK%%#is',
				create_function(
					'$matches',
					'return stripslashes(base64_decode($matches[1]));'
				),
				$iehacks_in
			);
		} else {
			$iehacks_out=$iehacks_in;
		}
		return $iehacks_out;
	}

        protected function hide_comments($comments_in) {
                if ( strpos( $comments_in, '<!--' ) !== false ) {
                        $comments_out = preg_replace_callback(
                                '#<!--.*?-->#is',
                                create_function(
                                        '$matches',
                                        'return "%%COMMENTS%%".base64_encode($matches[0])."%%COMMENTS%%";'
                                ),
                                $comments_in
                        );
                } else {
                        $comments_out = $comments_in;
                }
                return $comments_out;
        }

        protected function restore_comments($comments_in) {
                if ( strpos( $comments_in, '%%COMMENTS%%' ) !== false ) {
                        $comments_out = preg_replace_callback(
                                '#%%COMMENTS%%(.*?)%%COMMENTS%%#is',
                                create_function(
                                        '$matches',
                                        'return stripslashes(base64_decode($matches[1]));'
                                ),
                                $comments_in
                        );
                } else {
                        $comments_out=$comments_in;
                }
                return $comments_out;
	}
	
	protected function url_replace_cdn($url) {		
		if (!empty($this->cdn_url)) {
			$url=str_replace(AUTOPTIMIZE_WP_SITE_URL,rtrim($this->cdn_url,'/'),$url);
		}
		return $url;
	}

	protected function warn_html() {
		$this->content .= "<!--noptimize--><!-- Autoptimize found a problem with the HTML in your Theme, check if the title or body-tags are missing --><!--/noptimize-->";
	}
}
