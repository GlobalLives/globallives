<?php global $clip; ?>
<article class="participant-clip-listing">
    <div class="row">
    	<div class="clip-thumbnail span2" data-clip-id="<?php echo $clip->ID; ?>"><img src="http://img.youtube.com/vi/<?php the_field('youtube_id', $clip->ID); ?>/0.jpg"></div>
    	<div class="span4">
    		<h5 class="clip-title"><?php echo $clip->post_title; ?></h5>
    		<p class="clip-duration"><?php the_field('duration',$clip->ID); ?></p>
    		<?php if ($download_url = get_field('download_url',$clip->ID)) : ?><a class="" href="<?php echo $download_url; ?>"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?>
    	</div>
    </div>
</article>
