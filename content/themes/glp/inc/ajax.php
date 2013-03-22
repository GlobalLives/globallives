<?php

add_action( 'wp_ajax_nopriv_get_participant_summary', 'get_participant_summary' );
add_action( 'wp_ajax_get_participant_summary', 'get_participant_summary' );
 
function get_participant_summary() {
	$post_id = $_POST['post_id'];

	query_posts(array( 'post_type' => 'participant', 'p' => $post_id ));
	$response = get_template_part('templates/participant', 'summary');
	
	echo $response; 
	exit;
}

add_action( 'wp_ajax_nopriv_get_participant_clip', 'get_participant_clip' );
add_action( 'wp_ajax_get_participant_clip', 'get_participant_clip' );
 
function get_participant_clip() {
	$clip_id = $_POST['clip_id'];

	query_posts(array( 'post_type' => 'clip', 'p' => $clip_id ));
	$response = get_template_part('templates/clip', 'stage');
	
	echo $response; 
	exit;
}

add_action( 'wp_ajax_nopriv_clip_submit_comment', 'clip_submit_comment' );
add_action( 'wp_ajax_clip_submit_comment', 'clip_submit_comment' );
 
function clip_submit_comment() {
	
    $time = current_time('mysql');
    $clip_id = (int) $_POST['post_id'];
    $user = wp_get_current_user();
    $comment = sanitize_text_field($_POST['comment']);
    $minutes = (int) $_POST['minutes'];
    $seconds = (int) $_POST['seconds'];
    $position = (int) $_POST['position'];
    
    if ( !$clip_id )
        $r['message'] = __("There was an error", 'glp');
    if ( !$user->ID )
        $r['message'] = __("You need to be logged in to comment", 'glp');
    if ( !$comment )
        $r['message'] = __("A comment is required", 'glp');
    if ( ! ($minutes || $seconds) )
        $r['message'] = __("A time is required", 'glp');
    
    // Bail if we have an error
    if ($r['message']) {
        $r['success'] = 0;
        echo json_encode($r); 
        exit;
    }
    
    $data = array(
        'comment_post_ID' => $clip_id,
        'comment_author' => $user->user_login,
        'comment_author_email' => $user->user_email,
        'comment_author_url' => $user->user_url,
        'comment_content' => $comment,
        'user_id' => $user->ID,
        'comment_date' => $time,
        'comment_approved' => 1,
        'comment_author_IP' => $_SERVER['REMOTE_ADDR']
    );

    $comment_id = wp_insert_comment($data);
    $comment_meta = update_comment_meta( $comment_id, 'clip_time', array('m' => $minutes, 's' => $seconds, 'p' => $position ) );
    if ($comment_id && $comment_meta) {
        $r['message'] = __("Comment added", 'glp');
        $r['success'] = 1;
    }
	
    echo json_encode($r); 
    exit;
}