<?php
if ( !class_exists( 'CPK_WPCSV_View' ) ) {
	class CPK_WPCSV_View {

		function page( $page_name, $options ) {
			if ( is_array( $options ) ) extract( $options ); // Variabalise for easier access in the view

			$inner_page = "${page_name}_view.php";
			$sidebar_page = "sidebar.php";
			require_once( 'layout.php' );
		}

	}
}

?>
