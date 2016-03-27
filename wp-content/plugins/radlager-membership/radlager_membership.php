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
	// start gathering the HTML output
	ob_start();
?>
<p>Bitte überweise den Jahresmitgliedsbeitrag von 20€ und du kannst du die Tätigkeiten des Vereins unterstützten und alle Vergünstigungen in Anspruch nehmen. Als Verwendungszweck gib bei der Überweisung  an. Die Verlängerung erfolgt per Überweisung des Mitgliedsbeitrages auf folgendes Konto:</p>

<p>Verwendungszweck: <strong>"Mitgliedsbeitrag <?php echo date("Y", strtotime('+31 days'));?> <?php printNameIfAvailable(); ?>"</strong></br>
Bank: Raiffeisenlandesbank Steiermark</br>
BIC: RZSTAT2G</br>
IBAN: AT673800000007132327</p>

<input type="button" value="Habe bezahlt!" />

<?php
	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_status', 'RadlagerMembershipStatus' );
?>
