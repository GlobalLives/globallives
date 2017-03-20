<?php global $themes; ?>
<nav id="nav-themes">
	<div class="nav-themes-inner container">
		<div id="theme-carousel" class="carousel slide">
			<ul class="carousel-inner">
				<li class="theme-navitem span2 active"><?php echo _e('All Themes','glp'); ?></li>			
<?php
	$total_themes = count($themes);
	$themes_per_row = 4;
	foreach($themes as $i => $theme) :
?>
				<?php if ($i % $themes_per_row == 0) : ?><div class="item row<?php if ($i == 0) :?> active<?php endif; ?>"><?php endif; ?>
				<li class="theme-navitem span2" data-term="<?php echo $theme->slug; ?>">
					<a class="theme-link hide" href="<?php echo get_term_link($theme,'theme'); ?>">
						<div class="theme-watch"><?php _e('Watch this theme','glp'); ?></div>
						<div class="thumbnails">
						<?php if ($theme_participants = get_posts(array( 'post_type' => 'participant', 'tax_query' => array(array('taxonomy' => 'themes', 'field' => 'slug', 'terms' => $theme->slug)) ))) : foreach ($theme_participants as $theme_participant) : ?>
							<img src="<?php the_participant_thumbnail_url( $theme_participant->ID, 'small' ); ?>">
						<?php endforeach; endif; ?>
						</div>
					</a>
					<a class="theme-filter"><?php echo $theme->name; ?></a>
				</li>
				<?php if ($i == $total_themes - 1 || $i % $themes_per_row == ($themes_per_row - 1)) : ?></div><?php endif; ?>

<?php endforeach; ?>

			</ul>
			<?php if ($total_themes > $themes_per_row) : ?><a class="carousel-control right" href="#theme-carousel" data-slide="next">&#9654;</a><?php endif; ?>
		</div>
	</div>
</nav>