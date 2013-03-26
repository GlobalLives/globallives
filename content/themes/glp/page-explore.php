<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => -1 )); ?>
<?php get_template_part('templates/nav','explore'); ?>

<!-- Grid View -->
<div id="gridview" class="view">
	<?php foreach ($participants as $participant) : ?>
		<div class="participant-grid"><a href="<?php echo get_permalink($participant->ID); ?>">
			<div class="participant-meta">
				<h3><?php echo $participant->post_title; ?></h3>
				<p><?php the_field('location',$participant->ID); ?></p>
			</div>
			<img src="<?php the_participant_thumbnail_url( $participant->ID ); ?>">
		</a></div>
	<?php endforeach; ?>
</div>

<?php get_template_part('templates/view','map'); ?>