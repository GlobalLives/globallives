<?php while(have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('participant-summary'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>"<?php endif; ?>>
		<header>
	    	<h2 class="participant-title"><?php the_title(); ?><span class="participant-location"> &mdash; <?php the_field('location'); ?></span></h2>
	    </header>
	    <div class="row">
		<div class="span4">
			<img class="participant-map" src="http://maps.googleapis.com/maps/api/staticmap?center=<?php echo urlencode(get_field('location')); ?>&zoom=6&size=570x250&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100">
			<div class="participant-meta row">
	    		<div class="span2">
	    			<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field('occupation'); ?><br>
	    			<?php if ($dob = get_field('dob')) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
	    		</div>
	    		<div class="span2">
	    			<b><?php _e('Religion','glp'); ?>:</b> <?php the_field('religion'); ?><br>
	    			<b><?php _e('Income','glp'); ?>:</b> <?php the_field('income'); ?>
	    		</div>
	    	</div>
	    	<div class="participant-content">
			    <?php the_content(); ?>
			</div>
			<a class="btn" href="<?php the_permalink(); ?>">&#9658; <?php _e('Full Story','glp'); ?></a>
		</div>
		<div class="span8">
			<?php $summary_videos = get_field('summary_video'); ?>
			<iframe src="http://www.youtube.com/embed/<?php the_field('youtube_id', $summary_videos[0]->ID); ?>?showinfo=0&amp;modestbranding=1&amp;rel=0" height="405" width="720" allowfullscreen="" frameborder="0"></iframe>
			[SHARE] [QUEUE] [DOWNLOAD]
		</div>
	    </div>
	</article>
<?php endwhile; ?>