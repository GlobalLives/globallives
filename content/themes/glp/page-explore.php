<div id="mapview"></div>
<div class="overlay"></div>
<div id="popover" class="span4">
	<h3><span class="popover-name"></span> &mdash; <span class="popover-location"></h3>
	<div class="row">
		<div class="span1"><img class="popover-thumbnail" src=""></div>
		<div class="span3">
			<b><?php _e('Occupation'); ?>:</b> <span class="popover-occupation"></span><br>
			<p><a class="btn popover-permalink" href="">&#9658;&nbsp;<?php _e('Full Story','glp'); ?></a></p>
		</div>
	</div>
	<button type="button" class="close">&times;</button>
</div>

<script>
var participants = [
<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => -1 )); foreach ($participants as $participant) : ?>
	{ name: '<?php echo $participant->post_title; ?>', permalink: '<?php echo get_permalink($participant->ID); ?>', thumbnail: '<?php the_participant_thumbnail_url( $participant->ID ); ?>', occupation: '<?php the_field('occupation', $participant->ID); ?>', dob: '<?php the_field('dob', $participant->ID); ?>', location: '<?php the_field('location', $participant->ID); ?>', continent: '<?php the_field('continent', $participant->ID); ?>', latitude: <?php the_field('latitude', $participant->ID); ?>, longitude: <?php the_field('longitude', $participant->ID); ?> },
<?php endforeach; ?>
];
</script>