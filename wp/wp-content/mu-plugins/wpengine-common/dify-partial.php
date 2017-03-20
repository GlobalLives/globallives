<?php

/**
 * Output the basic structure where dify content will be placed in the wpe widget or plugin in wp-admin
 * @param string $show_hidden The string 'true' if we are displaying Dify posts that are hidden for QA style checking
 *	      'false' if only showing live content meant for production
 * @param string $install_name The install who's wpe-admin we're currently displaying dify for
 */
function display_wpe_dify($show_hidden, $install_name)
{
?>
	<div class='wpe-dify-posts' data-page=0 data-source='wp_admin' data-show-hidden=<?= $show_hidden ?> data-install-name=<?= $install_name ?> >
		<hr class="wpe-dify-section-break">
		<div class="wpe-dify-section-title">
			<h2>WP Engine has your back</h2>
		</div>
		<div class="wpe-dify-content">
			<ul>
			</ul>
		</div>
		<div class="wpe-dify-show-more">
		    <a href="#">Show More</a>
		    <img class="wpe-dify-blog-spinner" src="<?php echo WPE_PLUGIN_URL; ?>/images/ajax-loader-transparent.gif"/ >
		</div>
	</div>
<?php
}