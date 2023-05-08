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
 * Delete all options.
 *
 * @return void
 */
function cs_uninstall() {
	delete_option( 'cs_options' );
}

