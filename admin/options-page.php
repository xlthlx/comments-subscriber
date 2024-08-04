<?php
/**
 * Options page.
 *
 * @package comments_subscriber
 */

/**
 * Set up options page.
 *
 * @return void
 */
function cs_options_page() {
	$options = get_option( 'cs_options' );
	$test    = empty( $options['test'] ) ? '' : sanitize_text_field( $options['test'] );

	// Save the options.
	if ( ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-lstc-options' ) ) {

		// Maybe send a test message, if requested.
		if ( isset( $_POST['savethankyou'] ) ) {
			if ( ! empty( $_POST['options']['test'] ) ) {
				$test = sanitize_email( wp_unslash( $_POST['options']['test'] ) );
			}
			$ty_message            = empty( $options['ty_message'] ) ? '' : $options['ty_message'];
			$cs_data               = new stdClass();
			$cs_data->author       = esc_html__( 'Author', 'comments-subscriber' );
			$cs_data->link         = get_option( 'home' );
			$cs_data->comment_link = get_option( 'home' );
			$cs_data->title        = esc_html__( 'The post title', 'comments-subscriber' );
			$cs_data->content      = esc_html__( 'This is a long comment. Be a yardstick of quality. Some people are not used to an environment where excellence is expected.', 'comments-subscriber' );
			$message               = cs_replace( $ty_message, $cs_data );
			$subject               = $options['ty_subject'];
			$subject               = str_replace(
				array( '{title}', '{author}' ),
				array(
					$cs_data->title,
					$cs_data->author,
				),
				$subject
			);
			cs_mail( $test, $subject, $message );
		}
	}

	// Removes a single email for all subscriptions.
	if ( isset( $_POST['remove_email'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'remove_email' ) ) {
			die( esc_html__( 'Nonce not verified', 'comments-subscriber' ) );
		}
		$email = strtolower( sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) ) );

		remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

		$comments = get_comments(
			array(
				'author_email' => $email,
				'type'         => 'subscription',
			)
		);

		add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment, true );
		}
	}

	if ( isset( $_POST['remove'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'remove' ) ) {
			die( esc_html__( 'Nonce not verified', 'comments-subscriber' ) );
		}

		$ids = isset( $_POST['id'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['id'] ) ) : array();
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				wp_delete_comment( $id, true );
			}
		}
	}

}
