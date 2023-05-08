<?php
/**
 * Set comment status.
 *
 * @package comments_subscriber
 */

/**
 * Subscribe and notify after moderation.
 *
 * Subscribe user when their comment is finally approved
 * after being held in moderation. Notify other subscribers
 * of this comment.
 * Called when a comment is changed of status, as when approving
 * a comment that has been held in moderation.
 *
 * @param int    $comment_id The comment ID.
 * @param string $status     New comment status, either 'hold', 'approve', 'spam', or 'trash'.
 */
function cs_set_comment_status( $comment_id, $status ) {
	// Get original comment info.
	$comment = get_comment( $comment_id );
	if ( ! $comment ) {
		return;
	}
	$post_id = $comment->comment_post_ID;
	$email   = strtolower( trim( $comment->comment_author_email ) );
	$name    = $comment->comment_author;

	// When a comment is approved later, notify the subscribers, and subscribe this comment author.
	if ( 'approve' === $status ) {
		cs_thank_you_message( $comment_id );
		cs_notify( $comment_id );
		cs_subscribe_later( $post_id, $email, $name, $comment_id );
	}
}
