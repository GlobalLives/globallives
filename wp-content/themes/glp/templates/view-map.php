<?php global $participants; ?>
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
		occupation: '<?php the_field('occupation', $participant->ID); ?>',
		location: '<?php the_field('location', $participant->ID); ?>',
		dob: '<?php the_field('dob', $participant->ID); ?>',

		latitude: <?php the_field('latitude', $participant->ID); ?>,
		longitude: <?php the_field('longitude', $participant->ID); ?>,
		continent: '<?php the_field('continent', $participant->ID); ?>',

		series: ['<?php echo implode("','",get_participant_taxonomy_slugs($participant->ID,'series')); ?>'],
		series_labels: '<?php echo get_the_term_list($participant->ID,'series','',', '); ?>',
		themes: ['<?php echo implode("','",get_participant_taxonomy_slugs($participant->ID,'themes')); ?>'],
		theme_labels: '<?php echo get_the_term_list($participant->ID,'themes','',', '); ?>',

		gender: '<?php the_field('gender', $participant->ID); ?>',
		gender_label: '<?php $field = get_field_object('gender', $participant->ID); $value = get_field('gender', $participant->ID); echo $field['choices'][ $value ]; ?>',
		income: '<?php the_field('income_group', $participant->ID); ?>',
		income_label: '<?php $field = get_field_object('income_group', $participant->ID); $value = get_field('income_group', $participant->ID); echo $field['choices'][ $value ]; ?>',
		age: '<?php the_field('age_group', $participant->ID); ?>',
		age_label: '<?php $field = get_field_object('age_group', $participant->ID); $value = get_field('age_group', $participant->ID); echo $field['choices'][ $value ]; ?>',
		proposed: '<?php the_field('proposed', $participant->ID); ?>',
		filtered: false,

		id: <?php echo $participant->ID; ?>,
		thumbnail: '<?php the_participant_thumbnail_url( $participant->ID ); ?>',	
		permalink: '<?php echo get_permalink($participant->ID); ?>'
	},
<?php endforeach; ?>
];
</script>