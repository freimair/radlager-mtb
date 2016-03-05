<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php

		// create new loop based on the categories named in the title of the post
		// - but first make sure nothing can go sideways
		$categories = $post->post_title;
		if(preg_match("/^[a-zA-Z0-9,]+$/", $categories))
			$categories = $post->post_title;
		else
			// do not show anything in case of an error
			$categories = "category_that_d0es_n0t_exist_almost_100_perZent";

		// - now start the query
		$query = new WP_Query( array ('category_name' => $categories ) );

		// Start the loop.
		while ( $query->have_posts() ) : $query->the_post();

			// Include the page content template.
			get_template_part( 'template-parts/content', 'page' );

			// End of the loop.
		endwhile;

		wp_reset_postdata(); //resetting the post query
		?>

	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
