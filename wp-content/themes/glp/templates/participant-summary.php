<?php global $field_keys; while(have_posts()) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('participant-summary'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>"<?php endif; ?>>
		<header>
	    	<h2 class="participant-title"><?php the_title(); ?><span class="participant-location"> &mdash; <?php the_field($field_keys['participant_location'],get_the_ID()); ?></span></h2>
	    	<?php $item_id = get_the_ID(); $class = "btn"; include(locate_template('templates/link-favorite.php')); ?>

	    </header>
	    <div class="row">
		<div class="span4">
			<a class="btn btn-inverse map-explore" href="<?php the_permalink(); echo "#mapview"; ?>"><i class="icon icon-globe"></i> <?php _e('Explore in map','glp'); ?></a>
			<a href="https://maps.google.com/maps?q=loc:<?php the_field($field_keys['participant_latitude'],get_the_ID()); ?>,<?php the_field($field_keys['participant_longitude'],get_the_ID()); ?>&hl=en&ll=<?php the_field($field_keys['participant_latitude'],get_the_ID()); ?>,<?php the_field($field_keys['participant_longitude'],get_the_ID()); ?>&z=6" target="new"><img class="participant-map" src="http://maps.googleapis.com/maps/api/staticmap?center=<?php the_field($field_keys['participant_latitude'],get_the_ID()); ?>,<?php the_field($field_keys['longitude'],get_the_ID()); ?>&zoom=6&size=570x250&markers=color:red|<?php the_field($field_keys['participant_latitude'],get_the_ID()); ?>,<?php the_field($field_keys['participant_longitude'],get_the_ID()); ?>&maptype=roadmap&sensor=false&style=feature:all%7Celement:geometry%7Csaturation:-100"></a>			
			<div class="participant-meta row">
	    		<div class="span2">
	    			<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field($field_keys['participant_occupation'],get_the_ID()); ?><br>
	    			<?php if ($dob = get_field($field_keys['participant_dob'],get_the_ID())) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
	    		</div>
	    		<div class="span2">
	    			<b><?php _e('Religion','glp'); ?>:</b> <?php the_field($field_keys['participant_religion'],get_the_ID()); ?><br>
	    			<b><?php _e('Income','glp'); ?>:</b> <?php $incomes = get_field_object($field_keys['participant_income']); $income = get_field($field_keys['participant_income'], get_the_ID()); echo $incomes['choices'][$income]; ?>
	    		</div>
	    	</div>
	    	<div class="participant-content">
			    <?php the_content(); ?>
			</div>
			<a class="btn btn-inverse" href="<?php the_permalink(); ?>">&#9658;&nbsp;<?php _e('Learn More','glp'); ?></a>
		</div>
		<div class="span8">
		<?php
			if ( $summary_video = get_field($field_keys['participant_summary_video'],get_the_ID()) ) {
				query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
				get_template_part('templates/clip','summary');
				wp_reset_query();
			}
		?>
	    </div>
	</article>
<?php endwhile; ?>