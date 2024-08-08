<?php
/**
 * Settings Tab Three.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tab_Three.
 */
class Settings_Tab_Three {
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
	 * @return Settings_Tab_Three|null
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
		$page_slug = 'comments-subscriber-settings-tab3';
		$section   = 'section_three';
		$group     = 'cs-group-three';

		add_settings_section(
			$section,
			__( 'Thank You Email Settings', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure a "Thank you" message for first time commentators. Messages are sent when comments are approved.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[ty_enabled]',
			__( 'Enable Thank You Message', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'ty_enabled',
				'group' => $group,
				'desc'  => __( 'Send a "Thank you" message to visitor on their first comment', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[ty_subject]',
			__( 'Subject', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'ty_subject',
				'group' => $group,
				'desc'  => __( 'Tags: <br> {title} - the post title <br> {author} - the commenter name', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[ty_message]',
			__( 'Message Body', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_editor',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'ty_message',
				'group' => $group,
				'desc'  => __( 'Tags: <br> {title} - the post title <br> {author} - the commenter name <br> {link} - link to the post/page <br> {comment_link} - link to the comment <br> {content} - the comment text', 'comments-subscriber' ),
			)
		);
	}
}

add_action( 'plugins_loaded', array( Settings_Tab_Three::class, 'get_instance' ) );
