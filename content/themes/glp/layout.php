<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <?php get_template_part('templates/header'); ?>

  <div id="wrap" role="document">
    <div id="content" class="container">
      <div id="main" class="row" role="main">
        <?php include main_template_path(); ?>
      </div>

      <aside id="sidebar" role="complementary">
        <?php get_template_part('templates/sidebar'); ?>
      </aside>

    </div><!-- /#content -->
  </div><!-- /#wrap -->

  <?php get_template_part('templates/footer'); ?>

</body>
</html>
