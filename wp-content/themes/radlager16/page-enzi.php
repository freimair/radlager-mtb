<?php
/**
    Template Name: Enzi
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

wp_enqueue_style( 'enzi', get_template_directory_uri() . '/enzi.css', array( 'twentysixteen-style' ), '20160630' );

	get_header("enzi");
?>
<div id="primary" class="content-area">
<?php
$allow_reporting = false;
	include('page_commons.php');
 ?>
