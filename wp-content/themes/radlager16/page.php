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
			// tag each article
			// - start with a default tagging by category
			foreach(get_the_category() as $current) {
				$tags .= "filter-".$current->slug." ";
			}
?>
			<article id="post-<?php the_ID(); ?>" <?php post_class($tags); ?>>
<?php
			/*
			 * Include the Post-Format-specific template for the content.
			 * If you want to override this in a child theme, then include a file
			 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
			 */
			get_template_part( 'template-parts/content', get_post_format() );
?>
			</article>
<?php
			// End of the loop.
		endwhile;

		wp_reset_postdata(); //resetting the post query
		?>

	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

	<aside id="secondary" class="sidebar widget-area" role="complementary">
<script type="text/javascript">
   jQuery(document).ready(function() {
	jQuery(".filter").click(function() {
			if(jQuery(this).hasClass("selected")) {
				jQuery("article[class^=filter-]").show();
				jQuery(".filter").removeClass("selected");
			} else {
				jQuery("article[class^=filter-]").hide();
				jQuery(".filter-" + jQuery(this)[0].innerText).show();
				jQuery(".filter").removeClass("selected");
				jQuery(this).addClass("selected");
			}
		});
	});
</script>

		<ul>
		<?php
		// create the filter controls
		// - but first make sure nothing can go sideways
		$post_content = $post->post_content;
		if(preg_match("/^[a-zA-Z0-9;]+$/", $post_content))
			$items = preg_split("/;/", $post->post_content);
		else
			// do not show anything in case of an error
			$items = array ();

		foreach ($items as $current):
		?>
			<li class="filter"><?php echo $current; ?></li>
		<?php
		endforeach;
		?>
		</ul>

	</aside><!-- .sidebar .widget-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
