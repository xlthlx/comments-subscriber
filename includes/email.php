<?php
/**
 * Email.
 *
 * @package comments_subscriber
 */

/**
 * Send an email.
 *
 * @param string $to Email to.
 * @param string $subject Email subject.
 * @param string $message Email message.
 *
 * @return bool|mixed
 */
function cs_mail( $to, $subject, $message ) {
	$options = get_option( 'cs_options' );
	$headers = "Content-type: text/html; charset=UTF-8\n";
	if ( ! empty( $options['name'] ) && ! empty( $options['from'] ) ) {
		$headers .= 'From: "' . $options['name'] . '" <' . $options['from'] . ">\n";
	}
	$message = wpautop( $message );
	return wp_mail( $to, $subject, $message, $headers );
}

/**
 *  Check that an email is a valid email structure and if so, sanitize it.
 *
 * @param string $email The email to check.
 *
 * @return false|string A sanitized email if a valid email was passed, otherwise false.
 */
function cs_valid_email( $email ) {
	return is_email( $email ) ? sanitize_email( $email ) : false;
}
