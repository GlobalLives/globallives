<?php $participants = get_posts(array( 'post_type' => 'participant' )); ?>
<nav id="nav-featured">
	<div class="nav-featured-inner container">
		<h2><?php _e('Featured Videos','glp'); ?> (<?php echo 1 + count($participants); ?>)</h2>
		<div id="featured-carousel" class="carousel slide row">
		    <ul class="carousel-inner">
		    	<li class="featured-thumbnail span2 active"><a href="<?php home_url(); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-featured.png"></a></li>
		    	<?php foreach ($participants as $participant) : ?>
		    	<li class="featured-thumbnail participant-thumbnail span2" data-id="<?php echo $participant->ID; ?>"><img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id($participant->ID,'thumbnail') ); ?>"></li>
		    	<?php endforeach; ?>
		    </ul>
<!--
		    <a class="carousel-control left" href="#featured-carousel" data-slide="prev">&lsaquo;</a>
		    <a class="carousel-control right" href="#featured-carousel" data-slide="next">&rsaquo;</a>
-->
		</div>
	</div>
</nav>
