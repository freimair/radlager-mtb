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

function name_profile_fields() {
$profileuser = get_user_to_edit(get_current_user_id());
?>
<table class="form-table">
	<tbody><tr class="user-first-name-wrap">
	<th><label for="first_name"><?php _e('First Name') ?></label></th>
	<td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($profileuser->first_name) ?>" class="regular-text" /></td>
</tr>

<tr class="user-last-name-wrap">
	<th><label for="last_name"><?php _e('Last Name') ?></label></th>
	<td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($profileuser->last_name) ?>" class="regular-text" /></td>
</tr>

<tr class="user-nickname-wrap">
	<th><label for="nickname"><?php _e('Nickname'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
	<td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr($profileuser->nickname) ?>" class="regular-text" /></td>
</tr>

<tr class="user-display-name-wrap">
	<th><label for="display_name"><?php _e('Display name publicly as') ?></label></th>
	<td>
		<select name="display_name" id="display_name">
		<?php
			$public_display = array();
			$public_display['display_nickname']  = $profileuser->nickname;
			$public_display['display_username']  = $profileuser->user_login;

			if ( !empty($profileuser->first_name) )
				$public_display['display_firstname'] = $profileuser->first_name;

			if ( !empty($profileuser->last_name) )
				$public_display['display_lastname'] = $profileuser->last_name;

			if ( !empty($profileuser->first_name) && !empty($profileuser->last_name) ) {
				$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
				$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
			}

			if ( !in_array( $profileuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
				$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;

			$public_display = array_map( 'trim', $public_display );
			$public_display = array_unique( $public_display );

			foreach ( $public_display as $id => $item ) {
		?>
			<option <?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
		<?php
			}
		?>
		</select>
	</td>
</tr>
</table>
<?php
}

function about_yourself_profile_fields() {
$profileuser = get_user_to_edit(get_current_user_id());
?>
<table class="form-table">
<tr class="user-description-wrap">
	<th><label for="description"><?php _e('Biographical Info'); ?></label></th>
	<td><textarea name="description" id="description" rows="5" cols="30"><?php echo $profileuser->description; // textarea_escaped ?></textarea>
	<p class="description"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></p></td>
</tr>

</tbody></table>
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

//[personal_information]
function PersonalInformation( $atts ) {
$user_id = get_current_user_id();
	// start gathering the HTML output
	ob_start();
?>
<?php
	name_profile_fields();
	rl_add_custom_user_profile_fields(get_current_user_id());
	about_yourself_profile_fields();
	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'personal_information', 'PersonalInformation' );

function modify_user_contact_methods( $user_contact ) {
	// Add user contact methods
	$user_contact['facebook']   = __( 'Facebook Username'   );

	return $user_contact;
}
add_filter( 'user_contactmethods', 'modify_user_contact_methods');


?>
