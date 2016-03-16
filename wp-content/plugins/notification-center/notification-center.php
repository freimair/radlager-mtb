<?php
/*
Plugin Name: Notification Center
Description: Send private messages to users and notify them via different media.
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

global $wpdb, $notification_center_db_version, $notification_center_table_name;
$notification_center_db_version = "1.0";
$notification_center_table_name = $wpdb->prefix . "notification_center";

/**
 * Basic options function for the plugin settings
 * @param no-param
 * @return void
 */
function InstallNotificationCenter() {
	global $wpdb, $notification_center_db_version, $notification_center_table_name;

	// Creating the database table on activating the plugin

	if ($wpdb->get_var("show tables like '$notification_center_table_name'") != $notification_center_table_name) {
		$sql = "CREATE TABLE " . $notification_center_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`subject` varchar(30) NOT NULL,
			`message` blob NOT NULL,
			`date_time` datetime NOT NULL,
			PRIMARY KEY (`id`)
			)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
 }
register_activation_hook(__FILE__, 'InstallNotificationCenter');

/**
 * For dropping the database table
 * @param no-param
 * @return no-return
 */
function UninstallNotificationCenter() {
	global $wpdb, $notification_center_table_name;
	
	$wpdb->query("DROP TABLE IF EXISTS ". $notification_center_table_name );
}

register_uninstall_hook(__FILE__, 'UninstallNotificationCenter');
?>
