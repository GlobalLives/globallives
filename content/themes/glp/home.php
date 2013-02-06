<h1 class="blog-title section-title"><?php echo __('The Global Lives Blog','glp'); ?></h1>

<?php get_template_part('templates/no-results'); ?>

<?php global $query_string; query_posts( $query_string . '&posts_per_page=1' ); // Get just the top post first ?>

<div class="top-post row">
<?php while (have_posts()) : the_post(); ?>
	<div class="span6"><?php get_template_part('templates/content', get_post_type()); ?></div>
<?php endwhile; ?>
</div>

<?php query_posts( $query_string . '&offset=1' ); // Then get the rest ?>

<div class="past-posts-container page-container container">
	<div class="posts-inner row">

		<div class="span3">
			<div class="sidebar-blog">
			<?php dynamic_sidebar('sidebar-blog'); ?>
			</div>
		</div>

		<div class="span9">
			<h4><?php echo __('Past Articles','glp'); ?></h4>

			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('templates/content', get_post_type()); ?>
			<?php endwhile; ?>

			<div class="posts-navigation"><?php posts_nav_link(' ',__('Newer Entries','glp'),__('Older Entries','glp')); ?></div>
		</div>
	</div>
</div>