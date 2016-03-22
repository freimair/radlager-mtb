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

acf_form_head();
get_header(); ?>

<script>
// these scripts control the ajax pagination
	jQuery(function(){
		var page = 2; // start at page 2
		var loadmore = 'on'; // ready to go

		// hook the scroll, resize and ready events to see if the spinner is visible
		jQuery(document).on('scroll resize ready', function() {
			if ('on' == loadmore && jQuery(window).scrollTop() + jQuery(window).height() + 60 > jQuery('#spinner').offset().top) {
				loadmore = 'off';
				jQuery('#spinner').css('visibility', 'visible');
				// do the funny string concatination because whenever this js gets pulled in by the following ajax call, the very same string is found and the cleanup job cannot determine if there are more sites to load. Hence, leaven the '+' out results in an infinite loop.
				jQuery('#main').append(jQuery('<div class'+'="page" id="p' + page + '">').load(window.location + '?page=' + page + ' .page > *', function() {
					page++;
					loadmore = 'on';
					jQuery('#spinner').css('visibility', 'hidden');
				}));
			}
		});

		// also hook the ajaxComplete event in order to clean up after each ajax load
		jQuery( document ).ajaxComplete(function( event, xhr, options ) {
updateFilter();
			// do the funny string concatination because whenever this js gets delivered via ajax, the very same string is found which of course results in an infinite loop
			if (xhr.responseText.indexOf('<div class'+'="page"') == -1) {
				// disable ajax loading if there is nothing more to get
				loadmore = 'off';
			} else if ('on' == loadmore) {
				// retrigger the check event. the event will seize creating new ajax events as soon as the spinner is out of sight
				jQuery(document).trigger("resize");
			}

		});

	});
</script>

<div id="primary" class="content-area">
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
		$categories = preg_split("/;/", $configuration)[0];

		// - find filter configuration and prepare filter gui
		$filterconfiguration = preg_split("/;/", $configuration)[1];
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
			$posts_array = get_posts( array( 'post_status' => 'publish', 'category_name' => $categories ));
			foreach($posts_array as $current) {
				$filters[$current->ID] = $current->post_title;
			}
		}

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
				frontend_edit_posts_form('new', get_categories(array('include' => $tmp)), 'Selbst etwas berichten!');
			}
		}

		// create new loop based on the categories named in the title of the post
		// - now start the query
		$paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
		$query = new WP_Query( array ('category_name' => $categories , 'posts_per_page' => 3, 'paged' => $paged ) );

		if($query->have_posts()) :
?>
		<div class="page" id="p<?php echo $paged; ?>">
<?php
		// Start the loop.
		while ( $query->have_posts() ) : $query->the_post();
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
<script type="text/javascript">
// these scripts control the filter mechanism
   jQuery(document).ready(function() {
	// this function controls selecting and deselecting filters and applies the filter afterwards
	jQuery(".filter").click(function() {
			if(jQuery(this).hasClass("selected")) {
				jQuery(".filter").removeClass("selected");
			} else {
				jQuery(".filter").removeClass("selected");
				jQuery(this).addClass("selected");
			}
			// apply the filter
			updateFilter();
			// see if we have room for more content after the filter was applied
			jQuery(document).trigger("resize");
		});
	});

	// this function checks applies the filter to the articles
	function updateFilter() {
		selected = jQuery('.filter.selected');
		if(0 == selected.length) {
			jQuery("article[class^=filter-]").show();
		} else {
			jQuery("article[class^=filter-]").hide();
			jQuery(".filter-" + selected[0].getAttribute('value')).show();
		}
	}
</script>

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

<?php get_sidebar(); ?>
<?php get_footer(); ?>
