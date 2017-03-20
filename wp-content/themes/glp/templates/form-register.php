<form id="registerform" method="post" action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" class="form-inline pull-right">
	<?php do_action('register_form'); ?>
	<input type="text" name="user_login" value="<?php echo esc_attr(stripslashes($user_login)); ?>" placeholder="<?php _e('Username','glp'); ?>" />
	<input type="text" name="user_email" value="<?php echo esc_attr(stripslashes($user_email)); ?>" placeholder="<?php _e('Email','glp'); ?>" />

	<button type="submit" name="user-submit" class="btn"><?php _e('Register','glp'); ?></button>  
	<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>?register=true" />
	<input type="hidden" name="user-cookie" value="1" />

	<p class="help-block"><?php $register = $_GET['register']; if($register == true) { _e('Check your email for your password.','glp'); } else { _e('Your password will be emailed to you.','glp'); } ?></p>
</form>