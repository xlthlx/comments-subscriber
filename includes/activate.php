<?php
/**
 * On activate plugin.
 *
 * @package comments_subscriber
 */

/**
 * Process subcriber data, then import subcribers into our table.
 *
 * @param array $subscriber_data The subscriber details.
 *
 * @return void
 */
function cs_process_import_subscribers( $subscriber_data ) {
	global $wpdb;

	foreach ( $subscriber_data as $key => $data ) {
		$valid = cs_valid_email( $data->email );
		if ( $valid ) {
			$data->email = $valid;// sanitize emails to be inserted.
		} else {
			unset( $subscriber_data[ $key ] );// remove invalid subscribers.
			continue;
		}

		// Get comment author name, which is missing from STC and STCR postmeta.
		$comment_author = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_author FROM {$wpdb->prefix}comments WHERE comment_author_email = %s",
				$data->email
			)
		);
		// Add the name to the array of subscriber data.
		$subscriber_data[ $key ]->name = empty( $comment_author ) ? __( 'Subscriber', 'comments-subscriber' ) : $comment_author;
	}
	// Insert subscribers into our table.
	foreach ( $subscriber_data as $data ) {
		// Skip if something is missing.
		if ( empty( $data->post_id ) || empty( $data->name ) || empty( $data->email ) ) {
			continue;
		}
		$token = md5( mt_rand() );
		// TODO: check duplicates

		// Checks for duplicates.
		$duplicate = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT email FROM {$wpdb->prefix}comment_subscriber WHERE email = %s and post_id = %d",
				$data->email,
				$data->post_id
			)
		);

		if(empty($duplicate)) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}comment_subscriber 
				( post_id, name, email, token )
				VALUES ( %d, %s, %s, %s )
			",
					array(
						$data->post_id,
						$data->name,
						$data->email,
						$token,
					)
				)
			);
		}
	}
}

/**
 * Remove spammers that were previously subscribed with Comment Notifier plugin.
 * This only runs on activation.
 */
function cs_cleanup_prior() {
	global $wpdb;

	// Empty the bin and spam.
	$wpdb->delete( $wpdb->comments, array( 'comment_approved' => 'trash' ) );
	$wpdb->delete( $wpdb->comments, array( 'comment_approved' => 'spam' ) );

	// delete every email in the comment_subscriber table that doesn’t have a corresponding comment.
	$wpdb->query(
		"DELETE FROM {$wpdb->prefix}comment_subscriber 
       WHERE email 
                 NOT IN ( SELECT comment_author_email 
                          FROM {$wpdb->comments} )" 
	);

	// Delete every email in the comment_subscriber table isn't valid.
	$comment_subscribers = $wpdb->get_col( "SELECT email FROM {$wpdb->prefix}comment_subscriber" );
	foreach ( $comment_subscribers as $email ) {
		if ( ! cs_valid_email( $email ) ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}comment_subscriber WHERE email = %s", $email )
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
	// Create table unless it exists for Comment Notifier plugin.
	$wpdb->query(
		"CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."comment_subscriber` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `post_id` int(10) unsigned NOT NULL DEFAULT 0,
		  `name` varchar(100) NOT NULL DEFAULT '',
		  `email` varchar(100) NOT NULL DEFAULT '',
		  `token` varchar(50) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `post_id_email` (`post_id`,`email`),
		  KEY `token` (`token`)
		);"
	);

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
	/* translators: 1: Comment author, 2: Post title. */
	$default_options['subject']    = sprintf( __( 'A new comment from %1$s on "%2$s"', 'comments-subscriber' ), '{author}', '{title}' );
	$default_options['thankyou']   = __( 'Your subscription has been removed. You\'ll be redirect to the home page within few seconds.', 'comments-subscriber' );
	$default_options['name']       = get_option( 'blogname' );
	$default_options['from']       = get_option( 'admin_email' );
	$default_options['checkbox']   = '1';
	$default_options['checked']    = '1';
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
		$options = array();// setting the get_option default to empty array did not work as expected.
	}

	$options = array_merge( $default_options, $options );
	update_option( 'cs_options', $options );
	// Remove spammers that were previously subscribed by Comment Notifier plugin.
	cs_cleanup_prior();
	// Import subscribers from Subscribe to Comments plugin, if any exist.
	$stc_subscribers = $wpdb->get_results(
		"SELECT LCASE(meta_value) as email, post_id 
FROM {$wpdb->prefix}postmeta 
WHERE meta_key = '_sg_subscribe-to-comments'" 
	);
	if ( $stc_subscribers ) {
		cs_process_import_subscribers( $stc_subscribers );
	}
	// Import subscribers from Subscribe to Comments Reloaded, if any active subscribers exist.
	$stcr_subscribers = $wpdb->get_results(
		"SELECT REPLACE(meta_key, '_stcr@_', '') AS email, post_id 
FROM {$wpdb->prefix}postmeta 
WHERE meta_key LIKE '\_stcr@\_%' 
  AND meta_value LIKE '%|Y'" 
	);

	if ( $stcr_subscribers ) {
		cs_process_import_subscribers( $stcr_subscribers );
	}

}
