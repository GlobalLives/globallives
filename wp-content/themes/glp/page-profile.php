<h1 class="section-title profile-title"><?php echo __('Your Profile','glp'); ?></h1>

<?php if (is_user_logged_in()) {
	global $current_user;
	$profile = get_userdata( $current_user->ID );
	get_template_part('templates/profile');
} else { ?>
	<p class="alert">You are not logged in.</p>
	<script>$(function() { $('#modal-login').modal('show'); });</script>
<?php } ?>