<?php $user_id = $user->ID; ?>
<article id="user-<?php echo $user_id; ?>" class="result result-user row-fluid">
	<div class="result-thumbnail span2"><a href="<?php echo get_author_posts_url($user_id); ?>"><img src="<?php the_profile_thumbnail_url($user_id); ?>"></a></div>
	<div class="result-meta span10">
		<h4><a href="<?php echo get_author_posts_url($user_id); ?>"><?php the_fullname($user_id); ?></a> <small><?php the_profile_field('occupation', $user_id); ?> &mdash; <?php the_profile_field('location', $user_id); ?></small></h4>
		<p><?php echo wp_trim_words(get_the_author_meta('description', $user_id), 40); ?></p>
	</div>
</article>