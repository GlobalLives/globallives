<article id="post-<?php the_ID(); ?>" <?php post_class('tribe-events-event clearfix'); ?><?php if (has_post_thumbnail()) : ?> data-bg="<?php echo wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()) ); ?>"<?php endif; ?> itemscope itemtype="http://schema.org/Event">
		
	<?php if ( tribe_is_new_event_day() && !tribe_is_day() && !tribe_is_multiday() ) : ?>
	<div class="entry-date"><?php echo tribe_get_start_date( null, false ); ?></div>
	<?php endif; ?>
	<?php if( !tribe_is_day() && tribe_is_multiday() ) : ?>
	<div class="entry-date"><?php echo tribe_get_start_date( null, false ); ?> – <?php echo tribe_get_end_date( null, false ); ?></div>
	<?php endif; ?>
	<?php if ( tribe_is_day() && $first ) : $first = false; ?>
	<div class="entry-date"><?php echo tribe_event_format_date(strtotime(get_query_var('eventDate')), false); ?></div>
	<?php endif; ?>
	
	<header class="entry-header">
		<h3 class="entry-category"><?php _e('Event','glp'); ?></h3>
		<h2 class="entry-title" itemprop="name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	</header>

	<div class="entry-content tribe-events-event-entry" itemprop="description">
	<?php if (has_excerpt ()): ?>
		<?php the_excerpt(); ?>
	<?php else: ?>
		<?php the_content(); ?>
	<?php endif; ?>
		<a class="btn" href="<?php the_permalink(); ?>">&#9658; Learn More</a>
	</div> <!-- End tribe-events-event-entry -->

</article> <!-- End post -->