<?php
/**
 * Settings Tab Five.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

use stdClass;

/**
 * Class Settings_Tab_Five.
 */
class Settings_Tab_Five {
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
	 * A reference for Notify_And_Send class.
	 *
	 * @var object
	 */
	private $notify;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->fields = ( new Settings_Fields() )::get_instance();
		$this->notify = ( new Notify_And_Send() )::get_instance();
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Settings_Tab_Five|null
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
		$page_slug = 'comments-subscriber-settings-tab5';
		$section   = 'section_five';
		$group     = 'cs-group-five';

		add_settings_section(
			$section,
			__( 'Advanced Settings', 'comments-subscriber' ),
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

		add_settings_field(
			$group . '[test]',
			__( 'Email address where to send test emails', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'test',
				'group' => $group,
			)
		);

		add_settings_field(
			$group . '[copy]',
			__( 'Extra email address where to send a copy of EACH notification', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'copy',
				'group' => $group,
				'desc'  => __( 'Leave empty to disable.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[delete_data]',
			__( 'Delete Data on Uninstall', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name'  => 'delete_data',
				'group' => $group,
				'desc'  => __( 'Check this box if you would like this plugin to <strong>delete all</strong> of its data when the plugin is deleted. <br/>This would delete the entire list of subscribers and their subscriptions. This does NOT delete the actual comments.', 'comments-subscriber' ),
			)
		);
	}

	/**
	 * Send test email.
	 *
	 * @param array $data The POST data.
	 *
	 * @return void
	 */
	public function send_test_email( $data ) {
		$options = get_option( 'cs-group-three' );

		if ( isset( $data['savethankyou'] ) && ! empty( $data['cs-group-five']['test'] ) ) {
			$test = sanitize_email( wp_unslash( $data['cs-group-five']['test'] ) );

			$ty_message            = empty( $options['ty_message'] ) ? '' : $options['ty_message'];
			$cs_data               = new stdClass();
			$cs_data->author       = esc_html__( 'Author', 'comments-subscriber' );
			$cs_data->link         = get_option( 'home' );
			$cs_data->comment_link = get_option( 'home' );
			$cs_data->title        = esc_html__( 'The post title', 'comments-subscriber' );
			$cs_data->content      = esc_html__( 'This is a long comment. Be a yardstick of quality. Some people are not used to an environment where excellence is expected.', 'comments-subscriber' );
			$message               = $this->notify->replace_placeholders( $ty_message, $cs_data );
			$subject               = $options['ty_subject'];
			$subject               = str_replace(
				array( '{title}', '{author}' ),
				array(
					$cs_data->title,
					$cs_data->author,
				),
				$subject
			);
			$this->notify->email_send( $test, $subject, $message );
		}
	}
}

add_action( 'plugins_loaded', array( Settings_Tab_Five::class, 'get_instance' ) );
