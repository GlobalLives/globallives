<footer id="content-info" role="contentinfo">
	<div class="footer-inner container">
		<div class="footer-navigation-menus row">
			<div class="span3">
			<?php
				if (has_nav_menu('primary_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('primary_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'primary_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
			<?php
				if (has_nav_menu('about_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('about_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'about_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
			<?php
				if (has_nav_menu('resources_footer_navigation')) :
					echo "<h4>" . wp_nav_menu_title('resources_footer_navigation') . "</h4>";
					wp_nav_menu(array('theme_location' => 'resources_footer_navigation'));
				endif;
			?>
			</div>
			<div class="span3">
				<?php dynamic_sidebar('sidebar-footer'); ?>
			</div>
		</div>
	</div>
</footer>

<?php if (!is_user_logged_in()) : ?>
	<?php get_template_part('templates/modal','register'); ?>
	<?php get_template_part('templates/modal','login'); ?>
<?php endif; ?>

<script>
  var _gaq=[['_setAccount','<?php echo GOOGLE_ANALYTICS_ID ? GOOGLE_ANALYTICS_ID : 'UA-2159509-3'; ?>'],['_trackPageview']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
</script>

<?php wp_footer(); ?>
