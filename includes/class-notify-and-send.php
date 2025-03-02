<?php
/**
 * Notify and send functions.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

use stdClass;

/**
 * Class Notify_And_Send.
 */
class Notify_And_Send {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Notify_And_Send|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Send a thank-you message to first timers after their 1st comment is approved,
	 * regardless of whether they subscribe, if enabled.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return void
	 */
	public function thank_you_message( $comment_id ) {

		$options = get_option( 'cs-group-three' );
		if ( ! isset( $options['ty_enabled'] ) ) {
			return;
		}
		if ( empty( $options['ty_message'] ) ) {
			return;
		}

		$comment = get_comment( $comment_id );
		$email   = esc_html( strtolower( $comment->comment_author_email ) );

		if ( $this->is_valid_email( $email ) ) {

			$args = array(
				array( 'comment_status' => 1 ),
				'author_email' => $email,
				'count'        => true,
			);

			$query = ( new Get_Comments() )::get_instance();
			if ( $query ) {
				$count = $query->query_comments( $args );

				if ( 1 !== $count ) {
					return;
				}
				$post = get_post( $comment->comment_post_ID );

				$data               = new stdClass();
				$data->post_id      = $comment->comment_post_ID;
				$data->title        = $post->post_title;
				$data->link         = get_permalink( $comment->comment_post_ID );
				$data->comment_link = get_comment_link( $comment_id );
				$data->author       = $comment->comment_author;
				$data->content      = $comment->comment_content;

				$message = $this->replace_placeholders( $options['ty_message'], $data );

				// Fill the message subject with same for all data.
				$subject = $options['ty_subject'];
				$subject = str_replace(
					array( '{title}', '{author}' ),
					array(
						$post->post_title,
						$comment->comment_author,
					),
					$subject
				);

				$this->email_send( $comment->comment_author_email, $subject, $message );
			}
		}
	}

	/**
	 * Replace placeholders in a body message with subscriber data and post/comment data.
	 *
	 * @param string $message Email message.
	 * @param object $data Email data.
	 *
	 * @return string
	 */
	public function replace_placeholders( $message, $data ) {

		$options = get_option( 'cs-group-two' );
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
		$replace = wp_strip_all_tags( $data->content );
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

	/**
	 * Send an email.
	 *
	 * @param string $to Email to.
	 * @param string $subject Email subject.
	 * @param string $message Email message.
	 *
	 * @return bool|mixed
	 */
	public function email_send( $to, $subject, $message ) {

		$options = get_option( 'cs-group-two' );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$email = get_option( 'admin_email' );
		$name  = get_option( 'blogname' );

		if ( ! empty( $options['from'] ) && $this->is_valid_email( $options['from'] ) ) {
			$email = $options['from'];
		}

		if ( ! empty( $options['name'] ) ) {
			$name = $options['name'];
		}

		$headers[] = 'From: ' . $name . ' <' . $email . '>';
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';

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
	public function is_valid_email( $email ) {
		return is_email( $email ) ? sanitize_email( $email ) : false;
	}

	/**
	 * Sends out the notification of a new comment for subscribers.
	 * The notification is not sent to the email address of the author of the comment.
	 *
	 * @param int $comment_id Comment ID.
	 *
	 * @return void
	 */
	public function notify_subscribers( $comment_id ) {

		$options      = get_option( 'cs-group-two' );
		$options_five = get_option( 'cs-group-five' );
		$comment      = get_comment( $comment_id );

		if ( 'trackback' === $comment->comment_type || 'pingback' === $comment->comment_type ) {
			return;
		}

		$post_id = $comment->comment_post_ID;
		if ( empty( $post_id ) ) {
			return;
		}
		$author_email = strtolower( trim( $comment->comment_author_email ) );

		$args = array(
			'post_id'      => $post_id,
			'type'         => 'subscription',
			'hierarchical' => 'flat',
		);

		$query = ( new Get_Comments() )::get_instance();
		if ( $query ) {
			$subscriptions = $query->query_comments( $args );

			if ( empty( $subscriptions ) ) {
				return;
			}

			$post = get_post( $post_id );
			if ( empty( $post ) ) {
				return;
			}

			$data               = new stdClass();
			$data->post_id      = $post_id;
			$data->title        = $post->post_title;
			$data->link         = get_permalink( $post_id );
			$data->comment_link = get_comment_link( $comment_id );
			$comment            = get_comment( $comment_id );
			$data->author       = $comment->comment_author;
			$data->content      = $comment->comment_content;
			$message            = $this->replace_placeholders( $options['message'], $data );

			// Fill the message subject with same for all data.
			$subject = $options['subject'];
			$subject = str_replace(
				array( '{title}', '{author}' ),
				array(
					$post->post_title,
					$comment->comment_author,
				),
				$subject
			);

			$url = get_option( 'home' ) . '/?';

			// Set up the copy email if the options is set.
			if ( ! empty( $options_five['copy'] ) ) {
				$copy            = new stdClass();
				$copy->token     = 'fake';
				$copy->id        = 0;
				$copy->email     = $options_five['copy'];
				$copy->name      = get_option( 'blogname' );
				$subscriptions[] = $copy;
			}

			foreach ( $subscriptions as $subscription ) {

				$to = strtolower( trim( $subscription->comment_author_email ) );

				if ( $to !== $author_email ) {

					$m = $message;
					$m = str_replace(
						array( '{name}', '{unsubscribe}' ),
						array(
							$subscription->comment_author,
							$url . 'cs_id=' . $subscription->comment_ID . '&cs_t=' . $subscription->comment_content,
						),
						$m
					);
					$s = $subject;
					$s = str_replace( '{name}', $subscription->comment_author, $s );

					if ( $this->is_valid_email( $to ) ) {
						$this->email_send( $to, $s, $m );
					}
				}
			}
		}
	}

	/**
	 * Subscribe a user to a post.
	 *
	 * @param int    $post_id Post ID on which to subscribe.
	 * @param string $email User's email.
	 * @param string $name User's name.
	 *
	 * @return false|int
	 */
	public function subscribe( $post_id, $email, $name ) {

		$args = array(
			'author_email' => $email,
			'type'         => 'subscription',
			'post__in'     => $post_id,
		);

		$query = ( new Get_Comments() )::get_instance();
		if ( $query ) {
			$subscribed = $query->query_comments( $args );

			if ( ! empty( $subscribed ) ) {
				return false;
			}

			$token = md5( wp_rand() );

			return wp_insert_comment(
				array(
					'comment_post_ID'      => $post_id,
					'comment_author_email' => $email,
					'comment_author'       => $name,
					'comment_content'      => $token,
					'comment_type'         => 'subscription',
				)
			);
		}

		return false;
	}

	/**
	 * Subscribe a comment author to a post after his comment has
	 * been held in moderation and is finally approved.
	 *
	 * @param int    $post_id Post ID on which comment was made.
	 * @param string $email Comment author's email.
	 * @param string $name Comment author's name.
	 * @param int    $comment_id Comment ID.
	 */
	public function subscribe_later( $post_id, $email, $name, $comment_id ) {

		$type = 'subscription';

		$args = array(
			'author_email' => $email,
			'type'         => $type,
			'post__in'     => $post_id,
		);

		$query = ( new Get_Comments() )::get_instance();
		if ( $query ) {
			$subscribed = $query->query_comments( $args );

			if ( ! empty( $subscribed ) ) {
				return;
			}

			// If the comment author checks the box to subscribe.
			if ( $comment_id && get_comment_meta( $comment_id, 'comment_subscribe', true ) ) {

				// The random token for unsubscription.
				$token = md5( wp_rand() );
				wp_insert_comment(
					array(
						'comment_post_ID'      => $post_id,
						'comment_author_email' => $email,
						'comment_author'       => $name,
						'comment_content'      => $token,
						'comment_type'         => $type,
					)
				);

				delete_comment_meta( $comment_id, 'comment_subscribe' );
			}
		}
	}

}

add_action( 'plugins_loaded', array( Notify_And_Send::class, 'get_instance' ) );
