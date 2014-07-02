<?php $participant_id = $participant->ID; ?>
<article id="participant-<?php echo $participant_id; ?>" data-gender="<?php the_participant_field('gender', $participant_id); ?>" data-age="<?php the_participant_field('age', $participant_id); ?>" data-income="<?php the_participant_field('income', $participant_id); ?>" data-region="<?php the_participant_field('continent', $participant_id); ?>" class="result result-participant row-fluid<?php foreach (get_participant_themes($participant_id) as $theme) { echo ' theme-' . $theme; } ?>">
	<div class="result-thumbnail span2"><a href="<?php echo get_permalink($participant_id); ?>"><img src="<?php the_participant_thumbnail_url($participant_id); ?>"></a></div>
	<div class="result-meta span10">
		<h4><a href="<?php echo get_permalink($participant_id); ?>"><?php echo $participant->post_title; ?> &mdash; <?php the_field($field_keys['participant_location'], $participant->ID); ?></a></h4>
		<?php get_the_excerpt($participant_id); ?>
		<p><?php echo wp_trim_words($participant->post_content, 40); ?></p>
		<p><b><i class="fa fa-tag"></i></b> <?php the_participant_themes($participant_id); ?></p>
	</div>
<?php
	if ($participant_clips) {
?>
	<p><div class="toggle-clips"><?php echo count($participant_clips); ?> <?php echo count($participant_clips) !== 1 ? __('clips','glp') : __('clip','glp'); ?></div></p>
<?php
		foreach ($participant_clips as $clip_id) {
			$clip = get_post($clip_id);
			include(locate_template('templates/result-clip.php'));
		}
	}
?>
</article>