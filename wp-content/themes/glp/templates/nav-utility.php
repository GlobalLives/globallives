<div id="nav-utility">
	<div class="container">
		<ul class="nav nav-tabs">
		<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
			<li><a href="/profile"><?php _e('Profile','glp'); ?></a></li>
			<li><a href="<?php echo wp_logout_url( home_url() ); ?>"><?php _e('Log out','glp'); ?></a></li>
		<?php else : ?>
			<li><a id="register-tab" href="<?php echo wp_registration_url(); ?>"><?php _e('Sign up','glp'); ?></a></li>
			<li><a id="login-tab" href="<?php echo wp_login_url(); ?>"><?php _e('Log in','glp'); ?></a></li>
		<?php endif; ?>
		</ul>
	</div>
</div>