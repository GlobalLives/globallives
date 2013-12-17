<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
    <a class="btn-toggle btn-favorite" data-toggle-type="favorite" data-user-id="<?php echo $current_user->ID; ?>" data-clip-id="<?php echo $item_id; ?>"><?php echo apply_filters('clip_toggle_favorite_status', $text, $item_id, $current_user->ID); ?></a>
<?php endif; ?>