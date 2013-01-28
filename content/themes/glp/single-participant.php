<?php while (have_posts()) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('participant-detail'); ?>>
	<div id="stage" class="participant-detail-video container">
		<div class="row">
			<iframe class="participant-video-embed span12" src="http://www.youtube.com/embed/<?php the_field('summary_video'); ?>?showinfo=0&amp;modestbranding=1&amp;rel=0" allowfullscreen="" frameborder="0"></iframe>
			<div class="participant-video-buttons span12"><a class="btn">[SHARE]</a> <a class="btn">[EMBED]</a> <a class="btn">[ENQUEUE]</a> <a class="btn">[DOWNLOAD]</a></div>
		</div>
	</div>
	
	<div class="participant-detail-content">
	    <div class="container">
	    	<div class="row">
	
	    		<div class="span6">
	    		    <header>
	    		    	<h2 class="participant-title"><span class="participant-name"><?php the_title(); ?> </span> &mdash; <span class="participant-location"><?php the_field('location'); ?></span></h2>
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
	    		
	    		    	<div class="participant-clips span4">
	    		    		<h3><?php _e('Footage','glp'); ?> (<?php echo count(get_field('clips')); ?>)</h3>
	    		    		<?php foreach( get_field('clips') as $clip ) : ?>
	    		    			<article class="participant-clip">
		    		    			<div class="row">
		    		    				<div class="clip-thumbnail span2" data-clip-id="<?php echo $clip->ID; ?>"><img src="http://img.youtube.com/vi/<?php the_field('youtube_id', $clip->ID); ?>/0.jpg"></div>
		    		    				<div class="span2">
	    		    						<h5 class="clip-title"><?php echo $clip->post_title; ?></h5>
	    		    						<p class="clip-duration"><?php the_field('duration',$clip->ID); ?></p>
	    		    						<?php if ($download_url = get_field('download_url',$clip->ID)) : ?><a class="" href="<?php echo $download_url; ?>"><i class="icon icon-white icon-arrow-down"></i> Download</a><?php endif; ?> <a class="" href=""><i class="icon icon-white icon-plus"></i> Queue</a>
	    		    					</div>
		    		    			</div>
	    		    			</article>
	    		    		<?php endforeach; ?>
	    		    	</div>
	    		
	    		    	<div class="span2">
	    		    		<div class="participant-filter-clips">
	    		    		<h4><?php _e('Filter Clips','glp'); ?></h4>
	    		    		<h5><?php _e('By Popular Tags','glp'); ?></h5>
	    		    		<?php foreach( get_participant_clip_tags(get_the_ID()) as $clip_tag ) : ?>
	    		    			<a><?php echo $clip_tag->name; ?></a>
	    		    		<?php endforeach; ?>
	    		    		</div>	    		    		
	    		    	</div>
	    		    </div>
	    		</div>
	    
	    	</div>
	    </div>
	</div>
</article>
<?php endwhile; ?>