<div id="home">
	<?php while (have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('front-page'); ?>>
		<div class="page-content"><?php the_content(); ?></div>
	</article>
	<?php endwhile; ?>
</div>
<div id="stage"></div>