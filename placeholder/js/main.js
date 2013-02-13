$(function(){

	$('#youtube-embed').hide();
	
	$('#play-button').click(function() {
		$('#play-button, #site-description').hide();
		$('#youtube-embed').show();
	});
	
});