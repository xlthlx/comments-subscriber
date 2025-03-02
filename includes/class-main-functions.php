<?php
/**
 * Main functions.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

use WP_Comment_Query;

/**
 * Class Main_Functions.
 */
class Main_Functions {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;
	/**
	 * Notify class.
	 *
	 * @var object
	 */
	private $notify;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->notify = ( new Notify_And_Send() )::get_instance();
	}
	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Main_Functions|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add a subscribe checkbox above the submit button.
	 *
	 * @param string $submit_field The Submit field HTML.
	 *
	 * @return string
	 */
	public function comment_form_submit_field( $submit_field ) {
		$checkbox = $this->checkbox_html();
		return $checkbox . $submit_field;
	}

	/**
	 * Returns the HTML for the checkbox as a string, if the checkbox is enabled.
	 *
	 * @return string
	 */
	public function checkbox_html() {

		$output  = '';
		$options = get_option( 'cs-group-one' );

		if ( ! empty( $options['checkbox'] ) ) {
			$output .= wp_nonce_field( 'nonce_comment', 'nonce_comment', true, false );
			$output .= '<p id="cs-comment-subscription">';
			$output .= '<input type="checkbox" value="1" name="comment_subscribe" id="comment_subscribe" ';
			$output .= isset ( $options['checked'] ) ? 'checked="checked" ' : '';
			$output .= '>&nbsp;<label for="comment_subscribe">' . esc_html( $options['label'] ) . '</label>';
			$output .= '</p>';
		}

		return wp_kses( $output, CS_KSES_DEFAULT );
	}

	/**
	 * Add a subscribe checkbox below the comment form submit button.
	 *
	 * @return void
	 */
	public function comment_form() {
		echo wp_kses( $this->checkbox_html(), CS_KSES_DEFAULT );
	}

	/**
	 * Removes a subscription.
	 *
	 * @return void
	 */
	public function unsubscribe() {

		if ( is_home() || is_front_page() ) {

			// @codingStandardsIgnoreStart
			$token = isset( $_GET['cs_t'] ) ? sanitize_key( wp_unslash( $_GET['cs_t'] ) ) : '';
			$id    = isset( $_GET['cs_id'] ) ? sanitize_key( wp_unslash( $_GET['cs_id'] ) ) : '';
			// @codingStandardsIgnoreEnd

			if ( '' === $token || '' === $id ) {
				return;
			}

			$unsubscribed = false;

			$args = array(
				'comment__in' => array( $id ),
				'type'        => 'subscription',
			);

			$query = ( new Get_Comments() )::get_instance();
			if ( $query ) {
				$comments = $query->query_comments( $args );

				foreach ( $comments as $comment ) {
					if ( $token === $comment->comment_content ) {
						wp_delete_comment( $comment, true );
						$unsubscribed = true;
					}
				}
			}

			$options   = get_option( 'cs-group-four' );
			$thank_you = '';

			if ( $unsubscribed ) {
				$thank_you = wp_unslash( __( 'You are already unsubscribed. You will be redirect to the home page within few seconds.', 'comments-subscriber' ) );

			} else {

				if ( isset( $options['unsubscribe_url'] ) && $options['unsubscribe_url'] ) {
					wp_safe_redirect( esc_url_raw( $options['unsubscribe_url'] ) );
				} else {

					$thank_you = wp_unslash( __( 'Your subscription has been removed. You will be redirect to the home page within few seconds.', 'comments-subscriber' ) );

					if ( isset( $options['thankyou'] ) && $options['thankyou'] ) {
						$thank_you = wp_kses_post( wp_unslash( $options['thankyou'] ) );
					}
				}
			}

			$output  = '<html lang="en">';
			$output .= '<head><title>Thank you</title>';
			$output .= '<meta http-equiv="refresh" content="5;url=' . esc_url( get_option( 'home' ) ) . '"/>';
			$output .= '</head>';
			$output .= '<body>';
			$output .= '<p>' . $thank_you . '</p>';
			$output .= '</body>';
			$output .= '</html>';

			echo wp_kses( $output, CS_KSES_DEFAULT );

			die();
		}
	}

	/**
	 * Subscribe and notify after moderation.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $status     New comment status, either 'hold', 'approve', 'spam', or 'trash'.
	 */
	public function notify_comment_after( $comment_id, $status ) {

		// Get original comment info.
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}
		$post_id = $comment->comment_post_ID;
		$email   = strtolower( trim( $comment->comment_author_email ) );
		$name    = $comment->comment_author;

		// When a comment is approved later, notify the subscribers, and subscribe this comment author.
		if ( 'approve' === $status ) {
			$this->notify->thank_you_message( $comment_id );
			$this->notify->notify_subscribers( $comment_id );
			$this->notify->subscribe_later( $post_id, $email, $name, $comment_id );
		}
	}

	/**
	 * Subscribe comment author and notify subscribers.
	 *
	 * @param int        $comment_id The comment ID.
	 * @param int|string $comment_approved 1 if the comment is approved, 0 if not, 'spam' if spam.
	 */
	public function notify_comment( $comment_id, $comment_approved ) {

		$comment = get_comment( $comment_id );
		$name    = $comment->comment_author;
		$email   = strtolower( trim( $comment->comment_author_email ) );
		$post_id = $comment->comment_post_ID;

		// Only subscribe if comment is approved.
		if ( 1 === $comment_approved ) {
			$this->notify->thank_you_message( $comment_id );
			$this->notify->notify_subscribers( $comment_id );

			// If comment author subscribed, subscribe author since the comment is automatically approved.
			if ( isset( $_REQUEST['nonce_comment'], $_POST['comment_subscribe'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce_comment'] ) ), 'nonce_comment' ) ) {
				$this->notify->subscribe( $post_id, $email, $name );
			}
		}

		// If comment goes to moderation and if comment author subscribed, add comment meta key for pending subscription.
		if ( ( 0 === $comment_approved ) && isset( $_REQUEST['nonce_comment'], $_POST['comment_subscribe'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce_comment'] ) ), 'nonce_comment' ) ) {
			add_comment_meta( $comment_id, 'comment_subscribe', true, true );
		}

	}

	/**
	 * Hide the subscription type comments from queries.
	 *
	 * @param array|int|null   $comment_data The comments data.
	 * @param WP_Comment_Query $query The comments query.
	 *
	 * @return void
	 */
	public function hide_subscription_comments( $comment_data, $query ) {
		$query->query_vars['type__not_in'] = 'subscription';
	}
}

add_action( 'plugins_loaded', array( Main_Functions::class, 'get_instance' ) );
