<?php
/*
Plugin Name: Frontend Create Posts
Description: Enables users to create content from the frontend. Uses ACF4!
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

function my_pre_save_post( $post_id ) {

	// check if this is to be a new post
	if( $post_id != 'new' )
	{
		return $post_id;
	}

	// TODO check for permissions
	// TODO check for valid post categories
	// TODO fix media upload and gallery stuff

	// Create a new post
	$post = array(
		'post_status'  => 'pending',
		'post_title'  => $_POST['title'],
		'post_content'  => $_POST['editor'],
		'post_category' => $_POST['post_category']
	);

	// insert the post
	$post_id = wp_insert_post( $post );

	// update $_POST['return']
	$_POST['return'] = add_query_arg( array('post_id' => $post_id), $_POST['return'] );

	// return the new ID
	return $post_id;
}

add_filter('acf/pre_save_post' , 'my_pre_save_post' );

//[pending_posts]
function ListPendingPosts( $atts ) {
	// start gathering the HTML output
	ob_start();

	// get all messages for the current user
	global $wpdb;
	$user_id = get_current_user_id();

	// get posts the user created
	$posts = get_posts( array ( 'author' => $user_id , 'category_name' => 'medien', 'post_status' => 'pending'));

	echo "<ul>";
	foreach($posts as $currentevent) :
		echo "<li>".$currentevent->post_title."</li>";
		// TODO add edit functionality
	endforeach;
	echo "</ul>";

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'pending_posts', 'ListPendingPosts' );
?>
