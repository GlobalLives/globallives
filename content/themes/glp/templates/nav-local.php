<ul class="local-navigation-menu span3">
<?php
	$ancestors = get_post_ancestors( $post->ID );
	if ( $ancestors ) {
	    # Page is a sub-page, return all pages under its top level ancestor
	    wp_list_pages('depth=0&title_li=&child_of='.end($ancestors));
	} else {
	    # Page is top level, return all of its descendants
	    wp_list_pages('depth=0&title_li=&child_of='.$post->ID);
	}
?>
</ul>