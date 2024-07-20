<?php
/**
 * Set the checkbox for subscribe.
 *
 * @package comments_subscriber
 */

/**
 * Returns the HTML for the checkbox as a string, if the checkbox is enabled.
 *
 * @return string
 */
function cs_checkbox_html() {
	$output  = '';
	$options = get_option( 'cs_options' );
	if ( ! empty( $options['checkbox'] ) ) {
		$output .= wp_nonce_field( 'nonce_comment', 'nonce_comment', true, false );
		$output .= '<p id="cs-comment-subscription">
					<input type="checkbox" value="1" name="comment_subscribe" id="comment_subscribe"';
		if ( ! empty( $options['checked'] ) ) {
			$output .= ' checked="checked"';
		}
		$output .= '/>&nbsp;<label for="comment_subscribe">' . esc_html( $options['label'] ) . '</label>
					</p>';
	}

	return wp_kses(
		$output,
		array(
			'p'     => array(),
			'input' => array(
				'type'  => array(),
				'value' => array(),
				'name'  => array(),
				'id'    => array(),
			),
			'label' => array(
				'for' => array(),
			),
		)
	);
}

/**
 * Add a subscribe checkbox below the comment form submit button.
 *
 * @return void
 */
function cs_comment_form() {
	echo wp_kses(
		cs_checkbox_html(),
		array(
			'p'     => array(),
			'input' => array(
				'type'  => array(),
				'value' => array(),
				'name'  => array(),
				'id'    => array(),
			),
			'label' => array(
				'for' => array(),
			),
		)
	);
}

/**
 * Add a subscribe checkbox above the submit button.
 *
 * @param string $submit_field The Submit field HTML.
 *
 * @return string
 */
function cs_comment_form_submit_field( $submit_field ) {
	$checkbox = cs_checkbox_html();
	return $checkbox . $submit_field;
}
