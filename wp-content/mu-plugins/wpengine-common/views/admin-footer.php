<script>
	jQuery(document).ready( function ($) { 
		$('input.wpe-pointer').pointer({
				'content': '<h3>New: Deploy Your Site From Staging!</h3> <p>This will move all the files and content from your staging site to your live site. A restore point will be created automatically so you can roll back this deploy.</p>',
				'position': 'top',
				'close': function() { 
						$.post(ajaxurl,{ 'action': 'wpe-ajax','wpe-action':'hide-pointer','pointer':'deploy-staging' });
					}	
			}).pointer('open');
		$(document).on('click','input[name="deploy-from-staging"]',function() { wpe_deploy_staging(); });
	}); 
</script>
