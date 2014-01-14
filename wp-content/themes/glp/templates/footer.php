<footer id="content-info" role="contentinfo">
	<div class="footer-inner container">
		<div class="footer-navigation-menus row">
			<div class="span3">
			<?php
				if (has_nav_menu('primary_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('primary_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'primary_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
			<?php
				if (has_nav_menu('about_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('about_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'about_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
			<?php
				if (has_nav_menu('resources_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('resources_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'resources_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
				<?php dynamic_sidebar('sidebar-footer'); ?>
			</div>
		</div>
	</div>
</footer>

<?php if (GOOGLE_ANALYTICS_ID) : ?>
<script>
  var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS_ID; ?>'],['_trackPageview']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
</script>
<?php endif; ?>

<?php if (!is_user_logged_in()) : ?>
<div id="signup-modal" class="modal hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php _e('Sign Up','glp'); ?></h3>
	</div>
	<div class="modal-body row-fluid">
		<div class="span6">
			<h4><?php _e('Sign up with email','glp'); ?></h4>
			<form name="registerform" id="registerform" action="<?php echo wp_login_url(); ?>?action=register" method="post">
				<p>
					<label for="user_email"><?php _e('Email Address','glp'); ?><br />
					<input type="text" name="user_email" id="user_email" class="input" value="" size="25" /></label>
				</p>
				<p>
					<label for="user_login"><?php _e('Username','glp');?><br />
					<input type="text" name="user_login" id="user_login" class="input" value="" size="20" /></label>
				</p>
				<p id="reg_passmail"><?php _e('A password will be emailed to you.','glp'); ?></p>
				<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" />
				<p class="submit">
					<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php _e('Register','glp'); ?>" />
				</p>
			</form>
		</div>
		<div class="span6">
			<h4><?php _e('Sign up via','glp'); ?></h4>
			<?php do_action('oa_social_login'); ?>		
		</div>
	</div>
</div>
<div id="login-modal" class="modal hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php _e('Log In','glp'); ?></h3>
	</div>
	<div class="modal-body row-fluid">
		<div class="span6">
			<h4><?php _e('Log in with email','glp'); ?></h4>
			<form name="loginform" id="loginform" action="<?php echo wp_login_url(); ?>" method="post">
				<p class="login-username">
					<label for="user_login"><?php _e('Username','glp'); ?></label>
					<input type="text" name="log" id="user_login" class="input" value="" size="20" />
				</p>
				<p class="login-password">
					<label for="user_pass"><?php _e('Password','glp'); ?></label>
					<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" />
				</p>
				<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" />
				<input type="hidden" name="rememberme" value="forever" />
				<p class="submit">
					<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php _e('Log In','glp'); ?>" />
				</p>
			</form>
		</div>
		<div class="span6">
			<h4><?php _e('Log in via','glp'); ?></h4>
			<?php do_action('oa_social_login'); ?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
