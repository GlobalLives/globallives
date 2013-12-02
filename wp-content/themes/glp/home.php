<h1 class="blog-title section-title"><a href="/blog"><?php echo __('Blog','glp'); ?></a></h1>

<?php get_template_part('templates/no-results'); ?>

<?php if( !is_paged() ) : ?>

<?php global $query_string; query_posts( $query_string . '&posts_per_page=1&ignore_sticky_posts=1' ); // Get just the top post first ?>

<div class="top-post row">
<?php while (have_posts()) : the_post(); ?>
	<div class="span6"><?php get_template_part('templates/content', get_post_type()); ?></div>
<?php endwhile; ?>
</div>

<?php query_posts( $query_string . '&offset=1' ); // Then get the rest ?>
<? endif; ?>

<div class="page-container container">
	<div class="posts-inner row">
		<div class="span3">
			<div class="sidebar-blog">
			<?php dynamic_sidebar('sidebar-blog'); ?>
			</div>
		</div>
		<div class="span9">
			<div class="past-posts">
			<!-- <h4><?php echo __('Past Articles','glp'); ?></h4> -->

			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('templates/content', get_post_type()); ?>
			<?php endwhile; ?>

			<div class="posts-navigation"><?php posts_nav_link(' ',__('Newer Entries','glp'),__('Older Entries','glp')); ?></div>
			</div>
		</div>
	</div>
</div>