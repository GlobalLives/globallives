<nav id="nav-featured" class="nav">

	<div class="nav-featured-inner container">

		<div id="featured-carousel" class="carousel slide" data-ride="carousel">

      <!-- Wrapper for slides -->
      <div class="carousel-inner" role="listbox">

				<div class="item active row">
					<ul>
						<li class="featured-thumbnail home-thumbnail span2 active" data-controls="content_glp">
							<a href="#content_glp" aria-controls="content_glp" role="tab" data-toggle="tab" title="Global Lives Matter">
							<img src="<?php bloginfo('stylesheet_directory'); ?>/img/logo-featured.png">
						</li>
						<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 5)); // First row only grabs 5, because of the "Home" thumbnail ?>
						<?php foreach ($participants as $participant) : ?>
							<li class="featured-thumbnail participant-thumbnail span2" data-controls="content_<?php echo $participant->ID; ?>" title="<?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
								<a href="#content_<?php echo $participant->ID; ?>" aria-controls="content_<?php echo $participant->ID; ?>" role="tab" data-toggle="tab" title="<?php echo $participant->post_title; ?>: <?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
									<img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>" alt="<?php echo $participant->post_title; ?>: <?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="item row">
					<ul>
						<?php $participants = get_posts(array( 'post_type' => 'participant', 'posts_per_page' => 5, 'offset' => 5 )); // Second row only grabs 5, because of the "More" thumbnail ?>
						<?php foreach ($participants as $participant) : ?>
							<li class="featured-thumbnail participant-thumbnail span2" data-controls="content_<?php echo $participant->ID; ?>" title="<?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
								<a href="#content_<?php echo $participant->ID; ?>" aria-controls="content_<?php echo $participant->ID; ?>" role="tab" data-toggle="tab" title="<?php echo $participant->post_title; ?>: <?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
									<img src="<?php the_participant_thumbnail_url( $participant->ID, 'small' ); ?>" alt="<?php echo $participant->post_title; ?>: <?php the_field('occupation',$participant->ID);?> <?php _e('in','glp');?> <?php the_field('location',$participant->ID);?>">
								</a>
							</li>
						<?php endforeach; ?>
						<li class="span2">
							<a href="/explore/#gridview" title="More Featured People">More</a>
						</li>
					</ul>
				</div>
			</div>

			<!-- Controls -->
		  <a class="left carousel-control" href="#featured-carousel" role="button" data-slide="prev">
		    <span class="">&lt;</span>
		  </a>
		  <a class="right carousel-control" href="#featured-carousel" role="button" data-slide="next">
		    <span class="">&gt;</span>
		  </a>
		</div>
	</div>
</nav>