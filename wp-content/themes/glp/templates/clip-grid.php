<?php while(have_posts()) : the_post(); global $participant; ?>
<article class="participant-clip" id="participant-clip" data-next-clip-id="<?php echo get_next_clip(get_the_ID()); ?>">
	<div class="participant-meta">
		<h3><?php echo $participant->post_title; ?></h3>
		<p><?php the_field('location',$participant->ID); ?></p>
	</div>
	<iframe class="participant-video-embed" id="participant-video-embed-<?php the_ID(); ?>" src="http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>?showinfo=0&modestbranding=1&rel=0&enablejsapi=1&controls=0&wmode=transparent&cc_load_policy=1" wmode="opaque" allowfullscreen="" frameborder="0"></iframe>
</article>
<?php endwhile; ?>