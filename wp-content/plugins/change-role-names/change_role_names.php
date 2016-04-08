<?php
/*
Plugin Name: I18n Role Names
Description: Internationalize Rolenames
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

function change_role_names() {
	global $wp_roles;

	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();

	$newNames['subscriber'] = __('Kandidat');
	$newNames['contributor'] = __('Mitglied');
	$newNames['author'] = __('Vorstand');
	$newNames['editor'] = __('Redakteur');
	$newNames['administrator'] = __('Administrator');
	$newNames['usermanager'] = __('Mitgliederverwalter');

	foreach ($newNames as $key => $value) {
		$wp_roles->roles[$key]['name'] = $value;
		$wp_roles->role_names[$key] = $value;           
	}
}
add_action('init', 'change_role_names');
?>
