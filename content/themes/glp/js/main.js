$(function() {

	// Affix main navigation menu to top of page, once you scroll past it
	$('#nav-main').affix({ offset: $('#nav-main').position() });

	// Jump the main navigation to the top of the page, on pages other than Home
	if( $('body:not(.home)').length ) {
		$('body,html').scrollTop( $('#nav-main').offset().top ); // Firefox needs the 'html' selector
	}

/* Functions */

	function set_background( src, arg ) {
		var fade_from = 'rgba(0,0,0,0)',
			fade_to = 'rgba(0,0,0,0)',
			bg = new Image();
			
		if (arg) {
			fade_from = arg.from ? arg.from : fade_from;
			fade_to = arg.to ? arg.to : fade_to;
		}

		bg.src = src;
		bg.onload = function() {
/* 			var gradient = '-webkit-linear-gradient('+fade_from+' 0, '+fade_to+' '+this.height+'px)'; */
			var gradient = '-webkit-linear-gradient('+fade_from+' 0, '+fade_to+' 640px)';
			var bg_url = 'url('+this.src+')';
			if (bg.src) { $('#wrap').css('background-image', gradient + ', ' + bg_url); }
		};
	}

	function set_stage( post_id ) {
		$('#stage').fadeOut().load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_summary', post_id: post_id }
		).fadeIn();
	}

	function set_popover( d, el ) {
		var dy = $(el).position().top - 50,
			dx = $(el).position().left + 25;
		$('#popover').css('top', dy).css('left', dx);
		$('#popover .popover-name').text(d.name);
		$('#popover .popover-location').text(d.location);
		$('#popover .popover-occupation').text(d.occupation);
		$('#popover .popover-dob').text(d.dob);
		$('#popover .popover-thumbnail').attr('src', d.thumbnail);
		$('#popover .popover-permalink').attr('href', d.permalink);
		$('#popover').show();
	}
	function show_mapthumb( i ) {
		$('.mapthumb').hide();
		$('#mapthumb-'+i).show();
	}
	
/* Front Page */

	$('.carousel').carousel('pause');
	
	$('#nav-featured .participant-thumbnail').click(function() {
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
		$('#home').fadeOut();
		set_stage( $(this).data('id') );
	});
	$('#nav-featured .home-thumbnail').click(function() {
		$('#stage').fadeOut('',function() {	$('#home').fadeIn(); });
	});
	
/* Explore the Collection */
	
	if ($('#mapview').length) { // Make sure we're in Explore : Map View
	
		// D3 Functions
		var	xy = d3.geo.mercator().scale( $('#mapview').width() ).translate([$('#mapview').width() / 2, $('#mapview').height() / 1.75]),
		path = d3.geo.path().projection(xy),
	
		// SVG groups
		map = d3.select('#mapview').append('svg:svg').attr('height','100%').attr('width','100%'),
		defs = map.append('svg:defs'),
		countries = map.append('svg:g').attr('id', 'countries'),
		cells = map.append('svg:g').attr('id','cells'),
		locations = map.append('svg:g').attr('id', 'locations'),
		positions = [];

		participants = participants.filter(function(participant) {
			if (true) { // Use this in the future to filter by theme, etc.
				var location = [+participant.longitude, +participant.latitude];
				positions.push(xy(location));
				return true;
			}
		});
		
		var thumbnails = defs.selectAll('thumbnails')
			.data(participants)
			.enter().append('svg:pattern')
				.attr('id', function(d,i) { return 'image-'+i; })
			.attr('patternUnits', 'objectBoundingBox')
			.attr('width', 50)
			.attr('height', 50)
			.append('svg:image')
				.attr('xlink:href', function(d) { return d.thumbnail; })
				.attr('x', 0)
				.attr('y', 0)
				.attr('width', 50)
				.attr('height', 50);
				
		var polygons = d3.geom.voronoi(positions);

		var g = cells.selectAll('g')
			.data(participants)
			.enter().append('svg:g')
			.attr('opacity',0)
		g.append('svg:path')
			.attr('d', function(d, i) { return 'M' + polygons[i].join('L') + 'Z'; })
			.on('mouseover',function(d,i){ show_mapthumb(i); });

		// Add Participant markers
		
		var markers = locations.selectAll('marker')
			.data(participants)
			.enter().append('svg:g')
				.attr('class', function(d) { return 'marker ' + d.continent; })
				.attr('transform', function(d) { return 'translate(' + xy([+d.longitude, +d.latitude]) + ')'; })
				.on('click', function(d) { set_popover(d,this); });
		markers.append('svg:path') // Add the pins
			.attr('class', 'pin')
			.attr('d', 'M240,80c-60,0-107,48-107,107c0,25,9,49,24,67 c18,22,56,42,64,131c0,5,3,16,19,16c16,0,19-11,20-16 c8-88,46-108,64-131c15-18,24-42,24-67C347,127,299,80,240,80z M238,221c-19,0-35-15-35-35c0-19,15-35,35-35 c19,0,35,15,35,35C273,206,257,221,238,221z')
			.attr('transform','translate(-30,-50), scale(0.125)');
		markers.append('svg:circle')
			.attr('id',function(d,i){ return 'mapthumb-'+i; })
			.attr('class', 'mapthumb')
			.attr('cy',-40)
			.attr('r',25)
			.attr('fill',function(d,i) { return 'url(#image-'+i+')';});
	
		// Load the low-res country outlines
		d3.json('/content/themes/glp/js/vendor/countries.json', function( json ) {
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});
		
		// Simultaneously load the hi-res country outlines, which will replace the low-res ones once they're done loading
		d3.json('/content/themes/glp/js/vendor/countries-hires.json', function( json ) {
			countries.selectAll('path').remove();
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});
	}
	
	$('.mapthumb').hide();
	$('#popover').hide();
	$('#popover .close').click( function() {
		$(this).parent().hide();
	});

/* Participant Detail */

	$('.participant-clip .clip-thumbnail').click(function() {
		var clip_id = $(this).data('clip-id');
		$('#stage').fadeOut().load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_clip', clip_id: clip_id },
			function() {
				$(this).addClass('active');
				$('#stage').fadeIn();
			}
		);
	});
		
/* Blog */
	
	if ($('.blog').length) { // Make sure we're on the blog page
		var bg = $('.blog .post').first().data('bg');
		if (bg) { set_background( bg, {to: '#262626'} ); }
		$('.past-posts-container .post').each(function() {
			var bg = $(this).data('bg');
			if (bg) { $(this).css('background-image', 'url('+bg+')'); }
		});
	}

/* Search */

	$('.search-sidebar :checkbox').change(function(){
		var post_type = $(this).val();
		$('.search-result.'+post_type).slideToggle('',function() {
			$('.results-found').html( $('.search-result:visible').length );		
		});
	});
	
});