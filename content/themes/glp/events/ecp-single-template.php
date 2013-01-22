<h1 class="events-title blog-title"><?php echo __('Community & Events','glp'); ?></h1>
<?php tribe_events_before_html(); ?>
<?php the_post(); global $post; ?>
<!-- <?php var_dump( $post ); ?> -->

<header class="single-post-header span9 offset3">
	<div class="entry-date"><?php echo date('F j, Y', strtotime($post->EventStartDate)); ?></div>
	<div class="entry-header">
	    	<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>		
	</div>
</header>

<div class="single-event-container single-post-container container">
	<div class="post-inner row">

	<div class="single-post-sidebar span3">
		<?php dynamic_sidebar('sidebar-events'); ?>
	</div>
	<div class="single-post-content span9">
		<?php if (has_post_thumbnail()) : ?>
		<div class="entry-thumbnail"><?php echo get_the_post_thumbnail(); ?></div>
		<?php endif; ?>
		<div class="entry-meta">
			<div class="entry-author"><?php echo __('Posted in','glp'); ?> <?php the_category(' ');?></div>
			<div class="entry-tags"><?php echo __('Tags:','glp'); ?> <?php the_tags(' '); ?></div>
		</div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		[SHARE] [COMMENT] [BOOKMARK] [REPOST]
		<?php if(tribe_get_option('showComments','no') == 'yes'){ comments_template(); } ?>
	</div>
	
	</div>
</div>

<?php tribe_events_after_html(); ?>