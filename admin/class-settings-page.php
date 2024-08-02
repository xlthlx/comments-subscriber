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
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_menu', array( $this, 'page_init' ) );
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
			__( 'Subscriber Settings', 'comments-subscriber' ),
			'manage_options',
			'comments-subscriber-settings',
			array( $this, 'create_settings_page' )
		);

	}

	/**
	 * Settings page callback.
	 */
	public function create_settings_page() {

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
		$action      = 'options.php';
		?>
		<form method="post" action="<?php echo esc_attr( $action ); ?>">
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
				settings_fields( 'comments-subscriber-settings-' . $current_tab . '-settings' );
				do_settings_sections( 'comments-subscriber-settings-' . $current_tab );

				echo '<p class="submit">';
				if ( 'tab6' === $current_tab ) {
					submit_button( __( 'Remove', 'comments-subscriber' ), 'primary large', 'remove_email', false, array( 'id' => 'remove_email' ) );
					echo '</p>';
					echo '<form method="post">';
					wp_nonce_field( 'remove' );
					$list = ( new Subscribers_List() )::get_instance();
					$list->show_subscribers_list();
					echo '</form>';
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
					echo '</p>';
				}
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
}

add_action( 'plugins_loaded', array( \Comments\Subscriber\Settings_Page::class, 'get_instance' ) );
