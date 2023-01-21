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

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}comment_subscriber 
	WHERE id=%d 
	AND token=%s",
			$id,
			$token 
		) 
	);

}
