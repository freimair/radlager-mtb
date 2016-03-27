<?php
/*
Plugin Name: Radlager Membership
Description: Implements the life-cycle logic for being a Radlager Club Member.
Version: 1.0
Author: florianreimair
License: GPLv2 or later

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

//[radlager_membership_register]
function RadlagerMembershipRegister( $atts ) {
	// start gathering the HTML output
	ob_start();
//	echo '<iframe src="'.wp_login_url().'" />';
	wp_register();

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_register', 'RadlagerMembershipRegister' );

//[radlager_membership_login]
function RadlagerMembershipLogin( $atts ) {
	return wp_login_form(array ( 'redirect' => site_url(), 'value_remember' => true, 'echo' => false ));
}

add_shortcode( 'radlager_membership_login', 'RadlagerMembershipLogin' );

function printNameIfAvailable() {
  $current_user = wp_get_current_user();
  if ( is_user_logged_in() ) {
    echo $current_user->first_name . " " . $current_user->last_name . " (" . $current_user->user_login . ")";
  } else {
    echo "Vorname Nachname (Benutzername)";
  }
}

//[radlager_membership_status]
function RadlagerMembershipStatus( $atts ) {
	// check the payment status
	$payment_status = get_user_meta(get_current_user_id(), 'radlager_membership_fee_status', true);
	$show_button = true;
	if('open' != $payment_status && !empty($payment_status)) {
		echo '<p>Du hast bereits bezahlt.</p>';
		$show_button = false;
	}

	// start gathering the HTML output
	ob_start();

	if($show_button) :
?>

<p>Verwendungszweck: <strong>"<?php echo date("Y", strtotime('+31 days'));?> <?php printNameIfAvailable(); ?>"</strong></p>

<input type="button" id="radlager_membership_payment_claim" value="Habe bezahlt!" />


<?php
	endif;

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_status', 'RadlagerMembershipStatus' );


function RadlagerMembershipClaim() {
	// TODO do security checks

	update_usermeta( get_current_user_id(), 'radlager_membership_fee_status', 'claim' );
}

add_action('wp_ajax_radlager_membership_claim', 'RadlagerMembershipClaim');
add_action('wp_ajax_nopriv_radlager_membership_claim', 'RadlagerMembershipClaim');

/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function RadlagerMembershipScripts() {
     wp_register_script( 'radlager_membership_script', plugins_url( 'js/radlager_membership.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'radlager_membership_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'radlager_membership_script' );
}

add_action('init', 'RadlagerMembershipScripts');
?>
