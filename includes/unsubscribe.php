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
	global $wpdb;

	// @codingStandardsIgnoreStart
	if ( empty( $_GET['cs_id'] ) ) {
		return;
	}

	if ( empty( $_GET['cs_t'] ) ) {
		return;
	}

	$token = sanitize_key( wp_unslash( $_GET['cs_t'] ) );
	$id    = sanitize_key( wp_unslash( $_GET['cs_id'] ) );
	// @codingStandardsIgnoreEnd

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

	$unsubscribe_url = empty( $options['unsubscribe_url'] ) ? '' : esc_url_raw( $options['unsubscribe_url'] );

	if ( $unsubscribe_url ) {
		header( 'Location: ' . $unsubscribe_url );
	} else {
		$output    = '';
		$thank_you = empty( $options['thankyou'] ) ?
			__( 'Your subscription has been removed. You\'ll be redirect to the home page within few seconds.', 'comments-subscriber' ) :
			esc_html( $options['thankyou'] );

		$output .= '<html lang="en">
				<head><title>Thank you</title>';
		$output .= '<meta http-equiv="refresh" content="3;url=' . esc_url( get_option( 'home' ) ) . '"/>';
		$output .= '</head><body>';
		$output .= $thank_you;
		$output .= '</body>
		</html>';

		echo esc_html( $output );
	}

	flush();
	die();

}
