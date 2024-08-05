<?php
/**
 * Main hooks: activate, uninstall plugin.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

const CS_OPTIONS = array(
	'cs-group-one',
	'cs-group-two',
	'cs-group-three',
	'cs-group-four',
	'cs-group-five',
);

/**
 * Class Main_Hooks.
 */
class Main_Hooks {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Main_Hooks|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Upon activation, set up the default settings.
	 *
	 * @return void
	 */
	public static function plugin_activate() {

		foreach ( CS_OPTIONS as $option ) {
			$group = explode( '-', $option );

			if ( 'one' === $group[2] && empty( get_option( $option ) ) ) {
				$default_options             = array();
				$default_options['checkbox'] = '1';
				$default_options['label']    = __( 'Notify me when new comments are added.', 'comments-subscriber' );
				$default_options['checked']  = '1';

				add_option( $option, $default_options );
			}

			if ( 'two' === $group[2] && empty( get_option( $option ) ) ) {
				$default_options         = array();
				$default_options['name'] = get_option( 'blogname' );
				$default_options['from'] = get_option( 'admin_email' );
				/* translators: 1: Comment author, 2: Post title. */
				$default_options['subject'] = sprintf( __( 'A new comment from %1$s on "%2$s"', 'comments-subscriber' ), '{author}', '{title}' );
				$default_options['message'] =
					/* translators: 1: Subscriber name. */
					sprintf( __( 'Hi %s,', 'comments-subscriber' ), '{name}' ) .
					"\n\n" .
					/* translators: 1: Comment author, 2: Post title. */
					sprintf( __( '%1$s has just written a new comment on "%2$s". Here is an excerpt:', 'comments-subscriber' ), '{author}', '{title}' ) .
					"\n\n" .
					'{content}' .
					"\n\n" .
					/* translators: 1: Comment url. */
					sprintf( __( 'To read more, <a href="%s">click here</a>.', 'comments-subscriber' ), '{comment_link}' ) .
					"\n\n" .
					__( 'Bye', 'comments-subscriber' ) .
					"\n\n" .
					/* translators: 1: Unsubscribe url. */
					sprintf( __( 'To unsubscribe from this notification service, <a href="%s">click here</a>.', 'comments-subscriber' ), '{unsubscribe}' );

				add_option( $option, $default_options );
			}

			if ( 'three' === $group[2] && empty( get_option( $option ) ) ) {
				$default_options               = array();
				$default_options['ty_subject'] = __( 'Thank you for your first comment', 'comments-subscriber' );
				$default_options['ty_message'] =
					/* translators: 1: Subscriber name. */
					sprintf( __( 'Hi %s,', 'comments-subscriber' ), '{author}' ) .
					"\n\n" .
					__( 'I received and published your first comment on my blog on the article:', 'comments-subscriber' ) .
					"\n\n" .
					'<a href="{link}">{title}</a>' .
					"\n\n" .
					__( 'Have a lovely day!', 'comments-subscriber' );

				add_option( $option, $default_options );
			}

			if ( 'four' === $group[2] && empty( get_option( $option ) ) ) {
				$default_options             = array();
				$default_options['thankyou'] = __( 'Your subscription has been removed.', 'comments-subscriber' ) . "\n\n" .
											   __( 'You\'ll be redirected to the home page within a few seconds.', 'comments-subscriber' );

				add_option( $option, $default_options );
			}

			if ( 'five' === $group[2] && empty( get_option( $option ) ) ) {
				$default_options         = array();
				$default_options['test'] = get_option( 'admin_email' );

				add_option( $option, $default_options );
			}
		}
	}

	/**
	 * Upon uninstall, delete all options and subscriptions.
	 *
	 * @return void
	 */
	public static function plugin_uninstall() {

		$delete = get_option( 'cs-group-five' );
		if ( isset( $delete['delete_data'] ) && $delete['delete_data'] ) {
			foreach ( CS_OPTIONS as $option ) {
				delete_option( $option );
			}

			$args = array(
				'type' => 'subscription',
			);

			$query = ( new Get_Comments() )::get_instance();
			if ( $query ) {
				$comments = $query->query_comments( $args );

				foreach ( $comments as $comment ) {
					wp_delete_comment( $comment, true );
				}
			}
		}
	}

}

add_action( 'plugins_loaded', array( Main_Hooks::class, 'get_instance' ) );
