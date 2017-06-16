<h1 class="section-title profile-title"><?php echo __('About','glp'); ?></h1>
<div class="page-container static-page-container container">
  <div class="row">
    <div class="span3">
      <?php get_template_part('templates/nav','local'); ?>
    </div>
    <div class="span9">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header>
          <h2 class="page-title"><?php the_title(); ?></h2>
          <?php get_template_part('templates/entry-meta'); ?>
        </header>
        <div class="entry-summary">
          <?php $rows = get_field('individual'); ?>
          <?php
            foreach ($rows as $individual) { ?>
              <div>
                <h4><?php echo $individual['individual_name']; ?> <small><?php echo $individual['individual_title']; ?></small></h4>
                <?php if($individual['individual_picture']){ ?><img src="<?php echo $individual['individual_picture']; ?>" /><?php } ?><?php echo $individual['individual_description']; ?>
              </div>
          <?php } ?>
        </div>
        <footer>
          <?php the_tags('<ul class="entry-tags"><li>','</li><li>','</li></ul>'); ?>
        </footer>
      </article>
    </div><!-- .span9 -->
  </div><!-- .row -->
</div><!-- .container -->