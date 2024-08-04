<?php
/**
 * Subscribers list.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Subscribers_List.
 */
class Subscribers_List {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Subscribers_List|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Show subscribers list.
	 *
	 * @return void
	 */
	public function show_subscribers_list() {
		$output = '<h2>' . __( 'Subscribers list', 'comments-subscriber' ) . '</h2>';
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
			$output .= '<ul style="list-style: square;padding-left:10px">';
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
				$output .= '<input class="button-secondary button-large" type="submit" id="remove" name="remove" value="' . esc_html__( 'Remove', 'comments-subscriber' ) . '"/>';
				$output .= '</ul>';
			}
		} else {
			$output .= '<p>' . esc_html__( 'There are no subscribers.', 'comments-subscriber' ) . '</p>';
		}

		echo wp_kses( $output, CS_KSES_DEFAULT );
	}
}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Subscribers_List::class, 'get_instance' ) );
