<?php global $profile; ?>
<article id="user-<?php echo $profile->ID; ?>" class="container">
	<div class="profile-form row span8 offset2">
		<?php gravity_form('Create a Profile', false, true, false, null, false); ?>
	</div>
</article>