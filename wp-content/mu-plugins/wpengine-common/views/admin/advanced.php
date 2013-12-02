<div id="wrap">

<h2 class="nav-tab-wrapper">	<a href="?page=wpengine-common" class="nav-tab <?php if($_REQUEST['page'] == 'wpengine-common' || !isset($_REQUEST['page'])) { echo 'nav-tab-active'; } ?>" ><?php echo esc_html( $plugin->get_plugin_title() ) ?></a>
	<?php if( is_super_admin() ): ?>
		<a href="?page=wpengine-advanced" class="nav-tab <?php if($_REQUEST['page'] == 'wpengine-advanced') { echo 'nav-tab-active'; } ?>" >Advanced</a>	
	<?php endif; ?>
</h2>

<?php if ( ! empty( $error ) ) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
<?php endif; ?>

<?php if ( ! empty( $message ) ) : ?>
        <div class="updated fade"><p><?php echo $message; ?></p></div>
 <?php endif; ?>

<?php if ( ! is_wpe_snapshot() ) : ?>
	<h3>Advanced Configuration</h3>
		<p>
			<b>With great power comes great responsibility!</b>  These tools can greatly enhance... or completely break... your
			website, so exercise caution and don't be shy about <a href="mailto:support@wpengine.com">contacting support</a>
			if you have questions.
			<br>
			<i>Hint:</i> To test regular expressions, check out <a href="http://regexpal.com/">Regexpal</a>, a free online tool.
		</p>
    <form method="post" name="advanced" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
    <?php wp_nonce_field( PWP_NAME . '-config' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="rand_enabled">Allow ORDER BY RAND()</label></th>
				<td>
                    <select name="rand_enabled">
                        <option value="1" <?= $this->is_rand_enabled() ? "selected" : "" ?> >Enabled</option>
                        <option value="0" <?= $this->is_rand_enabled() ? "" : "selected" ?> >Disabled</option>
                    </select>
					<div class="description">
						Normally we disable <code>ORDER BY RAND()</code> orderings in MySQL queries because this
						is a big no-no for large databases which we've seen cause massive slow-downs for dozens
						of our customers.  However, you can enable it if you know what you're doing, for example
						if you cache the results for 5-15 minutes so that you're not pummeling the database with
						these slow queries.
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="regex_html_post_process">HTML Post-Processing</label></th>
				<td>
                    <textarea name="regex_html_post_process" cols="60" rows="5"><?= htmlspecialchars($fv_regex_html_post_process) ?></textarea>
					<div class="description">
						A mapping of PHP regular expressions to replacement values which are executed on all blog
						HTML after WordPress finishes emitting the entire page.  The pattern and replacement
						behavior is in the manner of <a href="http://php.net/manual/en/function.preg-replace.php">preg_replace()</a>.
						<br><br>
						The following example removes all HTML comments in the first pattern, and causes a favicon (with any filename extension) to be
						loaded from another domain in the second pattern:
						<br>
<pre>#&lt;!--.*?--&gt;#s =>
#\bsrc="/(favicon\..*)"# => src="http://mycdn.somewhere.com/$1"</pre>
					</div>
				</td>
			</tr>
			<tr><td></td><td>
                <p class="submit submit-top">
                    <input type="submit" name="advanced" value="Save" class="button-primary" />
                </p></td>
			</tr>
		</table>
    </form>
    
    <hr/>
    
    <h3>Popup Warnings</h3>
    
    <p><?php _e("Disable popup warnings",'wpengine'); ?>
    <form>
    	<?php wp_nonce_field( PWP_NAME . '-config' ); ?>
    	<input name="disable_popup_restore-point" value="1" id="popup-disabled" />
    </form></p>
    
<?php endif; ?>
</div>