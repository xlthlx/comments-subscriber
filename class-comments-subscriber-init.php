<?php
/**
 * Comments Subscriber init.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * WP KSES constants.
 */
const CS_KSES_DEFAULT = array(
	'strong' => array(),
	'em'     => array(),
	'h2'     => array(),
	'p'      => array(),
	'ul'     => array(),
	'li'     => array(),
	'br'     => array(),
	'input'  => array(
		'type'  => array(),
		'name'  => array(),
		'value' => array(),
		'class' => array(),
		'id'    => array(),
	),
	'a'      => array(
		'href'   => array(),
		'target' => array(),
	),
	'label'  => array(
		'for' => array(),
	),
	'title'  => array(),
	'head'   => array(),
	'meta'   => array(
		'http-equiv' => array(),
		'content'    => array(),
	),
);

/**
 * Options groups.
 */
const CS_OPTIONS = array(
	'cs-group-one',
	'cs-group-two',
	'cs-group-three',
	'cs-group-four',
	'cs-group-five',
);

/**
 * Class Comments_Subscribers_Init.
 */
class Comments_Subscriber_Init {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Comments_Subscriber_Init|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init() {
		$options = get_option( 'cs-group-one' );

		$main = ( new Main_Functions() )::get_instance();

		if ( isset( $options['theme_compat'] ) && $options['theme_compat'] ) {
			add_filter( 'comment_form_submit_field', array( $main, 'comment_form_submit_field' ), 9999 );
		} else {
			add_action( 'comment_form', array( $main, 'comment_form' ), 9999 );
		}

		add_filter( 'template_redirect', array( $main, 'unsubscribe' ) );
		add_action( 'comment_post', array( $main, 'notify_comment' ), 10, 2 );
		add_action( 'wp_set_comment_status', array( $main, 'notify_comment_after' ), 10, 2 );
		add_filter( 'comments_pre_query', array( $main, 'hide_subscription_comments' ), 10, 2 );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'comments-subscriber', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Attach settings in WordPress Plugins list.
	 *
	 * @return void
	 */
	public function plugin_settings() {
		add_action( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 4 );
	}

	/**
	 * Add settings link to plugin actions.
	 *
	 * @param array  $plugin_actions The plugin actions.
	 * @param string $plugin_file The plugin file path.
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $plugin_actions, $plugin_file ) {
		$new_actions = array();

		if ( 'comments-subscriber/comments-subscriber.php' === $plugin_file ) {
			$new_actions['cs_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page=comments-subscriber-settings' ) ) . '">' . __( 'Settings', 'comments-subscriber' ) . '</a>';
		}

		return array_merge( $new_actions, $plugin_actions );
	}

}

add_action( 'plugins_loaded', array( Comments_Subscriber_Init::class, 'get_instance' ) );
