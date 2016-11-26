<?php
/**
 * The template part for displaying content
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>




	<header class="entry-header">
		<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
			<span class="sticky-post"><?php _e( 'Featured', 'twentysixteen' ); ?></span>
		<?php endif; ?>

	<div class="article-meta">
		
		<div id="category">
			<?php foreach((get_the_category()) as $category) {echo $category->cat_name . ' ';}?>
		</div>
		<div id="date">
			<?php the_date('j F, Y'); ?>
		</div>	
	</div>

		
		
	

	</header><!-- .entry-header -->
	
	<!-- moved footer forward -->
	<footer class="entry-footer">
	
		
	</footer><!-- .entry-footer -->
	

	
	<div class="artcont" id="<?php foreach((get_the_category()) as $category){echo $category->cat_name . '';}?>">
	
	
	<div style="position: relative";>
		<?php if ( has_post_thumbnail() ) : ?>
    	 <?php twentysixteen_post_thumbnail('full'); ?>
	    <span style="position:absolute; bottom: 20px; background-color: rgba(23, 38, 21, 0.63); width:	100%">  <?php the_title( '<h2 class="entry-title">', '</a></h2>' ); ?></span>
	

<?php else: the_title( '<h2 class="entry-title">', '</a></h2>' ); 
 endif; ?>


	</div>	
	 
	

	
	<?php twentysixteen_excerpt(); ?>

	

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

	</div>
	
	
