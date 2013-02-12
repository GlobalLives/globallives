<?php

add_action( 'after_setup_theme', 'create_custom_menus' );
function create_custom_menus() {

	register_nav_menus(array(

		# Header Menus
		'primary_header_navigation' => __('Primary Header Navigation', 'glp'),
		'social_navigation' => __('Social Media Navigation', 'glp'),
		
		# Footer Menus
		'primary_footer_navigation' => __('Primary Footer Navigation', 'glp'),
		'about_footer_navigation' => __('About Footer Navigation', 'glp'),
		'resources_footer_navigation' => __('Resources Footer Navigation', 'glp')
		
	));
}

function wp_nav_menu_title( $theme_location ) {
	$title = '';
	if ( $theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $theme_location ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $theme_location ] );
			
		if( $menu && $menu->name ) {
			$title = $menu->name;
		}
	}
	return apply_filters( 'wp_nav_menu_title', $title, $theme_location );
}

add_action( 'widgets_init', 'create_custom_sidebars' );
function create_custom_sidebars() {

	register_sidebar(array(
		'name' => __( 'Footer Sidebar', 'glp' ),
		'id' => 'sidebar-footer',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>'
	));
	
	register_sidebar(array(
		'name' => __( 'Blog Sidebar', 'glp' ),
		'id' => 'sidebar-blog',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>'
	));
	
	register_sidebar(array(
		'name' => __( 'Modules Sidebar', 'glp' ),
		'id' => 'sidebar-modules',
		'before_widget' => '<aside id="%1$s" class="widget %2$s span4"><div class="widget-inner">',
		'after_widget' => '</div></aside>',
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>'
	));
	
	register_sidebar(array(
		'name' => __( 'Events Sidebar', 'glp' ),
		'id' => 'sidebar-events',
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget' => '</div></aside>',
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>'
	));

}

?>