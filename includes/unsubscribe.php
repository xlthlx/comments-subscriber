<?php
/**
 * Unsubscribe.
 *
 * @package comments_subscriber
 */

/**
 * Removes a subscription.
 *
 * @return void
 */
function cs_unsubscribe() {

	// @codingStandardsIgnoreStart
	$token = isset( $_GET['cs_t'] ) ? sanitize_key( wp_unslash( $_GET['cs_t'] ) ) : '';
	$id    = isset( $_GET['cs_id'] ) ? sanitize_key( wp_unslash( $_GET['cs_id'] ) ) : '';
	// @codingStandardsIgnoreEnd

	if ( '' === $token || '' === $id ) {
		return;
	}

	$type = 'subscription';

	remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

	$comments = get_comments(
		array(
			'comment__in' => array( $id ),
			'type'        => $type,
		)
	);

	add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

	foreach ( $comments as $comment ) {
		if ( $token === $comment->comment_content ) {
			wp_delete_comment( $comment, true );
		}
	}

	$unsubscribe_url = empty( $options['unsubscribe_url'] ) ? '' : esc_url_raw( $options['unsubscribe_url'] );

	if ( $unsubscribe_url ) {
		wp_safe_redirect( $unsubscribe_url );
	} else {
		$output    = '';
		$thank_you = empty( $options['thankyou'] ) ?
			__( 'Your subscription has been removed. You\'ll be redirect to the home page within few seconds.', 'comments-subscriber' ) :
			wp_kses_post( $options['thankyou'] );

		$output .= '<html lang="en">';
		$output .= '<head>';
		$output .= '<title>Thank you</title>';
		$output .= '<meta http-equiv="refresh" content="10;url=' . esc_url( get_option( 'home' ) ) . '"/>';
		$output .= '</head>';
		$output .= '<body>';
		$output .= '<p>' . $thank_you . '</p>';
		$output .= '</body>';
		$output .= '</html>';

		echo wp_kses( $output, CS_KSES_DEFAULT );
	}

	die();
}
