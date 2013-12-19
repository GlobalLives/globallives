<article id="post-<?php the_ID(); ?>" <?php post_class('search-result row'); ?>>
<?php if( has_post_thumbnail() ) : ?>
	<div class="search-result-thumbnail span2">
		<?php the_post_thumbnail('medium'); ?>
	</div>
	<div class="span6">
<?php elseif ($youtube_id = get_field('youtube_id')) : ?>
	<div class="search-result-thumbnail span2">
		<img src="http://img.youtube.com/vi/<?php echo $youtube_id; ?>/default.jpg">
	</div>
	<div class="span6">
<?php else : ?>
	<div class="span8">
<?php endif; ?>
		<header>
			<h4><a href="<?php if (get_post_type() == 'clip') { $participant = get_clip_participant(get_the_ID()); echo ($participant->ID) ? get_permalink($participant->ID) : '#'; } else { the_permalink(); } ?>"><?php the_title(); ?></a></h4>
			<h5><?php $post_type = get_post_type_object(get_post_type(get_the_ID())); echo $post_type->labels->singular_name; ?></h5>
		</header>
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div>
	</div>
  </article>