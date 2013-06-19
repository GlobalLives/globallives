<?php
	$series = $wp_query->queried_object;
	$participants = get_posts(array('post_type' => 'participant', 'numberposts' => -1, 'series' => $series->slug));
?>

<?php get_template_part('templates/view','map'); ?>
<div id="maptoggle">
	<div class="container"><a class="btn btn-mapview"><i class="icon icon-globe icon-white"></i> <?php _e('Show series in map.'); ?></a></div>
</div>

<?php get_template_part('templates/nav','series'); ?>

<div class="container">
	<div id="home" class="row">
		<div class="span4">
			<p><?php echo term_description(); ?></p>
		</div>
		<div class="span8">
			<?php
			    if ( $summary_video = get_field('summary_video', 'series_'.$series->term_id) ) {
			    	query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
			    	get_template_part('templates/clip','stage');
			    	wp_reset_query();
			    }
			?>
		</div>
	</div>
	<div id="stage"></div>
</div>