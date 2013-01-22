<h1 class="events-title blog-title"><?php echo __('Community & Events','glp'); ?></h1>
<?php tribe_events_before_html(); ?>

<div class="events-container static-page-container container">
	<div class="events-inner row">

		<div class="span3">
			<?php dynamic_sidebar('sidebar-events'); ?>
		</div>

		<div class="span9">
			<?php include(tribe_get_current_template()); ?>
		</div>
	</div>
</div>

<?php tribe_events_after_html(); ?>