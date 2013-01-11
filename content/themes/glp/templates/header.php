<header id="banner" role="banner">
	<div id="nav-utility">
		<div class="container">
			<button class="lang-btn">[LANGUAGE SELECT]</button>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#searchtab" data-toggle="tab"><i class="icon-search"></i> Search</a></li>
				<?php if (is_user_logged_in()) : global $current_user; get_currentuserinfo(); ?>
				<li><a href="#profiletab" data-toggle="tab"><?php echo __('Hi,','glp') . " " . $current_user->display_name; ?></a></li>
				<li><a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php echo __('Log-out','glp'); ?></a></li>
				<?php else : ?>
				<li><a href="#jointab" data-toggle="tab"><?php echo __('Join Global Lives','glp'); ?></a></li>
				<li><a href="#logintab" data-toggle="tab">Log-in</a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div id="masthead" class="container">
		<h1 class="site-title span4"><a class="brand" href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?></a></h1>
		<div class="site-tabs span8 tabs">
			<div class="tab-content">
				<div class="tab-pane active" id="searchtab"><?php get_template_part('templates/form','search'); ?></div>
				<?php if (is_user_logged_in()) : ?>
				<div class="tab-pane" id="profiletab"><?php get_template_part('templates/nav','user'); ?></div>
				<?php else : ?>
				<div class="tab-pane" id="jointab"><?php get_template_part('templates/form','register'); ?></div>
				<div class="tab-pane" id="logintab"><?php get_template_part('templates/form','login'); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<nav id="nav-main" role="navigation">
		<div id="nav-main-inner" class="container">
		<?php if (has_nav_menu('primary_header_navigation')) { wp_nav_menu(array('theme_location' => 'primary_header_navigation')); } ?>
		<?php if (has_nav_menu('social_navigation')) { wp_nav_menu(array('theme_location' => 'social_navigation')); } ?>
		</div><!-- /#nav-main-inner -->
	</nav>
	<?php if (is_front_page()) : ?>
	<?php get_template_part('templates/nav', 'featured'); ?>
	<?php endif; ?>
</header>