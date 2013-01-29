<?php while(have_posts()) : the_post(); ?>
		<div class="row">
			<iframe class="participant-video-embed span12" src="http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>?showinfo=0&amp;modestbranding=1&amp;rel=0" allowfullscreen="" frameborder="0"></iframe>
			<div class="participant-video-buttons span12">
				<a class="btn addthis_button"><i class="icon icon-white icon-share"></i> Share</a>
				<a class="btn">[EMBED]</a> <a class="btn">[ENQUEUE]</a>
				<?php if ($download_url = get_field('download_url')) : ?><a class="btn"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
			</div>
		</div>
<?php endwhile; ?>