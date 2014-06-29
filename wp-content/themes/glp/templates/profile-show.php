<?php global $profile, $current_user, $field_keys; $can_edit = $profile->ID == $current_user->ID; ?>

<article id="user-<?php echo $profile->ID; ?>" class="container">
	<div class="profile-container row">

		<div class="profile-sidebar span3">
			<?php if ($can_edit) { ?><a href="#modal-profile-1" data-toggle="modal" class="edit-profile"><i class="fa fa-edit"></i></a><?php } ?>

			<h4><?php _e('Hello!','glp'); ?></h4>
			<p><?php echo $profile->description; ?></p>

<?php
	if (get_field($field_keys['user_contact'],'user_'.$profile->ID)) {
?>
			<p>
<?php
		while (has_sub_field($field_keys['user_contact'],'user_'.$profile->ID)) {
			if (get_sub_field('contact_information') !== '') {
?>
				<i class="fa fa-<?php echo strtolower(get_sub_field('contact_channel')); ?>"></i>
				<?php the_sub_field('contact_information'); ?><br>
<?php
			}
		}
?>
			</p>
<?php
	}
?>
			<?php if ($profile->user_url) { ?><a href="<?php echo $profile->user_url; ?>"><?php _e('website','glp'); ?></a><?php } ?>
			<hr>
			<?php if ($can_edit) { ?><a href="#modal-profile-2" data-toggle="modal" class="edit-profile"><i class="fa fa-edit"></i></a><?php } ?>


<?php
	if ($user_skills = get_field($field_keys['user_skills'], 'user_'.$profile->ID)) {
?>
			<h4><i class="fa fa-heart"></i> <?php _e('Volunteer','glp'); ?></h4>
			<ul>
<?php
		foreach($user_skills[0] as $skill_category) {
			foreach($skill_category as $skill) {
?>
				<li><?php echo $skill; ?></li>
<?php
			}
		}
?>
			</ul>
<?php
	}
	if (get_field($field_keys['user_languages'],'user_'.$profile->ID)) {
?>
			<h4><i class="fa fa-comment"></i> <?php _e('Speaks','glp'); ?></h4>
			<ul>
<?php
		while (has_sub_field($field_keys['user_languages'],'user_'.$profile->ID)) {
			if (get_sub_field('language_name')) {
?>
			<li class="row-fluid"><span class="span8"><?php the_sub_field('language_name'); ?></span> <span class="span4"><?php $language_level = get_sub_field('language_level'); echo $language_level; ?></span></li>
<?php
			}
		}
?>
			</ul>
<?php
	}
?>
		</div>

		<div class="profile-body span9">
			<div class="profile-header" style="background-image: url('<?php the_profile_thumbnail_url($profile->ID, 'large'); ?>');">
				<div class="profile-location">
					<?php the_field($field_keys['user_location'], 'user_'.$profile->ID); ?>
				</div>
				<div class="profile-name">
					<?php echo $profile->first_name; ?> <?php echo $profile->last_name; ?>,
					<small><?php the_field($field_keys['user_occupation'], 'user_'.$profile->ID); ?></small>
				</div>
				<div class="profile-username">
					@<?php echo $profile->user_login; ?>
				</div>
			</div>

			<div class="profile-library">
				<?php include(locate_template('templates/profile-library.php')); ?>
			</div>
		</div>

	</div>
</article>
<?php
	if ($can_edit) {
?>
<form id="form-profile" action="<?php echo site_url('/profile'); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="mode" value="save">

	<div id="modal-profile-1" class="modal hide">
		<div class="modal-body">
			<?php get_template_part('templates/modal', 'profile-1'); ?>
		</div>
		<div class="modal-footer">
			<input type="submit" class="btn" value="Save">
		</div>
	</div>

	<div id="modal-profile-2" class="modal hide">
		<div class="modal-body">
			<?php get_template_part('templates/modal', 'profile-2'); ?>
		</div>
		<div class="modal-footer">
			<input type="submit" class="btn" value="Save">
		</div>
	</div>
</form>

<?php
	}
?>