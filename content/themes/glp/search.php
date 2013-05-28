<?php query_posts( array_merge(array( 'posts_per_page' => -1 ), $wp_query->query )); // Show all results ?>
<h1 class="search-title section-title"><?php echo __('Search','glp'); ?></h1>

<div class="search-results-container page-container container">
	<div class="search-results-inner row">
	
		<div class="span3">
			<div class="search-sidebar">
				<h4><?php _e('Narrow your search','glp'); ?></h4>
				<hr>
										
				<h4><?php _e('Content type','glp'); ?></h4>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="page" />Pages</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="post" />Articles</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="participant" />Participants</label>
				<label class="checkbox"><input type="checkbox" name="post_type[]" checked value="tribe_events" />Events</label>

<!--
					<h4><?php _e('Theme','glp'); ?></h4>
					<h4><?php _e('Time Period','glp'); ?></h4>
					<h4><?php _e('Region','glp'); ?></h4>
-->
				
			</div>
		</div>
		
		<div class="span9">
			<div class="search-entries">
				<h3><span class="results-found"><?php global $wp_query; $total_results = $wp_query->found_posts; echo $total_results; ?></span> <?php _e('results with','glp'); ?> '<?php the_search_query(); ?>'</h3> 
				<?php get_template_part('templates/result', get_post_type()); ?>
			</div>
		</div>
	</div>
</div>