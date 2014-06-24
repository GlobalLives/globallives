<div id="modal-register" class="modal hide">
	<div class="modal-header">
		<div class="row-fluid">
			<h3 class="span6"><?php _e('Join Global Lives','glp'); ?></h3>
			<span class="span6 text-right">Already a member? <a class="login-toggle">Log in</a></span>
		</div>
	</div>
	<div class="modal-body row-fluid">
		<div class="span6">
			<p><?php _e('Create your account','glp'); ?></p>
			<form name="registerform" id="registerform" action="<?php echo site_url('wp-login.php?wpe-login=globallives&action=register'); ?>" method="post">
				<p><input type="text" name="user_email" id="user_email" class="input" value="" size="25" placeholder="Email Address" /></label></p>
				<p><input type="text" name="user_login" id="user_login" class="input" value="" size="20" placeholder="Username" /></label></p>
				<p id="reg_passmail"><?php _e('A password will be emailed to you.','glp'); ?></p>
				<p><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php _e('Create my account','glp'); ?>" /></p>
				<input type="hidden" name="redirect_to" value="<?php echo site_url('/profile'); ?>" />
			</form>
		</div>
		<div class="span6">
			<p><?php _e('Or sign up with:','glp'); ?></p>
			<?php do_action('oa_social_login'); ?>
		</div>
	</div>
</div>