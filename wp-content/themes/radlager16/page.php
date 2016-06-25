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
<div id="payment_notification"><strong>Zahl endlich!</strong> Lt. unseren Aufzeichnung hast du noch nicht bezahlt. Weitere Infos hierzu findest du in deinem Profil. Sobald du bezahlt hast verschwindet auch diese Erinnerung. Danke!<input type="button" value="<?php _e("Später..."); ?>" onclick="jQuery('div#payment_notification').remove();"/></div>
<?php
	endif;
?>

		<div style="float:left; border-color: red;border-style: solid;border-width: 1px;background-color: #FFE4E4;margin-bottom: 20px;padding:10px;font-size:12px;"><p>Wir bekommen gerade eine neue Website. Es funktioniert noch nicht alles ganz wie es soll. <a href="https://alt.radlager-mtb.at">Hier</a>, jedenfalls, gehts zur alten Website!</p>
<p>Und natürlich gibts noch Kinderkrankheiten:
<ul><li>Wie es scheint ist beim Übersiedeln der Profile (speziell die Bikes und die Telefonnummern) teilweise was durcheinander gekommen. Sollte das bei dir der Fall sein bitte teil uns das kurz mit an <a href="mailto:<?php echo antispambot("web@radlager-mtb.at")."?subject=Profilproblem ".printNameIfAvailable()."&body=Danke!\">".antispambot("web@radlager-mtb.at"); ?></a>. Wir kümmern uns drum!</li>
<li>Benachrichtigungen scheinen bei jeder Änderung eines Beitrags ausgesendet zu werden. Ist bekannt :).</li>
</ul></p>

</div>
<?php
$allow_reporting = true;
include('page_commons.php');
 ?>

