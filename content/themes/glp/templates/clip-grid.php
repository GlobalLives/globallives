<?php while(have_posts()) : the_post(); ?>
<article class="participant-clip" id="participant-clip" data-next-clip-id="<?php echo get_next_clip(get_the_ID()); ?>">
	<iframe class="participant-video-embed" id="participant-video-embed-<?php the_ID(); ?>" src="http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>?showinfo=0&modestbranding=1&rel=0&enablejsapi=1&controls=0&wmode=transparent&cc_load_policy=1" wmode="opaque" allowfullscreen="" frameborder="0"></iframe>
	<?php #get_template_part('templates/clip', 'stage-controls') ?>
</article>
<?php endwhile; ?>