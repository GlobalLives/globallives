$(function() {

	$('a:has(img)').addClass('image-link');
	same_height( $('#nav-modules .widget') );

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
		$('#popover .popover-series').html(d.series);
		$('#popover .popover-thumbnail').attr('src', d.thumbnail);
		$('#popover .popover-permalink').attr('href', d.permalink);
		$('#popover, .overlay').show();
	}
	function show_mapthumb( i ) {
		$('.mapthumb').hide();
		$('#mapthumb-'+i).show();
	}
	
	function same_height( group ) {
		var tallest = 0;
		group.each(function() {
			var thisHeight = $(this).height();
			if(thisHeight > tallest) {
				tallest = thisHeight;
			}
		});
		group.height(tallest);
	}
	
/* Front Page */

	if ($('body.page-home').length) { // Make sure we're on the homepage

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

	}
	
/* Explore the Collection */
	
	if ($('body.page-explore').length) { // Make sure we're on Explore the Collection
			
		$('.btn-mapview').click(function() {
			$(this).addClass('active').siblings().removeClass('active');
			$('.view').slideUp(500,function() {
				$('#mapview').delay(700).slideDown(500);	
			});
		});
		$('.btn-gridview').click(function() {
			$(this).addClass('active').siblings().removeClass('active');
			$('.view').slideUp(500,function() {
				$('#gridview').delay(700).slideDown(500);
			});
		});
		
		$('#nav-explore input, #nav-explore select').change(function() {
			$(participants).each(function() {
				var participant = $('#participant-' + this.id);
				participant.removeClass('filtered');

				if ($('select[name=series]').val() !== "All" && this.series !== $('select[name=series]').val() ) { participant.addClass('filtered'); }				
				if ($('select[name=gender]').val() !== "All" && this.gender !== $('select[name=gender]').val() ) { participant.addClass('filtered'); }				
				if ($('select[name=income]').val() !== "All" && this.income !== $('select[name=income]').val() ) { participant.addClass('filtered'); }				
				if ($('select[name=age]').val()    !== "All" && this.age    !== $('select[name=age]').val() )    { participant.addClass('filtered'); }				
				if (!$('input[name=proposed]:checked').val() && this.proposed ) { participant.addClass('filtered'); }
			});
			$('.participant-grid').hide();
			$('.participant-grid:not(.filtered)').fadeIn();
			return false;
		});
		
	}
	
	if ($('#mapview').length) { // For all pages that have a Map View

		$('#mapview').hide();
	
		var height = $('#mapview').height(),
			width = $('#mapview').width();
			
		// D3 Functions
		var	projection = d3.geo.mercator()
			.scale( width * 0.15 )
			.translate([width / 2, height / 1.75]);
		var path = d3.geo.path()
			.projection(projection);
		var zoom = d3.behavior.zoom()
			.translate(projection.translate())
			.scale(projection.scale())
			.scaleExtent([width * 0.15, 8 * height]);
/* 			.on('zoom',zoom); */
	
		// SVG groups
		var map = d3.select('#mapview').append('svg:svg')
			.attr('height',height).attr('width',width);
		var countries = map.append('svg:g').attr('id', 'countries');
/* 			.call(zoom); */
		countries.append('rect')
			.attr('class', 'background')
			.attr('width', width).attr('height', height);
		
		var locations = map.append('svg:g').attr('id', 'locations'),
			positions = [];

		participants = participants.filter(function(participant) {
			if (true) { // Use this in the future to filter by theme, etc.
				var location = [+participant.longitude, +participant.latitude];
				positions.push(projection(location));
				return true;
			}
		});
		
		// Set up Participant thumbnails as SVG patterns
		
		var thumbnails = map.append('svg:defs').selectAll('thumbnails')
			.data(participants)
			.enter().append('svg:pattern')
				.attr('id', function(d,i) { return 'image-'+i; })
			.attr('patternUnits', 'objectBoundingBox')
			.attr('width', 50).attr('height', 50)
			.append('svg:image')
				.attr('xlink:href', function(d) { return d.thumbnail; })
				.attr('x', 0).attr('y', 0)
				.attr('width', 50).attr('height', 50);
				
		// Add Participant markers
		
		var markers = locations.selectAll('marker')
			.data(participants)
			.enter().append('svg:g')
				.attr('class', function(d) { return 'marker ' + d.continent + (d.proposed ? ' proposed' : ''); })
				.attr('transform', function(d) { return 'translate(' + projection([+d.longitude, +d.latitude]) + ')'; })
				.on('click', function(d) { set_popover( d, this ); });

/*
		markers.append('svg:path') // Add the pins
			.attr('class', 'pin')
			.attr('d', 'M240,80c-60,0-107,48-107,107c0,25,9,49,24,67 c18,22,56,42,64,131c0,5,3,16,19,16c16,0,19-11,20-16 c8-88,46-108,64-131c15-18,24-42,24-67C347,127,299,80,240,80z M238,221c-19,0-35-15-35-35c0-19,15-35,35-35 c19,0,35,15,35,35C273,206,257,221,238,221z')
			.attr('transform','translate(-30,-50), scale(0.125)');
*/

		markers.append('svg:circle').attr('class', 'mapthumb')
			.attr('id',function(d,i){ return 'mapthumb-'+i; })
			.attr('r',25)
			.attr('fill',function(d,i) { return 'url(#image-'+i+')';});
		
		var label = markers.append('svg:text').attr('class','label')
			.attr('dx',-25).attr('dy',40);
		label.append('svg:tspan').attr('class','name')
			.text(function(d) { return d.name });
		label.append('svg:tspan').attr('class','location')
			.text(function(d) { return d.location })
			.attr('x',-25).attr('dy',18);
		label.append('svg:tspan').attr('class','occupation')
			.text(function(d) { return d.occupation })
			.attr('x',-25).attr('dy',18);
				
		// Load the low-res country outlines
		d3.json('/content/themes/glp/js/vendor/countries.json', function( json ) {
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
/* 			.on('click',click); */
		});
		
		// Simultaneously load the hi-res country outlines, which will replace the low-res ones once they're done loading
/*
		d3.json('/content/themes/glp/js/vendor/countries-hires.json', function( json ) {
			countries.selectAll('path').remove();
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});
*/
	        
		function click(d) {
			var centroid = path.centroid(d),
				translate = projection.translate();
			projection.translate([
				translate[0] - centroid[0] + width / 2,
				translate[1] - centroid[1] + height / 2
			]);
			zoom.translate(projection.translate());
			countries.selectAll('path').transition()
				.duration(1000)
				.attr('d', path);
		}
		
		function zoom() {
			projection.translate(d3.event.translate).scale(d3.event.scale);
			countries.selectAll('path').attr('d',path);
		}
		
		$('.overlay, .label, #popover').hide();
		$('.marker').mouseover( function() {
			$('.label').hide();
			$(this).find('.label').show();
		})
		$('.overlay, #popover .close').click( function() {
			$('#popover, .overlay').hide();
		});
	}

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

/* Series */

	if ($('body.tax-series').length) { // Make sure we're on the Series archive page

		/* Carousel */
		
		$('.carousel').carousel('pause');
		$('#series-carousel').bind('slide',function(){
			$('#series-carousel').css('overflow','hidden');
		});
		$('#series-carousel').bind('slid',function(){
			$('#series-carousel').css('overflow','visible');
		});
		
		$('#nav-series .participant-thumbnail').click(function() {
			$('#home').fadeOut('slow');
			set_stage( $(this).data('id') );
		});
		$('#nav-series .home-thumbnail').click(function() {
			$('#stage').fadeOut('slow',function() {
				$('#home').fadeIn('slow');
			});
		});
		
		/* Map View */
			
		$('.btn-mapview').click(function() {
			$('#mapview').slideToggle(500);
		});
		
	}
	
});