$(function () {

	$('a:has(img)').addClass('image-link');
	same_height('#nav-modules .widget');
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
			var gradient = '('+fade_from+' 75%, '+fade_to+' 100%)';
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
			glpAjax.ajaxurl,
			{ action: 'get_participant_summary', post_id: post_id },
			function() {
				$('#stage').fadeIn('slow');
				$(window).trigger("setup_players");
				reinit_addthis();
			}
		);
	}

	function show_mapthumb( i ) {
		$('.mapthumb').hide();
		$('#mapthumb-'+i).show();
	}

	function same_height( group ) {
		var resizeTimer;
		$(window).resize(function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function() {
				var tallest = 0;
				$(group).height('auto');
				$(group).each(function() {
					var thisHeight = $(this).height();
					if (thisHeight > tallest) { tallest = thisHeight; }
				});
				$(group).height(tallest);
			}, 250);
		});
		$(window).resize();
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

/* Banner Modals */

	function popModal(el) {
		$('.modal').modal('hide');
		$(el).modal('show');
	}

	$('#register-tab, .register-toggle').click(function(ev){ ev.preventDefault(); popModal('#modal-register'); });
	$('#login-tab, .login-toggle').click(function(ev){ ev.preventDefault(); popModal('#modal-login'); });

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

				if ($('select[name=series]').val() && $('select[name=series]').val() !== "All" && $.inArray($('select[name=series]').val(),this.series) == -1) { this.filtered = true; }
				if ($('select[name=gender]').val() !== "All" && this.gender !== $('select[name=gender]').val() ) { this.filtered = true; }
				if ($('select[name=income]').val() !== "All" && this.income !== $('select[name=income]').val() ) { this.filtered = true; }
				if ($('select[name=age]').val()    !== "All" && this.age    !== $('select[name=age]').val() )    { this.filtered = true; }
			});
			if ($('input[name=proposed]:checked').val()) {
				$('.proposed').removeClass('hide');
				d3.selectAll('.marker.proposed').style('opacity',1);
			} else {
				$('.proposed').addClass('hide');
				d3.selectAll('.marker.proposed').style('opacity',0);
			}

			filterParticipants();
			return false;
		});
	}

	if ($('#nav-themes').length) { // Make sure the Themes navbar is on the page

		$('#nav-themes li').hover(
			function() {
				$(this).siblings().find('.theme-link').hide();
				$(this).find('.theme-link').slideDown();
			},
			function() {
				$(this).children('.theme-link').slideUp();
			}
		);
		$('#nav-themes li').click(function() {
			var theme = $(this).attr('data-term');
			$(participants).each(function() {
				this.filteredByTheme = false;

				if (theme && $.inArray(theme,this.themes) == -1) {
					this.filteredByTheme = true;
				}

			});

			filterParticipants();
			$(this).addClass('active').siblings().removeClass('active');
		});

		$('#nav-themes').find('.thumbnails').cycle({timeout: 250, speed: 250});

		var filterParticipants = function () {
			$(participants).each(function() {
				if (this.filtered === true || this.filteredByTheme === true) {
					$('#participant-' + this.id).addClass('filtered');
					d3.selectAll('#marker-'+this.id).classed('filtered',true);
				} else {
					$('#marker-' + this.id + ', #participant-' + this.id).removeClass('filtered');
					d3.selectAll('#marker-'+this.id).classed('filtered',false);
				}
			});
		};
	}

	if ($('#mapview').length) { // For all pages that have a Map View

		var showConnections = function (hub_id) {

			var hub_marker = map.select('#marker-' + hub_id),
				hub_xy = [+hub_marker.attr('data-x'), +hub_marker.attr('data-y')],
				hub_participant = $.grep(participants, function(p) { return p.id == hub_id; })[0];

			$(participants).each( function() {

				var shared_themes = shared(hub_participant.themes, this.themes);

				if (this.id !== hub_id && shared_themes.length > 0) {
					var spoke_id = this.id,
						spoke_marker = map.select('#marker-' + spoke_id),
						spoke_xy = [+spoke_marker.attr('data-x'), +spoke_marker.attr('data-y')];

					var edge = underlay.append('path').attr('class','edge')
						.attr('id', 'edge-' + spoke_id)
						.attr('d', function(d){ return (hub_xy[0] < spoke_xy[0]) ? 'M'+hub_xy[0]+','+hub_xy[1]+'L'+spoke_xy[0]+','+spoke_xy[1]+'Z' : 'M'+spoke_xy[0]+','+spoke_xy[1]+'L'+hub_xy[0]+','+hub_xy[1]+'Z'; })
						.style('stroke','#fff')
						.style('stroke-width',3)
						.style('opacity',0.25);

					var label = underlay.append('text').attr('class','edge-label')
						.style('fill','#fff')
						.style('text-anchor','middle')
						.attr('dy',3)
						.append('textPath')
							.attr('xlink:href', '#edge-' + spoke_id)
							.attr('startOffset','25%')
							.text(shared_themes);
				}
			});
		};

		var showConnection = function (hub_id, spoke_id) {

			var hub_marker = map.select('#marker-' + hub_id),
				hub_xy = [+hub_marker.attr('data-x'), +hub_marker.attr('data-y')],
				hub_participant = $.grep(participants, function(p) { return p.id == hub_id; })[0],
				spoke_participant = $.grep(participants, function(p) { return p.id == spoke_id; })[0],
				shared_themes = shared(hub_participant.themes, spoke_participant.themes);

			if (hub_id !== spoke_id && shared_themes.length > 0) {
				var spoke_marker = map.select('#marker-' + spoke_id),
					spoke_xy = [+spoke_marker.attr('data-x'), +spoke_marker.attr('data-y')];

				var edge = underlay.append('path').attr('class','edge')
					.attr('id', 'edge-' + spoke_id)
					.attr('d', function(d){ return (hub_xy[0] < spoke_xy[0]) ? 'M'+hub_xy[0]+','+hub_xy[1]+'L'+spoke_xy[0]+','+spoke_xy[1]+'Z' : 'M'+spoke_xy[0]+','+spoke_xy[1]+'L'+hub_xy[0]+','+hub_xy[1]+'Z'; })
					.style('stroke','#fff')
					.style('stroke-width',3)
					.style('opacity',0.25);

				var label = underlay.append('text').attr('class','edge-label')
					.style('fill','#fff')
					.style('text-anchor','middle')
					.attr('dy',3)
					.append('textPath')
						.attr('xlink:href', '#edge-' + spoke_id)
						.attr('startOffset','25%')
						.text(shared_themes);
			}
		};

		var clearConnections = function () {
			map.selectAll('.edge, .edge-label').remove();
		};

		var shared = function (a, b) {
			if (a.length > 0 && b.length > 0) {
				return $.grep(a, function(i) { return a !== '' ? $.inArray(i, b) > -1 : false; });
			} else {
				return false;
			}
		};


		var single_participant_id = $('article.participant').attr('data-participant_id');

		if (!window.location.hash || window.location.hash !== 'mapview') { $('#mapview').hide(); }

		$('#mapview').css('max-height', function() {
			return $(window).height() - $('#content').offset().top - $('#nav-explore').height() - $('#nav-themes').height() - $('.handle').height();
			});

		var height = $('#mapview').height(),
			width = height * 2; //Math.min($('#mapview').width(),$('.container').width());

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
		var countries = map.append('g').attr('id', 'countries');
			// .call(zoom);
		countries.append('rect').attr('class', 'background')
			.attr('height',height).attr('width',width);
		var underlay = map.append('g').attr('id','underlay');

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
				.attr('width',function(d){ return (single_participant_id == d.id) ? 64 : 48; })
				.attr('height',function(d){ return (single_participant_id == d.id) ? 64 : 48; });

		// Add markers and labels for each Participant

		var marker = map.selectAll('.marker')
			.data(participants)
			.enter().append('g')
				.attr('id', function(d) { return 'marker-' + d.id; })
				.attr('class', function(d) { return 'marker ' + d.continent + (d.proposed ? ' proposed' : ''); })
				.attr('transform', function(d) { return 'translate(' + projection([+d.longitude, +d.latitude]) + ')'; })
				.attr('data-x', function(d) { var coords = projection([+d.longitude, +d.latitude]); return Math.round(coords[0]); })
				.attr('data-y', function(d) { var coords = projection([+d.longitude, +d.latitude]); return Math.round(coords[1]); })
				.on('click', function(d) { window.location = d.permalink; });
			marker.append('circle').attr('class', 'pin') // Add the pins
				.attr('r',5);
			marker.append('circle').attr('class', function(d){ return (single_participant_id == d.id) ? 'mapthumb single' : 'mapthumb'; }) // Add the map thumbs
				.attr('id',function(d,i){ return 'mapthumb-'+i; })
				.attr('r',function(d){ return (single_participant_id == d.id) ? 32 : 24; })
				.attr('fill',function(d,i) { return 'url(#image-'+i+')';});

		var label = marker.append('text').attr('class','label') // Add the labels
			.attr('dx',-25).attr('dy',function(d) { return (single_participant_id == d.id) ? 48 : 40; });
			label.append('tspan').attr('class','name')
				.text(function(d) { return d.name; });
			label.append('tspan').attr('class','occupation')
				.text(function(d) { return d.occupation; })
				.attr('x',-25).attr('dy',15);
			label.append('tspan').attr('class','location')
				.text(function(d) { return d.location; })
				.attr('x',-25).attr('dy',15);

		// Load the low-res country outlines, followed by hi-res to replace it when its ready
		d3.json('/wp-content/themes/glp/dist/countries.json', function( json ) {
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});

		d3.json('/wp-content/themes/glp/dist/countries-hires.json', function( json ) {
			countries.selectAll('path').remove();
			countries.selectAll('path').data(json.features).enter().append('svg:path').attr('d', path);
		});

		$('.overlay, .mapthumb:not(.single), .label, #popover').hide();

		var hub_id = $('article.participant').attr('data-participant_id');
		if (hub_id) {
			// Single participant - show all
			showConnections(hub_id);
			$('.marker').hover(
				function() { // Enter
					$(this).find('.mapthumb, .label').show();
				},
				function() { // Leave
					$(this).find('.mapthumb:not(.single), .label').hide();
				}
			);
		} else {
			// Explore View - show on hover
			$('.marker').hover(
				function() { // Enter
					$(this).find('.mapthumb, .label').show();
					showConnections($(this).attr('id').split('-')[1]);
				},
				function() { // Leave
					$(this).find('.mapthumb:not(.single), .label').hide();
					clearConnections();
				}
			);
		}
		$('.background, #popover .close').click( function() {
			$('#popover, .overlay').hide();
			clearConnections();
		});
		$('.background').click( function() {
			$('.mapthumb:not(.single), .label').hide();
		});
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

/* Theme */

	if ($('body.tax-themes').length) { // Make sure we're on the Theme archive page

		$('#theme-select').change(function() {
			window.location = '/themes/' + $(this).val();
		});

	}

/* Participant - Single */

	if ($('body.single-participant').length) { // Make sure we're on the Participant - Single page

		$('#nav-themes').hide();

		$('.participant-detail-map .handle').click(function() {
			$('#mapview, #nav-themes').slideToggle();
			$('.participant-detail-map .handle .btn span').toggle();
		});

		$('.participant-filter-clips a.filter').click(function () {
			$(this).toggleClass('active');
			$('.participant-clip-listing.'+$(this).data('tag')).toggle();
		});

	}

/* Donate Banner */

	if ($('#donate-banner').length) {
		var banner = $('#donate-banner');
		banner.delay(1000).slideDown(2000);
		$('.not-now').click(function(){ banner.slideUp(2000); });
	}

});