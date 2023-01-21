<?php
/**
 * Uninstall.
 *
 * @package comments_subscriber
 */

/**
 * Uninstall hook.
 *
 * @return void
 */
function cs_plugin_uninstall() {
	cs_uninstall();
}

/**
 * Delete all options and 'comment_subscriber' table.
 *
 * @return void
 */
function cs_uninstall() {
	global $wpdb;

	//$options = get_option( 'cs_options' );
	//if ( ! empty( $options['delete_data'] ) ) {
		delete_option( 'cs_options' );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}comment_subscriber" );
	//}
}

