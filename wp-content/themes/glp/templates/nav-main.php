<nav id="nav-main" role="navigation">
	<div id="nav-main-inner" class="container">
	<?php if (has_nav_menu('primary_header_navigation')) { wp_nav_menu(array('theme_location' => 'primary_header_navigation')); } ?>
	<?php if (has_nav_menu('social_navigation')) { wp_nav_menu(array('theme_location' => 'social_navigation')); } ?>
	</div><!-- /#nav-main-inner -->
</nav>