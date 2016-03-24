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
		// see if some attachments are to be removed

		// list attached images
		$attached_images = get_attached_media('image', $post_id);
		if(0 < count($attached_images)) {
			foreach ($attached_images as $current) {
				if(!in_array($current->ID, $_POST['images']))
					wp_delete_attachment($current->ID);
			}
		}
		return $post_id;
	}

	// TODO check for permissions
	// TODO check for valid post categories
	// derive post_status from chosen categories
	$post_status = 'pending';
	if('veranstaltungen' == get_category(get_category((int)$_POST['post_category'][0])->parent)->slug)
		$post_status = 'publish';

	// Create a new post
	$post = array(
		'post_status'  => $post_status,
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

function fep_render_basic_edit_fields($post_id, $categories) {
	// start gathering the HTML output
	ob_start();

	// in case we have a valid post id we fill everything we have into the form
	$post = get_post($post_id);

	// create title field
	echo '<label>Titel: <input type="text" name="title" value="'.$post->post_title.'"></label>';

	// create editor
	wp_editor($post->post_content, 'editor', array ( 'media_buttons' => false, 'quicktags' => false ) );
	\_WP_Editors::enqueue_scripts();
	print_footer_scripts();
	\_WP_Editors::editor_js();

	if(0 < count($categories)) {
		$post_categories = get_the_category($post_id);
		echo '<div id="acf_' . $acf['id'] . '" class="postbox acf_postbox ' . $acf['options']['layout'] . '">';
		echo '<h3 class="hndle"><span>Categories</span></h3>';
		echo '<div class="inside">';
		echo '<ul id="categorychecklist" class="categorychecklist">';
		foreach($categories as $current) {
			$checkbox = (in_array($current, $post_categories) ? 'checked="yes"':'');
			echo '<li id="category-'.$current->cat_ID.'"><label class="selectit"><input value="'.$current->cat_ID.'" type="checkbox" '.$checkbox.' name="post_category[]" id=in-category-'.$current->cat_ID.'"</input>'.$current->name.'</label></li>';
		}
		echo '</ul>';
		echo '</div></div>';
	}

	// list attached images
	$attached_images = get_attached_media('image', $post_id);
	if(0 < count($attached_images)) {
		foreach ($attached_images as $current) {
			$feat_image_url = wp_get_attachment_thumb_url( $current->ID );
			echo '<li><label class="selectit"><input value="'.$current->ID.'" type="checkbox" checked="yes" name="images[]" /><img src="'.$feat_image_url.'" /></label></li>';
		}
	}

	echo '<div id="acf_' . $acf['id'] . '" class="postbox acf_postbox ' . $acf['options']['layout'] . '">';
	echo '<h3 class="hndle"><span>Fileupload</span></h3>';
	echo '<div class="inside">';
	echo '<input type="file" id="my_image_upload" name="my_image_upload[]" multiple="multiple">';
	echo '</div></div>';

	// finalize gathering and return
	return ob_get_clean();
}

function FrontendEditPostForm() {
	$category_ids = $_POST['category_ids'];
	$post_id = $_POST['post_id'];
	// TODO do security checks
	// TODO do user permission check

	// include necessary styles and scripts
	acf_form_head();

	// TODO do we really want these styles here?
	$my_styles = wp_styles();
	$my_styles->do_items();
	$my_scripts = wp_scripts();
	$my_scripts->do_items();

	// cast the whole array again into an array of IDs
	foreach ($category_ids as $current) {
		$categories[] = get_category((int)$current);
	}

	$html = fep_render_basic_edit_fields($post_id, $categories);

	// TODO react to a post id other than new
	$settings = array(
		'post_id'	=> $post_id,
		'html_before_fields' => $html,
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
<input type="button" id="edit-post-<?php echo $post_id; ?>" data-categories="<?php echo json_encode($category_ids); ?>" data-post_id="<?php echo $post_id; ?>" value="<?php echo $caption; ?>" onclick="frontend_create_post_stuff(jQuery(this));"/>
<div id="edit-post-<?php echo $post_id; ?>-form"></div>
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
	foreach($posts as $current) :
		echo "<li>".$current->post_title." ";
		// fetch appropriate categories
		// - it is sufficient to fetch one of the categories and get the parent and then all childs
		$basis = get_the_category($current->ID)[0]->parent;
		// - get all child of the parent category
		$categories = get_categories(array( 'child_of' => $basis ));
		frontend_edit_posts_form($current->ID, $categories, "&Auml;ndern");
		echo "</li>";

		// TODO add edit functionality
	endforeach;
	echo "</ul>";

	// finalize gathering and return
	return ob_get_clean();
}

add_shortcode( 'pending_posts', 'ListPendingPosts' );

/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function FrontendCreatePostsScripts() {
     wp_register_script( 'frontend_create_posts_script', plugins_url( 'js/frontend_create_posts.js', __FILE__ ), array('jquery') );
     wp_localize_script( 'frontend_create_posts_script', 'data', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

     wp_enqueue_script( 'jquery' );
     wp_enqueue_script( 'frontend_create_posts_script' );
}

add_action('init', 'FrontendCreatePostsScripts');

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
