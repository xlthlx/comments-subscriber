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
 * @param string $email User's email.
 * @param string $name User's name.
 *
 * @return false|int
 */
function cs_subscribe( $post_id, $email, $name ) {
	$type = 'subscription';

	remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

	// Check if a user is already subscribed to this post.
	$subscribed = get_comments(
		array(
			'author_email' => $email,
			'type'         => $type,
			'post__in'     => $post_id,
		)
	);

	add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

	if ( ! empty( $subscribed ) ) {
		return false;
	}

	$token = md5( wp_rand() );

	return wp_insert_comment(
		array(
			'comment_post_ID'      => $post_id,
			'comment_author_email' => $email,
			'comment_author'       => $name,
			'comment_content'      => $token,
			'comment_type'         => $type,
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
	$type = 'subscription';

	// Check if a user is already subscribed to this post.
	$subscribed = get_comments(
		array(
			'author_email' => $email,
			'type'         => $type,
			'post__in'     => $post_id,
		)
	);

	add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

	if ( ! empty( $subscribed ) ) {
		return;
	}

	// If the comment author checks the box to subscribe.
	if ( $comment_id && get_comment_meta( $comment_id, 'comment_subscribe', true ) ) {

		// The random token for unsubscription.
		$token = md5( wp_rand() );
		wp_insert_comment(
			array(
				'comment_post_ID'      => $post_id,
				'comment_author_email' => $email,
				'comment_author'       => $name,
				'comment_content'      => $token,
				'comment_type'         => $type,
			)
		);

		delete_comment_meta( $comment_id, 'comment_subscribe' );
	}

}
