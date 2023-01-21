<?php
/**
 * Admin script.
 *
 * @package comments_subscriber
 */

/**
 * Enqueue admin script.
 *
 * @return void
 */
function cs_options_enqueue_script() {
	wp_register_script( 'cs-admin', plugins_url( 'admin/js/admin.min.js', __FILE__ ), array(), '1.0.0' );

	if ( 'comments-subscriber' === get_current_screen()->id ) {
		wp_enqueue_script( 'cs-admin' );
	}

}
add_action( 'admin_enqueue_scripts', 'cs_options_enqueue_script' );
