$(function() {
		
	function set_blog_background(src) {
		var fade_from =	'rgba(0,0,0,0)';
		var fade_to =	'#262626';
		
		var bg = new Image();
		bg.src = src;
		bg.onload = function() {
			var gradient = '-webkit-linear-gradient('+fade_from+' 0, '+fade_to+' '+this.height+'px), url('+this.src+')';
			if (bg.src) { $('#wrap').css('background-image', gradient); }
		};
	}

	if ($('.blog').length) { // Make sure we're on the blog page
		var bg = $('.blog .post').first().data('bg');
		if (bg) { set_blog_background( bg ); }
		$('.past-posts-container .post').each(function() {
			var bg = $(this).data('bg');
			if (bg) { $(this).css('background-image', 'url('+bg+')'); }
		});
	}
	
});