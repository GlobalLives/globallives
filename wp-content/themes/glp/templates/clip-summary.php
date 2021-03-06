<?php while(have_posts()) : the_post(); ?>
<article class="participant-clip" id="participant-clip">
	<iframe class="participant-video-embed" id="participant-video-embed-<?php the_ID(); ?>" src="https://www.youtube.com/embed/<?php the_field('youtube_id'); ?>?enablejsapi=1&showinfo=0&modestbranding=1&rel=0&controls=1&wmode=transparent&cc_load_policy=1" wmode="opaque" allowfullscreen="" frameborder="0"></iframe>
	
	
	<div class="participant-video-buttons">
		<a class="btn addthis_button"><i class="icon icon-white icon-share"></i> Share</a>
		<a class="btn btn-embed" href="#embed-<?php the_field('youtube_id'); ?>" data-toggle="modal">&lt;&gt; Embed</a>
		<?php $item_id = get_the_ID(); $class = "btn"; include(locate_template('templates/link-queue.php')); ?>
		<?php if ($download_url = get_field('download_url')) : ?><a href="<?php echo $download_url; ?>" class="btn"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
	</div>
	
	<div class="modal hide" id="embed-<?php the_field('youtube_id'); ?>">
		<div class="modal-header"><?php _e('Embed code','glp'); ?></div>
		<div class="modal-body">
			<input class="copyable" value="&lt;iframe width=&quot;560&quot; height=&quot;315&quot; src=&quot;http://www.youtube.com/embed/<?php the_field('youtube_id'); ?>&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;">
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php _e('Close','glp'); ?></button>
		</div>
	</div>
</article>
<?php endwhile; ?>