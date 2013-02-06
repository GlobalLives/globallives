<?php get_template_part('templates/page-excerpt'); ?>
<div class="page-container static-page-container container">
	<?php get_template_part('templates/nav','local'); ?>
	<?php get_template_part('templates/content', get_post_type()); ?>
</div>