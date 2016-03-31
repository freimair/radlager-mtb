<?php
/*
Plugin Name: Post Participants
Depends: Frontend Create Posts
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

global $wpdb, $post_participants_db_version, $post_participants_table_name;
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

	if ($wpdb->get_var($wpdb->prepare("show tables like %s", $post_participants_table_name)) != $post_participants_table_name) {
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

/**
 * Post the participation wish
 * @param no-param
 * @return no-return
 */
function PostUserParticipationIntent() {
	$post_id = (int)$_REQUEST['post_id'];
	$task = $_REQUEST['task'];

	if(!is_user_logged_in()) {
		$user = wp_get_current_user();
		if ( !in_array( 'contributor', (array) $user->roles ) )
			ReportAndExit("Access Denied");
	}

	$user_id = get_current_user_id();

	if("join" == $task)
		JoinPost($post_id, $user_id);
	else if("leave" == $task)
		LeavePost($post_id, $user_id);

	die();
}

function JoinPost($post_id, $user_id) {
	global $wpdb, $post_participants_table_name;

	// find post meta and check whether the use can join
	$max_participants = get_field('max_participants', $post_id);
	$deadline = get_field('anmeldeschluss', $post_id);

	// - check for registration deadline
	if(date("Y-m-d H:i") > $deadline) {
		ReportAndExit("registration deadline passed");
	}

	// - check whether event is full already
	$sql = $wpdb->prepare("SELECT * FROM $post_participants_table_name WHERE post_id = %d;", $post_id);
	$rows = $wpdb->get_results($sql);

	if(count($rows) >= $max_participants) {
		ReportAndExit("event is full");
	}

	// find participation mode: standard, voranmeldung
	// TODO

	// check whether the user already participates
	if(CheckParticipationStatus($user_id, $post_id)) {
		ReportAndExit("user already participates");
	}

	// save participation status
	$sql = $wpdb->prepare("INSERT INTO $post_participants_table_name (post_id,date_time,user_id) VALUES (%d, %s, %d);", array($post_id, date("Y-m-d H:i:s"), get_current_user_id()));
	$wpdb->get_results($sql);

	// notify the user
	if (function_exists('NotificationCenter_NotifyUser')) {
		NotificationCenter_NotifyUser(array('event_participation'), $user_id, "You have joined a post", get_post($post_id)->post_title);
	}

	ReportAndExit("leave");
}

function LeavePost($post_id, $user_id) {
	global $wpdb, $post_participants_table_name;

	// remove participant
	$sql = $wpdb->prepare("DELETE FROM $post_participants_table_name WHERE post_id = %d AND user_id = %d;", array($post_id, $user_id));
	$wpdb->get_results($sql);

	// notify the user
	if (function_exists('NotificationCenter_NotifyUser')) {
		NotificationCenter_NotifyUser(array('event_participation'), $user_id, "You have left a post", get_post($post_id)->post_title);
	}

	// join participants from the waiting list
	// TODO

	ReportAndExit("join");
}

function ReportAndExit($result) {
	// Check for method of processing the data
	if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
		$result = array("result" => $result);

		echo json_encode($result);
	} else {
		header( "location:" . $_SERVER["HTTP_REFERER"] );
	}

	die();
}

/**
 * Post the participation wish
 * @param no-param
 * @return boolean. True if user already participates
 */
function CheckParticipationStatus($user_id, $post_id) {
	global $wpdb, $post_participants_table_name;

	$sql = $wpdb->prepare("SELECT * FROM $post_participants_table_name WHERE post_id = %d AND user_id = %d;", array($post_id, $user_id));
	$user_participates = $wpdb->get_results($sql);

	return 0 < count($user_participates);
}

add_action('wp_ajax_post_participants_intent', 'PostUserParticipationIntent');
add_action('wp_ajax_nopriv_post_participants_intent', 'PostUserParticipationIntent');


/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function PostParticipantsScripts() {
     wp_register_script( 'post_participants_script', plugins_url( 'js/post_participants_post.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'post_participants_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'post_participants_script' );
}

add_action('init', 'PostParticipantsScripts');

//[post_participants_manage_own_events]
function ManageEventsUI( $atts ) {
	// start gathering the HTML output
	ob_start();

	// first get all events the user created
	global $wpdb, $post_participants_table_name;
	$user_id = get_current_user_id();

	// get the events the current user participates in
	$sql = $wpdb->prepare("SELECT * FROM $post_participants_table_name WHERE user_id = %d;", $user_id);
	$participations = $wpdb->get_results($sql);

	echo '<ul>';
	foreach($participations as $current) {
		// get posts the user created
		$post = get_post((int)$current->post_id);

		echo '<li>'.esc_html($post->post_title).'</li>';
	}
	echo '</ul>';

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'post_participants_list_joined_events', 'ManageEventsUI' );

//[post_participants_manage_own_events]
function ManageOwnEventsUI( $atts ) {
	// start gathering the HTML output
	ob_start();

	// first get all events the user created
	global $wpdb, $post_participants_table_name;
	$user_id = get_current_user_id();

	// get posts the user created
	$posts = get_posts( array ( 'author' => $user_id , 'category_name' => 'veranstaltungen'));
	echo "<ul>";
	foreach($posts as $currentevent) :
		echo "<li>".esc_html($currentevent->post_title);
		// fetch appropriate categories
		// - it is sufficient to fetch one of the categories and get the parent and then all childs
		$basis = get_the_category($currentevent->ID)[0]->parent;
		// - get all child of the parent category
		$categories = get_categories(array( 'child_of' => $basis ));

		// display edit button
		frontend_edit_posts_form($currentevent->ID, $categories, "&Auml;ndern", "event");

		// fetch participants
		$sql = $wpdb->prepare("SELECT * FROM $post_participants_table_name WHERE post_id = %d;", $currentevent->ID);
		$participants = $wpdb->get_results($sql);
		?><ul><?php
		foreach ($participants as $current) :

			?>
			<li><?php echo esc_html(get_user_by('id', $current->user_id)->display_name); ?></li>
			<?php

		endforeach;
		echo "</ul></li>";
	endforeach;
	echo "</ul>";

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'post_participants_manage_own_events', 'ManageOwnEventsUI' );
?>
