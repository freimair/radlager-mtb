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


<?php
$allow_reporting = true;
include('page_commons.php');
 ?>

