<?php $post_id = $blog_post->ID; ?>
<article id="post-<?php echo $post_id; ?>" class="result result-post row-fluid">
<?php if( has_post_thumbnail($post_id) ) : ?>
	<div class="post-thumbnail span3"><?php echo get_the_post_thumbnail($post_id, 'medium'); ?></div>
	<div class="span9">
<?php else : ?>
	<div class="span12">
<?php endif; ?>
		<h4><a href="<?php the_permalink(); ?>"><?php $blog_post->post_title; ?></a></h4>
		<p><?php echo wp_trim_words($blog_post->post_content, 40); ?>
	</div>
</article>