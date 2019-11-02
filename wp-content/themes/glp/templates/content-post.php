	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-bg="<?php the_featured_image_src($post->ID, 'large'); ?>">
		<div class="entry-date"><?php echo get_the_date(); ?></div>
		<header class="entry-header">
			<h3 class="entry-category"><?php the_category(' '); ?></h3>
	    	<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	    </header>
	    <div class="entry-content">
		    <?php the_excerpt(); ?>
		    <a class="btn" href="<?php the_permalink(); ?>">&#9658;&nbsp;<?php _e('Full Story','glp'); ?></a>
			</div>
		</footer>
	</article>