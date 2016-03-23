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
	// TODO derive post_status from chosen categories

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

	// in case there are upload attached, preprocess them, create appropriate media posts and attach them to the newly created post
	if ( $_FILES ) {
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$files = $_FILES["my_image_upload"];
		// wps media upload helper only works with single-file uploads.
		// thus, rebuild files array so that the wp media upload helper can handle it.
		foreach ($files['name'] as $key => $value) {
			if ($files['name'][$key]) {
				$file = array(
					'name' => $files['name'][$key],
					'type' => $files['type'][$key],
					'tmp_name' => $files['tmp_name'][$key],
					'error' => $files['error'][$key],
					'size' => $files['size'][$key]
				);
				$_FILES = array ("my_image_upload" => $file);
				// Let WordPress handle the upload.
				media_handle_upload( my_image_upload, $post_id );
			}
		}
	}

	// return the new ID
	return $post_id;
}

add_filter('acf/pre_save_post' , 'my_pre_save_post' );

function FrontendSavePostForm() {
	acf_form_head();
	die();
}

add_action('wp_ajax_frontend_save_post_form', 'FrontendSavePostForm');
add_action('wp_ajax_nopriv_frontend_save_post_form', 'FrontendSavePostForm');

function FrontendEditPostForm() {
	$category_ids = $_POST['category_ids'];
	$post_id = $_POST['post_id'];
	// TODO do security checks
	// TODO do user permission check

	// include necessary styles and scripts
	acf_form_head();

	$my_scripts = wp_scripts();
	$my_scripts->do_items();

	// cast the whole array again into an array of IDs
	foreach ($category_ids as $current) {
		$categories[] = get_category((int)$current);
	}

	// TODO react to a post id other than new
	$settings = array(
		'post_id'	=> $post_id,
		'post_title'	=> true,
		'post_content'	=> true,
		'categories'	=> $categories,
		'file_upload' 	=> true,
		'form_attributes' => array ( 'enctype' => 'multipart/form-data' ),
		'submit_value'	=> 'Create Post!'
	);


	// go and ask ACF4. They know which custom fields should be shown where.
	$settings['field_groups'] = apply_filters( 'acf/location/match_field_groups', array(), array ( 'post_category' => $category_ids ) );

	acf_form($settings);
	die();
}

/**
 * Wrapper for posting the form.
 * @param post-id, "new" if a new post should be created
 * @param categories: the categories that should be available to select from
 */
function frontend_edit_posts_form($post_id, $categories, $caption) {
	// cast the whole array again into an array of IDs
	foreach ($categories as $current) {
		$category_ids[] = $current->term_id;
	}
?>
<input type="button" id="edit-post" data-categories="<?php echo json_encode($category_ids); ?>" data-post_id="<?php echo $post_id; ?>" value="<?php echo $caption; ?>" />
<div id="edit-post-form"></div>
<script type="text/javascript" >
jQuery(document).ready(function(){
	jQuery("input#edit-post").click(function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");
		var categories = JSON.parse(jQuery(this).attr("data-categories"));

		jQuery('div#edit-post-form').load('/wp-admin/admin-ajax.php', {"action" : "frontend_edit_post_form", "post_id" : post_id, "category_ids" : categories}, function() {
			// trigger setup for all ACF fields in case there are some that need initializing
			jQuery(document).trigger('acf/setup_fields', jQuery('div#edit-post-form'));

			// hook the submit button in order to do an ajax submit
			jQuery('div#edit-post-form input#submit').click(function(e) {
					e.preventDefault();

					// the new and shiny editor does some woodoo with iframes and stuff. Hence,
					// the content you see is not in the submittable form. Hence, we have to
					// manually save its contents back to the form
					tinymce.triggerSave();

					var postData = new FormData(jQuery('div#edit-post-form form')[0]);
					postData.append("action", "frontend_save_post_form");
					jQuery.ajax({
						type: "post",
						url: '/wp-admin/admin-ajax.php',
						data: postData,
						contentType: false,
						cache: false,
						processData: false,
						success: function (returndata) {
							alert(returndata);
						}
					});
				});
			});
	});
});
</script>
<?php
}

/**
 * make available via ajax
 */
add_action('wp_ajax_frontend_edit_post_form', 'FrontendEditPostForm');
add_action('wp_ajax_nopriv_frontend_edit_post_form', 'FrontendEditPostForm');


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

/**
 * Remove buttons in TinyMCE editor. Keep it stupid simple.
 */
function myplugin_tinymce_buttons($buttons)
{
	// TODO leave any role above and including editor all options

	//Remove the format dropdown select and text color selector
	$remove = array('bold', 'strikethrough', 'blockquote', 'hr','alginleft','aligncenter','alignright','wp_more','fullscreen', 'underline','alignleft', 'alignjustify','wp_adv','forecolor','pastetext', 'charmap','wp_help');

	return array_diff($buttons,$remove);
}

add_filter('mce_buttons_2','myplugin_tinymce_buttons');
add_filter('mce_buttons','myplugin_tinymce_buttons');
?>
