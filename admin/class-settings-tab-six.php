<?php
/**
 * Settings Tab Six.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tab_Six.
 */
class Settings_Tab_Six {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * A reference for the settings.
	 *
	 * @var object
	 */
	private $fields;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->fields = ( new Settings_Fields() )::get_instance();
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Settings_Tab_Six|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add tab.
	 *
	 * @return void
	 */
	public function add_tab() {
		$page_slug = 'comments-subscriber-settings-tab6';
		$section   = 'section_six';
		$group     = 'cs-group-six';

		add_settings_section(
			$section,
			__( 'Email Management', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Remove a specific email from all subscriptions.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . 'email',
			__( 'Remove email', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'email',
				'group' => $group,
				'desc'  => __( 'Remove this email from all subscriptions.', 'comments-subscriber' ),
			)
		);
	}
}

add_action( 'plugins_loaded', array( Settings_Tab_Six::class, 'get_instance' ) );
