<?php
/**
 * Tools page handler
 *
 * @package CTAManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Tools {

	use CTA_Singleton;

	/**
	 * Render the tools admin page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$data             = CTA_Data::get_instance();
		$backup           = $data->get_backup() ?? [];
		$current_settings = $data->export_all();
		$export_timestamp = current_time( 'mysql' );

		include CTA_PLUGIN_DIR . 'templates/admin/tools.php';
	}

	/**
	 * Handle debug mode toggle submission.
	 *
	 * @return void
	 */
	public function handle_debug_toggle(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_debug_toggle', 'cta_debug_nonce' ) ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'invalid_nonce' ], CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$enabled = isset( $_POST['cta_debug_enabled'] ) ? (bool) wp_unslash( $_POST['cta_debug_enabled'] ) : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$data = CTA_Data::get_instance();
		$data->update_settings( [
			'debug' => [
				'enabled' => $enabled,
			],
		] );

		// Clear debug cache so new state takes effect immediately
		CTA_Debug::clear_cache();

		$message = $enabled ? 'debug_enabled' : 'debug_disabled';
		wp_safe_redirect( add_query_arg( [ 'message' => $message ], CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
		exit;
	}

	/**
	 * Handle debug mode toggle via AJAX.
	 *
	 * @return void
	 */
	public function ajax_toggle_debug(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized', 'cta-manager' ) ], 403 );
		}

		check_ajax_referer( 'cta_debug_toggle', 'nonce' );

		$enabled = isset( $_POST['enabled'] ) ? (bool) wp_unslash( $_POST['enabled'] ) : false;
		$enabled = filter_var( $enabled, FILTER_VALIDATE_BOOLEAN );

		$data = CTA_Data::get_instance();
		$data->update_settings( [
			'debug' => [
				'enabled' => $enabled,
			],
		] );

		// Clear debug cache so new state takes effect immediately
		CTA_Debug::clear_cache();

		// Log the state change (will only log if enabled is true)
		if ( $enabled ) {
			CTA_Debug::info( 'Debug mode enabled by user', 'settings' );
		}

		wp_send_json_success( [
			'enabled' => $enabled,
		] );
	}
}
