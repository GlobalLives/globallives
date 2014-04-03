<?php
	global $field_keys;
        
/*	==========================================================================
	Comment / Tag
	========================================================================== */
        
        $hashtag_regex = "/#\S*\w/i";
        
        if ( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) )  {
            add_filter('get_comment', 'style_hashtags');
            add_filter('the_comments', 'style_hashtags_on_comments_query');
        }
        
        function style_hashtags($comment) {
            global $hashtag_regex;
            preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
            foreach ( $hashtags[0] as $hashtag ) {
                if ( !empty($hashtag) ) {
                    $comment->comment_content = str_replace($hashtag, sprintf('<span class="tag">%s</span>', $hashtag), $comment->comment_content);
                }
            }
            return $comment;
        }
        
        
        function style_hashtags_on_comments_query($comments) {
            foreach ($comments as $k => $comment)
                $comments[$k] = style_hashtags($comment);
            
            return $comments;
        }
        
        add_action('wp_insert_comment', 'parse_hashtags_in_comments', 10, 2);
        function parse_hashtags_in_comments($comment_id, $comment) {
            global $hashtag_regex;
            
            // Restrict this to clips for now
            if ( 'clip' == get_post_type( $comment->comment_post_ID ) ) {
                // Do we have #tags
                preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
                foreach ( $hashtags[0] as $hashtag ) {
                    if ( !empty($hashtag) ) {
                        $clip_tag = wp_insert_term( str_replace('#', '', $hashtag), 'clip_tags' );
                        $clip_tags[] = $clip_tag->error_data['term_exists'] ? $clip_tag->error_data['term_exists'] : $clip_tag['term_id'];
                    }
                }

                if ( !empty($clip_tags) ) {
                    $clip_tags = array_map('intval', $clip_tags);
                    $clip_tags = array_unique( $clip_tags );
                    wp_set_object_terms( $comment->comment_post_ID, $clip_tags, 'clip_tags', true );
                }
            }
        }
        
        function comment_has_hastag($comment) {
            global $hashtag_regex;
            preg_match_all($hashtag_regex, $comment->comment_content, $hashtags);
            if ( !empty($hashtags[0]) )
                return true;
            else return false;
        }
        
        function comment_tagged_class($comment) {
            if ( comment_has_hastag($comment) ) echo "tagged";
        }