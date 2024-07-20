<?php
/**
 * On activate plugin.
 *
 * @package comments_subscriber
 */

/**
 * Process subscriber data, then import subscribers into our table.
 *
 * @param array $subscriber_data The subscriber details.
 *
 * @return void
 */
function cs_process_import_subscribers( $subscriber_data ) {

	foreach ( $subscriber_data as $key => $data ) {
		$valid = cs_valid_email( $data->email );

		if ( $valid ) {
			$data->email = $valid;
		} else {
			unset( $subscriber_data[ $key ] );
			continue;
		}

		remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

		// Get comment author name.
		$comment_author = get_comments(
			array(
				'comment_author_email' => $data->email,
			)
		);

		add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

		// Add the name to the array of subscriber data.
		$subscriber_data[ $key ]->name = empty( $comment_author ) ? __( 'Subscriber', 'comments-subscriber' ) : $comment_author[0]->comment_author;
	}

	foreach ( $subscriber_data as $data ) {

		if ( empty( $data->post_id ) || empty( $data->name ) || empty( $data->email ) ) {
			continue;
		}
		$token = md5( wp_rand() );

		remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

		// Checks for duplicates.
		$duplicate = get_comments(
			array(
				'comment_author_email' => $data->email,
				'type'                 => 'subscription',
				'comment_post_ID'      => $data->post_id,
			)
		);

		add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

		$type = 'subscription';

		if ( empty( $duplicate ) ) {

			wp_insert_comment(
				array(
					'comment_post_ID'      => $data->post_id,
					'comment_author_email' => $data->email,
					'comment_author'       => $data->name,
					'comment_content'      => $token,
					'comment_type'         => $type,
				)
			);
		}
	}
}

/**
 * Activation hook.
 *
 * @return void
 */
function cs_plugin_activate() {
	cs_activate();
}

/**
 * Upon activation, set up the database table, default settings,
 * and migrate subscribers from other comment subscriber plugins.
 *
 * @return void
 */
function cs_activate() {
	global $wpdb;

	$default_options['message'] =
		/* translators: 1: Subscriber name. */
		sprintf( __( 'Hi %s,', 'comments-subscriber' ), '{name}' ) .
		"\n\n" .
		/* translators: 1: Comment author, 2: Post title. */
		sprintf( __( '%1$s has just written a new comment on "%2$s". Here is an excerpt:', 'comments-subscriber' ), '{author}', '{title}' ) .
		"\n\n" .
		'{content}' .
		"\n\n" .
		/* translators: 1: Comment url. */
		sprintf( __( 'To read more, <a href="%s">click here</a>.', 'comments-subscriber' ), '{comment_link}' ) .
		"\n\n" .
		__( 'Bye', 'comments-subscriber' ) .
		"\n\n" .
		/* translators: 1: Unsubscribe url. */
		sprintf( __( 'To unsubscribe from this notification service, <a href="%s">click here</a>.', 'comments-subscriber' ), '{unsubscribe}' );

	$default_options['label'] = __( 'Notify me when new comments are added.', 'comments-subscriber' );
	$default_options['test']  = get_option( 'admin_email' );
	/* translators: 1: Comment author, 2: Post title. */
	$default_options['subject']    = sprintf( __( 'A new comment from %1$s on "%2$s"', 'comments-subscriber' ), '{author}', '{title}' );
	$default_options['thankyou']   = __( 'Your subscription has been removed.', 'comments-subscriber' ) . "\n\n" .
	__( 'You\'ll be redirected to the home page within a few seconds.', 'comments-subscriber' );
	$default_options['name']       = get_option( 'blogname' );
	$default_options['from']       = get_option( 'admin_email' );
	$default_options['ty_subject'] = __( 'Thank you for your first comment', 'comments-subscriber' );
	$default_options['ty_message'] =
		/* translators: 1: Subscriber name. */
		sprintf( __( 'Hi %s,', 'comments-subscriber' ), '{author}' ) .
		"\n\n" .
		__( 'I received and published your first comment on my blog on the article:', 'comments-subscriber' ) .
		"\n\n" .
		'<a href="{link}">{title}</a>' .
		"\n\n" .
		__( 'Have a nice day!', 'comments-subscriber' );

	$options = get_option( 'cs_options' );
	if ( ! $options ) {
		$options = array();
	}

	$options = array_merge( $default_options, $options );
	update_option( 'cs_options', $options );

	// @codingStandardsIgnoreStart

	// Import subscribers from Subscribe to Comments plugin if any exist.
	$stc_subscribers = $wpdb->get_results(
		"SELECT LCASE(meta_value) as email, post_id
FROM {$wpdb->prefix}postmeta
WHERE meta_key = '_sg_subscribe-to-comments'"
	);
	if ( $stc_subscribers ) {
		cs_process_import_subscribers( $stc_subscribers );
	}

	// Import subscribers from Subscribe to Comments Reloaded if any active subscribers exist.
	$stcr_subscribers = $wpdb->get_results(
		"SELECT REPLACE(meta_key, '_stcr@_', '') AS email, post_id
FROM {$wpdb->prefix}postmeta
WHERE meta_key LIKE '\_stcr@\_%'
  AND meta_value LIKE '%|Y'"
	);

	// @codingStandardsIgnoreEnd

	if ( $stcr_subscribers ) {
		cs_process_import_subscribers( $stcr_subscribers );
	}

}
