<?php
/**
 * Plugin Name: Comments Subscriber
 * Plugin URI: https://github.com/xlthlx/comments-subscriber
 * Description: Plugin to let visitors subscribe to comments and get email notifications.
 * Version: 1.0.0
 * Author: xlthlx
 * Author URI: https://piccioni.london
 * License: GPL3
 * Text Domain: comments-subscriber
 * Domain Path: languages
 *
 * @package comments_subscriber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Includes plugin init.
 */
require_once 'class-comments-subscriber-init.php';

/**
 * Includes all files from includes directory.
 */
foreach ( glob( plugin_dir_path( __FILE__ ) . 'includes/*.php' ) as $file ) {
	require_once $file;
}

/**
 * Includes all files from admin directory.
 */
if ( is_admin() ) {
	foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
		require_once $file;
	}
}

register_activation_hook( __FILE__, array( \Comments\Subscriber\Main_Hooks::class, 'plugin_activate' ) );
register_uninstall_hook( __FILE__, array( \Comments\Subscriber\Main_Hooks::class, 'plugin_uninstall' ) );

