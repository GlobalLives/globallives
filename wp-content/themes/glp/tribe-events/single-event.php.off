<h1 class="events-title section-title"><a href="/events"><?php _e('Events','glp'); ?></a></h1>
<?php tribe_events_before_html(); ?>
<?php the_post(); global $post; ?>

<div class="row">
<header class="single-post-header span9 offset3">
	<div class="entry-date"><?php echo date('F j, Y', strtotime($post->EventStartDate)); ?></div>
	<div class="entry-header">
	    	<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>		
	</div>
</header>
</div>

<div class="single-event-container page-container container">
	<div class="post-inner row">

	<div class="span3">
		<div class="sidebar-events page-sidebar">
		<?php dynamic_sidebar('sidebar-events'); ?>
		</div>
	</div>
	<div class="span9">
		<?php if (has_post_thumbnail()) : ?>
		<div class="entry-thumbnail"><?php echo get_the_post_thumbnail(); ?></div>
		<?php endif; ?>
		<div class="entry-meta">
			<div class="entry-author"><?php echo __('Posted in','glp'); ?> <?php _e('Events','glp'); ?></div>
			<div class="entry-tags"><?php echo __('Tags:','glp'); ?> <?php tribe_meta_event_cats(' '); ?></div>
		</div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		
		<?php #if(tribe_get_option('showComments','no') == 'yes'){ comments_template(); } ?>
	</div>
	
	</div>
</div>

<?php tribe_events_after_html(); ?>