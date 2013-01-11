<ul class="local-navigation-menu span3">
<?php
	$has_children = count( get_pages('child_of='.$post->ID)) != 0;
	if ( $has_children ) {
	    # Children
	    wp_list_pages('depth=1&title_li=&child_of='.$post->ID);
	} else {
	    # Siblings
	    wp_list_pages('depth=1&title_li=&child_of='.$post->post_parent);
	}
?>
</ul>