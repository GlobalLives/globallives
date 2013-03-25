<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

	<?php get_template_part('templates/header'); ?>

	<div id="wrap" role="document">
		<div id="content" class="container" role="main">
			<?php include main_template_path(); ?>
		</div><!-- /#content -->
	</div><!-- /#wrap -->

	<?php get_template_part('templates/nav','modules'); ?>
	<?php get_template_part('templates/footer'); ?>

</body>
</html>