<?php
/**
 * Settings Tab Two.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tab_Two.
 */
class Settings_Tab_Two {
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
	 * @return Settings_Tab_Two|null
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
		$page_slug = 'comments-subscriber-settings-tab2';
		$section   = 'section_two';
		$group     = 'cs-group-two';

		add_settings_section(
			$section,
			__( 'Notification Email Settings', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure the message sent to subscribers to notify them that a new comment was posted.', 'comments-subscriber' ),
			)
		);

		register_setting( 'comments-subscriber-settings-tab2-settings', $group );

		add_settings_field(
			$group . '[name]',
			__( 'From Name', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'name',
				'group' => $group,
			)
		);

		add_settings_field(
			$group . '[from]',
			__( 'From Email', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'from',
				'group' => $group,
			)
		);

		add_settings_field(
			$group . '[subject]',
			__( 'Subject', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'subject',
				'group' => $group,
				'desc'  => __( 'Tags: <br> {title} - the post title <br> {name} - the subscriber name <br> {author} - the commenter name', 'comments-subscriber' ),
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
				'desc'  => __( 'Tags: <br> {name} - the subscriber name <br> {author} - the commenter name <br> {title} - the post title <br> {content} - the comment text (eventually truncated) <br> {comment_link} - link to the comment <br> {link} - link to the post/page <br> {unsubscribe} - the unsubscribe link	', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[length]',
			__( 'Comment Excerpt Length', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_small_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'length',
				'group' => $group,
				'instr' => 'characters',
				'desc'  => __( 'The length of the comment excerpt to be inserted in the email notification. If blank, the default is 155 characters.', 'comments-subscriber' ),
			)
		);
	}
}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Settings_Tab_Two::class, 'get_instance' ) );
