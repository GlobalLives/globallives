<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => -1 )); ?>

<?php if (get_option('show_filter_bar')) { get_template_part('templates/nav','explore'); } ?>

<?php get_template_part('templates/view','grid'); ?>
<?php get_template_part('templates/view','map'); ?>

<?php if ($themes = get_terms('themes',array('orderby' => 'count', 'order' => 'DESC'))) { get_template_part('templates/nav','themes'); } ?>