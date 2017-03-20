<?php
/*
Plugin Name: Stop long comments
Description: A security precaution to stop comments that are too long. 
Version: 0.0.4
Author: WPEngine
Author URI: wpengine.com
License: GPLv2
*/
add_filter( 'pre_comment_content', 'wpengine_die_on_long_comment', 9999 );

function wpengine_die_on_long_comment( $text ) {
    if ( strlen($text) > 13000 ) {
        wp_die( 
            /*message*/ 'This comment is longer than the maximum allowed size and has been dropped.', 
            /*title*/ 'Comment Declined',
            /*args*/ array( 'response' => 413 )
        );
    }
    return $text;
}
