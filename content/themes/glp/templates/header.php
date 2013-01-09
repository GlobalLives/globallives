<header id="banner" role="banner">
	<div id="nav-utility">
		<div class="container">
			<button class="lang-btn">[LANGUAGE SELECT]</button>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#searchtab" data-toggle="tab">Search</a></li>
				<li><a href="#jointab" data-toggle="tab">Join Global Lives</a></li>
				<li><a href="#logintab" data-toggle="tab">Log-in</a></li>
			</ul>
		</div>
	</div>
	<div id="masthead" class="container">
		<h1 class="site-title span4"><a class="brand" href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?></a></h1>
		<div class="site-tabs span8 tabs">
			<div class="tab-content">
				<div class="tab-pane active" id="searchtab"><?php get_template_part('templates/searchform'); ?></div>
				<div class="tab-pane" id="jointab"><?php get_template_part('templates/registerform'); ?></div>
				<div class="tab-pane" id="logintab"><?php get_template_part('templates/loginform'); ?></div>
			</div>
		</div>
	</div>
	<nav id="nav-main" role="navigation">
		<div id="nav-main-inner" class="container">
		<?php
			if (has_nav_menu('primary_header_navigation')) :
				wp_nav_menu(array('theme_location' => 'primary_header_navigation'));
			endif;
		?>
		<?php
			if (has_nav_menu('social_navigation')) :
				wp_nav_menu(array('theme_location' => 'social_navigation'));
			endif;
		?>
		</div><!-- /#nav-main-inner -->
	</nav>
</header>