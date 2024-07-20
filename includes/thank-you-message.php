<?php
/**
 * Send thank you message.
 *
 * @package comments_subscriber
 */

/**
 * Send a thank-you message to first timers after their 1st comment is approved,
 * regardless of whether they subscribe, if enabled.
 *
 * @param int $comment_id The comment ID.
 *
 * @return void
 */
function cs_thank_you_message( $comment_id ) {
	global $wpdb;
	$options = get_option( 'cs_options' );
	if ( ! isset( $options['ty_enabled'] ) ) {
		return;
	}
	if ( empty( $options['ty_message'] ) ) {
		return;
	}

	$comment = get_comment( $comment_id );
	$email   = esc_html( strtolower( $comment->comment_author_email ) );

	remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

	$count = get_comments(
		array(
			array( 'comment_status' => 1 ),
			'author_email' => $email,
			'count'        => true,
		)
	);

	add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

	if ( 1 !== $count ) {
		return;
	}
	$post = get_post( $comment->comment_post_ID );

	$data               = new stdClass();
	$data->post_id      = $comment->comment_post_ID;
	$data->title        = $post->post_title;
	$data->link         = get_permalink( $comment->comment_post_ID );
	$data->comment_link = get_comment_link( $comment_id );
	$data->author       = $comment->comment_author;
	$data->content      = $comment->comment_content;

	$message = cs_replace( $options['ty_message'], $data );

	// Fill the message subject with same for all data.
	$subject = $options['ty_subject'];
	$subject = str_replace(
		array( '{title}', '{author}' ),
		array(
			$post->post_title,
			$comment->comment_author,
		),
		$subject
	);

	cs_mail( $comment->comment_author_email, $subject, $message );
}
