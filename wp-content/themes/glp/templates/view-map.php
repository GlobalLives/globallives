<?php global $participants, $field_keys; ?>
<div id="mapview" class="view"></div>
<div class="overlay"></div>

<div id="popover" class="span6">
	<div class="row">
		<div class="span2">
			<img class="popover-thumbnail" src="">
			<a class="btn btn-inverse popover-permalink" href="">&#9658;&nbsp;<?php _e('Learn More','glp'); ?></a>
		</div>
		<div class="span4">
			<h3><span class="popover-name"></span> &mdash; <span class="popover-location"></h3>
			<div class="row">
				<div class="span2">
					<b><?php _e('Occupation','glp'); ?></b> <span class="popover-occupation"></span><br>
					<b><?php _e('Gender','glp'); ?></b> <span class="popover-gender"></span><br>
				</div>
				<div class="span2">
					<b><?php _e('Income','glp'); ?></b> <span class="popover-income"></span><br>
					<b><?php _e('Age','glp'); ?></b> <span class="popover-age"></span><br>
				</div>
			</div>
			<br>
			<b><?php _e('Series','glp'); ?></b><br><span class="popover-series"><?php _e('none','glp'); ?></span><br>
			<b><?php _e('Themes','glp'); ?></b><br><span class="popover-themes"><?php _e('none','glp'); ?></span><br>
		</div>
	</div>
	<button type="button" class="close">&times;</button>
</div>

<script>
var participants = [
<?php foreach ($participants as $participant) : ?>
	{
		name: '<?php echo $participant->post_title; ?>',
		occupation: '<?php the_field($field_keys['participant_occupation'], $participant->ID); ?>',
		location: '<?php the_field($field_keys['participant_location'], $participant->ID); ?>',
		dob: '<?php the_field($field_keys['participant_dob'], $participant->ID); ?>',

		latitude: <?php the_field($field_keys['participant_latitude'], $participant->ID); ?>,
		longitude: <?php the_field($field_keys['participant_longitude'], $participant->ID); ?>,
		continent: '<?php the_field($field_keys['participant_continent'], $participant->ID); ?>',

		series: ['<?php echo implode("','",get_participant_taxonomy_slugs($participant->ID,'series')); ?>'],
		series_labels: '<?php echo get_the_term_list($participant->ID,'series','',', '); ?>',
		themes: ['<?php echo implode("','",get_participant_taxonomy_slugs($participant->ID,'themes')); ?>'],
		theme_labels: '<?php echo get_the_term_list($participant->ID,'themes','',', '); ?>',
		gender: '<?php the_field($field_keys['participant_gender'], $participant->ID); ?>',
		income: '<?php the_field($field_keys['participant_income'], $participant->ID); ?>',
		age: '<?php the_field($field_keys['participant_age'], $participant->ID); ?>',
		proposed: '<?php the_field($field_keys['participant_proposed'], $participant->ID); ?>',
		filtered: false,

		id: <?php echo $participant->ID; ?>,
		thumbnail: '<?php the_participant_thumbnail_url( $participant->ID ); ?>',	
		permalink: '<?php echo get_permalink($participant->ID); ?>'
	},
<?php endforeach; ?>
];
</script>