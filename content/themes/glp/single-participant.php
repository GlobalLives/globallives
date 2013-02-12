<?php while (have_posts()) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('participant-detail'); ?>>
	<div id="stage" class="participant-detail-video container">
		<?php
			if ( $summary_video = get_field('summary_video') ) {
				query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
				get_template_part('templates/clip','stage');
				wp_reset_query();
			}
		?>
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
	    		    		<?php if ($dob = get_field('dob')) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
	    		    	</div>
	    		    	<div class="span3">
	    		    		<b><?php _e('Religion','glp'); ?>:</b> <?php the_field('religion'); ?><br>
	    		    		<b><?php _e('Income','glp'); ?>:</b> <?php the_field('income'); ?>
	    		    	</div>
	    		    </div>
	    		    <div class="participant-content">
	    		    	<?php the_content(); ?>
	    		    </div>
	    		    <div class="participant-crew row">
		    		    <h3 class="span6">Crew Members</h3>
		    		    <?php if ( $crew_members = get_participant_crew_members( get_the_ID() )) : foreach ( $crew_members as $crew_member ) : ?>
	    		    		<?php get_template_part('templates/profile','crew_member'); ?>
	    		    		<?php endforeach; endif; ?>
	    		    </div>
	    		</div>
	    		
	    		<div class="span6">
	    		    <div class="row">
	    		
	    		    	<div class="participant-clips span6">
	    		    		<h3><?php _e('Footage','glp'); ?> (<?php echo count(get_field('clips')); ?>)</h3>
	    		    		<?php if (get_field('clips')) : foreach( get_field('clips') as $clip ) : ?>
	    		    			<?php get_template_part('templates/clip','listing'); ?>
	    		    		<?php endforeach; else : ?>
	    		    			<p class="alert alert-error"><?php _e('No clips for this participant.','glp'); ?></p>
	    		    		<?php endif; ?>
	    		    	</div>
	    		
	    		    	<?php /*<div class="span2">
	    		    		<div class="participant-filter-clips">
	    		    		<h4><?php _e('Filter Clips','glp'); ?></h4>
	    		    		<h5><?php _e('By Popular Tags','glp'); ?></h5>
	    		    		<?php if ( $clip_tags = get_participant_clip_tags( get_the_ID() )) : foreach( $clip_tags as $clip_tag ) : ?>
	    		    			<a><?php echo $clip_tag->name; ?></a>
	    		    		<?php endforeach; endif; ?>
	    		    		</div>	    		    		
	    		    	</div>*/?>
	    		    </div>
	    		</div>
	    
	    	</div>
	    </div>
	</div>
</article>
<?php endwhile; ?>