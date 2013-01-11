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
</div>

<script src="http://d3js.org/d3.v3.min.js"></script>
<script>
var data,
	// D3 Functions
	xy = d3.geo.mercator()
		.scale( $('#mapview').width() )
		.translate([$('#mapview').width() / 2, $('#mapview').height() / 1.75]),
	path = d3.geo.path().projection(xy),
	
	// SVG groups
	map = d3.select('#mapview').append('svg:svg'),
	countries = map.append('svg:g').attr('id', 'countries'),
	cells = map.append('svg:g').attr('id','cells'),
	markers = map.append('svg:g').attr('id', 'markers'),
	
	// Data arrays
	locations = [], positions = [],
	participants = [
		<?php $participants = get_posts(array( 'post_type' => 'participant' )); foreach ($participants as $participant) : ?>
		{ name: '<?php echo $participant->post_title; ?>', src: '<?php the_participant_thumbnail_url( $participant->ID ); ?>', occupation: '<?php the_field('occupation', $participant->ID); ?>', dob: '<?php the_field('dob', $participant->ID); ?>', location: '<?php the_field('location', $participant->ID); ?>', latitude: <?php the_field('latitude', $participant->ID); ?>, longitude: <?php the_field('longitude', $participant->ID); ?> },
		<?php endforeach; ?>
	];
 
$(function() {
	d3.json('<?php bloginfo('stylesheet_directory'); ?>/js/vendor/countries.json', function( json ) {

		// Create country paths
		
		d3.select( '#countries' )
			.selectAll('path')
			.data(json.features)
			.enter().append('svg:path')
			.attr('d', path)
			.attr('fill', '#333')
			.attr('stroke', '#666')
			.attr('stroke-width', 1);
		
		// Add Participant markers
		
		participants = participants.filter(function(participant) {
			if (true) { // Use this in the future to filter by theme, etc.
				var location = [+participant.longitude, +participant.latitude];
				positions.push(xy(location));
				return true;
			}
		});
		
		var polygons = d3.geom.voronoi(positions);

		var g = cells.selectAll('g')
			.data(participants)
			.enter().append('svg:g')
			.attr('opacity',0);

		g.append('svg:path')
			.attr('class', 'marker')
			.attr('d', function(d, i) { return 'M' + polygons[i].join('L') + 'Z'; });
/* 			.on('mouseover', function(d, i) { d3.select('#popover span').text(d.location); }); */

		markers.selectAll('marker')
			.data(participants)
			.enter().append('svg:circle')
			.attr('class','marker')
			.attr('cx', function(d,i) { return positions[i][0]; })
			.attr('cy', function(d,i) { return positions[i][1]; })
			.attr('fill','rgba(255,0,0,0.5)')
			.attr('r', function(d, i) { return 10; })
			.on('mouseover', function(d,i) {
				var dx = positions[i][0] + 10,
					dy = positions[i][1];
				d3.select('#popover')
					.style('opacity',1)
					.style('top', function() { return dx + 'px'; })
					.style('left', function() { return dy + 'px'; });
				d3.select('#popover .popover-name').text(d.name);
				d3.select('#popover .popover-location').text(d.location);
				d3.select('#popover .popover-occupation').text(d.occupation);
				d3.select('#popover .popover-dob').text(d.dob);
				d3.select('#popover .popover-thumbnail').attr('src', d.src);
			})
			.on('mouseout', function(d, i) {
				d3.select('#popover').style('opacity',0);
			});

	});	
});
</script>