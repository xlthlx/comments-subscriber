<?php
/**
 * Notify.
 *
 * @package comments_subscriber
 */

/**
 * Sends out the notification of a new comment for subscribers. This is the core function
 * of this plugin. The notification is not sent to the email address of the author
 * of the comment.
 *
 * @param int $comment_id Comment ID.
 *
 * @return void
 */
function cs_notify( $comment_id ) {

	$options = get_option( 'cs_options' );
	$comment = get_comment( $comment_id );

	if ( 'trackback' === $comment->comment_type || 'pingback' === $comment->comment_type ) {
		return;
	}

	$post_id = $comment->comment_post_ID;
	if ( empty( $post_id ) ) {
		return;
	}
	$email = strtolower( trim( $comment->comment_author_email ) );
	$type  = 'subscription';

	remove_filter( 'comments_pre_query', 'hide_subscriptions_from_comments' );

	$subscriptions = get_comments(
		array(
			'post_id'      => $post_id,
			'type'         => $type,
			'hierarchical' => 'flat',
		)
	);

	if ( 0 === $subscriptions ) {
		return;
	}

	add_filter( 'comments_pre_query', 'hide_subscriptions_from_comments', 10, 2 );

	// Fill the message body with same for all data.
	$post = get_post( $post_id );
	if ( empty( $post ) ) {
		return;
	}

	$data               = new stdClass();
	$data->post_id      = $post_id;
	$data->title        = $post->post_title;
	$data->link         = get_permalink( $post_id );
	$data->comment_link = get_comment_link( $comment_id );
	$comment            = get_comment( $comment_id );
	$data->author       = $comment->comment_author;
	$data->content      = $comment->comment_content;
	$message            = cs_replace( $options['message'], $data );

	// Fill the message subject with same for all data.
	$subject = $options['subject'];
	$subject = str_replace(
		array( '{title}', '{author}' ),
		array(
			$post->post_title,
			$comment->comment_author,
		),
		$subject
	);

	$url = get_option( 'home' ) . '/?';

	if ( ! empty( $options['copy'] ) ) {
		$fake            = new stdClass();
		$fake->token     = 'fake';
		$fake->id        = 0;
		$fake->email     = $options['copy'];
		$fake->name      = 'Test subscriber';
		$subscriptions[] = $fake;
	}

	foreach ( $subscriptions as $subscription ) {

		if ( strtolower( trim( $subscription->comment_author_email ) ) !== $email ) {

			$m = $message;
			$m = str_replace(
				array( '{name}', '{unsubscribe}' ),
				array(
					$subscription->comment_author,
					$url . 'cs_id=' . $subscription->comment_ID . '&cs_t=' . $subscription->comment_content,
				),
				$m
			);
			$s = $subject;
			$s = str_replace( '{name}', $subscription->comment_author, $s );
			cs_mail( $subscription->comment_author_email, $s, $m );

		}
	}
}

