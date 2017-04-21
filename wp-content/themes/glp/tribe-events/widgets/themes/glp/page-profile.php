<h1 class="section-title profile-title"><?php echo __('Your Profile','glp'); ?></h1>

<?php if(is_user_logged_in()) : global $current_user; $profile = get_userdata( $current_user->ID ); // Check if user is logged in ?>
	<?php get_template_part('templates/profile'); ?>
<?php else : ?>
	<div class="alert">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		You're not logged in.
	</div>
<?php endif; ?>