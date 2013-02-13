<?php if (!have_posts()) : ?>
  <div class="alert alert-block fade in">
    <a class="close" data-dismiss="alert">&times;</a>
    <p><?php _e('Sorry, no results were found.', 'glp'); ?></p>
  </div>
<?php endif; ?>

<?php while (have_posts()) : the_post(); ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class('search-result row'); ?>>
  	<?php if( has_post_thumbnail() ) : ?>
  		<div class="search-result-thumbnail span2"><?php the_post_thumbnail('medium'); ?></div>
  		<div class="span6"><?php endif; ?>
    <header>
      <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
    </header>
    <div class="entry-summary">
      <?php the_excerpt(); ?>
    </div>
    <?php if( has_post_thumbnail() ) : ?></div><?php endif; ?>
  </article>
<?php endwhile; ?>