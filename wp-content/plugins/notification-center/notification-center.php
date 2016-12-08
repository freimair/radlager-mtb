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
				$headers[] = 'From: Radlager Website <no-reply@radlager-mtb.at>';
				$headers[] = 'Content-Type: text/html;';
				$user = get_user_by('id', $user_id);
				wp_mail($user->user_email, $subject, $message, $headers);
				break;
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
	    echo "<li>".esc_html($currentmessage->date_time)." - ".esc_html($currentmessage->subject).": ".$currentmessage->message.' <input id="notification_center_delete_message" type="button" value="'.__('Löschen').'" onclick="NotificationCenter_DeleteMessage(jQuery(this), '.esc_attr($currentmessage->id).')"/></li>';
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
	$notification_slugs[__('sonstige Inhalte')] = array('event_participation' => __('Teilnahmeinformationen'), 'administrative' => __('Administratives'), 'newsletter' => __('Newsletter'));

	// gather contact options
	$contact_options['mail'] = __('Email'); // every user has a mail contact option
	$contact_options['pm'] = __("Persönliche Nachricht"); // every user has personal messages
	$contact_options = array_merge($contact_options, wp_get_user_contact_methods(wp_get_current_user()));

	echo '<form id="notification_center_settings"><table>';

	// init headings
	echo '<tr><th>'.__('Inhalte').'</th>';
	foreach($contact_options as $current_contact_option)
		echo '<th>'.esc_html($current_contact_option).'</th>';
	echo '</tr>';

	// print options
	foreach ($notification_slugs as $heading => $items) {
		echo '<tr><td colspan="'.esc_html(1 + count($contact_options)).'">'.esc_html($heading).'</td></tr>';
		foreach($items as $key => $value) {
			echo '<tr><td>'.esc_html($value).'</td>';
			foreach($contact_options as $current => $bla) {
				$checked = (NotificationCenter_IsNotifyUser(get_current_user_id(), $key, $current) ? 'checked="checked"' : '');
				echo '<td><input type="checkbox" name="'.esc_attr($key).'['.esc_attr($current).']" '.$checked.' /></td>';
			}
			echo '</tr>';
		}
	}
	echo '</table>';

	echo '<input type="submit" value="'.__('Save').'"/>';
	echo '<div class="ajax_spinner" style="display: none"><img src="'.get_site_url().'/wp-content/themes/radlager16/loading.gif"></div></div><div class="ajax_success" style="display: none">'.__("Gespeichert!").'</div><div class="ajax_error" style="display: none">'.__("Ein fehler ist aufgetreten!").'</div>';
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

var_dump($input);

	// save new config
	foreach($input as $current_hook => $values)
		foreach($values as $current_contact_method => $donotusethisbrain) 
			$wpdb->query($wpdb->prepare("INSERT INTO $notification_center_settings_table_name (user_id, hook, meta_key) VALUES (%d,%s,%s);", array(get_current_user_id(), $current_hook, $current_contact_method)));

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

/**
 * Notification Hook for informing users on freshly published media posts.
 */
function NotifyOnMedia($post, $category_slugs) {
	$post_title = $post->post_title;
	$post_url = site_url()."/index.php?post-".$post->ID;
	$img_url = get_the_post_thumbnail_url($post->ID);
	$teaser_text = substr($post->post_content, 0, strlen($post->post_content) > 300 ? 300 : strlen($post->post_content));

	$subject = "Neuer Bericht: $post_title";

	$message = NotificationCenterFillTemplate('new_post', array('TITLE' => $post_title, 'IMG' => $img_url, 'TEASER' => $teaser_text, 'URL' => $post_url));

	NotificationCenter_NotifyUsers($category_slugs, $subject, $message);
}

/**
 * Notification Hook for informing users on newsletters.
 */
function NotifyOnNewsletter($post, $category_slugs) {
	$subject = $post->post_title;
	$message = NotificationCenterFillTemplate('newsletter', array('CONTENT' => $post->post_content));

	NotificationCenter_NotifyUsers($category_slugs, $subject, $message);
}

/**
 * Notification Hook for informing users on events.
 */
function NotifyOnEvent($post, $category_slugs) {
	$post_title = $post->post_title;
	$post_url = site_url()."/index.php/veranstaltungen?post-".$post->ID;
	$img_url = get_the_post_thumbnail_url($post->ID);
	$teaser_text = substr($post->post_content, 0, strlen($post->post_content) > 300 ? 300 : strlen($post->post_content));

	$date = get_field('startdatum', $post->ID);
	$location = maybe_unserialize(get_field('ort', $post->ID)['address']);

	$categories = get_the_category($post->ID);
	foreach($categories as $current) {
		$tags[] = $current->cat_name;
	}

	$subject = "Neue Veranstaltung: ".$post->post_title;
	$message = NotificationCenterFillTemplate('event', array('TITLE' => $post_title, 'IMG' => $img_url, 'TEASER' => $teaser_text, 'URL' => $post_url, 'DATE' => $date, 'LOCATION' => $location, 'TAGS' => implode(", ", $tags)));

	NotificationCenter_NotifyUsers($category_slugs, $subject, $message);
}

/**
 * Hook for any publish action. Used as a duplexer point.
 */
function NotificationCenterPublishPostHook($post_id, $post) {
	// gather categories so that we can decide which notification to trigger
	$categories = get_the_category($post->ID);

	foreach($categories as $current) {
		$category_slugs[] = $current->slug;

		while(null != $current->parent) {
			$current = get_category($current->parent);
			$category_slugs[] = $current->slug;
		}
	}

	$category_slugs = array_unique($category_slugs);

	if(in_array('media', $category_slugs) || in_array('trailbau', $category_slugs)) {
		NotifyOnMedia($post, $category_slugs);
	} else if(in_array('newsletter', $category_slugs)) {
		NotifyOnNewsletter($post, $category_slugs);
	} else if(in_array('veranstaltungen', $category_slugs)) {
		NotifyOnEvent($post, $category_slugs);
	}
}
add_action(  'publish_post',  'NotificationCenterPublishPostHook', 10, 3 );

/**
 * Notification Hook for notifying editors on new pending posts.
 */
function NotificationCenterPendingPostHook($post_id, $post) {
	$editors = get_users(array('role' => 'editor'));

	$subject = "Ein Beitrag wartet auf Freigabe";
	$message = NotificationCenterFillTemplate('notify_editor', array('URL' => admin_url( 'edit.php' )));

	foreach($editors as $current)
		NotificationCenter_NotifyUser(array('administrative'), $current->ID, $subject, $message);
}

add_action( 'pending_post', 'NotificationCenterPendingPostHook', 10, 2);

function NotificationCenterFillTemplate($template, $values) {
	$notification_templates = array('notify_editor' => '
<p>Ein neuer Beitrag wartet auf Freigabe!</p>
<p><a href="%URL%">Hier</a> gehts direkt zur Liste.</p>
<p>Danke!</p>
<p>Deine Radlager-Mtb Website</p>
');
	$notification_templates['new_post'] = '
<p>Radlager-Mtb hat einen neuen Bericht für dich der dich interessieren könnte!</p>
<h1>%TITLE%</h1>
<p><img src="%IMG%" width="250px" style="float:left; margin-right:10px;">%TEASER%... <a href="%URL%">weiterlesen</a></p>
<p>Viel Spass beim Lesen!</p>
<p>Deine Radlager-Mtb Website</p>
';
	$notification_templates['newsletter'] = '
%CONTENT%
';
	$notification_templates['event'] = '
<p>Radlager-Mtb hat eine neue Veranstaltung die dich interessieren könnte!</p>
<h1>%TITLE%</h1>
<p><img src="%IMG%" width="250px" style="float:left; margin-right:10px;">%TEASER%... <a href="%URL%">weiterlesen</a></p>
<p><strong>Wann:</strong> %DATE%<br />
<strong>Wo:</strong> %LOCATION%<br />
<strong>Tags:</strong> %TAGS%</p>
<p><a href="%URL%">Hier</a> gehts zu allen Details und zur Anmeldung.</p>
<p>Deine Radlager-Mtb Website</p>
';
	$notification_templates['comment'] = '
<p>Eine Veranstaltung zu der du angemeldet bist wurde kommentiert!</p>
<p><strong>Titel:</strong> %TITLE%<br />
<strong>Wann:</strong> %DATE%<br />
<strong>Wo:</strong> %LOCATION%<br />
<strong>Tags:</strong> %TAGS%</p>
<ul>%COMMENTS%</ul>
<p><a href="%URL%">Hier</a> gehts zu allen Details.</p>
<p>Deine Radlager-Mtb Website</p>
';
	$notification_templates['eventanmeldung'] = '
<p>Du hast dich zu einer Veranstaltung <strong>angemeldet</strong>!</p>
<p><strong>Titel:</strong> %TITLE%<br />
<strong>Wann:</strong> %DATE%<br />
<strong>Wo:</strong> %LOCATION%<br />
<strong>Tags:</strong> %TAGS%</p>
<p><a href="%URL%">Hier</a> gehts zu allen Details.</p>
<p>Deine Radlager-Mtb Website</p>
';
	$notification_templates['eventabmeldung'] = '
<p>Du hast dich oder wurdest von folgender Veranstaltung <strong>abgemeldet</strong>!</p>
<p><strong>Titel:</strong> %TITLE%<br />
<strong>Wann:</strong> %DATE%<br />
<strong>Wo:</strong> %LOCATION%<br />
<strong>Tags:</strong> %TAGS%</p>
<p><a href="%URL%">Hier</a> gehts zu allen Details.</p>
<p>Deine Radlager-Mtb Website</p>
';
	$notification_templates['membership_reminder'] = '
<p>Hallo %FIRSTNAME%!</p>
<p>Laut unseren Aufzeichnung hast du den Mitgliedsbeitrag für das kommende Jahr noch nicht bezahlt.</p>
<p>Weitere Infos und was genau zu tun ist findest du in deinem <a href="'.site_url("index.php/profil").'">Profil</a>.</p>
<p>Sobald du bezahlt hast verschwindet auch diese Erinnerung :).</p>
<p>Wir freuen uns schon dich nächstes Jahr wieder als Mitglied begrüßen zu dürfen!</p>
<p>Deine Radlager Team</p>
';
	$notification_templates['membership_leave'] = '
<p>Hallo %FIRSTNAME%!</p>
<p>Es tut uns leid, dass wir dich als Vereinsmitglied verlieren! Schade, dass wir dich wohl zu wenig interessante Themen, Projekte, Beiträge, und Community bieten konnten.</p>
<p>Aber wir lernen gerne dazu! Wenn du ein paar Minuten Zeit hast, magst du uns ein kurzes Feedback an <a href="mailto:office@radlager-mtb.at?subject=Feedback">die Office-Adresse</a> mailen?</p>
<p>Wir haben deinen Benutzeraccount zurückgestuft und du wirst keine Mails mehr von uns bekommen. Auch wurden unsere Partner informiert, dass du nicht mehr ein Mitglied des Vereins bist. Sollte das alles ein Versehen gewesen sein kannst du dich jederzeit einloggen, bezahlen, und weiter gehts.</p>
<p>Wir wünschen dir noch viele wertvolle Erlebnisse mit dem Bike! Ride on!</p>
<p>Deine Radlager Team</p>
';

	$filled = $notification_templates[$template];
	foreach($values as $key => $value)
		$filled = preg_replace("/%$key%/", $value, $filled);

	// add some general information to the foot of each message
	$filled .= '
<p style="font-size: smaller;">Bitte beachte, dass dies eine automatisch generierte Nachricht ist. Nachrichten an diese eMail Adresse werden <strong>nicht</strong> gelesen.</p>
<p style="font-size: smaller;">Wenn dir diese Nachrichten schon auf die Nerven gehen kannst du in deinem <a href="'.site_url("index.php/profil").'">Profil</a> deine persönlichen Benachrichtungseinstellungen ändern!</p>';

	return $filled;
}

?>
