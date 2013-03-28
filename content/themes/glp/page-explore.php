<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => -1 )); ?>

<?php get_template_part('templates/nav','explore'); ?>

<?php get_template_part('templates/view','grid'); ?>
<?php get_template_part('templates/view','map'); ?>