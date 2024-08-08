<?php
/**
 * Settings Tab One.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Tab_One.
 */
class Settings_Tab_One {
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
	 * @return Settings_Tab_One|null
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
		$page_slug      = 'comments-subscriber-settings-tab1';
		$section        = 'section_one';
		$section_second = 'section_one_second';
		$group          = 'cs-group-one';

		add_settings_section(
			$section,
			__( 'Subscription Checkbox Settings', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' =>
					__( 'Options for the "Notify me" subscription checkbox in the comment form.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[checkbox]',
			__( 'Enable The Checkbox', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name'   => 'checkbox',
				'group'  => $group,
				'desc'   => __( 'Check this to add the "Notify me" subscription checkbox to the comment form.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[label]',
			__( 'Checkbox Label', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_text_field',
			),
			$page_slug,
			$section,
			array(
				'name'   => 'label',
				'group'  => $group,
				'desc'   => __( 'Label to be displayed near the subscription checkbox.', 'comments-subscriber' ),
			)
		);

		add_settings_field(
			$group . '[checked]',
			__( 'Checkbox Default Status', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_checkbox_field',
			),
			$page_slug,
			$section,
			array(
				'name'   => 'checked',
				'group'  => $group,
				'desc'   => __( 'Check here if you want the "Notify me" subscription checkbox to be checked by default.', 'comments-subscriber' ),
			)
		);

		add_settings_section(
			$section_second,
			__( 'Theme Compatibility', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_section',
			),
			$page_slug,
			array(
				'desc' => '',
			)
		);

		add_settings_field(
			$group . '[theme_compat]',
			__( 'Show Checkbox After The Comment Form', 'comments-subscriber' ),
			array(
				$this->fields,
				'add_checkbox_field',
			),
			$page_slug,
			$section_second,
			array(
				'name'   => 'theme_compat',
				'group'  => $group,
				'desc'   => __( 'If the checkbox is not appearing on your comment form, enable this option. <br/>Enabling this option will make the checkbox work on a larger variety of independent themes (themes that do not use standard WordPress comment form filters). <br/>This will add the checkbox <strong>below</strong> the comment form submit button. <br/>Check this to add the "Notify me" subscription checkbox to the comment form.', 'comments-subscriber' ),
			)
		);
	}
}

add_action( 'plugins_loaded', array( Settings_Tab_One::class, 'get_instance' ) );
