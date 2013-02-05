<div id="home">
	<?php while (have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('front-page'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>"<?php endif; ?>>
		<div class="page-content"><?php the_content(); ?></div>
		<h3><?php _e('Explore the Collection','glp'); ?></h3>
		<p><a class="btn" href="<?php echo get_permalink(get_page_by_title( 'Explore the Collection' )); ?>"><i class="icon-globe"></i> Map View</a></p>
	</article>
	<?php endwhile; ?>
</div>
<div id="stage"></div>