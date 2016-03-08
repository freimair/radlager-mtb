<?php
/*
Plugin Name: Post Participants
Description: Offers participants per posts. Useful for event subscription and/or ordering items presented in posts.
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

global $post_participants_db_version;
$post_participants_db_version = "1.0";
$post_participants_table_name = $wpdb->prefix . "post_participants";

/**
 * Basic options function for the plugin settings
 * @param no-param
 * @return void
 */
function InstallPostParticipants() {
	global $wpdb, $post_participants_db_version, $post_participants_table_name;

	// Creating the database table on activating the plugin

	if ($wpdb->get_var("show tables like '$post_participants_table_name'") != $post_participants_table_name) {
		$sql = "CREATE TABLE " . $post_participants_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`date_time` datetime NOT NULL,
			`user_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
 }
register_activation_hook(__FILE__, 'InstallPostParticipants');

/**
 * For dropping the database table
 * @param no-param
 * @return no-return
 */
function UninstallPostParticipants() {
	global $wpdb, $post_participants_table_name;
	
	$wpdb->query("DROP TABLE IF EXISTS ". $post_participants_table_name );
}

register_uninstall_hook(__FILE__, 'UninstallPostParticipants');

