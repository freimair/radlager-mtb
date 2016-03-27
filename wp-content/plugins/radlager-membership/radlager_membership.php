<?php
/*
Plugin Name: Radlager Membership
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
?>
