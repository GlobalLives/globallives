<?php 
fb($comment,'$comment'); 
$user = get_userdata($comment->user_id);
?>
<div class="comment">
    <div class="avatar"><?php echo get_avatar( $comment->user_id, 26, false, $user->data->display_name ); ?></div>
    <div class="comment-content">
        <p class="user"><?php echo $user->data->display_name ?> <?php printf( __( '(%s ago)' ), human_time_diff( strtotime($comment->comment_date), current_time('timestamp') ) ); ?></p>
        <p><?php echo esc_html($comment->comment_content); ?></p>
    </div>
</div>