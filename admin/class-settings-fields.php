<?php
/**
 * Fields settings.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Fields.
 */
class Settings_Fields {
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
	 * @return Settings_Fields|null
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
}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Settings_Fields::class, 'get_instance' ) );
