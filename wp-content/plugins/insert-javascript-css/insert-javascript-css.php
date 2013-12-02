<?php
/*
Plugin Name:    Insert JavaScript & CSS
Plugin URI:     http://www.nutt.net/tag/insert-javascript-css/
Description:    Adds a field to the post / page edit screens to allow you to insert custom JavaScript and CSS for just that post or page.
Version:        0.2
Author:         Ryan Nutt
Author URI: 	http://www.nutt.net
License:        GPLv2
*/

/* Settings */

/**
 * This is the WordPress capability that is required to be able to insert JavaScript
 * or CSS in posts and pages. 
 */
define('IJSC_CAPABILITY_REQUIRED', 'upload_files');

/* End of settings - you shouldn't need to edit anything below this point */

$ijsc = new InsertJavaScriptCSS();
class InsertJavaScriptCSS {
    
    public function __construct() {
        
        add_action('admin_head', array($this, 'admin_head'));
        wp_register_script('ijsc', plugins_url('/js/ijsc.js', __FILE__), array('jquery')); 
        wp_register_style('ijsc', plugins_url('/css/ijsc.css', __FILE__)); 
        add_action('admin_print_styles', array($this, 'print_styles')); 
        
        add_action('post_submitbox_misc_actions', array($this, 'post_fields'));
        add_action('save_post', array($this, 'save_post'));
        add_action('the_posts', array($this, 'the_posts')); 
    }
    
    public function the_posts($posts) {
        add_action('wp_head', array($this, 'add_to_head')); 
        return $posts; 
    }
    
    public function add_to_head() {
        global $posts; 
        $ray = array(); 
        foreach ($posts as $p) {
            $ray[] = $p->ID;
        }
        
        if (count($ray) < 1) {
            return; 
        }
        $ids = implode(',',$ray);
        
        $myPosts = get_posts(array(
            'post_type' => 'any',
            'post__in' => $ray,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_ijsc-js',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => '_ijsc-css',
                    'value' => '',
                    'compare' => '!='
                )
            )
        ));
        
        
        if (empty($myPosts)) {
            return; 
        }
        
        foreach ($myPosts as $p) {
            $vals = get_post_meta($p->ID);
             
            if (isset($vals['ijsc_single_only'][0]) && !is_single()) {
                continue; 
            }
            
            if (isset($vals['_ijsc-css'][0])) {
                echo $this->formatCSS($vals['_ijsc-css'][0]);
            }
            if (isset($vals['_ijsc-js'][0])) {
                echo $this->formatJS($vals['_ijsc-js'][0]); 
            }
            
        }
        
    }
    
    private function formatCSS($css) {
        $css = trim($css);
        
        if (!preg_match('/^<style(.*)<\/style>$/s', $css)) {
            $css = '<style type="text/css">'."\n".$css."\n</style>"; 
        }
        
        echo "\n".$css."\n";
    }
    
    private function formatJS($js) {
        $js = trim($js);
        
        if (!preg_match('/^<script(.*)<\/script>$/s', $js)) {
            $js = '<script type="text/javascript">'."\n".$js."\n</script>"; 
        }
        
        echo "\n".$js."\n"; 
    }
    
    public function save_post($postID) { 
        if (!current_user_can(IJSC_CAPABILITY_REQUIRED)) {
            return;
        }
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        if (isset($_POST['ijsc-field-js']) && trim($_POST['ijsc-field-js']) != '') {
            update_post_meta($postID, '_ijsc-js', $_POST['ijsc-field-js']);
        }
        else {
            delete_post_meta($postID, '_ijsc-js'); 
        }
        if (isset($_POST['ijsc-field-css']) && trim($_POST['ijsc-field-css']) != '') {
            update_post_meta($postID, '_ijsc-css', $_POST['ijsc-field-css']); 
        }
        else {
            delete_post_meta($postID, '_ijsc-css'); 
        }
    }
    
    /**
     * Add hidden input fields to the page. They're going in the submit meta
     * box because it has an easy action to hook on to.
     */
    public function post_fields($p) {
        global $post; 
        
        wp_enqueue_script('ijsc');
        
        echo '<textarea style="display:none;" name="ijsc-field-js" id="ijsc-field-js">'.htmlentities(get_post_meta($post->ID, '_ijsc-js', true)).'</textarea>';
        echo '<textarea style="display:none;" name="ijsc-field-css" id="ijsc-field-css">'.htmlentities(get_post_meta($post->ID, '_ijsc-css', true)).'</textarea>'; 
        
        echo '<script type="text/javascript">ijsc.initPostPage();</script>'; 
    }    
    
    
    public function admin_head() {
        if (current_user_can(IJSC_CAPABILITY_REQUIRED)) {
             add_action('media_buttons_context', array($this, 'media_buttons'));
             wp_enqueue_script('ijsc'); 
             
        }
    }
    
    public function print_styles() {
        wp_enqueue_script('ijsc'); 
    }
    
    public function media_buttons($cnt) {
        if (!current_user_can(IJSC_CAPABILITY_REQUIRED)) {
            /* User doesn't have capability, just return what's already there */
            return $cnt; 
        }
        
        global $post_ID, $temp_ID;
        
        $new = '<a href="'.plugins_url('/ijsc-frame.php', __FILE__).'?postID='.$post_ID.'&TB_iframe=true" class="thickbox" id="ijsc_link" title="'.__('Insert JavaScript / CSS', 'ijsc').'">';
        $new .= '<img title="'.__('Insert JavaScript / CSS', 'ijsc').'" alt="'.__('Insert JavaScript / CSS', 'ijsc').'" src="'.plugins_url('/img/add_icon_bw.png', __FILE__).'" data-color="'.plugins_url('/img/add_icon.png', __FILE__).'" data-bw="'.plugins_url('/img/add_icon_bw.png', __FILE__).'" data-edit="'.__('Edit existing JavaScript / CSS', 'ijsc').'" data-insert="'.__('Insert JavaScript / CSS', 'ijsc').'" id="ijsc-icon">';
        $new .= '</a>'; 
        
        return $cnt . $new; 
        
    }
    
}

?>