<h1 class="blog-title profile-title"><?php echo __('Your Profile','glp'); ?></h1>

<?php if(is_user_logged_in()) : global $current_user; $profile = get_userdata( $current_user->ID ); // Check if user is logged in ?>
	<?php if ($_GET['profile-edit']) : ?>
	<?php get_template_part('templates/profile','edit'); ?>
	<?php elseif ($_POST['profile-save']) : ?>
	<?php get_template_part('templates/profile','save'); ?>
	<?php get_template_part('templates/profile'); ?>
	<?php else : ?>
	<?php get_template_part('templates/profile'); ?>
	<?php endif; ?>
<?php else : ?>
	<div class="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		You're not logged in.
	</div>
<?php endif; ?>