<?php
/**
 * Sanitize settings.
 *
 * @package comments_subscriber
 */

/**
 * Sanitize settings before saving.
 *
 * @param array $options The array of options to be saved.
 *
 * @return array
 */
function cs_sanitize_settings( $options ) {
	// Integers.
	$int_keys = array(
		'checkbox',
		'checked',
		'ty_enabled',
		'delete_data',
		'theme_compat',
	);
	foreach ( $int_keys as $int_key ) {
		if ( isset( $options[ $int_key ] ) ) {
			$options[ $int_key ] = (int) $options[ $int_key ];
		}
	}
	// Text.
	$text_keys = array(
		'label',
		'name',
		'subject',
		'unsubscribe_url',
		'ty_subject',
		'copy',
	);
	foreach ( $text_keys as $text_key ) {
		if ( isset( $options[ $text_key ] ) ) {
			$options[ $text_key ] = sanitize_text_field( $options[ $text_key ] );
		}
	}
	// Some HTML.
	$richtext_keys = array(
		'message',
		'thankyou',
		'ty_message',
	);
	foreach ( $richtext_keys as $richtext_key ) {
		if ( isset( $options[ $richtext_key ] ) ) {
			$options[ $richtext_key ] = wp_slash( $options[ $richtext_key ] );
		}
	}
	// Emails.
	if ( isset( $options['from'] ) ) {
		$options['from'] = sanitize_email( $options['from'] );
	}

	if ( isset( $options['test'] ) ) {
		$options['test'] = sanitize_email( $options['test'] );
	}

	return $options;
}
