<?php get_template_part('templates/page-excerpt'); ?>
<div class="static-page-container container">
	<?php get_template_part('templates/local-navigation'); ?>
	<?php get_template_part('templates/content', get_post_type()); ?>
</div>