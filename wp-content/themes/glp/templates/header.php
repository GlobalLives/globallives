<?php if (get_option('show_donate_banner')) : ?><?php get_template_part('templates/banner', 'donate'); ?><?php endif; ?>
<header id="banner" role="banner">
	<div id="nav-utility">
		<div class="container">
			<ul class="nav nav-tabs">
			<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
				<li><a href="/profile"><?php _e('Profile','glp'); ?></a></li>
				<li><a href="<?php echo wp_logout_url( home_url() ); ?>"><?php _e('Log out','glp'); ?></a></li>
			<?php else : ?>
				<li><a id="signup-tab" href="<?php echo wp_registration_url(); ?>"><?php _e('Sign up','glp'); ?></a></li>
				<li><a id="login-tab" href="<?php echo wp_login_url(); ?>"><?php _e('Log in','glp'); ?></a></li>
			<?php endif; ?>
			</ul>
		</div>
	</div>
	<div id="masthead" class="container">
		<div class="row">
			<h1 class="site-title col-sm-6"><a class="brand" href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?></a></h1>
			<div class="site-tabs col-sm-6">
				<div class="tab-pane active" id="searchtab"><?php get_template_part('templates/form','search'); ?></div>
			</div>
		</div>
	</div>
	<nav id="nav-main" role="navigation">
		<div id="nav-main-inner" class="container">
      <!-- [THE SHRINKER] -->
      <input type="checkbox" id="shrinker" />
      <label class="shrinker-icon" for="shrinker">&#9776;</label>
      <label for="shrinker" class="directions">&larr; click to toggle menu</label>
			<?php if (has_nav_menu('primary_header_navigation')) { wp_nav_menu(array('theme_location' => 'primary_header_navigation')); } ?>
			<?php if (has_nav_menu('social_navigation')) { wp_nav_menu(array('theme_location' => 'social_navigation')); } ?>
		</div><!-- /#nav-main-inner -->
	</nav>
	<?php if (is_front_page()) : ?>
	<?php get_template_part('templates/nav', 'featured'); ?>
	<?php endif; ?>
</header>