<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>

		</div><!-- .site-content -->

		<footer id="colophon" class="site-footer" role="contentinfo">


		<?php if('open' === get_user_meta(get_current_user_id(), 'radlager_membership_fee_status', true) && is_user_logged_in()) :?>
			<div id="payment_notification" ></br> Laut unseren Aufzeichnung ist dein Mitgliedbeitrag noch nicht bezahlt. </br> Weitere Infos hierzu findest du in deinem Profil. Sobald du bezahlt hast verschwindet auch diese Erinnerung. Danke! </br></br><input type="button" value="<?php _e("Später..."); ?>" onclick="jQuery('div#payment_notification').remove();"/></br></div>
		<?php endif; ?>


		</footer><!-- .site-footer -->
	</div><!-- .site-inner -->
</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
