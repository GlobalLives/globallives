<h1 class="section-title profile-title"><?php echo __('About','glp'); ?></h1>
<div class="page-container static-page-container container">
  <div class="row">
    <div class="col-sm-3">
      <?php get_template_part('templates/nav','local'); ?>
    </div><!-- .col-sm-3 -->
    <div class="col-sm-9">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header>
          <h2 class="page-title"><?php the_title(); ?></h2>
        </header>
        <?php the_content(); ?>
        <?php $rows = get_field('partner'); //sets the feildname of ACF
        ; ?>
        <?php for($i = 0; $i < count($rows); $i++) {
          if($i%3 == 0) { echo '<div class="row">'; }
          $individual = $rows[$i];
        ?>
          <div class="col-sm-4 text-center">
            <a href="http://<?php echo $individual['partner_url']; //getting the repeater information ?>"><?php $logo = $individual['partner_logo']; //getting the repeater information ?>
            <img src="<?php echo $logo['url']; //getting the $logo image information ?>" alt="<?php echo $logo['alt']; //getting the $logo image information ?>"></a>
          </div>
        <?php
          if($i%3 == 2) { echo '</div><!-- .row -->'; } 
        } ?>
      </article>
    </div><!-- .col-sm-9 -->
  </div><!-- .row -->
</div><!-- .container -->