<?php
	$participant_id = $participant->ID;
	$clips = get_library_participant_clips($profile_id, $participant_id);
?>
<div class="library-participant ">
	<div class="library-participant-header row-fluid">
		<h4 class="span10">
			<small><?php echo count($clips); ?> <?php echo (count($clips) == 1) ? __('clip','glp') : __('clips','glp'); ?></small>
		<?php echo $participant->post_title; ?> &mdash; <?php the_field($field_keys['participant_location'], $participant->ID); ?>
		</h4>
		<a href="#" class="span2 toggle-meta text-right"><?php _e('Show info','glp'); ?></a>
	</div>
	<div class="participant-meta row-fluid hide">
	    <div class="span6">
	    	<b><?php _e('Occupation','glp'); ?>:</b> <?php the_field($field_keys['participant_occupation'], $participant_id); ?><br>
	    		<?php if ($dob = get_field($field_keys['participant_dob'], $participant_id)) : ?><b><?php _e('Date of Birth','glp'); ?>:</b> <?php echo $dob; ?><?php endif; ?>
	    </div>
	    <div class="span6">
	    	<b><?php _e('Religion','glp'); ?>:</b> <?php the_field($field_keys['participant_religion'], $participant_id); ?><br>
	    	<b><?php _e('Income','glp'); ?>:</b> <?php $incomes = get_field_object($field_keys['participant_income']); $income = get_field($field_keys['participant_income'], $participant_id); echo $incomes['choices'][$income]; ?>
	    </div>
	    <div>
	    	<?php _e('Themes:'); ?> <?php the_participant_themes($participant_id); ?>
	    </div>
	</div>
	<div class="library-clips">
<?php
	foreach ($clips as $clip) {
		include(locate_template('templates/library-clip.php'));
	}
?>
	</div>
</div>