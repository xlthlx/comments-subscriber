<?php
/**
 * Unsubscribe.
 *
 * @package comments_subscriber
 */

/**
 * Removes a subscription.
 *
 * @param int    $id Subscription ID.
 * @param string $token Subscription Token.
 *
 * @return void
 */
function cs_unsubscribe( $id, $token ) {
	global $wpdb;
	$type = 'subscription';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}comments 
			WHERE comment_type = %s
			AND comment_post_ID=%d 
			AND comment_content=%s",
			$type,
			$id,
			$token
		)
	);

}
