<pre>[SHOW IN MAP]</pre>

<div class="row">
<?php $series = $wp_query->queried_object; ?>
<?php get_template_part('templates/nav','series'); ?>
</div>

<div class="row">
<div class="span4">
	<p><?php echo term_description(); ?></p>
</div>

<div class="span8">
	<?php
		$first_participant = get_posts(array( 'post_type' => 'participant', 'numberpost' => 1, 'series' => $series->slug));
		if ( $summary_video = get_field('summary_video', $first_participant->ID) ) {
			query_posts(array( 'post_type' => 'clip', 'p' => $summary_video[0]->ID ));
			get_template_part('templates/clip','stage');
			wp_reset_query();
		}
	?>
	<pre>[SUMMARY VIDEO]</pre>
</div>
</div>