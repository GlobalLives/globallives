<?php if (has_excerpt()) : ?>
	<div class="page-excerpt"><?php the_excerpt(); ?></div>
<?php elseif (has_excerpt( $post->post_parent )) : $parent = get_post( $post->post_parent ); ?>
	<div class="page-excerpt parent-excerpt"><?php echo apply_filters('the_excerpt', $parent->post_excerpt); ?></div>
<?php endif; ?>