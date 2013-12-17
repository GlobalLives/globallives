<?php if (!have_posts()) : ?>
	<div class="alert alert-block fade in">
		<a class="close" data-dismiss="alert">&times;</a>
		<p><?php _e('Sorry, no results were found.', 'glp'); ?></p>
	</div>
	<?php get_search_form(); ?>
<?php endif; ?>