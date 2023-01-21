<?php
/**
 * Admin menu.
 *
 * @package comments_subscriber
 */

/**
 * Add admin options page.
 *
 * @return void
 */
function cs_admin_menu() {
	// @codingStandardsIgnoreStart
	add_options_page( __( 'Comments Subscriber', 'comments-subscriber' ), __( 'Comments Subscriber', 'comments-subscriber' ), 'manage_options', 'comments-subscriber', 'cs_options_page' );
	// @codingStandardsIgnoreEnd
}
