<?php
/**
 * Settings Tab Four.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tab_Four.
 */
class Settings_Tab_Four {
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
	 * @return Settings_Tab_Four|null
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
		$page_slug = 'comments-subscriber-settings-tab4';
		$section   = 'section_four';
		$group     = 'cs-group-four';

		add_settings_section(
			$section,
			__( 'Unsubscribe Settings', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure what to show to unsubscribing users. You may set an "Unsubscribe Page URL" to send the user to a specific page, or configure a specific message.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[unsubscribe_url]',
			__( 'Unsubscribe Page URL', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'unsubscribe_url',
				'group' => $group,
				'desc'  => __( 'If you want to create a page with your content to say "ok, you are unsubscribed", enter the URL here. <br/>Otherwise, leave this field blank and the following message will be used.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[thankyou]',
			__( 'Unsubscribe Message', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_editor',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'thankyou',
				'group' => $group,
				'desc'  => __( 'Example: You have unsubscribed successfully. Thank you. I will send you to the home page in 3 seconds.', 'comments-subscriber' ),
			)
		);
	}
}

add_action( 'plugins_loaded', array( Settings_Tab_Four::class, 'get_instance' ) );
