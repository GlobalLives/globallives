<?php global $themes; ?>
<nav id="nav-themes">
	<div class="nav-themes-inner container">
		<div id="theme-carousel" class="carousel slide">
			<ul class="carousel-inner">
				<div class="item active row">
					<li class="theme-navitem span2 active"><?php echo _e('All Themes','glp'); ?></li>
					<?php foreach ($themes as $theme) : ?>

					<li class="theme-navitem span2" data-term="<?php echo $theme->slug; ?>">
						<div class="flyup hide">
							<a href="<?php echo get_term_link($theme,'theme'); ?>"><?php _e('Watch this theme','glp'); ?></a>
							<div class="thumbnails">
							<?php if ($theme_participants = get_posts(array( 'post_type' => 'participant', 'tax_query' => array(array('taxonomy' => 'themes', 'field' => 'slug', 'terms' => $theme->slug)) ))) : foreach ($theme_participants as $theme_participant) : ?>
								<img src="<?php the_participant_thumbnail_url( $theme_participant->ID, 'small' ); ?>">
							<?php endforeach;
							endif; ?>
							</div>
						</div>
						<?php echo $theme->name; ?>

					</li><?php endforeach; ?>
				</div>
			</ul>
			<!-- <a class="carousel-control left" href="#theme-carousel" data-slide="prev">&#9664;</a> -->
			<a class="carousel-control right" href="#theme-carousel" data-slide="next">&#9654;</a>
		</div>
	</div>
</nav>