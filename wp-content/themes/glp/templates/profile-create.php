<?php
	global $current_user, $field_keys;
	$user_id = $current_user->ID;
?>
<form id="form-profile" action="<?php echo site_url('/profile'); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="mode" value="save">

	<div id="modal-profile-1" class="modal hide" data-next="modal-profile-2">
		<div class="modal-header">
			<div class="row-fluid">
				<h3><?php _e('Welcome to Global Lives','glp'); ?></h3>
				<p><? _e('Get started by telling us a bit about yourself.','glp'); ?></p>
			</div>
		</div>
		<div class="modal-body">
			<?php get_template_part('templates/modal', 'profile-1'); ?>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn next disabled" disabled>Next</button>
		</div>
	</div>

	<div id="modal-profile-2" class="modal hide" data-next="modal-profile-3">
		<div class="modal-header">
			<div class="row-fluid">
				<h3><?php _e('Become a Volunteer!','glp'); ?></h3>
				<p><? _e('Be a part of the Global Lives Project.','glp'); ?></p>
			</div>
		</div>
		<div class="modal-body">
		<?php get_template_part('templates/modal', 'profile-2'); ?>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn next">Next</button>
		</div>
	</div>
	<div id="modal-profile-3" class="modal hide">
		<div class="modal-header">
			<div class="row-fluid">
				<h3><?php _e('How did you hear about us?','glp'); ?></h3>
				<p><?php _e('We’re always looking to help spread the word and curious to know how you found us. This will only take a moment, we promise.','glp'); ?></p>
			</div>
		</div>
		<div class="modal-body">
			<?php get_template_part('templates/modal', 'profile-3'); ?>
		</div>
		<div class="modal-footer">
			<input class="btn" type="submit" value="Save">
		</div>
	</div>
</form>

<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
<script>$(function() { $('#modal-profile-1').modal('show'); $('#user_location').geocomplete(); });</script>