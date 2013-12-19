<h1 class="blog-title section-title"><a href="/blog"><?php echo __('Blog','glp'); ?></a></h1>

<?php while (have_posts()) : the_post(); ?>
<div class="row">
<header class="single-post-header span9 offset3">
	<?php posts_nav_link(); ?>
	<div class="entry-date"><?php echo get_the_date(); ?></div>
	<div class="entry-header">
			<h2 class="entry-category"><?php the_category(' '); ?></h3>
	    	<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>		
	</div>
</header>
</div>

<div class="single-post-container page-container container">
	<div class="post-inner row">

	<div class="span3">
		<div class="single-post-sidebar sidebar-blog">
		<div class="entry-about-author">
			<h4><?php echo __('About the Author','glp'); ?></h4>
			<div class="author-thumbnail"><img src="<?php the_profile_thumbnail_url(get_the_author_meta('ID')); ?>"></div>
			<div class="author-meta">
				<b><?php the_author_meta('display_name'); ?></b><br>
				<?php the_field('position','user_'.get_the_author_ID()); ?>
			</div>
			<div class="author-description">
				<?php echo get_the_author_meta('description'); ?>
			</div>
		</div>
		<?php dynamic_sidebar('sidebar-blog'); ?>
		</div>
	</div>
	<div class="single-post-content span9">
		<div class="entry-meta">
			<div class="entry-author"><?php echo __('By','glp'); ?> <?php the_author(); ?> / <?php echo __('Posted in','glp'); ?> <?php the_category(' ');?></div>
			<?php if ( has_tag() ) : ?><div class="entry-tags"><?php echo __('Tags:','glp'); ?> <?php the_tags(' '); ?></div><?php endif; ?>
		</div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	
	</div>
</div>
<?php endwhile; ?>
