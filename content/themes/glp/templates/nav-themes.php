<?php global $themes; ?>
<nav id="nav-themes">
	<div class="nav-themes-inner container">
		<div id="theme-carousel" class="carousel slide">
			<ul class="carousel-inner">
				<div class="item active row">
					<li class="theme-navitem span2 active"><?php echo _e('All Themes','glp'); ?></li>
					<?php foreach ($themes as $theme) : ?>
					<li class="theme-navitem span2"><?php echo $theme->name; ?></li>
					<?php endforeach; ?>
				</div>
			</ul>
			<a class="carousel-control left" href="#theme-carousel" data-slide="prev">&lsaquo;</a>
			<a class="carousel-control right" href="#theme-carousel" data-slide="next">&rsaquo;</a>
		</div>
	</div>
</nav>