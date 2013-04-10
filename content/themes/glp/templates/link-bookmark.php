<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
    <a class="btn-toggle <?php echo $class; ?>" data-toggle-type="bookmark" data-user-id="<?php echo $current_user->ID; ?>" data-clip-id="<?php echo $item_id; ?>"><?php echo apply_filters('clip_toggle_bookmark_status', $text, $item_id, $current_user->ID); ?></a>
<?php endif; ?>