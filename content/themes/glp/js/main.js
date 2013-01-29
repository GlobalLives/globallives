$(function() {

	// Affix main navigation menu to top of page, once you scroll past it
	$('#nav-main').affix({ offset: $('#nav-main').position() });

	// Jump the main navigation to the top of the page, on pages other than Home
	$('body:not(.home)').scrollTop( $('#nav-main').offset().top );

/* Functions */

	function set_background( src, arg ) {
		var fade_from = 'rgba(0,0,0,0)';
		var fade_to = 'rgba(0,0,0,0)';
		if (arg) {
				fade_from = arg.from ? arg.from : fade_from;
				fade_to = arg.to ? arg.to : fade_to;
		}
		
		var bg = new Image();
		bg.src = src;
		bg.onload = function() {
			var gradient = '-webkit-linear-gradient('+fade_from+' 0, '+fade_to+' '+this.height+'px)';
			var bg_url = 'url('+this.src+')';
			if (bg.src) { $('#wrap').css('background-image', gradient + ', ' + bg_url); }
		};
	}

	function set_stage( post_id ) {
		$('#stage').fadeOut().load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_summary', post_id: post_id }
		).fadeIn();
	}

/* Front Page */

	$('.carousel').carousel('pause');
	
	$('#nav-featured .participant-thumbnail').click(function() {
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
		set_stage( $(this).data('id') );
	});
	
	if ( $('.front-page').length ) { // Make sure we're on the front page
		var src = $('.front-page').data('bg');
		set_background( src );
	}

/* Explore the Collection */

	/* Most of the JS is in the page-explore.php template because it needs PHP. */
	
	$('#popover').hide();
	$('#popover .close').click( function() {
		$(this).parent().hide();
	});

/* Participant Detail */

	$('.participant-clip .clip-thumbnail').click(function() {
		var clip_id = $(this).data('clip-id');
		$('#stage').load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_clip', clip_id: clip_id },
			function() {
				$(this).addClass('active');
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
	
});