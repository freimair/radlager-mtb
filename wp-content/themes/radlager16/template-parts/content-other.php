<?php
/**
 * The template part for displaying content
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>

<div class="artcont" id="<?php foreach((get_the_category()) as $category){echo $category->cat_name . '';}?>">	



	<header class="entry-header">
		<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
			<span class="sticky-post"><?php _e( 'Featured', 'twentysixteen' ); ?></span>
		<?php endif; ?>


		
		
	</header><!-- .entry-header -->
	
	
	
	<a data-post_id="<?php echo get_the_ID(); ?>"  class="more-link" style="background-color: black; float:none; margin: 0px; padding: 0px;" href="<?php echo get_site_url()."/index.php/".$wp->request."?post-".get_the_ID(); ?>" ><?php the_title( '<h2 class="entry-title">', '</h2>' ); ?><span class="screen-reader-text"> <?php the_title(); ?></span></a>

	<div class="readmoreinline" id="readmoreinline<?php echo get_the_ID(); ?>" style="display:none">
	
	

	<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>

	<?php twentysixteen_excerpt(); ?>

	<?php twentysixteen_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
			/* translators: %s: Name of current post */
			the_content( sprintf(
				__( 'Weiterlesen...<span class="screen-reader-text"> "%s"</span>', 'twentysixteen' ),
				get_the_title()
			) );

			// display registration button if applicable
			// - applicable?
			$applicable = false;
			foreach(get_the_category() as $current) {
				$applicable |= get_term_by('term_id', $current->parent, 'category')->slug == 'veranstaltungen';
			}

			// - if applicable
			if($applicable && is_user_logged_in()) :
				// - display button
?>
				<input type="button" class="post_participate" data-task="<?php echo CheckParticipationStatus(get_current_user_id(), get_the_ID()) ? "leave" : "join"; ?>" data-post_id="<?php echo the_ID(); ?>" value="<?php echo CheckParticipationStatus(get_current_user_id(), get_the_ID()) ? "Abmelden" : "Bin dabei!"; ?>" />
<?php
			endif;
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer" style="display: none;">
		<?php twentysixteen_entry_meta(); ?>
		<?php
			edit_post_link(
				sprintf(
					/* translators: %s: Name of current post */
					__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'twentysixteen' ),
					get_the_title()
				),
				'<span class="edit-link">',
				'</span>'
			);
		?>
	</footer><!-- .entry-footer -->
	</div><!-- readmore -->
</div><!-- .artcont -->
