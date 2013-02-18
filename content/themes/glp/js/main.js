$(function() {

	$('a:has(img)').addClass('image-link');

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
			var gradient = '('+fade_from+' 0, '+fade_to+' '+this.height+'px)';
			var bg_url = 'url('+this.src+')';
			if (bg.src) {
				$('#wrap').css('background-image', '-webkit-linear-gradient' + gradient + ', ' + bg_url);
				$('#wrap').css('background-image', '-moz-linear-gradient' + gradient + ', ' + bg_url);
				$('#wrap').css('background-image', 'linear-gradient' + gradient + ', ' + bg_url);
			};
		};
	}

	function set_stage( post_id ) {
		$('#stage').fadeOut('slow').load(
			'/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_summary', post_id: post_id },
			function() { $('#stage').fadeIn('slow'); }
		);
	}

	function set_popover( d, el ) {
		var width = $('#mapview').width(),
			dy = $(el).position().top,
			dx = $(el).position().left;
		if ( dx < width/2 ) {
			dx_offset = 30;
		} else {
			dx_offset = -340;
		}
		$('#popover').css('top', dy).css('left', dx + dx_offset);
		$('#popover .popover-name').text(d.name);
		$('#popover .popover-location').text(d.location);
		$('#popover .popover-occupation').text(d.occupation);
		$('#popover .popover-thumbnail').attr('src', d.thumbnail);
		$('#popover .popover-permalink').attr('href', d.permalink);
		$('#popover, .overlay').show();
	}
	function show_mapthumb( i ) {
		$('.mapthumb').hide();
		$('#mapthumb-'+i).show();
	}
	
/* Front Page */

	$('.carousel').carousel('pause');
	$('#featured-carousel').bind('slide',function(){
		$('#featured-carousel').css('overflow','hidden');
	});
	$('#featured-carousel').bind('slid',function(){
		$('#featured-carousel').css('overflow','visible');
	});
	
	$('#nav-featured .participant-thumbnail').click(function() {
		$('.home-thumbnail, .participant-thumbnail').removeClass('active');
		$(this).addClass('active');
		$('#home').fadeOut('slow');
		set_stage( $(this).data('id') );
	});
	$('#nav-featured .home-thumbnail').click(function() {
		$('#stage').fadeOut('slow',function() {
			$('.participant-thumbnail').removeClass('active');
			$('.home-thumbnail').addClass('active');
			$('#home').fadeIn('slow');
		});
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
			.attr('d', function(d, i) { return 'M' + polygons[i].join('L') + 'Z'; });
/* 			.on('mouseover',function(d,i){ show_mapthumb(i); }); */

		// Add Participant markers
		
		var markers = locations.selectAll('marker')
			.data(participants)
			.enter().append('svg:g')
				.attr('class', function(d) { return 'marker ' + d.continent; })
				.attr('transform', function(d) { return 'translate(' + xy([+d.longitude, +d.latitude]) + ')'; })
				.on('click', function(d) { set_popover(d,this); });
/*
		markers.append('svg:path') // Add the pins
			.attr('class', 'pin')
			.attr('d', 'M240,80c-60,0-107,48-107,107c0,25,9,49,24,67 c18,22,56,42,64,131c0,5,3,16,19,16c16,0,19-11,20-16 c8-88,46-108,64-131c15-18,24-42,24-67C347,127,299,80,240,80z M238,221c-19,0-35-15-35-35c0-19,15-35,35-35 c19,0,35,15,35,35C273,206,257,221,238,221z')
			.attr('transform','translate(-30,-50), scale(0.125)');
*/
		markers.append('svg:circle')
			.attr('id',function(d,i){ return 'mapthumb-'+i; })
			.attr('class', 'mapthumb')
/* 			.attr('cy',-40) */
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
	
	$('.overlay').hide();
	$('#popover').hide();
	$('#popover .close').click( function() {
		$(this).parent().hide();
	});
	$('.overlay').click( function() {
		$('#popover, .overlay').hide();
	});

/* Participant Detail */

	$('.participant-clip-listing .clip-thumbnail').click(function() {
		$('html, body').scrollTop(0);
		var clip_id = $(this).data('clip-id');
		$(this).parents('.participant-clip-listing').addClass('active').siblings().removeClass('active');
		$('#stage').slideUp().load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_clip', clip_id: clip_id },
			function() { $('#stage').delay(250).slideDown(); }
		);
	});
		
/* Blog */
	
	if ($('.blog').length) { // Make sure we're on the blog page
		
		var bg = $('.blog .post').first().data('bg');
		if (bg) { set_background( bg, {to: '#262626'} ); }
		$('.past-posts .post').each(function() {
			var bg = $(this).data('bg');
			if (bg) { $(this).css('background-image', 'url('+bg+')'); }
		});
	}
	
/* Events */

	if ($('.events-list').length) { // Make sure we're on the events page
		$('.tribe-events-event').each(function() {
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