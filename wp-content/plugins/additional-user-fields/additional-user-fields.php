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

function contact_info_profile_fields() {
$profileuser = get_user_to_edit(get_current_user_id());
?>
<table class="form-table">
<tr class="user-email-wrap">
	<th><label for="email"><?php _e('Email'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
	<td><input type="email" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ) ?>" class="regular-text ltr" />
	<?php
	$new_email = get_option( $current_user->ID . '_new_email' );
	if ( $new_email && $new_email['newemail'] != $current_user->user_email && $profileuser->ID == $current_user->ID ) : ?>
	<div class="updated inline">
	<p><?php
		printf(
			__( 'There is a pending change of your email to %1$s. <a href="%2$s">Cancel</a>' ),
			'<code>' . $new_email['newemail'] . '</code>',
			esc_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ) )
	); ?></p>
	</div>
	<?php endif; ?>
	</td>
</tr>

<?php
	foreach ( wp_get_user_contact_methods( $profileuser ) as $name => $desc ) {
?>
<tr class="user-<?php echo $name; ?>-wrap">
	<th><label for="<?php echo $name; ?>">
		<?php
		/**
		 * Filter a user contactmethod label.
		 *
		 * The dynamic portion of the filter hook, `$name`, refers to
		 * each of the keys in the contactmethods array.
		 *
		 * @since 2.9.0
		 *
		 * @param string $desc The translatable label for the contactmethod.
		 */
		echo apply_filters( "user_{$name}_label", $desc );
		?>
	</label></th>
	<td><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr($profileuser->$name) ?>" class="regular-text" /></td>
</tr>
<?php
	}
?>
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
<form id="personal-profile-fields">
<?php
	name_profile_fields();
	rl_add_custom_user_profile_fields(get_current_user_id());
	about_yourself_profile_fields();
?>

<input type="button" name="submit" id="submit" class="button button-primary" value="Update Profile">
</form>
<script>
jQuery("form#personal-profile-fields").click(function(e) {post_user_data(jQuery(this));});
</script>
<?php
	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'personal_information', 'PersonalInformation' );

//[contact_information]
function ContactInformation( $atts ) {
$user_id = get_current_user_id();
	// start gathering the HTML output
	ob_start();
?>
<form id="contact_information-fields">
<?php
	contact_info_profile_fields();
?>

<input type="button" name="submit" id="submit" class="button button-primary" value="Update Profile">
</form>
<script>
jQuery("form#contact_information-fields").click(function(e) {post_user_data(jQuery(this));});
</script>
<?php
	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'contact_information', 'ContactInformation' );

function UpdateUserData() {
	$user_id = get_current_user_id();

	if ( !current_user_can('edit_user', $user_id) )
		wp_die(__('You do not have permission to edit this user.'));

	do_action( 'personal_options_update', $user_id );

	// Update the user.
	$_POST['ID'] = $user_id;
	$user_id = wp_update_user( $_POST );

	if ( is_wp_error( $user_id ) ) {
		// There was an error, probably that user doesn't exist.
		// TODO properly report error
	}

	die();
}

add_action('wp_ajax_update_user_data', 'UpdateUserData');
add_action('wp_ajax_nopriv_update_user_data', 'UpdateUserData');


/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function AdditionalUserFieldsScripts() {
     wp_register_script( 'additional_user_fields_script', plugins_url( 'js/additional_user_fields.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'additional_user_fields_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'additional_user_fields_script' );
}

add_action('init', 'AdditionalUserFieldsScripts');

function modify_user_contact_methods( $user_contact ) {
	// Add user contact methods
	$user_contact['facebook']   = __( 'Facebook Username'   );

	return $user_contact;
}
add_filter( 'user_contactmethods', 'modify_user_contact_methods');


?>
