<div id="donate-banner"><div class="container"><div class="row">
	<h2 class="span6"><?php echo get_option('donate_banner_header'); ?></h2>
	<p class="span4"><?php echo get_option('donate_banner_body'); ?></p>
	<div class="span2 text-center">
		<a href="<?php echo get_option('donate_button_url'); ?>" class="btn btn-large"><?php _e('Donate','glp'); ?></a><br>
		<a href="<?php echo get_option('donate_learn_more_url'); ?>"><?php _e('Learn more','glp'); ?></a>
	</div>
	<a class="not-now"><?php _e('Not now','glp'); ?></a>
</div></div></div>