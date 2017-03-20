<?php
include('../../../wp-admin/admin.php'); 

if (!current_user_can(IJSC_CAPABILITY_REQUIRED)) {
    wp_die(__('You do not have permission to view this page', 'ijsc')); 
} 
wp_enqueue_style('colors'); 
wp_enqueue_style('wp-admin');
wp_enqueue_style('media');
wp_enqueue_script('ijsc'); 
wp_enqueue_style('ijsc'); 

// Don't want the admin bar showing up
wp_dequeue_style('admin-bar'); 
wp_dequeue_script('admin-bar'); 
remove_action('wp_footer', 'wp_admin_bar_render', 1000); 

?>
<!DOCTYPE html>
<html>
    <head>
    <?php iframe_header(); ?>
    </head>
    <body id="" style="height:100px;">
        <div id="media-upload-header">
	<ul id='sidemenu'>
            <li id='tab-ijsc-js'><a href='' class='current' data-child="ijsc-insertJS"><?php _e('JavaScript', 'ijsc'); ?></a></li>
            <li id='tab-ijsc-css'><a href='' data-child="ijsc-insertCSS"><?php _e('CSS', 'ijsc'); ?></a></li>
            <li id='tab-ijsc-help'><a href='' data-child="ijsc-help"><?php _e('Help', 'ijsc'); ?></a></li>
        </ul>
	</div>
        <div id="ijsc-wrap" style="padding: 16px;">
        <div id="ijsc-insertJS" class="ijsc-view">
            <div class="updated" id="ijsc-js-message">
                <p style="text-align:justify;">
                    <?php
                    _e('Use the field below to include JavaScript on this post or page. Any code inserted will be included in the &lt;head&gt; section of your page. You can include the &lt;script&gt;&lt;/script&gt; tags or you can leave them out. If you leave them out, they will be added automatically.', 'ijsc'); 
                    ?>
                </p>                
            </div>
            <div style="margin: 16px;">
                <textarea id="ijsc-js-code" style="width:100%;resize:none;"></textarea>
            </div>
        </div>
        
        <div id="ijsc-insertCSS" class="ijsc-view" style="display:none;">
            
            <div class="updated" style="text-align:justify;">
                <p>
                    <?php
                    _e('Enter any custom CSS for this post or page into the field below. You do not need to include the &lt;style&gt; tags, although if you do they will be included. If you leave them out the tags will be added.', 'ijsc'); 
                    ?>
                </p>                
            </div>
            <div style="margin:16px;">
                <textarea id="ijsc-css-code" style="width:100%;resize:none;"></textarea>
            </div>
            
        </div>
        
        <div id="ijsc-help" class="ijsc-view" style="display:none; ">
            <div id="ijsc-help-wrap" style="overflow:auto;text-align:justify; ">
                <div class="updated">
                    <p style="text-align:justify;">
                        <strong><?php _e("Most important tip...", 'ijsc'); ?></strong><br>
                        <?php _e("JavaScript and CSS entered through this plugin is not validated or checked to see if it's safe. Use at your own risk.", 'ijsc'); ?>
                    </p>
                </div>
                <div class="help">
                    <h3><?php _e('Usage', 'ijsc'); ?></h3>
                    <p>
                        <?php _e("When activated, a new icon is added to the post edit page near the icon you use to add media to a post. Clicking the new icon brings up a screen where you can insert JavaScript and CSS that will be included with the current post. Odds are you've already figured that part out though if you're reading this.", 'ijsc'); ?>
                    </p>
                    <p>
                        <?php
                        _e('Anything entered into the text field on the JavaScript tab will be inserted into the &lt;head&gt; section of your web page. Same is true of the CSS tab. This allows you to insert arbitrary JavaScript and CSS into any post or page you would like without having to resort to loading it on all pages.', 'ijsc'); 
                        ?>
                    </p>

                    <h3><?php _e('What can I enter?', 'ijsc'); ?></h3>
                    <p><?php _e("Any JavaScript or CSS. Nothing is checked to make sure it's valid so you can put in whatever you want.", 'ijsc'); ?></p>
                    <p><?php _e("One note. You can include the &lt;script&gt; &amp &lt;/script&gt; or &lt;style&gt; &amp &lt;/style&gt; tags around your code, oy you can leave it off. If they're not there the plugin will wrap your code accordingly.", 'ijsc'); ?></p>
                    
                    <h3><?php _e('Who can use it?', 'ijsc'); ?></h3>
                    <p>
                        <?php _e('By default, any user with the permission <code>upload_files</code> can insert JavaScript or CSS into a post. You can change this by editing the insert-javascript-css.php file and looking for the constant <code>IJSC_CAPABILITY_REQUIRED</code>. Change its value to whatever capability you would like, including a new capability if you want to go that route.', 'ijsc'); ?>
                    </p>
                    
                    <h3><?php _e('What about archive pages?', 'ijsc'); ?></h3>
                    <p><?php _e("By default, this plugin will insert any JavaScript or CSS included with a post onto the archive pages as well.", 'ijsc'); ?></p>
                    <p><?php _e("This can be a problem if two or more posts on the same archive page have JavaScript or CSS that conflicts. If this happens, you can disable addition of the JS or CSS by including the custom field <code>ijsc_single_only</code> with any value on your post. When <code>ijsc_single_only</code> is present for a post the JavaScript or CSS will only be added to the &lt;head&gt; section of the page if it's a single page. For the WordPress geeks, that means it will only be inserted when <code>is_single()</code> is true. ", 'ijsc'); ?></p>
                    
                    <h3><?php _e('Help, I need more help!', 'ijsc'); ?></h3>
                    <p><?php _e('Visit <a href="http://www.nutt.net/tags/insert-javascript-css/" target="_blank">my website</a> for more information about this plugin.', 'ijsc'); ?></p>
                    <p><?php _e("I'm also occassionally on the WordPress forums, and watch for topics on my plugins. But you'll get a much quicker response from my site.", 'ijsc'); ?></p>
                </div>
            </div>
        </div>
        </div>
        
        <div id="ijsc-footer" style="margin-top:16px;text-align:right;padding-right:16px;position:absolute;bottom:16px;right:16px;">
            <a href="" class="button-primary" id="ijsc-save"><?php _e('Save'); ?></a>
            <a href="" class="button-secondary" id="ijsc-cancel"><?php _e('Cancel'); ?></a>
        </div>
        
    </body>
<script type="text/javascript">
    ijsc.initFrame(); 
</script>
<?php wp_footer(); ?>
</html>