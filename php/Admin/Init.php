<?php
/**
 * Set up add plugin functionality.
 *
 * @package WPPIC
 */

namespace MediaRon\WPPIC\Admin;

use MediaRon\WPPIC\Functions;
use MediaRon\WPPIC\Options;

/**
 * Init admin class for WPPIC.
 */
class Init {

	/**
	 * Holds the URL to the admin panel page
	 *
	 * @since 1.0.0
	 * @static
	 * @var string $url
	 */
	private static $url = '';

	/**
	 * Main constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_wppic_save_options', array( $this, 'ajax_save_options' ) );
		add_action( 'wp_ajax_wppic_reset_options', array( $this, 'ajax_reset_options' ) );
		// Init tabs.
		new Tabs\Main();
		new Tabs\Plugin_Screenshots();
		// new Tabs\Advanced();
		// new Tabs\Appearance();
		// new Tabs\Callbacks();
		// new Tabs\Integrations();
		// new Tabs\Labels();
		// new Tabs\Lazy_Load();
		// //new Tabs\Pagination();
		// new Tabs\Selectors();
		// new Tabs\Support();
	}

	/**
	 * Save options via Ajax.
	 */
	public function ajax_save_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = Options::get_options();
		// Get posted options.
		$posted_options = filter_input( INPUT_POST, 'wppicFormData', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$nonce          = sanitize_text_field( $posted_options['saveNonce'] );

		if ( ! wp_verify_nonce( $nonce, 'wppic-save-options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'wp-plugin-info-card' ) ) );
		}

		// Validate options.
		$validated_options = Functions::sanitize_array_recursive( $posted_options );

		// Save options.
		Options::update_options( $validated_options );

		wp_send_json_success( array( 'message' => __( 'Options saved', 'wp-plugin-info-card' ) ) );
	}

	/**
	 * Reset options via Ajax.
	 */
	public function ajax_reset_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = Options::get_options();
		$nonce   = sanitize_text_field( filter_input( INPUT_POST, 'resetNonce', FILTER_DEFAULT ) );

		if ( ! wp_verify_nonce( $nonce, 'wppic-reset-options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'wp-plugin-info-card' ) ) );
		}

		// Reset options.
		$defaults = Options::get_defaults();
		Options::update_options( $defaults );

		wp_send_json_success( array( 'message' => __( 'Options reset', 'wp-plugin-info-card' ) ) );
	}

	/**
	 * Output admin scripts/styles.
	 */
	public function admin_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen->base ) && 'settings_page_wp-plugin-info-card' === $screen->base ) {
			wp_enqueue_style(
				'wppic-styles-admin',
				Functions::get_plugin_url( 'dist/wppic-admin.css' ),
				array(),
				Functions::get_plugin_version(),
				'all'
			);

			// Get current tab and trigger action.
			$current_tab = Functions::get_admin_tab();
			if ( empty( $current_tab ) ) {
				$current_tab = 'home';
			}
			do_action( 'wppic_admin_enqueue_scripts_' . $current_tab );
		}
	}
}
