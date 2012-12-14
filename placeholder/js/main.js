$(function(){

	$('#kickstarter-video').hide();
	
	$('#play-button').click(function() {
		$('#play-button, #site-description').hide();
		$('#kickstarter-video').show();
	});
	
});