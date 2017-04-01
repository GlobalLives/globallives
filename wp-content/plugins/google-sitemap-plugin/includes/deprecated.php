<?php
/**
* Includes deprecated functions
*/

/** 
* Renaming old version options
* @deprecated since 3.0.8
* @todo remove after 28.10.2017
*/
if ( ! function_exists( 'gglstmp_check_old_options' ) ) {
	function gglstmp_check_old_options() {
		if ( is_multisite() ) {
			global $wpdb;
			if ( get_site_option( 'gglstmp_settings' ) ) {
				$old_options = get_site_option( 'gglstmp_settings' );
				if ( ! get_site_option( 'gglstmp_options' ) )
					add_site_option( 'gglstmp_options', $old_options );
				else
					update_site_option( 'gglstmp_options', $old_options );
				delete_site_option( 'gglstmp_settings' );
			}
			$blogids  = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			$old_blog = $wpdb->blogid;
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				if ( get_option( 'gglstmp_settings' ) ) {
					$old_options = get_option( 'gglstmp_settings' );
					if ( ! get_option( 'gglstmp_options' ) )
						add_option( 'gglstmp_options', $old_options );
					else
						update_option( 'gglstmp_options', $old_options );
					delete_option( 'gglstmp_settings' );
				}
			}
			switch_to_blog( $old_blog );
		} else {
			if ( get_option( 'gglstmp_settings' ) ) {
				$old_options = get_option( 'gglstmp_settings' );
				if ( ! get_option( 'gglstmp_options' ) )
					add_option( 'gglstmp_options', $old_options );
				else
					update_option( 'gglstmp_options', $old_options );
				delete_option( 'gglstmp_settings' );
			}
		}
	}
}