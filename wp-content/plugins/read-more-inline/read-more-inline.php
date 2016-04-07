<?php
/*
Plugin Name: Read More Without Reload
Description: Allow users to toggle content following the 'more' link on a page.
Author: Stephen Gray
Author URI: http://www.pobo.org
Version: 0.3
*/

function pobo_rmi_js()
{

    // Register the script like this for a plugin:
    wp_register_script( 'custom-script', plugins_url( 'js/pobo_rmi.js', __FILE__ ), array(), 1, TRUE );

    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'custom-script' );
}
add_action( 'init', 'pobo_rmi_js' );

/**
 * This filter gets the content after the 'more' link
 * and places it in a div with a class of 'readmoreinline',
 * which the javascript a) hides and b) adds a toggle to,
 * so clicking the 'more' link alternately shows and hides the extra content.
 * <br>
 * @param $link
 * @return $link with new class
 */
function pobo_rmi_morelink_filter($link){
    global $post;


    $my_id = $post->ID;

    $link = str_replace("<a ", '<a data-post_id="'.$my_id.'" ', $link);

        $post_object= get_post($my_id);
        $content = $post_object->post_content;
        // grab only the stuff after 'more'
        $debris = explode('<!--more-->', $content);

    $link.='</p><div class="readmoreinline" id="readmoreinline'.$my_id.'" style="display:none">'.$debris[1].'</div>';

    return $link;
}

add_filter('the_content_more_link', 'pobo_rmi_morelink_filter', 999);

?>
