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


get_header();
 ?>

<div id="primary" class="content-area">
<?php
	if('open' === get_user_meta(get_current_user_id(), 'radlager_membership_fee_status', true) && is_user_logged_in()) :
?>
<div id="payment_notification"><strong>Zahl endlich!</strong> Hier die direkte Erinnerung, sollte der Mitgliedsbeitrag noch nicht bezahlt worden sein. Vielleicht auch als Overlay das man nur wegbekommt wenn man den Button da drückt. <input type="button" value="Later" onclick="jQuery('div#payment_notification').remove();"/></div>
<?php
	endif;
?>
	<main id="main" class="site-main" role="main">
		<?php
		// read the configuration
		// - fetch the configuration and check for malicious contents
		$configuration = $post->post_content;
		if(!preg_match("/^[a-z,]+;[a-z_]+$/", $configuration)) {
			echo("Configuration error in: ".$post->post_title);
			exit;
		}

		// - find categories to be displayed
		$categories = preg_split("/;/", $configuration);
		$categories = $categories[0];

		// - find filter configuration and prepare filter gui
		$filterconfiguration = preg_split("/;/", $configuration);
		$filterconfiguration = $filterconfiguration[1];
		if(preg_match("/^use_categories$/", $filterconfiguration)) { // use categories as tags
			$filtermode = "categories";
			$configured_categories = preg_split("/,/", $categories);
			foreach($configured_categories as $current) {
				$current_category = get_category_by_slug($current);

				$child_categories = get_categories(array('child_of' => $current_category->term_id));
				if(count($child_categories)) { // in case there are child categories, add the child categories instead
					foreach($child_categories as $child_category) {
						$filters[$child_category->cat_ID] = $child_category->name;
					}
				} else { // in case there are no child categories, add the current one
					$filters[$current] = $current_category->name;
				}

			}
		} else if(preg_match("/^use_titles$/", $filterconfiguration)) { // use post id as tags
			$filtermode = "titles";
			$posts_array = get_posts( array( 'post_status' => 'publish', 'category_name' => $categories, 'posts_per_page' => '-1' ));
			foreach($posts_array as $current) {
				// TODO find better place for this piece of code
				if('true' === get_post_meta($current->ID, '_wpac_is_members_only', true)) {
					$required = maybe_unserialize(get_post_meta($current->ID, '_wpac_restricted_to', true));
					$available = wp_get_current_user()->roles;
					$result = array_diff($required, $available);
					if(empty($result))
						$filters[$current->ID] = $current->post_title;
				} else
					$filters[$current->ID] = $current->post_title;
			}
		}

		// determine post type
		$tmp = array_keys($filters);
		if('veranstaltungen' == get_category(get_category($tmp[0])->parent)->slug)
			$type = 'event';
		else
			$type = 'media';

		// remove duplicate entries just in case
		array_unique($filters);

		// show create post form if applicable
		if (function_exists('frontend_edit_posts_form')) {
			if("categories" === $filtermode) {
				// assemble categories from filter list
				foreach ($filters as $key => $value) {
				    $tmp .= $key.",";
				}

				// render editor
				frontend_edit_posts_form('new', get_categories(array('include' => $tmp)), ('media' == $type ? 'Selbst etwas berichten!' : 'Selbst etwas veranstalten!'), $type);
			}
		}

		// create new loop based on the categories named in the title of the post
		// - now start the query
		$paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
		$args = array ('post_status' => 'publish', 'category_name' => $categories , 'posts_per_page' => 3, 'paged' => $paged );
		if('event' == $type) {
			$args['orderby'] = 'meta_value';
			$args['order'] = 'ASC';
			$args['meta_key'] = 'startdatum';
			$args['meta_value'] = time();
			$args['meta_compare'] = '>';
		}

		$query = new WP_Query( $args );

		if($query->have_posts()) :
?>
		<div class="page" id="p<?php echo $paged; ?>">
<?php
		// Start the loop.
		while ( $query->have_posts() ) : $query->the_post();
			// TODO find better place for this piece of code
			if('true' === get_post_meta(get_the_ID(), '_wpac_is_members_only', true)) {
				$required = maybe_unserialize(get_post_meta(get_the_ID(), '_wpac_restricted_to', true));
				$available = wp_get_current_user()->roles;
				$result = array_diff($required, $available);
				if(!empty($result))
					continue;
			}

			// tag each article
			// - reset the tags variable first to avoid erroneos behaviour with ajax pagination
			$tags = "";
			// - decide, which mode to use
			if("categories" == $filtermode) { // use categories as tags
				foreach(get_the_category() as $current) {
					$tags .= "filter-".$current->cat_ID." ";
				}
			} else if("titles" == $filtermode) { // use post id as tags
				$tags .= "filter-".$post->ID." ";
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
		</div>
<?php
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
?>

	</main><!-- .site-main -->
	<div id="spinner">
	  <img src="http://localhost/wp-content/themes/radlager16/loading.gif">
	</div>

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

	<aside id="secondary" class="sidebar widget-area" role="complementary">
		<ul>
		<?php
		// create the filter controls
		// do a listitem for each filter
		foreach ($filters as $key => $value):
		?>
			<li class="filter" value="<?php echo $key.'">'.$value; ?></li>
		<?php
		endforeach;
		?>
		</ul>

	</aside><!-- .sidebar .widget-area -->

<?php get_footer(); ?>
