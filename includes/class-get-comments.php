<?php
/**
 * Utility class to get comments.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

use WP_Comment;

/**
 * Class Get_Comments.
 */
class Get_Comments {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;
	/**
	 * Main functions class.
	 *
	 * @var object
	 */
	private $main;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->main = ( new Main_Functions() )::get_instance();
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Get_Comments|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get the comments from an array of arguments.
	 *
	 * @param array $args The query arguments.
	 *
	 * @return int|int[]|WP_Comment[]
	 */
	public function query_comments( $args ) {

		remove_filter( 'comments_pre_query', array( $this->main, 'hide_subscription_comments' ) );

		$comments = get_comments( $args );

		add_filter( 'comments_pre_query', array( $this->main, 'hide_subscription_comments' ), 10, 2 );

		return $comments;
	}
}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Get_Comments::class, 'get_instance' ) );
