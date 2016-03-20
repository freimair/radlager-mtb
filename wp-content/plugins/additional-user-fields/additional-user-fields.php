<?php
/*
Plugin Name: Additional User Fields
Description: Creates and manages additional user fields in user profile.
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

function rl_add_custom_user_profile_fields( $user ) {
?>
	<table class="form-table">
		<tr>
			<th>
				<label for="bikes">deine Bikes</label>
			</th>
			<td>
				<input type="text" name="bikes" id="bikes" value="<?php echo esc_attr( get_the_author_meta( 'bikes', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Welche Bikes fährst du?</span>
			</td>
		</tr>
		<tr>
			<th>
				<label for="phone">deine Telefonnummer</label>
			</th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Deine Telefonnummer? (Ist maximal für Vereinsmitgliedern sichtbar!)</span>
			</td>
		</tr>
		<tr>
			<th>
				<label for="home">Wo ist dein Basecamp?</label>
			</th>
			<td>
				<input type="text" name="home" id="home" value="<?php echo esc_attr( get_the_author_meta( 'home', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Von wo aus startest du deine Touren hauptsächlich? Anders gefragt: Wo wohnst du? Aber bitte nicht zu genau werden. Sowas wie <em>Steiermark/Graz</em> oder <em>Deutschland/Bayern/Staudach</em> beispielsweise wäre optimal. (Auch dieses Feld ist maxmial für Vereinsmitglieder sichtbar)</span>
			</td>
		</tr>
	</table>
<?php
}

function rl_save_custom_user_profile_fields( $user_id ) {
	
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	
	update_usermeta( $user_id, 'function', $_POST['function'] );
	update_usermeta( $user_id, 'bikes', $_POST['bikes'] );
	update_usermeta( $user_id, 'phone', $_POST['phone'] );
	update_usermeta( $user_id, 'home', $_POST['home'] );
	update_usermeta( $user_id, 'facebook', $_POST['facebook'] );
}

add_action( 'show_user_profile', 'rl_add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'rl_add_custom_user_profile_fields' );

add_action( 'personal_options_update', 'rl_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'rl_save_custom_user_profile_fields' );

function modify_user_contact_methods( $user_contact ) {
	// Add user contact methods
	$user_contact['facebook']   = __( 'Facebook Username'   );

	return $user_contact;
}
add_filter( 'user_contactmethods', 'modify_user_contact_methods');


?>
