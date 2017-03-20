<div class="wrap">
<h2>Google Webmaster Tools</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<?php settings_fields('google_webmaster_tools'); ?>

<p>Insert your Google Webmaster Tools verification meta-tag below:</p>

<textarea name="gwebmasters_code" id="gwebmasters_code" rows="3"  cols="65" >
<?php echo get_option('gwebmasters_code'); ?> </textarea>


<input type="hidden" name="action" value="update" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
Have a question? Drop it at <a href="http://onlineads.lt/?utm_source=WordPress&utm_medium=Google+Webmaster+Tools+-+Options+page&utm_campaign=WordPress+plugins" title="Google Webmaster Tools">OnlineAds.lt</a>
</br></br>
<strong>Pro Tip:</strong> For periodic website performance reporting use <a href="https://nexusad.com/?utm_source=wordpress&utm_medium=Verify%2BGoogle%2BWebmaster%2BTools%2B1.3&utm_campaign=wp_plugins" title="nexusAd" target="_blank">nexusAd reporting tool</a> (for Google Analytics users).

</div>
