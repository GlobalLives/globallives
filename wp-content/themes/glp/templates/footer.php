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
				<div class="fb-like" data-href="http://www.globallives.org/" data-layout="standard" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>
				copyright 2012-<?php echo date("Y"); ?>
			</div>
		</div>
	</div>
</footer>

<?php if (!is_user_logged_in()) : ?>
<div id="signup-modal" class="modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php _e('Sign Up','glp'); ?></h3>
	</div>
	<div class="modal-body row-fluid">
		<div class="span6">
			<h4><?php _e('Sign up with email','glp'); ?></h4>
			<form name="registerform" id="registerform" action="<?php echo site_url('wp-login.php?wpe-login=globallives&action=register'); ?>" method="post">
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
<div id="login-modal" class="modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php _e('Log In','glp'); ?></h3>
	</div>
	<div class="modal-body row-fluid">
		<div class="span6">
			<h4><?php _e('Log in with email','glp'); ?></h4>
			<form name="loginform" id="loginform" action="<?php echo site_url('wp-login.php?wpe-login=globallives', 'login_post'); ?>" method="post">
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
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-2159509-3', 'auto');
  ga('send', 'pageview');

	$(function () {

/* Functions */
		function track_link(eventCatagory,eventAction,eventLabel) {
			ga('send','event',eventCatagory,eventAction,eventLabel);
		}

/* Global Calls */
		$('#menu-item-1710').click(function() {
			track_link('socialClick','click','twitter');
		});
		$('#menu-item-1711').click(function() {
			track_link('socialClick','click','facebook');
		});
		$('#menu-item-1712').click(function() {
			track_link('socialClick','click','youTube');
		});
		$('#menu-item-7575').click(function() {
			track_link('socialClick','click','instagram');
		});
	});
</script>
