	<main id="main" class="site-main" role="main">
		
		<div class="banner">
					<div class="RL-Logo_oben"></div>
					
				
						<div class="Logos">
								<div class="enzi-logo" >
									<a href="<?php echo get_site_url() ; ?>/index.php/enzimedien/">
										<img src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/Pictures/Logos/enzi-logo.png">
									</a>
								</div>
								
								<div class="rl-logo">
									<a href="<?php echo get_site_url() ; ?>">
										<img  src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/Pictures/Logos/RLgruen.png">
									</a>
								</div>
								
								<div class="ao-logo" >
									<a href="<?php echo get_site_url() ; ?>/index.php/areaonemedien/">
										<img src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/Pictures/Logos/areaone.png">
									</a>
						</div>
				</div>

					
					<div class="FB-Button">
						<a target="_blank" href="https://www.facebook.com/RadlagerMTB">
						<img style="margin-top:-3px;" src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/Pictures/SVG/facebook-icon-header.png">&nbsp;&nbsp;Radlager auf Facebook
						</a>
					</div>
					
		<!--		
				
					<div class="nav_to_subsite" ><a href="<?php 
// TODO that is nasty. fix that someday!
if("area_one" == $navigation)
	echo get_site_url() ;
else
	echo get_site_url() . "/index.php/area_one_medien/"; ?>" style="display: block; width: 100%; height: 100%"></a></div>
					
					
				-->
</div>
				
				
				
		
		<?php
		// read the configuration
		// - fetch the configuration and check for malicious contents
		$configuration = $post->post_content;
		if(!preg_match("/^[a-z,_-]+;[a-z_]+(;[a-z,-]+)?$/", $configuration)) {
			echo("Configuration error in: ".$post->post_title);
			exit;
		}

		// - parse config
		$config = preg_split("/;/", $configuration);

		// - find categories to be displayed
		$categories = $config[0];

		// - find filter configuration and prepare filter gui
		if(preg_match("/^use_categories$/", $config[1])) { // use categories as tags
			$filtermode = "categories";

			// - find categories to be used for filters
			if(2 < count($config))
				$filter_categories = preg_split("/,/", $config[2]);
			else
				// failsafe in case the page is not configured following the new structure
				$filter_categories = preg_split("/,/", $config[0]);

			// now add the additional categories if any
			foreach($filter_categories as $current) {
				$current_category = get_category_by_slug($current);

				$child_categories = get_categories(array('parent' => $current_category->term_id, 'hide_empty' => false));
				if(count($child_categories)) { // in case there are child categories, add the child categories instead
					foreach($child_categories as $child_category) {
						$filters[$child_category->cat_ID] = $child_category->name;
					}
				} else { // in case there are no child categories, add the current one
					$filters[$current] = $current_category->name;
				}

			}
		} else if(preg_match("/^use_titles$/", $config[1])) { // use post id as tags
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
		else if('areaone-veranstaltungen' == get_category(get_category($tmp[0])->parent)->slug)
			$type = 'event';
		else if('medien' == get_category(get_category($tmp[0])->parent)->slug)
			$type = 'media';
		else
			$type = 'other';

		// TODO böses hack
		if('titles' == $filtermode)
			$type = 'other';

		// remove duplicate entries just in case
		array_unique($filters);

		// show create post form if applicable
		
?>

	<?php if (function_exists('frontend_edit_posts_form') && $allow_reporting) {
			if("categories" === $filtermode) {
				$tmp = '';
				// assemble categories from filter list
				foreach ($filters as $key => $value) {
				    $tmp .= $key.", ";
				}

				// render editor
				frontend_edit_posts_form('new', get_categories(array('include' => $tmp)), ('media' == $type ? '&#xf040;' : '&#xf040;'), $type);
			}
		}
	?>

		<div id="masonry-grid" data-masonry='{ "itemSelector": ".post", "percentPosition": "true"}'>
<?php

		// create new loop based on the categories named in the title of the post
		// - now start the query
		$paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
		$args = array ('post_status' => 'publish', 'category_name' => $categories , 'posts_per_page' => 4, 'paged' => $paged );
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
					$parent = get_category($current->parent);
					if("veranstaltungen" != $parent->slug && "medien" != $parent->slug)
						$current = $parent;
					$tags .= "filter-".$current->cat_ID." ";
				}
			} else if("titles" == $filtermode) { // use post id as tags
				$tags .= "filter-".$post->ID." ";
			}
?>
			<article id="post-<?php the_ID(); ?>" <?php post_class($tags); ?>>
<?php

			get_template_part( 'template-parts/content', $type );
?>
			</article>
<?php
			// End of the loop.
		endwhile;

		wp_reset_postdata(); //resetting the post query
		?>
<?php
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
?>
	</div>
	<div id="spinner">
	  <img src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/loading.gif">
	</div>
	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->


	<aside id="secondary" class="sidebar widget-area" role="complementary">
		<input type="button" id="edit-post-new" data-categories="[5,152,6,7]" data-post_id="new" data-type="media" value="" onclick="frontend_create_post_stuff(jQuery(this), false);">
		<input id="filterbutton" type="button" value="&#xf0b0;" onclick="togglefilter()">
		
		<div class="filtermenu">
			<input class="closebutton" type="button" value="X" onclick="togglefilter()">
			<span style="font-weight: bold;margin-bottom: 10px">Filter:</br></span>
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
		<li class="filter" id="permalink" style="display:none;">Permalink</li>
		</ul>
		</div>
		<input id="searchbutton" type="button" value="&#xf002;" onclick="togglesearch()" >
		<div class="searchmenu">
			<input class="closebutton" type="button" value="X" onclick="togglesearch()">
			<span style="font-weight: bold;">Suche:</span>
			<span style="display:flex; -webkit-display:flex">
				<input id="searchbox" type="text" name="searchterm" value="">
				<input id="clearsearch" type="button" value="X" onclick="jQuery('#searchbox').val(''); updateFilter();">
			</span>
		</div>
	</aside><!-- .sidebar .widget-area -->

<?php get_footer(); ?>
