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

		<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
		<a class="permalink" href="<?php echo get_site_url()."/index.php/".$wp->request."?post-".get_the_ID(); ?>">permalink</a>
	</header><!-- .entry-header -->

	<?php twentysixteen_excerpt(); ?>

	<?php twentysixteen_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
			// getting location information
			$location = maybe_unserialize(get_field('ort'));
			if(!empty($location)) {
				echo '<div class="event_location">';
?>
<div class="gmap_canvas" id="gmap_canvas_<?php echo get_the_ID(); ?>" postid="<?php echo get_the_ID();?>" lat="<?php echo $location['lat'];?>" lng="<?php echo $location['lng'];?>" address="<?php echo $location['address']; ?>"><style>#gmap_canvas img{max-width:none!important;background:none!important}</style>
</div>
<?php
				echo "</div>";
			}
			setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de-DE', 'de', 'ge', 'de_DE.UTF8', 'de_DE@UTF8', 'German');
			echo '<div class="event_start"><strong>Termin:</strong> '.strftime("%A, %e. %B %G - %R", strtotime(get_field('startdatum')))."</div>";

			date("dS F,Y",strtotime(get_field('startdatum')));

			$end = get_field('enddatum');
			if(!empty($end))
				echo '<div class="event_end"><strong>bis:</strong> '.strftime("%A, %e. %B %G - %R",strtotime(get_field('enddatum')))."</div>";

			if(!empty($location)) {

				// only check when we are near the date anyways (i.e. +10 days)
				if(time() + (9 * 24 * 60 * 60) > strtotime(get_field('startdatum'))) {
					echo '<div id="event_'.get_the_ID().'" class="event_weather"><strong>Wetterbericht:</strong> ';
?>

<script type="text/javascript">
	var callbackFunction<?php echo get_the_ID(); ?> = function(data) {
		var eventdate = new Date("<?php echo get_field('startdatum'); ?>");
		eventdate.setHours(0);
		eventdate.setMinutes(0);
		eventdate.setSeconds(0);
		eventdate.setMilliseconds(0);

		for(i = 0; i < data.query.results.channel.length; i++) {
			var forecastdate = new Date(data.query.results.channel[i].item.forecast.date);
			if(0 == eventdate - forecastdate) {
				var icontext = data.query.results.channel[i].item.forecast.text;
				icontext = icontext.toLocaleLowerCase().replace(" ", "_");
				jQuery("div#event_<?php echo get_the_ID(); ?>").append('<div class="weather_icon weather_' + icontext + '"/>');
				break;
			}
		}
	};
</script>

<script src="https://query.yahooapis.com/v1/public/yql?q=select item.forecast from weather.forecast where woeid in (select woeid from geo.places(1) where text='<?php echo $location['address']; ?>')&format=json&callback=callbackFunction<?php echo get_the_ID(); ?>"></script>

<?php

					echo "</div>";
				}
			}
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
<div class="participate">
				<input type="button" class="post_participate" data-task="<?php echo CheckParticipationStatus(get_current_user_id(), get_the_ID()) ? "leave" : "join"; ?>" data-post_id="<?php echo the_ID(); ?>" value="<?php echo CheckParticipationStatus(get_current_user_id(), get_the_ID()) ? "Abmelden" : "Bin dabei!"; ?>" />
<div class="ajax_spinner" style="display: none"><img src="<?php echo get_site_url(); ?>/wp-content/themes/radlager16/loading.gif"></div>
<div id="edit-post-<?php echo esc_attr($post_id); ?>-form"></div></div>

<?php

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endif;
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer" style="display:none;">
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
