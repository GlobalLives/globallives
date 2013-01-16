<div id="mapview"></div>
<div id="popover" class="span4">
    <h3><span class="popover-name"></span> &mdash; <span class="popover-location"></h3>
    <div class="row">
    	<div class="span1"><img class="popover-thumbnail" src=""></div>
    	<div class="span3">
    		<b><?php _e('Occupation'); ?>:</b> <span class="popover-occupation"></span><br>
    		<b><?php _e('Date of Birth'); ?>:</b> <span class="popover-dob"></span>
    	</div>
    </div>
    <button type="button" class="close">&times;</button>
</div>

<script src="http://d3js.org/d3.v3.min.js"></script>
<script>
var data,
	// D3 Functions
	xy = d3.geo.mercator().scale( $('#mapview').width() ).translate([$('#mapview').width() / 2, $('#mapview').height() / 1.75]),
	path = d3.geo.path().projection(xy),
	
	// SVG groups
	map = d3.select('#mapview').append('svg:svg'),
	countries = map.append('svg:g').attr('id', 'countries'),
	markers = map.append('svg:g').attr('id', 'markers'),
		
	// Data arrays
	locations = [], positions = [],
	participants = [
<?php $participants = get_posts(array( 'post_type' => 'participant' )); foreach ($participants as $participant) : ?>
		{
			name: '<?php echo $participant->post_title; ?>',
			src: '<?php the_participant_thumbnail_url( $participant->ID ); ?>',
			occupation: '<?php the_field('occupation', $participant->ID); ?>',
			dob: '<?php the_field('dob', $participant->ID); ?>',
			location: '<?php the_field('location', $participant->ID); ?>',
			continent: '<?php the_field('continent', $participant->ID); ?>',
			latitude: <?php the_field('latitude', $participant->ID); ?>,
			longitude: <?php the_field('longitude', $participant->ID); ?> },
<?php endforeach; ?>
	];

	function set_popover( d, el ) {
		var dy = $(el).position().top - 50,
			dx = $(el).position().left + 25;
		$('#popover').css('top', dy).css('left', dx);
		$('#popover .popover-name').text(d.name);
		$('#popover .popover-location').text(d.location);
		$('#popover .popover-occupation').text(d.occupation);
		$('#popover .popover-dob').text(d.dob);
		$('#popover .popover-thumbnail').attr('src', d.src);
		$('#popover').show();
	}
	 
$(function() {
	d3.json('<?php bloginfo('stylesheet_directory'); ?>/js/vendor/countries.json', function( json ) {

		// Create country paths
		d3.select( '#countries' )
			.selectAll('path')
			.data(json.features)
			.enter().append('svg:path')
			.attr('d', path);
		
		// Add Participant markers
		markers.selectAll('marker')
			.data(participants)
			.enter().append('svg:g')
				.attr('class', function(d) { return 'marker ' + d.continent; })
				.attr('transform', function(d) { return 'translate(' + xy([+d.longitude, +d.latitude]) + ')'; })
				.on('click', function(d) { set_popover(d,this); })
			.append('svg:path') // Add the pins
				.attr('class', function(d) { return 'pin'; })
				.attr('d', 'M240,80c-60,0-107,48-107,107c0,25,9,49,24,67 c18,22,56,42,64,131c0,5,3,16,19,16c16,0,19-11,20-16 c8-88,46-108,64-131c15-18,24-42,24-67C347,127,299,80,240,80z M238,221c-19,0-35-15-35-35c0-19,15-35,35-35 c19,0,35,15,35,35C273,206,257,221,238,221z')
				.attr('transform','translate(-24,-48), scale(0.125)');
	});	
});
</script>