$(function() {

	$('#nav-main').affix({
		offset: $('#nav-main').position()
	});

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
		$('#stage').load('/wp/wp-admin/admin-ajax.php',
			{ action: 'get_participant_summary', post_id: post_id },
			function() {
				var src = $('#stage .participant-summary').first().data('bg');
				set_background( src, {from: 'rgba(0,0,0,0.5)', to: 'rgba(0,0,0,0.5)'} );
			}
		);
	}

/* Front Page */

	$('#nav-featured .participant-thumbnail').click(function() {
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
		set_stage( $(this).data('id') );
	});
	
	if ( $('.front-page').length ) { // Make sure we're on the front page
		var src = $('.front-page').data('bg');
		set_background( src, {from: '#fff'} );
	}

/* Explore the Collection */

	/* Most of the JS is in the page-explore.php template because it needs PHP. */
	
	$('#popover').hide();
	$('#popover .close').click( function() {
		$(this).parent().hide();
	});
		
/* Blog* */
	
	if ($('.blog').length) { // Make sure we're on the blog page
		var bg = $('.blog .post').first().data('bg');
		if (bg) { set_background( bg, {to: '#262626'} ); }
		$('.past-posts-container .post').each(function() {
			var bg = $(this).data('bg');
			if (bg) { $(this).css('background-image', 'url('+bg+')'); }
		});
	}
	
});