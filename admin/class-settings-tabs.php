<?php
/**
 * Tabs settings.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tabs.
 */
class Settings_Tabs {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;
	/**
	 * Options.
	 *
	 * @var array
	 */
	public $options;
	/**
	 * Options group.
	 *
	 * @var string
	 */
	public $option_group;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->options      = get_option( 'cs_options' );
		$this->option_group = 'cs_options';
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Settings_Tabs|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add generic section text.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function add_section( $args ) {
		printf(
			'<p>%s</p>',
			esc_attr( $args['desc'] )
		);
	}

	/**
	 * Add generic text field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function add_text_field( $args ) {

		$name  = $args['group'] . '[' . $args['name'] . ']';
		$value = ( isset( get_option( $args['group'] )[ $args['name'] ] ) ) ? esc_attr( get_option( $args['group'] )[ $args['name'] ] ) : '';

		printf(
			'<input type="text" id="%s" name="%s" value="%s" size="50" />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( $value )
		);

		if ( isset( $args['desc'] ) ) {
			$this->show_desc( $args['desc'] );
		}
	}

	/**
	 * Add small text field.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function add_small_text_field( $args ) {

		$name  = $args['group'] . '[' . $args['name'] . ']';
		$value = ( isset( get_option( $args['group'] )[ $args['name'] ] ) ) ? esc_attr( get_option( $args['group'] )[ $args['name'] ] ) : '';

		printf(
			'<input type="text" id="%s" name="%s" value="%s" size="5" />%s',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( $value ),
			isset( $args['instr'] ) ? ' ' . esc_attr( $args['instr'] ) : ''
		);

		if ( isset( $args['desc'] ) ) {
			$this->show_desc( $args['desc'] );
		}
	}

	/**
	 * Add generic checkbox.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function add_checkbox_field( $args ) {

		$name  = $args['group'] . '[' . $args['name'] . ']';
		$value = ( isset( get_option( $args['group'] )[ $args['name'] ] ) && get_option( $args['group'] )[ $args['name'] ] ) ? 'checked="checked"' : '';

		printf(
			'<input type="checkbox" id="%s" name="%s" value="1" %s />',
			esc_attr( $name ),
			esc_attr( $name ),
			esc_attr( $value )
		);

		if ( isset( $args['desc'] ) ) {
			$this->show_desc( $args['desc'], 'checkbox' );
		}
	}

	/**
	 * Add generic editor.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	public function add_editor( $args ) {

		$name  = $args['group'] . '[' . $args['name'] . ']';
		$value = ( isset( get_option( $args['group'] )[ $args['name'] ] ) ) ? esc_attr( get_option( $args['group'] )[ $args['name'] ] ) : '';

		$editor_args = array(
			'tinymce'       => true,
			'textarea_name' => $name,
			'media_buttons' => false,
			'textarea_rows' => 10,
			'quicktags'     => false,
			'teeny'         => true,
		);
		echo '<style>.wp-editor-container{width: 50%}</style>';
		wp_editor( esc_attr( $value ), esc_attr( $args['name'] ), $editor_args );

		if ( isset( $args['desc'] ) ) {
			$this->show_desc( $args['desc'] );
		}
	}

	/**
	 * Show description for fields.
	 *
	 * @param string $desc The description.
	 * @param string $type The field type.
	 *
	 * @return void
	 */
	public function show_desc( $desc, $type = '' ) {

		$format = '<p>%s</p>';
		if ( ( 'checkbox' === $type ) ) {
			$format = ' %s';
		}

		printf(
			wp_kses( $format, WP_KSES_DEFAULT ),
			wp_kses( $desc, WP_KSES_DEFAULT )
		);
	}

	/**
	 * Add settings for tab 2.
	 *
	 * @return void
	 */
	public function add_tab_two() {
		$page_slug    = 'comments-subscriber-settings-tab2';
		$option_group = 'comments-subscriber-settings-tab2-settings';
		$section      = 'section_two';
		$group        = 'cs-group-two';

		add_settings_section(
			$section,
			__( 'Notification Email Settings', 'comments-subscriber' ),
			array(
				$this,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure the message sent to subscribers to notify them that a new comment was posted.', 'comments-subscriber' ),
			)
		);

		register_setting( $option_group, 'cs-group-two' );

		add_settings_field(
			'cs-group-two[name]',
			__( 'From Name', 'comments-subscriber' ),
			array(
				$this,
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
			'cs-group-two[from]',
			__( 'From Email', 'comments-subscriber' ),
			array(
				$this,
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
			'cs-group-two[subject]',
			__( 'Subject', 'comments-subscriber' ),
			array(
				$this,
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
			'cs-group-two[ty_message]',
			__( 'Message Body', 'comments-subscriber' ),
			array(
				$this,
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
			'cs-group-two[length]',
			__( 'Comment Excerpt Length', 'comments-subscriber' ),
			array(
				$this,
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

	/**
	 * Add settings for tab 3.
	 *
	 * @return void
	 */
	public function add_tab_three() {
		$page_slug    = 'comments-subscriber-settings-tab3';
		$option_group = 'comments-subscriber-settings-tab3-settings';
		$section      = 'section_three';

		add_settings_section(
			$section,
			__( 'Thank You Email Settings', 'comments-subscriber' ),
			array(
				$this,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure a "Thank you" message for first time commentators. Messages are sent when comments are approved.', 'comments-subscriber' ),
			)
		);

		register_setting( $option_group, 'cs-group-three' );

		add_settings_field(
			'options[ty_enabled]',
			__( 'Enable Thank You Message', 'comments-subscriber' ),
			array(
				$this,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'options[ty_enabled]',
				'desc' => __( 'Send a "Thank you" message to visitor on their first comment', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			'options[ty_subject]',
			__( 'Subject', 'comments-subscriber' ),
			array(
				$this,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'options[ty_subject]',
				'desc' => __( 'Tags: <br> {title} - the post title <br> {author} - the commenter name', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			'options[ty_message]',
			__( 'Message Body', 'comments-subscriber' ),
			array(
				$this,
				'add_editor',
			),
			$page_slug,
			$section,
			array(
				'name' => 'ty_message',
				'desc' => __( 'Tags: <br> {title} - the post title <br> {author} - the commenter name <br> {link} - link to the post/page <br> {comment_link} - link to the comment <br> {content} - the comment text', 'comments-subscriber' ),
			)
		);
	}

	/**
	 * Add settings for tab 4.
	 *
	 * @return void
	 */
	public function add_tab_four() {
		$page_slug    = 'comments-subscriber-settings-tab4';
		$option_group = 'comments-subscriber-settings-tab4-settings';
		$section      = 'section_four';

		add_settings_section(
			$section,
			__( 'Unsubscribe Settings', 'comments-subscriber' ),
			array(
				$this,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure what to show to unsubscribing users. You may set an "Unsubscribe Page URL" to send the user to a specific page, or configure a specific message.', 'comments-subscriber' ),
			)
		);

		register_setting( $option_group, 'cs-group-four' );

		add_settings_field(
			'options[unsubscribe_url]',
			__( 'Unsubscribe Page URL', 'comments-subscriber' ),
			array(
				$this,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'options[unsubscribe_url]',
				'desc' => __( 'If you want to create a page with your content to say "ok, you are unsubscribed", enter the URL here. <br/>Otherwise, leave this field blank and the following message will be used.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			'options[thankyou]',
			__( 'Unsubscribe Message', 'comments-subscriber' ),
			array(
				$this,
				'add_editor',
			),
			$page_slug,
			$section,
			array(
				'name' => 'thankyou',
				'desc' => __( 'Example: You have unsubscribed successfully. Thank you. I will send you to the home page in 3 seconds.', 'comments-subscriber' ),
			)
		);

	}

	/**
	 * Add settings for tab 5.
	 *
	 * @return void
	 */
	public function add_tab_five() {
		$page_slug    = 'comments-subscriber-settings-tab5';
		$option_group = 'comments-subscriber-settings-tab5-settings';
		$section      = 'section_five';

		add_settings_section(
			$section,
			__( 'Advanced Settings', 'comments-subscriber' ),
			array(
				$this,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Configure the message sent to subscribers to notify them that a new comment was posted.', 'comments-subscriber' ),
			)
		);

		register_setting( $option_group, 'cs-group-five' );

		add_settings_field(
			'options[test]',
			__( 'Email address where to send test emails', 'comments-subscriber' ),
			array(
				$this,
				'add_text_field',
			),
			$page_slug,
			$section,
			array( 'name' => 'options[test]' )
		);

		add_settings_field(
			'options[copy]',
			__( 'Extra email address where to send a copy of EACH notification', 'comments-subscriber' ),
			array(
				$this,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'options[copy]',
				'desc' => __( 'Leave empty to disable.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			'options[delete_data]',
			__( 'Delete Data on Uninstall', 'comments-subscriber' ),
			array(
				$this,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'options[delete_data]',
				'desc' => __( 'Check this box if you would like this plugin to <strong>delete all</strong> of its data when the plugin is deleted. <br/>This would delete the entire list of subscribers and their subscriptions. This does NOT delete the actual comments.', 'comments-subscriber' ),
			)
		);

	}

	/**
	 * Add settings for tab 6.
	 *
	 * @return void
	 */
	public function add_tab_six() {
		$page_slug    = 'comments-subscriber-settings-tab6';
		$option_group = 'comments-subscriber-settings-tab6-settings';
		$section      = 'section_six';

		add_settings_section(
			$section,
			__( 'Email Management', 'comments-subscriber' ),
			array(
				$this,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Remove a specific email from all subscriptions.', 'comments-subscriber' ),
			)
		);

		register_setting( $option_group, 'cs-group-six' );

		add_settings_field(
			'email',
			__( 'Remove email', 'comments-subscriber' ),
			array(
				$this,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name' => 'email',
				'desc' => __( 'Remove this email from all subscriptions.', 'comments-subscriber' ),
			)
		);

	}

}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Settings_Tabs::class, 'get_instance' ) );
