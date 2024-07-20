<?php
/**
 * Plugin Name: Comments Subscriber
 * Plugin URI: https://github.com/xlthlx/comment-subscriber
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

/**
 * Includes all files from inc directory.
 */
foreach ( glob( plugin_dir_path( __FILE__ ) . 'includes/*.php' ) as $file ) {
	include_once $file;
}

/**
 * Includes all files from admin directory.
 */
if ( is_admin() ) {
	foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
		include_once $file;
	}
}

add_action( 'init', 'cs_init' );
add_action( 'init', 'cs_load_textdomain' );
add_action( 'admin_init', 'cs_plugin_settings' );

register_activation_hook( __FILE__, 'cs_plugin_activate' );
register_uninstall_hook( __FILE__, 'cs_plugin_uninstall' );

/**
 * Initialize the plugin.
 *
 * @return void
 */
function cs_init() {
	$options = get_option( 'cs_options' );

	if ( is_admin() ) {
		add_action( 'admin_menu', 'cs_admin_menu' );
	} else {
		cs_unsubscribe();
	}

	// If theme_compat is enabled, use the old filter to add checkbox after the submit button,
	// otherwise use our standard filter.
	if ( empty( $options['theme_compat'] ) ) {
		add_filter( 'comment_form_submit_field', 'cs_comment_form_submit_field', 9999 );
	} else {
		add_action( 'comment_form', 'cs_comment_form', 9999 );
	}

	add_action( 'wp_set_comment_status', 'cs_set_comment_status', 10, 2 );
	add_action( 'comment_post', 'cs_comment_post', 10, 2 );
	add_filter( 'comments_pre_query', 'hide_subscriptions_from_comments', 10, 2 );
}

/**
 * Load plugin textdomain.
 *
 * @return void
 */
function cs_load_textdomain() {
	load_plugin_textdomain( 'comments-subscriber', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Attach settings in WordPress Plugins list.
 *
 * @return void
 */
function cs_plugin_settings() {
	add_action( 'plugin_action_links', 'cs_add_plugin_settings', 10, 4 );
}

/**
 * Add settings link to plugin actions.
 *
 * @param array  $plugin_actions The plugin actions.
 * @param string $plugin_file The plugin file path.
 *
 * @return array
 */
function cs_add_plugin_settings( $plugin_actions, $plugin_file ) {
	$new_actions = array();

	if ( 'comments-subscriber/comments-subscriber.php' === $plugin_file ) {
		$new_actions['cs_settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=comments-subscriber' ) ) . '">' . __( 'Settings', 'comments-subscriber' ) . '</a>';
	}

	return array_merge( $new_actions, $plugin_actions );
}

/**
 * Hide the subscription type comments from queries.
 *
 * @param array|int|null   $comment_data The comments data.
 * @param WP_Comment_Query $query The comments query.
 *
 * @return void
 */
function hide_subscriptions_from_comments( $comment_data, $query ) {
	$query->query_vars['type__not_in'] = 'subscription';
}
