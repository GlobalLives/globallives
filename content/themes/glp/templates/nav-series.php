<?php global $series; ?>
<nav id="nav-series">
	<div class="nav-series-inner container">
		<div id="series-carousel" class="carousel slide">
			<ul class="carousel-inner">
				<div class="item active row">
					<li class="series-thumbnail home-thumbnail span2 active"><h4><?php echo $series->name; ?></h4><?php echo count($participants); ?> <?php _e('Lives'); ?></li>
					<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 5, 'series' => $series->slug )); // First row only grabs 5, because of the "Home" thumbnail ?>
					<?php foreach ($participants as $participant) : ?>
					<li class="series-thumbnail participant-thumbnail span2" data-id="<?php echo $participant->ID; ?>"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>"></li>
					<?php endforeach; ?>
				</div>
				<div class="item row">
					<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 6, 'offset' => 5, 'series' => $series->slug )); // First row only grabs 5, because of the "Home" thumbnail ?>
					<?php foreach ($participants as $participant) : ?>
					<li class="series-thumbnail participant-thumbnail span2" data-id="<?php echo $participant->ID; ?>"><img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>"></li>
					<?php endforeach; ?>
				</div>
			</ul>
			<a class="carousel-control left" href="#series-carousel" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#series-carousel" data-slide="next">&rsaquo;</a>
		</div>
	</div>
</nav>