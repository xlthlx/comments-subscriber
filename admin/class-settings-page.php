<?php
/**
 * Settings page.
 *
 * @package comments_subscriber
 */

namespace Comments\Subscriber;

/**
 * Class Settings_Page.
 */
class Settings_Page {
	/**
	 * A static reference to track the single instance of this class.
	 *
	 * @var object
	 */
	private static $_instance;
	/**
	 * Reference to Settings_Tab_Five class.
	 *
	 * @var object
	 */
	private $tab_five;
	/**
	 * Reference to Settings_Tab_Six class.
	 *
	 * @var object
	 */
	private $tab_six;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_menu', array( $this, 'page_init' ) );
		$this->tab_five = ( new Settings_Tab_Five() )::get_instance();
		$this->tab_six  = ( new Settings_Tab_Six() )::get_instance();
	}

	/**
	 * Method used to provide a single instance of this class.
	 *
	 * @return Settings_Page|null
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add settings page.
	 */
	public function add_plugin_page() {

		add_options_page(
			__( 'Comments Subscriber Settings', 'comments-subscriber' ),
			__( 'Comments Settings', 'comments-subscriber' ),
			'manage_options',
			'comments-subscriber-settings',
			array( $this, 'create_settings_page' )
		);

	}

	/**
	 * Settings page callback.
	 */
	public function create_settings_page() {

		if ( ! empty( $_POST['_update-form'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_update-form'] ) ), 'update-form' ) ) {

			if ( isset( $_POST['cs-group-five'] ) ) {
				$this->tab_five->send_test_email( $_POST );
			}

			if ( isset( $_POST['cs-group-six'] ) ) {
				$this->tab_six->remove_ids( $_POST );
				$this->tab_six->remove_email( $_POST );
			}

			foreach ( CS_OPTIONS as $option ) {
				if ( isset( $_POST[ $option ] ) ) {
					update_option( $option, $this->sanitize_settings( array_map( 'wp_kses_post', wp_unslash( $_POST[ $option ] ) ) ) );
				}
			}

			add_settings_error( 'cs-messages', 'cs-message', __( 'Settings saved.', 'comments-subscriber' ), 'updated' );
		}

		settings_errors( 'cs-messages' );

		?>
		<div class="wrap">
		<h1 style="margin-bottom: 9px;"><?php echo esc_attr( get_admin_page_title() ); ?></h1>
		<?php
		$tabs        = array(
			'tab1' => __( 'Checkbox', 'comments-subscriber' ),
			'tab2' => __( 'Notification Email', 'comments-subscriber' ),
			'tab3' => __( 'Thank You Email', 'comments-subscriber' ),
			'tab4' => __( 'Unsubscribe', 'comments-subscriber' ),
			'tab5' => __( 'Advanced', 'comments-subscriber' ),
			'tab6' => __( 'Subscribers list', 'comments-subscriber' ),
		);
		$current_tab = isset( $_GET['tab'], $tabs[ $_GET['tab'] ] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : array_key_first( $tabs );
		?>
		<form method="post">
			<nav class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab => $name ) {
					$current = $tab === $current_tab ? ' nav-tab-active' : '';
					$url     = add_query_arg(
						array(
							'page' => 'comments-subscriber-settings',
							'tab'  => $tab,
						),
						''
					);
					echo '<a class="nav-tab' . esc_attr( $current ) . '" href="' . esc_url( $url ) . '">' . esc_attr( $name ) . '</a>';
				}
				?>
			</nav>
			<div class="wrap" style="margin-top: 30px;">
				<?php
				wp_nonce_field( 'update-form', '_update-form' );
				settings_fields( 'comments-subscriber-settings-' . $current_tab . '-settings' );
				do_settings_sections( 'comments-subscriber-settings-' . $current_tab );

				echo '<p class="submit">';
				if ( 'tab6' === $current_tab ) {
					submit_button( __( 'Remove', 'comments-subscriber' ), 'primary large', 'remove_email', false, array( 'id' => 'remove_email' ) );

					$list = ( new Subscribers_List() )::get_instance();
					if ( $list ) {
						$list->show_subscribers_list();
					}
				} else {
					submit_button( __( 'Save', 'comments-subscriber' ), 'primary large', 'save', false, array( 'id' => 'save' ) );

					if ( 'tab5' === $current_tab ) {
						submit_button(
							__( 'Save and send a Thank You test email', 'comments-subscriber' ),
							'secondary large',
							'savethankyou',
							false,
							array(
								'id'    => 'savethankyou',
								'style' => 'margin-left:10px',
							)
						);
					}
				}

				echo '</p>';
				?>
			</div>
		</form>
		<?php
	}

	/**
	 * Register and add settings.
	 *
	 * @return void
	 */
	public function page_init() {

		$tab_one = ( new Settings_Tab_One() )::get_instance();
		if ( $tab_one ) {
			$tab_one->add_tab();
		}

		$tab_two = ( new Settings_Tab_Two() )::get_instance();
		if ( $tab_two ) {
			$tab_two->add_tab();
		}

		$tab_three = ( new Settings_Tab_Three() )::get_instance();
		if ( $tab_three ) {
			$tab_three->add_tab();
		}

		$tab_four = ( new Settings_Tab_Four() )::get_instance();
		if ( $tab_four ) {
			$tab_four->add_tab();
		}

		$tab_five = ( new Settings_Tab_Five() )::get_instance();
		if ( $tab_five ) {
			$tab_five->add_tab();
		}

		$tab_six = ( new Settings_Tab_Six() )::get_instance();
		if ( $tab_six ) {
			$tab_six->add_tab();
		}
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $options The array of options to be saved.
	 *
	 * @return array
	 */
	public function sanitize_settings( $options ) {
		// Integers.
		$int_keys = array(
			'checkbox',
			'checked',
			'ty_enabled',
			'delete_data',
			'theme_compat',
		);
		foreach ( $int_keys as $int_key ) {
			if ( isset( $options[ $int_key ] ) ) {
				$options[ $int_key ] = (int) $options[ $int_key ];
			}
		}
		// Text.
		$text_keys = array(
			'label',
			'name',
			'subject',
			'unsubscribe_url',
			'ty_subject',
			'copy',
		);
		foreach ( $text_keys as $text_key ) {
			if ( isset( $options[ $text_key ] ) ) {
				$options[ $text_key ] = sanitize_text_field( $options[ $text_key ] );
			}
		}
		// Emails.
		if ( isset( $options['from'] ) ) {
			$options['from'] = sanitize_email( $options['from'] );
		}

		if ( isset( $options['test'] ) ) {
			$options['test'] = sanitize_email( $options['test'] );
		}

		// WYSIWYG.
		$text_keys = array(
			'message',
			'ty_message',
			'thankyou',
		);
		foreach ( $text_keys as $text_key ) {
			if ( isset( $options[ $text_key ] ) ) {
				$options[ $text_key ] = wp_kses_post( $options[ $text_key ] );
			}
		}

		return $options;
	}
}

add_action( 'plugins_loaded', array( Settings_Page::class, 'get_instance' ) );
