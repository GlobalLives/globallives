<?php while (have_posts()) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('participant-detail'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>"<?php endif; ?>>
	<div class="participant-detail-video container">
	    <iframe class="participant-video-embed span12" src="http://www.youtube.com/embed/<?php the_field('summary_video'); ?>?showinfo=0&amp;modestbranding=1&amp;rel=0" allowfullscreen="" frameborder="0"></iframe>
	    <div class="participant-video-buttons span12">[SHARE] [EMBED] [ENQUEUE] [DOWNLOAD]</div>
	</div>
	
	<div class="participant-detail-content">
	    <div class="container">
	    	<div class="row">
	
	    		<div class="span6">
	    		    <header>
	    		    	<h2 class="participant-title"><?php the_title(); ?> <span class="participant-location"><?php the_field('location'); ?></span></h2>
	    		    </header>
	    		    <div class="participant-meta row">
	    		    	<div class="span3">
	    		    		<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field('occupation'); ?><br>
	    		    		<b><?php _e('Date of Birth','glp'); ?>:</b> <?php the_field('dob'); ?>
	    		    	</div>
	    		    	<div class="span3">
	    		    		<b><?php _e('Religion','glp'); ?>:</b> <?php the_field('religion'); ?><br>
	    		    		<b><?php _e('Income','glp'); ?>:</b> <?php the_field('income'); ?>
	    		    	</div>
	    		    </div>
	    		    <div class="participant-content">
	    		    	<?php the_content(); ?>
	    		    </div>
	    		    <h3>Crew Members</h3>
	    		</div>
	    		
	    		<div class="span6">
	    		    <div class="row">
	    		
	    		    	<div class="span4">
	    		    		<h3><?php _e('Footage','glp'); ?></h3>
	    		    	</div>
	    		
	    		    	<div class="span2">
	    		    		<h3><?php _e('Filter Clips','glp'); ?></h3>
	    		    	</div>
	    		    </div>
	    		</div>
	    
	    	</div>
	    </div>
	</div>
</article>
<?php endwhile; ?>