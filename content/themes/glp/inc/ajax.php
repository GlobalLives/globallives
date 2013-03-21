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