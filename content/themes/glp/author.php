<h1 class="blog-title profile-title"><?php echo __('Member Profile','glp'); ?></h1>

<?php $profile = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author)); ?>
	<?php get_template_part('templates/profile'); ?>