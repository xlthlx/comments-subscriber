<?php
/**
 * Comment post.
 *
 * @package comments_subscriber
 */

/**
 * Subscribe comment author and notify subscribers
 * when comment posts with approved status.
 * If comment goes to moderation, add comment meta if user subscribed.
 *
 * @param int        $comment_id The comment ID.
 * @param int|string $comment_approved 1 if the comment is approved, 0 if not, 'spam' if spam.
 */
function cs_comment_post( $comment_id, $comment_approved ) {
	$comment = get_comment( $comment_id );
	$name    = $comment->comment_author;
	$email   = strtolower( trim( $comment->comment_author_email ) );
	$post_id = $comment->comment_post_ID;

	// Only subscribe if comment is approved.
	// If comment is approved automatically, notify subscribers.
	if ( 1 === $comment_approved ) {
		cs_thank_you_message( $comment_id );
		cs_notify( $comment_id );

		// If comment author subscribed, subscribe author since the comment is automatically approved.
		if ( isset( $_REQUEST['nonce_comment'], $_POST['comment_subscribe'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce_comment'] ) ), 'nonce_comment' ) ) {
			cs_subscribe( $post_id, $email, $name );
		}
	}

	// If comment goes to moderation and if comment author subscribed, add comment meta key for pending subscription.
	if ( ( 0 === $comment_approved ) && isset( $_REQUEST['nonce_comment'], $_POST['comment_subscribe'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce_comment'] ) ), 'nonce_comment' ) ) {
		add_comment_meta( $comment_id, 'comment_subscribe', true, true );
	}

}
