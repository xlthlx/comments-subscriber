<?php
/**
 * Replace.
 *
 * @package comments_subscriber
 */

/**
 * Replace placeholders in body message with subscriber data and post/comment data.
 *
 * @param string $message Email message.
 * @param object $data Email data.
 *
 * @return string
 */
function cs_replace( $message, $data ) {
	$options = get_option( 'cs_options' );
	$message = str_replace(
		array( '{title}', '{link}', '{comment_link}', '{author}' ),
		array(
			$data->title,
			$data->link,
			$data->comment_link,
			$data->author,
		),
		$message
	);
	$replace = strip_tags( $data->content );
	$length  = empty( $options['length'] ) ? 155 : esc_html( $options['length'] );

	if ( ! is_numeric( $length ) ) {
		$length = 155;
	}

	if ( $length && strlen( $replace ) > $length ) {
		$x = strpos( $replace, ' ', $length );
		if ( false !== $x ) {
			$replace = substr( $replace, 0, $x ) . '...';
		}
	}
	return str_replace( '{content}', $replace, $message );
}
