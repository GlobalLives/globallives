<?php if (get_option('show_donate_banner')) : ?><?php get_template_part('templates/banner', 'donate'); ?><?php endif; ?>

<header id="banner" role="banner">
	<?php get_template_part('templates/nav', 'utility'); ?>
	<?php get_template_part('templates/masthead'); ?>
	<?php get_template_part('templates/nav', 'main'); ?>
	<?php if (is_front_page()) { get_template_part('templates/nav', 'featured'); } ?>
</header>