<nav id="nav-featured">
	<div class="nav-featured-inner container">
		<div id="featured-carousel" class="carousel slide">
			<ul class="carousel-inner">
				<div class="item active row">
					<li class="featured-thumbnail home-thumbnail span2 active"><img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-featured.png"></li>
					<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 5 )); // First row only grabs 5, because of the "Home" thumbnail ?>
					<?php foreach ($participants as $participant) : ?>
					<li class="featured-thumbnail participant-thumbnail span2" data-id="<?php echo $participant->ID; ?>"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>"></li>
					<?php endforeach; ?>
				</div>
				<div class="item row">
					<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 6, 'offset' => 5 )); // First row only grabs 5, because of the "Home" thumbnail ?>
					<?php foreach ($participants as $participant) : ?>
					<li class="featured-thumbnail participant-thumbnail span2" data-id="<?php echo $participant->ID; ?>"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>"></li>
					<?php endforeach; ?>
				</div>
			</ul>
			<a class="carousel-control left" href="#featured-carousel" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#featured-carousel" data-slide="next">&rsaquo;</a>
		</div>
	</div>
</nav>
