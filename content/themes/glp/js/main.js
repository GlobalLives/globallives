$(function() {

	$('a:has(img)').addClass('image-link');
	same_height( $('#nav-modules .widget') );
	$('input.copyable').click(function(){ $(this).select(); });

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
			}
		};
	}

	function set_stage( post_id ) {
		$('#stage').fadeOut('slow').load(
			'/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_summary', post_id: post_id },
			function() {
				$('#stage').fadeIn('slow');
				$(window).trigger("setup_players");
				reinit_addthis();
			}
		);
	}

	function set_popover( d, el ) {
		var width = $('#mapview').width(),
			dy = $(el).position().top,
			dx = $(el).position().left;
		if ( dx < width/2 ) {
			dx_offset = 30;
		} else {
			dx_offset = -480;
		}
		$('#popover').css('top', dy).css('left', dx + dx_offset);
		$('#popover').attr('data-participant_id',d.id);
		$('#popover .popover-name').text(d.name);
		$('#popover .popover-occupation').text(d.occupation);
		$('#popover .popover-location').text(d.location);
		$('#popover .popover-gender').text(d.gender_label);
		$('#popover .popover-income').text(d.income_label);
		$('#popover .popover-age').text(d.age_label);
		$('#popover .popover-series').html(d.series_labels || '');
		$('#popover .popover-themes').html(d.theme_labels || '');
		$('#popover .popover-thumbnail').attr('src', d.thumbnail);
		$('#popover .popover-permalink').attr('href', d.permalink);
		// $('#popover, .overlay').show();
		$('#popover').show();

		$('.popover-series a').on('mouseenter', {taxonomy: 'series', participant: d.id }, connectByTaxonomy);
		$('.popover-themes a').on('mouseenter', {taxonomy: 'themes', participant: d.id }, connectByTaxonomy);
		$('.popover-series a, .popover-themes a').on('mouseleave', clearConnections);
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

	function reinit_addthis() {
		var addthis_url = "//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-510832576c1fd9d6";
		if (window.addthis) {
			window.addthis = null;
			window._adr = null;
			window._atc = null;
			window._atd = null;
			window._ate = null;
			window._atr = null;
			window._atw = null;
		}
		$.getScript( addthis_url );
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
		$('#nav-featured .participant-thumbnail').popover();
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

	function toggle_view( view ) {
		$('.view').slideUp(500,function() {
			$(view).delay(700).slideDown(500);
			$('.btn-'+view.substring(1)).addClass('active').siblings().removeClass('active');
		});
		window.location.hash = view;
	}

	if ($('body.page-explore').length) { // Make sure we're on Explore the Collection

		var view = window.location.hash;
		if (view === '#mapview' || view === '#gridview') {
			toggle_view(view);
		}

		$('.btn-mapview').click(function() { toggle_view('#mapview'); });
		$('.btn-gridview').click(function() { toggle_view('#gridview'); });

		$('#nav-explore input, #nav-explore select').change(function() {
			$(participants).each(function() {
				this.filtered = false;

				if ($('select[name=series]').val() && $('select[name=series]') !== "All" && this.series !== $('select[name=series]').val() ) { this.filtered = true; }
				if ($('select[name=gender]').val() !== "All" && this.gender !== $('select[name=gender]').val() ) { this.filtered = true; }
				if ($('select[name=income]').val() !== "All" && this.income !== $('select[name=income]').val() ) { this.filtered = true; }
				if ($('select[name=age]').val()    !== "All" && this.age    !== $('select[name=age]').val() )    { this.filtered = true; }

				if ( this.proposed ) { this.filtered = true; }
				if ($('input[name=proposed]:checked').val() && this.proposed ) { this.filtered = false; }

			});

			filterParticipants();
			return false;
		});

		$('#nav-themes li').click(function() {
			var theme = $(this).text().toLowerCase();
			$(participants).each(function() {
				this.filteredByTheme = false;

				if (theme !== 'all themes' && $.inArray(theme,this.themes) == -1) {
					this.filteredByTheme = true;
				}

			});

			filterParticipants();
			$(this).addClass('active').siblings().removeClass('active');
		});

		function filterParticipants() {
			$(participants).each(function() {

				if (this.filtered === true || this.filteredByTheme === true) {
					$('#participant-' + this.id).addClass('filtered');
					d3.selectAll('#marker-'+this.id).classed('filtered',true);
				} else {
					$('#marker-' + this.id + ', #participant-' + this.id).removeClass('filtered');
					d3.selectAll('#marker-'+this.id).classed('filtered',false);
				}

			});
		}
	}

	if ($('#mapview').length) { // For all pages that have a Map View

		$('#mapview').hide();

		var height = $('#mapview').height(),
			width = $('#mapview').width();

		// D3 Functions
		var	projection = d3.geo.mercator()
			.scale( width * 0.16 )
			.translate([width / 2, height / 1.75]);
		var path = d3.geo.path().projection(projection);
		var zoom = d3.behavior.zoom()
			.translate(projection.translate())
			.scale(projection.scale())
			.scaleExtent([width * 0.15, 8 * height])
			.on('zoom',function() {
				projection.translate(d3.event.translate).scale(d3.event.scale);
				countries.selectAll('path').attr('d',path);
				map.selectAll('.marker').attr('transform', function(d) { return 'translate(' + projection([+d.longitude, +d.latitude]) + ')'; });
			});

		// SVG groups
		var map = d3.select('#mapview').append('svg')
			.attr('height',height).attr('width',width);
		var countries = map.append('g').attr('id', 'countries')
			.call(zoom);
		countries.append('rect').attr('class', 'background')
			.attr('height',height).attr('width',width);

		// Set up Participant thumbnails as SVG patterns

		var thumbnails = map.append('defs').selectAll('thumbnails')
			.data(participants)
			.enter().append('pattern')
				.attr('id', function(d,i) { return 'image-'+i; })
				.attr('patternUnits', 'objectBoundingBox')
				.attr('width', 50).attr('height', 50)
			.append('image')
				.attr('xlink:href', function(d) { return d.thumbnail; })
				.attr('x', 0).attr('y', 0)
				.attr('width', 50).attr('height', 50);

		// Add markers and labels for each Participant

		var marker = map.selectAll('.marker')
			.data(participants)
			.enter().append('g')
				.attr('id', function(d) { return 'marker-' + d.id; })
				.attr('class', function(d) { return 'marker ' + d.continent; })
				.attr('transform', function(d) { return 'translate(' + projection([+d.longitude, +d.latitude]) + ')'; })
				.attr('data-x', function(d) { var coords = projection([+d.longitude, +d.latitude]); return Math.round(coords[0]); })
				.attr('data-y', function(d) { var coords = projection([+d.longitude, +d.latitude]); return Math.round(coords[1]); })
				.on('click', function(d) { set_popover( d, this ); });
			marker.append('path').attr('class', 'pin') // Add the pins
				.attr('d', 'M240,80c-60,0-107,48-107,107c0,25,9,49,24,67 c18,22,56,42,64,131c0,5,3,16,19,16c16,0,19-11,20-16 c8-88,46-108,64-131c15-18,24-42,24-67C347,127,299,80,240,80z M238,221c-19,0-35-15-35-35c0-19,15-35,35-35 c19,0,35,15,35,35C273,206,257,221,238,221z')
				.attr('transform','translate(-30,-50), scale(0.125)');
			marker.append('circle').attr('class', 'mapthumb') // Add the map thumbs
				.attr('id',function(d,i){ return 'mapthumb-'+i; })
				.attr('r',25).attr('cy',-25)
				.attr('fill',function(d,i) { return 'url(#image-'+i+')';});

		var label = marker.append('text').attr('class','label') // Add the labels
			.attr('dx',-25).attr('dy',18);
			label.append('tspan').attr('class','name')
				.text(function(d) { return d.name; });
			label.append('tspan').attr('class','occupation')
				.text(function(d) { return d.occupation; })
				.attr('x',-25).attr('dy',18);
			label.append('tspan').attr('class','location')
				.text(function(d) { return d.location; })
				.attr('x',-25).attr('dy',18);

		// Load the low-res country outlines, followed by hi-res to replace it when its ready
		d3.json('/content/themes/glp/js/vendor/countries.json', function( json ) {
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});
/*
		d3.json('/content/themes/glp/js/vendor/countries-hires.json', function( json ) {
			countries.selectAll('path').remove();
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});
*/

		$('.overlay, .mapthumb, .label, #popover').hide();
		$('.marker').on('mouseenter', function() {
			$('.mapthumb, .label').hide();
			$(this).find('.mapthumb, .label').show();
			if( $('#popover').is(':visible') ) { connectByMarker(this); }
		});
		$('.background, #popover .close').click( function() {
			$('#popover, .overlay').hide();
			clearConnections();
		});
		$('.background').click( function() {
			$('.mapthumb, .label').hide();
		});

		function connectByTaxonomy(event) {
			var term = $(this).attr('href');
			term = term.split('/').slice(-2)[0]; // Only use the slug

			var hub = map.select('#marker-' + event.data.participant);
			var hub_xy = [+hub.attr('data-x'), +hub.attr('data-y')];

			$(participants).each( function() {
				if ($.inArray(term,this[event.data.taxonomy]) > -1) {

					var spoke = map.select('#marker-' + this.id);

					spoke.append('line').attr('class','edge')
						.attr('x1', 0).attr('y1', 0)
						.attr('x2',function(d) { var coords = projection([+d.longitude, +d.latitude]); return hub_xy[0] - coords[0]; })
						.attr('y2',function(d) { var coords = projection([+d.longitude, +d.latitude]); return hub_xy[1] -coords[1]; })
						.style('stroke','#fff')
						.style('stroke-width',2);
				}
			});
		}
		function connectByMarker(marker) {
			clearConnections();
			var popover_id = $('#popover').attr('data-participant_id');
			var marker_id = $(marker).attr('id').split('-');

			// console.log( participants );

			var popover_participant = $.grep(participants, function(p) { return p.id == popover_id; });
			var marker_participant = $.grep(participants, function(p) { return p.id == marker_id[1]; });

			// console.log( popover_participant, marker_participant );

			if ( marker_id !== popover_id ) {

				var common = $.grep(popover_participant[0].themes, function(t) {
					return $.inArray(t, marker_participant[0].themes) > -1;
				});
				if ( common.length > 0 ) {
					var popover_g = map.select('#marker-' + popover_id);
					var marker_g = map.select('#marker-' + marker_id[1]);
					var popover_xy = [+popover_g.attr('data-x'), +popover_g.attr('data-y')];
					var marker_xy = [+marker_g.attr('data-x'), +marker_g.attr('data-y')];

					var edge = map.append('line').attr('class','edge')
						.attr('x1', popover_xy[0])
						.attr('y1', popover_xy[1])
						.attr('x2', marker_xy[0])
						.attr('y2', marker_xy[1])
						.style('stroke','#fff')
						.style('stroke-width',2);

					var label = map.append('text').attr('class','label')
						.attr('dx', (popover_xy[0] + marker_xy[0]) / 2)
						.attr('dy', 30 + (popover_xy[1] + marker_xy[1]) / 2);
					$.each(common, function(i,v) {
						label.append('tspan').attr('class','theme')
							.text(function() { return v; })
							.style('fill','#fff');
					});
				}
			}
		}

		function clearConnections() {
			map.selectAll('.edge').remove();
		}
	}


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