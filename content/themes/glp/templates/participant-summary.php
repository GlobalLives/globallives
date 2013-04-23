<?php while(have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('participant-summary'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>"<?php endif; ?>>
		<header>
	    	<h2 class="participant-title"><?php the_title(); ?><span class="participant-location"> &mdash; <?php the_field('location'); ?></span></h2>
	    </header>
	    <div class="row">
		<div class="span4">
			<a href="https://maps.google.com/maps?q=loc:<?php the_field('latitude'); ?>,<?php the_field('longitude'); ?>&hl=en&ll=<?php the_field('latitude'); ?>,<?php the_field('longitude'); ?>&z=6" target="new"><img class="participant-map" src="http://maps.googleapis.com/maps/api/staticmap?center=<?php the_field('latitude'); ?>,<?php the_field('longitude'); ?>&zoom=6&size=570x250&markers=color:red|<?php the_field('latitude'); ?>,<?php the_field('longitude'); ?>&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100"></a>			
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
			<a class="btn btn-inverse" href="<?php the_permalink(); ?>">&#9658;&nbsp;<?php _e('Learn More','glp'); ?></a>
		</div>
		<div class="span8">
		<?php
			if ( $summary_video = get_field('summary_video') ) {
				query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
				get_template_part('templates/clip','stage');
				wp_reset_query();
			}
		?>
	    </div>
	</article>
<?php endwhile; ?>