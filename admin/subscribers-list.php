<?php
/**
 * Subscribers list.
 *
 * @package comments_subscriber
 */

/**
 * Output the subscribers list.
 *
 * @return void
 */
function cs_subscribers_list() {
	$output = '';
	remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

	$list = get_comments(
		array(
			'orderby'      => 'comment_post_ID',
			'type'         => 'subscription',
			'hierarchical' => 'flat',
		)
	);

	add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

	if ( ! empty( $list ) ) {
		foreach ( $list as $r ) {
			$comment_post_id = (int) $r->comment_post_ID;
			$post_list       = get_post( $comment_post_id );

			remove_filter( 'comments_pre_query', 'cs_hide_subscription' );

			$list2 = get_comments(
				array(
					'comment_post_ID' => $comment_post_id,
					'type'            => 'subscription',
					'hierarchical'    => 'flat',
				)
			);

			add_filter( 'comments_pre_query', 'cs_hide_subscription', 10, 2 );

			$total = count( $list2 );

			$output  = '<li>';
			$output .= '<a href="' . esc_url( get_permalink( $post_list->ID ) ) . '" target="_blank">';
			$output .= esc_html( $post_list->post_title ) . '</a><br/>';
			$output .= esc_html__( 'Subscribers: ', 'comments-subscriber' ) . esc_attr( $total );
			$output .= '</li>';
			$output .= '<ul>';

			foreach ( $list2 as $r2 ) {
				$output .= '<li><input type="checkbox" name="id[]" value="' . esc_attr( $r2->comment_ID ) . '"/> ' . esc_html( $r2->comment_author_email ) . '</li>';
			}
			$output .= '</ul>';
			$output .= '<input class="button-secondary" type="submit" name="remove" value="' . esc_html__( 'Remove', 'comments-subscriber' ) . '"/>';
		}
	} else {
		$output = '<p>' . esc_html__( 'There are no subscribers.', 'comments-subscriber' ) . '</p>';
	}

	echo wp_kses(
		$output,
		array(
			'ul'    => array(),
			'li'    => array(),
			'br'    => array(),
			'input' => array(
				'type'  => array(),
				'name'  => array(),
				'value' => array(),
				'class' => array(),
			),
			'a'     => array(
				'href'   => array(),
				'target' => array(),
			),
		)
	);
}
