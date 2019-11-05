<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @since Multiple Business 1.0.0
 */
get_header();
multiple_business_inner_banner();
?>
<section class="wrapper wrap-detail-page">
	<div class="container">
		<div class="row">
			<div class="col-12 col-lg-10 offset-lg-1">
				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/page/content', '' );

					# If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; # End of the loop.
				?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();