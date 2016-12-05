<?php
/*
Plugin Name: Radlager Membership
Depends: Notification Center
Description: Implements the life-cycle logic for being a Radlager Club Member.
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

function radlager_membership_user_register_hook() {
	update_usermeta( get_current_user_id(), 'radlager_membership_fee_status', 'open' );
}
add_action( 'user_register', 'radlager_membership_user_register_hook');

//[radlager_membership_register]
function RadlagerMembershipRegister( $atts ) {
	// start gathering the HTML output
	ob_start();
//	echo '<iframe src="'.wp_login_url().'" />';
	wp_register();

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_register', 'RadlagerMembershipRegister' );

//[radlager_membership_login]
function RadlagerMembershipLogin( $atts ) {
	return wp_login_form(array ( 'redirect' => site_url(), 'value_remember' => true, 'echo' => false ));
}

add_shortcode( 'radlager_membership_login', 'RadlagerMembershipLogin' );

function printNameIfAvailable() {
  $current_user = wp_get_current_user();
  if ( is_user_logged_in() ) {
    return esc_html($current_user->first_name) . " " . esc_html($current_user->last_name) . " (" . esc_html($current_user->user_login) . ")";
  } else {
    return "Vorname Nachname (Benutzername)";
  }
}

//[radlager_membership_status]
function RadlagerMembershipStatus( $atts ) {
	// check the payment status
	$payment_status = get_user_meta(get_current_user_id(), 'radlager_membership_fee_status', true);
	$show_button = true;


	// start gathering the HTML output
	ob_start();

	if('open' !== $payment_status) {
		echo '<div class="payment_status">'.__("Du hast bereits bezahlt. Danke!").'</div>';
		$show_button = false;
	}

	if($show_button) :
?>

<p>Verwendungszweck: <strong>"<?php echo esc_html(date("Y", strtotime('+61 days')));?> <?php echo printNameIfAvailable(); ?>"</strong></p>

<input type="button" id="radlager_membership_payment_claim" value="<?php _e("Habe bezahlt!"); ?>" />


<?php
	endif;

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_status', 'RadlagerMembershipStatus' );

function RadlagerMembershipConfirm() {
	if(!is_user_logged_in() || !current_user_can('edit_users'))
		die();

	update_usermeta( $_POST['userid'], 'radlager_membership_fee_status', 'confirmed' );
}

add_action('wp_ajax_radlager_membership_confirm', 'RadlagerMembershipConfirm');

function RadlagerMembershipClaim() {
	if(!is_user_logged_in())
		die();

	if('confirmed' === get_user_meta(get_current_user_id(), 'radlager_membership_fee_status', true))
		die();

	update_usermeta( get_current_user_id(), 'radlager_membership_fee_status', 'claim' );
}

add_action('wp_ajax_radlager_membership_claim', 'RadlagerMembershipClaim');
add_action('wp_ajax_nopriv_radlager_membership_claim', 'RadlagerMembershipClaim');

/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function RadlagerMembershipScripts() {
     wp_register_script( 'radlager_membership_script', plugins_url( 'js/radlager_membership.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'radlager_membership_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'radlager_membership_script' );
}

add_action('init', 'RadlagerMembershipScripts');


// add fee status column to user list
add_filter('manage_users_columns', 'radlager_membership_add_columns');
function radlager_membership_add_columns($columns) {
    $columns['fee_status'] = 'fee status';
    return $columns;
}

add_action('manage_users_custom_column',  'radlager_membership_show_column_content', 10, 3);
function radlager_membership_show_column_content($value, $column_name, $user_id) {
	if ( 'fee_status' == $column_name ) {
		$value = get_user_meta($user_id, 'radlager_membership_fee_status', true);
		if(empty($value)) {
			update_usermeta( $user_id, 'radlager_membership_fee_status', 'open' );
			$value = 'open';
		}
		if('confirmed' !== $value && current_user_can('edit_users'))
			$value .= ' <input type="button" value="'.__('confirm').'" onclick="radlager_membership_confirm(jQuery(this),'.$user_id.')" />';
	}
    return $value;
}


// WP seems to haven an issue here. Queries for metadata and other data are connected with a hardcoded AND which is bs.
/*
function pre_get_users( $user_query ){
	$merken = $user_query->query_vars;
	$merken['meta_query'] = array('relation' => 'AND', array('key' => 'radlager_membership_fee_status', 'value' => substr($merken['search'], 1, -1), 'compare' => 'LIKE'), array('key' => 'radlager_membership_fee_status', 'compare' => 'EXISTS'));

	// temporarly disable filter to avoid infinite loop
	remove_filter( 'pre_get_users', 'pre_get_users');
	$user_query = new WP_User_Query($merken);
	add_filter( 'pre_get_users', 'pre_get_users');

	return $user_query;
}
add_filter( 'pre_get_users', 'pre_get_users');
*/

function pre_user_query( $user_query ){
	global $wpdb;

	// workaround for static AND when working with metadata query.
	if($user_query->query_vars['search']) {
		$user_query->query_from .= ' INNER JOIN '.$wpdb->prefix.'usermeta ON ( '.$wpdb->prefix.'users.ID = '.$wpdb->prefix.'usermeta.user_id ) ';
		$user_query->query_where .= $wpdb->prepare(" OR ( ".$wpdb->prefix."usermeta.meta_key = 'radlager_membership_fee_status' AND CAST(".$wpdb->prefix."usermeta.meta_value AS CHAR) LIKE %s )", "%".str_replace('*', '%', $user_query->query_vars['search']));
	}

	return $user_query;
}
add_filter( 'pre_user_query', 'pre_user_query');

/*function add_query_vars_filter( $vars ){
  $vars[] = "fee_status";
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );
*/

function getTransitions() {
	return array('11-01' => '01-15', '01-15' => '02-01', '02-01' => '02-14', '02-14' => '03-01', '03-01' => '11-01');
}

// setup and maintain cron job
function radlager_membership_notify_users($state) {
	$transitions = getTransitions();
	$action = array('11-01' => 'reset', '01-15' => 'reminder', '02-01' => 'reminder', '02-14' => 'reminder', '03-01' => 'kick');

	// find start state if we just got fired up
	if(empty($state)) {
		krsort($transitions);
		foreach ($transitions as $key => $value) {
			$state = $key;
			if(date("m-d") >= $key)
				break;
		}
	}

	// execute task
	switch($action[$state]) {
		case 'reset':
			foreach (get_users(array('who' => 'authors')) as $current) {
				update_user_meta($current->ID, 'radlager_membership_fee_status', 'open');

				$username = get_user_meta($current->ID, 'first_name', true);
				if(empty($username))
					$username = $current->user_login;

				$subject = 'Der Mitgliedsbeitrag für '.date("Y", strtotime("next year")).' ist fällig';
				$message = NotificationCenterFillTemplate('membership_reminder', array('FIRSTNAME' => $username));
				NotificationCenter_NotifyUser(array('administrative'), $current->ID, $subject, $message);
			}
			break;
		case 'reminder':
			foreach (get_users(array('who' => 'authors')) as $current) {
				$usermeta = get_user_meta($user_id, 'radlager_membership_fee_status', true);
				if(!empty($usermeta)) {

					$username = get_user_meta($current->ID, 'first_name', true);
					if(empty($username))
						$username = $current->user_login;

					$subject = 'Der Mitgliedsbeitrag für '.date("Y", strtotime("next year")).' ist fällig';
					$message = NotificationCenterFillTemplate('membership_reminder', array('FIRSTNAME' => $username));
					NotificationCenter_NotifyUser(array('administrative'), $current->ID, $subject, $message);
				}
			}
			break;
		case 'kick':
			foreach (get_users(array('who' => 'authors')) as $current) {
				$usermeta = get_user_meta($user_id, 'radlager_membership_fee_status', true);
				if(!empty($usermeta) && !in_array('administrator',$current->roles)) {

					$username = get_user_meta($current->ID, 'first_name', true);
					if(empty($username))
						$username = $current->user_login;

					$current->set_role('subscriber');
					$subject = 'Der Mitgliedsbeitrag für '.date("Y", strtotime("next year")).' ist fällig';
					$message = NotificationCenterFillTemplate('membership_leave', array('FIRSTNAME' => $username));
					NotificationCenter_NotifyUser(array('administrative'), $current->ID, $subject, $message);
				}
			}
			break;
	}

	// calculate next execution timestamp
	$next_date = strtotime(date("Y").'-'.$transitions[$state]);
	if(time() > $next_date)
		$next_date = strtotime(date("Y", strtotime("next year")).'-'.$transitions[$state]);

	// schedule next execution
	wp_schedule_single_event($next_date, 'radlager_membership_notify_users', $transitions[$state]);
}
add_action( 'radlager_membership_notify_users','radlager_membership_notify_users' );

// start cron-job on plugin activation
register_activation_hook(__FILE__, 'radlager_membership_notify_users');

// stop cron-job on plugin deactivation
function radlager_membership_deactivate_cron() {
	$transitions = getTransitions();

	foreach($transitions as $key => $value) {
		$nextrun = wp_next_scheduled('radlager_membership_notify_users', $key);
		wp_unschedule_event($nextrun, 'radlager_membership_notify_users', $key);
	}
}
register_deactivation_hook(__FILE__, 'radlager_membership_deactivate_cron');

/**
 * Member list utils
 */
// [radlager_membership_active_member_count]
function rl_get_active_member_count() {
	global $wpdb;
	return $wpdb->get_var('SELECT COUNT(meta_value) FROM '.$wpdb->usermeta.' WHERE '.$wpdb->usermeta.'.meta_key = \''.$wpdb->prefix.'capabilities\' AND  '.$wpdb->usermeta.'.meta_value NOT LIKE \'%subscriber%\'');
}

add_shortcode( 'radlager_membership_active_member_count', 'rl_get_active_member_count' );

// [radlager_membership_memberlist]
function rl_print_member_list() {
	global $wpdb;

	$current_user = wp_get_current_user();
	$see_list = (0 == strcmp($current_user->roles[0], "contributor")) || (0 == strcmp($current_user->roles[0], "administrator"));

	if(!$see_list)
		return "";

	// start gathering the HTML output
	ob_start();

	// we suddenly do want everyone to see the members
	//if(0 == strcmp($current_user->roles[0], "subscriber"))
	//	return;

	// we do want sorting for display name everytime
	//if(0 <> strcmp($current_user->roles[0], "subscriber")) {
	//	$wp_user_search = $wpdb->get_results("SELECT * FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE $wpdb->usermeta.meta_key = 'last_name' ORDER BY $wpdb->usermeta.meta_value ASC");
	//} else {
		$wp_user_search = $wpdb->get_results("SELECT * FROM $wpdb->users INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE $wpdb->usermeta.meta_key = '".$wpdb->prefix."capabilities' AND $wpdb->usermeta.meta_value NOT LIKE '%subscriber%' ORDER BY $wpdb->users.display_name ASC");
	//}


	foreach ( $wp_user_search as $userid ) {
		$user = new WP_User($userid->ID);
		print("<div class=\"usertile\">");
		userphoto_thumbnail($user, "<span class=\"usertile-image\">", "</span>");
		print("<div class=\"usertile-content\"><b>");

		print(get_the_author_meta("display_name", $userid->ID)."</b>");
		if($see_list) {
			print("<br />".get_the_author_meta("first_name", $userid->ID)." ".get_the_author_meta("last_name", $userid->ID));
		}

		foreach(array("function", "bikes", "home") as $current) {
			if("" <> get_the_author_meta($current, $userid->ID))
				print("<br />".get_the_author_meta($current, $userid->ID));
		}

		if($see_list) {
			foreach(array("phone") as $current) {
				if("" <> get_the_author_meta($current, $userid->ID))
					print("<br />".get_the_author_meta($current, $userid->ID));
			}
			$email = get_the_author_meta("user_email", $userid->ID);
			if("" <> $email)
				print("<br /><a href=\"mailto:$email\">$email</a>");
		}

		print("</div>");
		print("</div>");
	}
	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'radlager_membership_memberlist', 'rl_print_member_list' );

function rl_member_list_css() {
	$current_user = wp_get_current_user();
	$see_list = (0 == strcmp($current_user->roles[0], "contributor")) || (0 == strcmp($current_user->roles[0], "administrator"));

	echo "
	<style type='text/css'>
	h2 {
		
	}
	.usertile {
		display: table-row;
		margin: 10px 0;
		width: ";
	if($see_list) {
		echo "49%;";
	} else {
		echo "33%;";
	}
	echo "
		float: left;
	}
	.usertile-image {
		display: table-cell;
		width: 100px;
		height: 90px;
		text-align: center;
		vertical-align: top;
	}
	.usertile-content {
		display: table-cell;
		vertical-align: top;
		height: 130px;
	}
	</style>
	";
}

add_action( 'wp_head', 'rl_member_list_css' );


?>
