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
		$('#popover, .overlay').hide();
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
        
/* Participant Video */
                
        $('.participant-video-controls').each(function(){ 
            var id = $(this).siblings('.participant-video-embed').attr('id');
            var player = players[id];
            
            // Bind ready events
            $('#'+id).bind("player_ready", videoSetTimer);
            $('#'+id).bind("player_ready", videoUpdateTimer);
            
            //Bind ontimeupdate events
            $('#'+id).bind("player_time_update", videoUpdateTimer);
            $('#'+id).bind("player_time_update", videoUpdatePosition);
            
            // Setup the slider. 0 to 1000 for precision
            var slider = $('#'+id).siblings('.participant-video-controls').find('.control-slider').slider({
                range: "min",
                min: 0,
                max: 1000
            });
            
            $( slider ).on( "slidestop", function( event, ui ) {
                var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
                players[id].seekTo( Math.round((players[id].getDuration() * (ui.value / 1000)) * 100) / 100, true );
                
                //Rebind the slider position now that it has been dropped
                $('#'+id).bind("player_time_update", videoUpdatePosition);
            } );

            $( slider ).on( "slidestart", function( event, ui ) {
                // Unbind the slider position auto update to prevent it bouncing around whilst still being dragged
                var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
                $('#'+id).unbind("player_time_update", videoUpdatePosition);
            } );
            
        });
        
        $(".controls").live('click', function() {
            var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
            var player = players[id];
            var control = $(this).attr('data-control');

            switch(control) {
                case 'play':
                    players[id].playVideo();
                    $(this).attr("data-control", 'pause').find('span').toggleClass('icon-pause', 'icon-play');
                break;
                case 'pause':
                    players[id].pauseVideo();
                    $(this).attr("data-control", 'play').find('span').toggleClass('icon-pause', 'icon-play');
                break;
                    
                case 'fullscreen':
                    var el = document.getElementById(id);
                    if (el.requestFullScreen) {
                        el.requestFullScreen();
                    } else if (el.mozRequestFullScreen) {
                        el.mozRequestFullScreen();
                    } else if (el.webkitRequestFullScreen) {
                        el.webkitRequestFullScreen();
                    }
                break;
            }
        });
        
        $(".taggable-area").each(function() {
            var content = $(this).next().find('.content').html();
            var title = $(this).next().find('.title').html();
            
            $(this).popover({ 
                html: true,
                animation: false,
                content: content,
                title: title
            });
        } ).click(function(e) {
            var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
            var player = players[id];
            var xpos = parseInt(e.pageX - $(this).offset().left);
            var percent = (xpos / $(this).width());
            var spos = Math.round((player.getDuration() * percent) * 100) / 100 ;
            var m = parseInt( spos / 60 ) % 60;
            var s = parseInt( spos % 60, 10);
            var offset = xpos - ( $(this).next().width() /2 );
            var popover = $(this).next();
            
            $(this).data('m',m).data('s',s).data('p',xpos);
            $(popover).find('.comment-box').prepend( $('#marker-'+xpos+' .content').html() );
            $(popover).css('left', offset).find('.time').text( m + ':' +s);
            popover_fix_height(popover);
        });
        
        $(document).on("click", ".popover .close", function(){ 
            $(this).closest('.popover').prev().popover('hide');
        });
        
        $(document).on("submit", ".popover form", function() {
            $(this).find('.error').fadeOut('slow', function() { $(this).remove(); });
            $.post(glpAjax.ajaxurl, {   
                action: 'clip_submit_comment',
                comment: $(this).find('input[name="comment"]').val(),
                minutes: $('.taggable-area' ).data('m'),
                seconds: $('.taggable-area' ).data('s'),
                position: $('.taggable-area' ).data('p'),
                post_id:  $('.participant-video-embed').attr('id').replace('participant-video-embed-', '')
            }, 
            function(response) {
                r = $.parseJSON(response);
                $('.comment-box').prepend(r.message).closest('form').find('input').val('');
                if (r.success) {
                    add_comment_to_marker_box(r.success, r.message);
                }
                popover_fix_height($('.popover'));
            });
            return false;
        });
        
        function popover_fix_height(popover) {
            $(popover).css('top', ( 0 - $(popover).height() - 20 ) )
        }
        function add_comment_to_marker_box(position, comment_html) {
            var marker_box = $('#marker-'+position);
            if (marker_box.length) {
                $(marker_box).find('.content').prepend(comment_html);
            } 
            else {
                $('.clip-markers').append('<a class="marker" id="marker-'+position+'" style="left: '+position+'px"><div class="arrow"></div><div class="hide content">'+comment_html+'</div></div>')
            }
        }
});

var players = {};
var t;
// Load YouTube Frame API
(function() { // Closure, to not leak to the scope
    var s = document.createElement("script");
    s.src = (location.protocol == 'https:' ? 'https' : 'http') + "://www.youtube.com/player_api";
    var before = document.getElementsByTagName("script")[0];
    before.parentNode.insertBefore(s, before);
})();
// This function will be called when the API is fully loaded
function onYouTubePlayerAPIReady() {YT_ready(true)}

function getFrameID(id){
    var elem = document.getElementById(id);
    if (elem) {
        if(/^iframe$/i.test(elem.tagName)) return id; //Frame, OK
        // else: Look for frame
        var elems = elem.getElementsByTagName("iframe");
        if (!elems.length) return null; //No iframe found, FAILURE
        for (var i=0; i<elems.length; i++) {
           if (/^https?:\/\/(?:www\.)?youtube(?:-nocookie)?\.com(\/|$)/i.test(elems[i].src)) break;
        }
        elem = elems[i]; //The only, or the best iFrame
        if (elem.id) return elem.id; //Existing ID, return it
        // else: Create a new ID
        do { //Keep postfixing `-frame` until the ID is unique
            id += "-frame";
        } while (document.getElementById(id));
        elem.id = id;
        return id;
    }
    // If no element, return null.
    return null;
}

// Define YT_ready function.
var YT_ready = (function() {
    var onReady_funcs = [], api_isReady = false;
    /* @param func function     Function to execute on ready
     * @param func Boolean      If true, all qeued functions are executed
     * @param b_before Boolean  If true, the func will added to the first
                                 position in the queue*/
    return function(func, b_before) {
        if (func === true) {
            api_isReady = true;
            while (onReady_funcs.length) {
                // Removes the first func from the array, and execute func
                onReady_funcs.shift()();
            }
        } else if (typeof func == "function") {
            if (api_isReady) func();
            else onReady_funcs[b_before?"unshift":"push"](func); 
        }
    }
})();

// Add function to execute when the API is ready
YT_ready(function(){
    $(".participant-video-embed").each(function() {
        var identifier = this.id;
        var frameID = getFrameID(identifier);
        if (frameID) {
            players[frameID] = new YT.Player(frameID, {
                events: {
                    "onStateChange": onStateChange(frameID, identifier),
                    "onReady": onReady(frameID, identifier)
                }
            });
        }
    });
});

function onStateChange(frameID, identifier) {
    return function (event) {
        var player = players[frameID];
        
        switch (event.data) {
            case 1:
            case 3:
                // Video has begun playing/buffering
                console.log('playing/buffering');
                $('#'+frameID).trigger("player_start_play_buffer");
                t = setInterval(function () {
                    playerTimeUpdate(frameID);
                }, 1000);
            break;
            
            case 2:
            case 0:
                // Video has been paused/ended
                console.log('paused/ended');
                $('#'+frameID).trigger("player_pause_end");
                clearTimeout(t)
            break;
        }
    }
}

function onReady(frameID, identifier) {
    return function (event) {
        var player = players[frameID];
        
        // Special case for handling browser opted in at http://www.youtube.com/html5. 
        // getDuration doesn't work on html5 embeds until the video has been initialised, which only happens on play.
        // Need to find a solution/workaround to this.
        var isHtml5Player = !player.cueVideoByFlashvars;
        if (isHtml5Player && ! player.getDuration() ) {
            alert("Youtube html5 detected. Tagging/commenting on this clip won't work until you hit play");
        }
        $('#'+frameID).trigger("player_ready");
    }
}

function playerTimeUpdate(frameID) {
    $('#'+frameID).trigger("player_time_update");
}

function videoUpdatePosition(event) {
    var player = players[event.currentTarget.id];
    var slider = $('#'+event.currentTarget.id).siblings('.participant-video-controls').find('.control-slider');
    $(slider).slider('value', (player.getCurrentTime() / player.getDuration() ) * 1000);
}
function videoUpdateTimer(event) {
    var player = players[event.currentTarget.id];
    var m = parseInt( player.getCurrentTime() / 60 ) % 60;
    var s = parseInt( player.getCurrentTime() % 60, 10);
    $('.control-time-current .time-m').text(m);
    $('.control-time-current .time-s').text(s);
}
function videoSetTimer(event) {
    var player = players[event.currentTarget.id];
    var m = parseInt( player.getDuration() / 60 ) % 60;
    var s = parseInt( player.getDuration() % 60, 10);
    $('.control-time-total .time-m').text(m);
    $('.control-time-total .time-s').text(s);
}