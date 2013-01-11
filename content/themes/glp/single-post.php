<h1 class="blog-title"><?php echo __('The Global Lives Blog','glp'); ?></h1>

<?php while (have_posts()) : the_post(); ?>
<header class="single-post-header span9 offset3">
	<?php posts_nav_link(); ?>
	<div class="entry-date"><?php echo get_the_date(); ?></div>
	<div class="entry-header">
			<h2 class="entry-category"><?php the_category(' '); ?></h3>
	    	<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>		
	</div>
</header>

<div class="single-post-container container">
	<div class="post-inner row">

	<div class="single-post-sidebar span3">
		<div class="entry-about-author">
			<h4><?php echo __('About the Author','glp'); ?></h4>
			<div class="author-thumbnail"><?php echo get_avatar(get_the_author_meta('ID')); ?></div>
			<div class="author-meta">
				<b><?php the_author(); ?></b><br>[AUTHOR TAGLINE]
			</div>
			<div class="author-description">
				<?php echo get_the_author_meta('description'); ?>
			</div>
		</div>
		<?php dynamic_sidebar('sidebar-blog'); ?>
	</div>
	<div class="single-post-content span9">
		<?php if (has_post_thumbnail()) : ?>
		<div class="entry-thumbnail"><?php echo get_the_post_thumbnail(); ?></div>
		<?php endif; ?>
		<div class="entry-meta">
			<div class="entry-author"><?php echo __('By','glp'); ?> <?php the_author(); ?> / <?php echo __('Posted in','glp'); ?> <?php the_category(' ');?></div>
			<div class="entry-tags"><?php echo __('Tags:','glp'); ?> <?php the_tags(' '); ?></div>
		</div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		[SHARE] [COMMENT] [BOOKMARK] [REPOST]
	</div>
	
	</div>
</div>
<?php endwhile; ?>
