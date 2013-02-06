<?php query_posts( array_merge(array( 'posts_per_page' => -1 ), $wp_query->query )); // Show all results ?>
<h1 class="search-title section-title"><?php echo __('Search','glp'); ?></h1>

<div class="search-results-container page-container container">
	<div class="search-results-inner row">
	
		<div class="span3">
			<div class="search-sidebar">
				<h4><?php _e('Narrow your search','glp'); ?></h4>
				<hr>
				<h4><?php _e('Search query','glp'); ?></h4>
				
				<form role="search" method="post" id="searchform" action="<?php echo home_url( '/' ); ?>">
					<input type="text" name="s" id="s" placeholder="<?php _e('Keywords','glp'); ?>"<?php if(is_search()) : ?> value="<?php the_search_query(); ?>"<?php endif; ?> /><br />
										
					<h4><?php _e('Content type','glp'); ?></h4>
					<?php $query_types = get_query_var('post_type'); ?>
					<label class="checkbox"><input type="checkbox" name="post_type[]" <?php if ($query_types == "any" || in_array('post',$query_types)) : ?>checked <?php endif; ?>value="post" />Articles</label>
					<label class="checkbox"><input type="checkbox" name="post_type[]" <?php if ($query_types == "any" || in_array('participant',$query_types)) : ?>checked <?php endif; ?>value="participant" />Participants</label>
					<label class="checkbox"><input type="checkbox" name="post_type[]" <?php if ($query_types == "any" || in_array('clip',$query_types)) : ?>checked <?php endif; ?>value="clip" />Videos</label>
<!--
					<h4><?php _e('Theme','glp'); ?></h4>
					<h4><?php _e('Time Period','glp'); ?></h4>
					<h4><?php _e('Region','glp'); ?></h4>
-->
					<input type="submit" id="searchsubmit" value="Filter" class="btn" />
				</form>
				
			</div>
		</div>
		
		<div class="span9">
			<div class="search-entries">
				<h3><?php global $wp_query; $total_results = $wp_query->found_posts; echo $total_results; ?> <?php _e('results found.','glp'); ?></h3> 
				<?php get_template_part('templates/result', get_post_type()); ?>
			</div>
		</div>
	</div>
</div>