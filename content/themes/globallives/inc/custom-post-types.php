<?php

add_action( 'init', 'create_custom_post_types' );
function create_custom_post_types() {

	# Participants
	register_post_type( 'participant', array(
		'labels' => array(
			'name' 			=> __( 'Participants' ),
			'singular_name'	=> __( 'Participant' )
		),
		'public' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
		'menu_position' => 5,
		'has_archive' => true,
		'rewrite' => array(
			'slug' 			=> 'participants',
			'with_front' 	=> true
		)
	));
	
}
?>