<h1 class="search-title section-title"><?php echo __('Search','glp'); ?></h1>

<div class="search-results-container page-container container">
	<div class="search-results-inner row">
	
		<div class="span3">
			<div class="search-sidebar">
				<h4><?php _e('Narrow your search','glp'); ?></h4>
				<hr>
				<h4><?php _e('Search query','glp'); ?></h4>
				<?php get_template_part('templates/form','search'); ?>
				<hr>
				<h4><?php _e('Content type','glp'); ?></h4>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="participant" />Participants</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="clip" />Clips</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="page" />Pages</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="post" />Posts</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="tribe_events" />Events</label>
			</div>
		</div>
		
		<div class="span9">
			<div class="search-entries">
				<h3><span class="results-found"><?php global $wp_query; $posts=query_posts($query_string . '&posts_per_page=-1'); $total_results = $wp_query->found_posts; echo $total_results; ?></span> <?php _e('results with','glp'); ?> '<?php the_search_query(); ?>'</h3> 
				
				<?php if (!have_posts()) : ?>
				<div class="alert alert-block fade in">
					<p><?php _e('Sorry, no results were found.', 'glp'); ?></p>
				</div>
				<?php endif; ?>

				<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part('templates/result', get_post_type()); ?>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</div>