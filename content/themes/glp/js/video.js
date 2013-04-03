$(function() {
    
    $(window).bind("setup_players", setup_players);

    $(".controls").live('click', function() {
        var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
        var player = players[id];
        var control = $(this).attr('data-control');

        switch(control) {
            case 'play':
                players[id].playVideo();
            break;
            case 'pause':
                players[id].pauseVideo();
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
            
            case 'comments':
                $('.clip-markers').toggle();
            break;
            
            case 'dimmer':
                turn_out_the_lights();
            break;
            
        }
    });
    
    $(document).on("click", ".popover .close", function(){ 
        $(this).closest('.popover').prev().popover('hide');
    });

    $(document).on("submit", ".popover form", function() {
        $(this).find('.error').fadeOut('slow', function() {$(this).remove();});
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
    
    $(document).on('click', '#shadow', function() {
        turn_out_the_lights();
    });
    
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
YT_ready(function(){ $(window).trigger("setup_players"); });

function setup_players() {
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
            
            // Bind ready events
            $('#'+frameID).bind("player_ready", videoSetTimer);
            $('#'+frameID).bind("player_ready", setup_position_slider);
            $('#'+frameID).bind("player_ready", setup_volume_slider);

            // Bind play events
            $('#'+frameID).bind("player_start_play_buffer", videoSetTimer);
            $('#'+frameID).bind("player_start_play_buffer", enable_taggable_area);
            $('#'+frameID).bind("player_start_play_buffer", display_taggable_area);
            $('#'+frameID).bind("player_start_play_buffer", toggle_play_pause_button);

            // Bind Pause
            $('#'+frameID).bind("player_pause_end", toggle_play_pause_button);

            //Bind ontimeupdate events
            $('#'+frameID).bind("player_time_update", videoUpdateTimer);
            $('#'+frameID).bind("player_time_update", videoUpdatePosition);
        }
    });
}

function onStateChange(frameID, identifier) {
    return function (event) {
        var player = players[frameID];
        
        switch (event.data) {
            case 1:
            case 3:
                // Video has begun playing/buffering
                $('#'+frameID).trigger("player_start_play_buffer");
                t = setInterval(function () {
                    playerTimeUpdate(frameID);
                }, 1000);
            break;
            
            case 2:
            case 0:
                // Video has been paused/ended
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
//            alert("Youtube html5 detected. Tagging/commenting on this clip won't work until you hit play");
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

function enable_taggable_area() {
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
}

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

// Flash the taggable area at the start of play
function display_taggable_area(event) {
    $(".taggable-area").each(function() {
        $(this).show().find('span').each(function(){
            $(this).show().delay(1000).fadeOut('slow', function(){
                $(this).css('display', '');
            });
        });
    } );
    $('.clip-markers').show();
}

function toggle_play_pause_button(event) {
    var player = players[event.currentTarget.id];
    switch(player.getPlayerState()) {
        case 1: // Play
            $('.play-pause').attr("data-control", 'pause').find('span').removeClass('icon-play').addClass('icon-pause');
        break;

        case 2: // Pause
            $('.play-pause').attr("data-control", 'play').find('span').removeClass('icon-pause').addClass('icon-play');
        break;
    }
}

function setup_position_slider(event) {
    // Setup the slider. 0 to 1000 for precision
    var slider = $('#'+event.currentTarget.id).siblings('.participant-video-controls').find('.control-slider').slider({
        range: "min",
        min: 0,
        max: 1000
    });

    $( slider ).on( "slidestart", function( event, ui ) {
        // Unbind the slider position auto update to prevent it bouncing around whilst still being dragged
        var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
        $('#'+id).unbind("player_time_update", videoUpdatePosition);
    } );
    
    $( slider ).on( "slidestop", function( event, ui ) {
        var id = $(this).closest('.participant-clip').find('.participant-video-embed').attr('id');
        players[id].seekTo( Math.round((players[id].getDuration() * (ui.value / 1000)) * 100) / 100, true );

        //Rebind the slider position now that it has been dropped
        $('#'+id).bind("player_time_update", videoUpdatePosition);
    } );
}

function setup_volume_slider(event) {
    var player = players[event.currentTarget.id];
    
    var slider = $('#'+event.currentTarget.id).siblings('.participant-video-controls').find('.volume-slider').slider({
        orientation: "vertical",
        range: "min",
        min: 0,
        max: 100,
        value: player.getVolume()
    });
    
    $( slider ).on( "slide", function( event, ui ) {
        player.setVolume(ui.value);
    } );
}

function turn_out_the_lights() {
    if ( $('#shadow').length ) {
        $('#shadow').remove();
    }
    else {
        $('body').append('<div id="shadow"></div>');
    }
    
}