<div id="home">
	<?php while (have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('front-page'); ?>>
		<div class="page-content"><?php the_content(); ?></div>
	</article>
	<?php endwhile; ?>
	<div id="explore">
		<h4><?php _e('Explore the Collection','glp'); ?></h4>
		<a href="/explore/#gridview" class="btn btn-inverse"><i class="icon icon-th-large"></i> <?php _e('Grid View','glp'); ?></a>
		<a href="/explore/#mapview" class="btn btn-inverse"><i class="icon icon-globe"></i> <?php _e('Map View','glp'); ?></a>
	</div>
</div>

<div id="stage"></div>