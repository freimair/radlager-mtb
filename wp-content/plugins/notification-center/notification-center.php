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

/**
 * Notify a single user
 * @param userid, subject, message
 * @return no-return
 */
function NotificationCenter_NotifyUser($userid, $subject, $message) {
	global $wpdb, $notification_center_table_name;

	// do security checks
	if(!preg_match("/^[a-zA-Z0-9 ]+$/", $subject)) {
		exit;
	}
	if(!preg_match("/^[a-zA-Z0-9 ]+$/", $message)) {
		// TODO do we need links and stuff?
		exit;
	}

	// save to database
	$sql = "INSERT INTO " . $notification_center_table_name . " (user_id,date_time,subject,message) VALUES (".$userid.",'".date("Y-m-d H:i:s")."','".$subject."','".$message."');";
	$wpdb->get_results($sql);

	// trigger notifications
	// TODO
}

//[notification_center_show_messages]
function NotificationCenter_ListMessages( $atts ) {
	// start gathering the HTML output
	ob_start();

	// get all messages for the current user
	global $wpdb, $notification_center_table_name;
	$user_id = get_current_user_id();

	$sql = "SELECT * FROM " . $notification_center_table_name . " WHERE user_id = ".$user_id.";";
	$messages = $wpdb->get_results($sql);

	echo "<ul>";
	foreach ($messages as $currentmessage) {
	    echo "<li>".$currentmessage->date_time." - ".$currentmessage->subject.": ".$currentmessage->message."</li>";
	}
	echo "</ul>";

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'notification_center_show_messages', 'NotificationCenter_ListMessages' );

//[notification_center_settings]
function NotificationCenter_Settings( $atts ) {
	// start gathering the HTML output
	ob_start();

	// gather subscription hooks
	$categories = (array) get_categories(array( 'parent' => 0 ));
	$categories_without_descendants = (array) get_categories(array( 'parent' => 0, 'childless' => true ));

	foreach($categories as $current_parent) {
		if(!in_array($current_parent, $categories_without_descendants)) {
			$tmp = [];
			foreach(get_categories(array( 'parent' => $current_parent->cat_ID )) as $current) {
				$tmp[] = $current->name;
			}
			$notification_slugs[$current_parent->name] = $tmp;
		}
	}

	// add special subscription hooks
	$notification_slugs['misc'] = array('event_participation');

	// gather contact options
	$contact_options[] = "mail"; // every user has a mail contact option
	$contact_options[] = "personal_message"; // every user has personal messages
	$contact_options = array_merge($contact_options, wp_get_user_contact_methods(wp_get_current_user()));

	echo '<form id="notification_center_settings"><table>';

	// init headings
	echo '<tr><th>hook</th>';
	foreach($contact_options as $current_contact_option)
		echo '<th>'.$current_contact_option.'</th>';
	echo '</tr>';

	// print options
	foreach ($notification_slugs as $heading => $items) {
		echo '<tr><td colspan="'.(1 + count($contact_options)).'">'.$heading.'</td></tr>';
		foreach($items as $item) {
			echo '<tr><td>'.$item.'</td>';
			foreach($contact_options as $current)
				echo '<td><input type="checkbox"/></td>';
			echo '</tr>';
		}
	}
	echo '</table>';

	echo '<input type="submit" value="save"/>';
	echo '</form>';

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'notification_center_settings', 'NotificationCenter_Settings' );
?>
