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

global $wpdb, $notification_center_db_version, $notification_center_table_name, $notification_center_settings_table_name;
$notification_center_db_version = "1.0";
$notification_center_table_name = $wpdb->prefix . "notification_center";
$notification_center_settings_table_name = $wpdb->prefix . "notification_center_settings";

/**
 * Basic options function for the plugin settings
 * @param no-param
 * @return void
 */
function InstallNotificationCenter() {
	global $wpdb, $notification_center_db_version, $notification_center_table_name, $notification_center_settings_table_name;

	// Creating the database table on activating the plugin

	if ($wpdb->get_var($wpdb->prepare("show tables like %s", $notification_center_table_name)) != $notification_center_table_name) {
		$sql = "CREATE TABLE " . $notification_center_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`subject` varchar(100) NOT NULL,
			`message` blob NOT NULL,
			`date_time` datetime NOT NULL,
			PRIMARY KEY (`id`)
			)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	if ($wpdb->get_var($wpdb->prepare("show tables like %s", $notification_center_settings_table_name)) != $notification_center_settings_table_name) {
		$sql = "CREATE TABLE " . $notification_center_settings_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`hook` varchar(30) NOT NULL,
			`meta_key` varchar(15) NOT NULL,
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
	global $wpdb, $notification_center_table_name, $notification_center_settings_table_name;
	
	$wpdb->query("DROP TABLE IF EXISTS ". $notification_center_table_name );
	$wpdb->query("DROP TABLE IF EXISTS ". $notification_center_settings_table_name );
}

register_uninstall_hook(__FILE__, 'UninstallNotificationCenter');

/**
 * Notify all users
 */
function NotificationCenter_NotifyUsers($hooks, $subject, $message) {
	// TODO check for roles
	foreach(get_users() as $user)
		NotificationCenter_NotifyUser($hooks, $user->ID, $subject, $message);
}

/**
 * Notify a single user
 */
function NotificationCenter_NotifyUser($hooks, $user_id, $subject, $message) {
	global $wpdb, $notification_center_table_name;

	$subject = sanitize_text_field($subject);
	$message = sanitize_text_field($message);

	// trigger notifications
	// fetch user notification settings
	global $wpdb, $notification_center_settings_table_name;
	$sql = $wpdb->prepare("SELECT DISTINCT meta_key FROM $notification_center_settings_table_name WHERE user_id = %d AND (", $user_id);
	foreach($hooks as $current_hook)
		$sql .= $wpdb->prepare("hook = %s OR ", $current_hook);
	$sql = substr($sql, 0, -4).");";

	$rows = $wpdb->get_results($sql);
	foreach($rows as $current) {
		switch($current->meta_key) {
			case 'mail' :
				//wp_mail( 'admin@example.com', $subject, $message );
				break;	// TODO implement email notification
			case 'pm' :
				// save to database
				$sql = $wpdb->prepare("INSERT INTO $notification_center_table_name (user_id,date_time,subject,message) VALUES (%d,%s,%s,%s);", array($user_id, date("Y-m-d H:i:s"), $subject, $message));
				$wpdb->query($sql);
				break;
		}
	}
}

function NotificationCenterDeleteMessage() {
	global $wpdb, $notification_center_table_name;

	if(!is_user_logged_in())
		die();

	$messageid = (int)$_POST['messageid'];
	$current_user_id = get_current_user_id();

	$sql = $wpdb->prepare("DELETE FROM $notification_center_table_name WHERE id = %d AND user_id = %d", array($messageid, $current_user_id));
	$wpdb->query($sql);
}


add_action('wp_ajax_notification_center_delete_message', 'NotificationCenterDeleteMessage');
add_action('wp_ajax_nopriv_notification_center_delete_message', 'NotificationCenterDeleteMessage');

//[notification_center_show_messages]
function NotificationCenter_ListMessages( $atts ) {
	// start gathering the HTML output
	ob_start();

	// get all messages for the current user
	global $wpdb, $notification_center_table_name;
	$user_id = get_current_user_id();

	$sql = $wpdb->prepare("SELECT * FROM $notification_center_table_name WHERE user_id = %d ORDER BY date_time DESC;", $user_id);
	$messages = $wpdb->get_results($sql);

	echo "<ul>";
	foreach ($messages as $currentmessage) {
	    echo "<li>".esc_html($currentmessage->date_time)." - ".esc_html($currentmessage->subject).": ".esc_html($currentmessage->message).' <input id="notification_center_delete_message" type="button" value="'.__('Delete').'" onclick="NotificationCenter_DeleteMessage(jQuery(this), '.esc_attr($currentmessage->id).')"/></li>';
	}
	echo "</ul>";

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'notification_center_show_messages', 'NotificationCenter_ListMessages' );

function NotificationCenter_IsNotifyUser($user_id, $hook, $contact_method) {
	global $wpdb, $notification_center_settings_table_name;
	$sql = $wpdb->prepare("SELECT hook, meta_key FROM $notification_center_settings_table_name WHERE user_id = %d AND hook = %s AND meta_key = %s;", array($user_id, $hook, $contact_method));
	$rows = $wpdb->get_results($sql);

	return !empty($rows);
}

//[notification_center_settings]
function NotificationCenter_Settings( $atts ) {
	// start gathering the HTML output
	ob_start();

	// gather subscription hooks
	$categories = (array) get_categories(array( 'parent' => 0 ));
	$categories_without_descendants = (array) get_categories(array( 'parent' => 0, 'childless' => true ));

	foreach($categories as $current_parent) {
		if(!in_array($current_parent, $categories_without_descendants)) {
			$tmp = array();
			foreach(get_categories(array( 'parent' => $current_parent->cat_ID )) as $current) {
				$tmp[$current->slug] = $current->name;
			}
			$notification_slugs[$current_parent->name] = $tmp;
		}
	}

	// add special subscription hooks
	$notification_slugs[__('hook_misc')] = array('event_participation' => __('event_participation'), 'administrative' => __('administrative'));

	// gather contact options
	$contact_options[] = __('Email'); // every user has a mail contact option
	$contact_options[] = __("pm"); // every user has personal messages
	$contact_options = array_merge($contact_options, wp_get_user_contact_methods(wp_get_current_user()));

	echo '<form id="notification_center_settings"><table>';

	// init headings
	echo '<tr><th>'.__('hook').'</th>';
	foreach($contact_options as $current_contact_option)
		echo '<th>'.esc_html($current_contact_option).'</th>';
	echo '</tr>';

	// print options
	foreach ($notification_slugs as $heading => $items) {
		echo '<tr><td colspan="'.esc_html(1 + count($contact_options)).'">'.esc_html($heading).'</td></tr>';
		foreach($items as $key => $value) {
			echo '<tr><td>'.esc_html($value).'</td>';
			foreach($contact_options as $current) {
				$checked = (NotificationCenter_IsNotifyUser(get_current_user_id(), $key, $current) ? 'checked="checked"' : '');
				echo '<td><input type="checkbox" name="'.esc_attr($key).'['.esc_attr($current).']" '.$checked.' /></td>';
			}
			echo '</tr>';
		}
	}
	echo '</table>';

	echo '<input type="submit" value="'.__('Save').'"/>';
	echo '</form>';

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'notification_center_settings', 'NotificationCenter_Settings' );

function NotificationCenterSaveSettings() {
	global $wpdb, $notification_center_settings_table_name;

	if(!is_user_logged_in())
		die();

	// TODO check sanity
	$input = $_POST;
	unset($input['action']); // remove the action element

	// delete existing config
	$wpdb->query($wpdb->prepare("DELETE FROM $notification_center_settings_table_name WHERE user_id=%d;", get_current_user_id()));

	// save new config
	foreach($input as $current_hook => $values)
		foreach($values as $current_contact_method => $donotusethisbrain)
			$wpdb->query($wpdb->prepare("INSERT INTO $notification_center_table_name (user_id, hook, meta_key) VALUES (%d,%s,%s);", array(get_current_user_id(), $current_hook, $current_contact_method)));

	// TODO report errors appropriatly
	die();
}

add_action('wp_ajax_notification_center_save_settings', 'NotificationCenterSaveSettings');
add_action('wp_ajax_nopriv_notification_center_save_settings', 'NotificationCenterSaveSettings');


/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function NotificationCenterScripts() {
     wp_register_script( 'notification_center_script', plugins_url( 'js/notification_center.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'notification_center_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'notification_center_script' );
}

add_action('init', 'NotificationCenterScripts');

function NotificationCenterUpdatePostHook( $post_id, $post ) {
	$post_title = $post->post_title;
	$post_url = get_permalink( $post_id );

	$categories = get_the_category($post_id);
	$category_slugs = array();
	foreach($categories as $current)
		$category_slugs[] = $current->slug;

	$subject = 'Neues auf der Website';

	$message = "Neuer Content auf der Website:\n\n";
	$message .= $post_title . ": " . $post_url;

	NotificationCenter_NotifyUsers($category_slugs, $subject, $message);
}
add_action( 'publish_post', 'NotificationCenterUpdatePostHook', 10, 2 );
?>
