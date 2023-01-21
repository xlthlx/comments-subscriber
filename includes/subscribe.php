<?php
/**
 * Subscribe.
 *
 * @package comments_subscriber
 */

/**
 * Subscribe a user to a post.
 *
 * @param int    $post_id Post ID on which to subscribe.
 * @param string $email   User's email.
 * @param string $name    User's name.
 */
function cs_subscribe( $post_id, $email, $name ) {
	global $wpdb;

	// Check if user is already subscribed to this post.
	$subscribed = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}comment_subscriber 
                WHERE post_id=%d 
                  AND email=%s",
			$post_id,
			$email
		)
	);

	if ( $subscribed > 0 ) {
		return;
	}

	$token = md5( mt_rand() );// The random token for unsubscription.
	$wpdb->insert(
		$wpdb->prefix . 'comment_subscriber',
		array(
			'post_id'      => $post_id,
			'email'        => $email,
			'name'         => $name,
			'token'        => $token,
		)
	);
}

/**
 * Subscribe a comment author to a post after his comment has
 * been held in moderation and is finally approved.
 *
 * @param int    $post_id    Post ID on which comment was made.
 * @param string $email      Comment author's email.
 * @param string $name       Comment author's name.
 * @param int    $comment_id Comment ID.
 */
function cs_subscribe_later( $post_id, $email, $name, $comment_id ) {
	global $wpdb;

	// Check if user is already subscribed to this post.
	$subscribed = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}comment_subscriber 
			WHERE post_id=%d 
			AND email=%s",
			$post_id,
			$email
		)
	);

	if ( $subscribed > 0 ) {
		return;
	}

	// If the comment author check the box to subscribe.
	if ( $comment_id ) {
		if ( get_comment_meta( $comment_id, 'comment_subscribe', true ) ) {

			// The random token for unsubscription.
			$token = md5( mt_rand() );
			$wpdb->insert(
				$wpdb->prefix . 'comment_subscriber',
				array(
					'post_id' => $post_id,
					'email'   => $email,
					'name'    => $name,
					'token'   => $token,
				)
			);

			delete_comment_meta( $comment_id, 'comment_subscribe' );
		}
	}

}
