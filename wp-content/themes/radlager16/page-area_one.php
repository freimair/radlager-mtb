<?php
/**
    Template Name: Area One
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

wp_enqueue_style( 'areaone', get_template_directory_uri() . '/areaone.css', array( 'twentysixteen-style' ), '20160630' );

	get_header("area_one");
?>
<div id="primary" class="content-area">
<?php
$allow_reporting = true;
	include('page_commons.php');
 ?>
