<?php
/**
 * Settings page handler
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Settings {

	use CTA_Singleton;

	/**
	 * Reset settings to defaults
	 *
	 * Clears settings from the settings table.
	 *
	 * @return void
	 */
	public function ajax_reset_settings(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// Clear from settings table if available
		$repo = CTA_Settings_Repository::get_instance();
		$repo->delete( 'general' );

		$data = CTA_Data::get_instance();

		wp_send_json_success( $data->get_settings() );
	}

	/**
	 * Render settings page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$data = CTA_Data::get_instance();
		$settings = $data->get_settings();

		include CTA_PLUGIN_DIR . 'templates/admin/settings.php';
	}

	/**
	 * AJAX handler for saving settings
	 *
	 * Handles section-based nested settings structure with conditional cleanup.
	 *
	 * @return void
	 */
	public function ajax_save_settings(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$raw_settings = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
			? wp_unslash( $_POST['settings'] )
			: [];

		// Sanitize nested structure
		$settings = CTA_Sanitizer::sanitize_settings_nested( $raw_settings );

		// Apply Pro restrictions and conditional cleanup
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		$settings = $this->apply_settings_rules( $settings, $is_pro );

		// Validate
		$errors = CTA_Validator::validate_settings_nested( $settings );
		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ], 400 );
		}

		// Save and return
		$data = CTA_Data::get_instance();
		if ( $data->update_settings( $settings ) ) {
			wp_send_json_success( [
				'message'  => __( 'Settings saved successfully.', 'cta-manager' ),
				'settings' => $data->get_settings(),
			] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to save settings' ], 500 );
		}
	}

	/**
	 * Apply Pro restrictions and conditional cleanup rules
	 *
	 * @param array $settings Settings array
	 * @param bool  $is_pro   Whether user has Pro
	 *
	 * @return array Cleaned settings
	 */
	private function apply_settings_rules( array $settings, bool $is_pro ): array {
		// Pro restrictions
		if ( ! $is_pro ) {
			// Force free tier values for analytics retention
			if ( isset( $settings['analytics'] ) ) {
				$free_retention_values = [ '1', '7' ];
				$current_retention = $settings['analytics']['retention'] ?? '7';

				// If a Pro-only value is selected, reset to default free tier value (1 week)
				if ( ! in_array( $current_retention, $free_retention_values, true ) ) {
					$settings['analytics']['retention'] = '7';
				}

				unset( $settings['analytics']['retention_custom_days'] );
			}
			// Reset Pro-only sections
			$settings['custom_css'] = [ 'css' => '' ];
			$settings['performance'] = [ 'load_scripts_footer' => false ];
			// Force delete on uninstall for free
			if ( isset( $settings['data_management'] ) ) {
				$settings['data_management']['delete_on_uninstall'] = true;
			}
		}

		// Conditional cleanup: if analytics disabled, remove retention fields
		if ( isset( $settings['analytics']['enabled'] ) && ! $settings['analytics']['enabled'] ) {
			unset( $settings['analytics']['retention'] );
			unset( $settings['analytics']['retention_custom_days'] );
		}

		// Conditional cleanup: if retention != 'custom', remove custom_days
		if ( isset( $settings['analytics']['retention'] ) && $settings['analytics']['retention'] !== 'custom' ) {
			unset( $settings['analytics']['retention_custom_days'] );
		}

		return $settings;
	}

	/**
	 * AJAX handler for activating the Pro plugin
	 *
	 * @return void
	 */
	public function ajax_activate_pro_plugin(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( [ 'message' => __( 'You do not have permission to activate plugins.', 'cta-manager' ) ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_activate_pro_plugin', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'cta-manager' ) ], 403 );
		}

		$pro_plugin_file = 'cta-manager-pro/cta-manager-pro.php';
		$pro_plugin_path = WP_PLUGIN_DIR . '/' . $pro_plugin_file;

		// Check if plugin file exists
		if ( ! file_exists( $pro_plugin_path ) ) {
			wp_send_json_error( [ 'message' => __( 'CTA Manager Pro plugin not found.', 'cta-manager' ) ], 404 );
		}

		// Check if already active
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( $pro_plugin_file ) ) {
			wp_send_json_success( [
				'message'  => __( 'CTA Manager Pro is already active.', 'cta-manager' ),
				'redirect' => admin_url( 'admin.php?page=cta-manager-settings' ),
			] );
		}

		// Activate the plugin
		$result = activate_plugin( $pro_plugin_file );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [
				'message' => sprintf(
					/* translators: %s: Error message */
					__( 'Failed to activate plugin: %s', 'cta-manager' ),
					$result->get_error_message()
				),
			], 500 );
		}

		wp_send_json_success( [
			'message'  => __( 'CTA Manager Pro activated successfully!', 'cta-manager' ),
			'redirect' => admin_url( 'admin.php?page=cta-manager-settings' ),
		] );
	}

	/**
	 * AJAX handler for getting all custom icons
	 *
	 * @return void
	 */
	public function ajax_get_custom_icons(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$data = CTA_Data::get_instance();

		wp_send_json_success( [
			'icons' => $data->get_custom_icons(),
		] );
	}

	/**
	 * AJAX handler for adding a custom icon
	 *
	 * @return void
	 */
	public function ajax_add_custom_icon(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// Check for Pro license
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( ! $is_pro ) {
			wp_send_json_error( [
				'message' => __( 'Custom icons require CTA Manager Pro.', 'cta-manager' ),
			], 403 );
		}

		$name = isset( $_POST['icon_name'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_name'] ) ) : '';
		$svg  = isset( $_POST['icon_svg'] ) ? wp_unslash( $_POST['icon_svg'] ) : '';

		if ( empty( $name ) ) {
			wp_send_json_error( [
				'message' => __( 'Icon name is required.', 'cta-manager' ),
			], 400 );
		}

		if ( empty( $svg ) ) {
			wp_send_json_error( [
				'message' => __( 'SVG code is required.', 'cta-manager' ),
			], 400 );
		}

		$data = CTA_Data::get_instance();

		// Validate SVG first
		$sanitized_svg = $data->sanitize_svg( $svg );
		if ( $sanitized_svg === false ) {
			wp_send_json_error( [
				'message' => __( 'Invalid SVG code. Please provide valid SVG markup starting with <svg and ending with </svg>.', 'cta-manager' ),
			], 400 );
		}

		$result = $data->add_custom_icon( $name, $svg );

		if ( $result === false ) {
			wp_send_json_error( [
				'message' => __( 'Failed to add icon. The name may already exist.', 'cta-manager' ),
			], 400 );
		}

		wp_send_json_success( [
			'message' => __( 'Icon added successfully.', 'cta-manager' ),
			'icon'    => $result,
			'icons'   => $data->get_custom_icons(),
		] );
	}

	/**
	 * AJAX handler for deleting a custom icon
	 *
	 * @return void
	 */
	public function ajax_delete_custom_icon(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// Check for Pro license
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( ! $is_pro ) {
			wp_send_json_error( [
				'message' => __( 'Custom icons require CTA Manager Pro.', 'cta-manager' ),
			], 403 );
		}

		$icon_id = isset( $_POST['icon_id'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_id'] ) ) : '';

		if ( empty( $icon_id ) ) {
			wp_send_json_error( [
				'message' => __( 'Icon ID is required.', 'cta-manager' ),
			], 400 );
		}

		$data = CTA_Data::get_instance();

		// Verify icon exists
		$icon = $data->get_custom_icon( $icon_id );
		if ( ! $icon ) {
			wp_send_json_error( [
				'message' => __( 'Icon not found.', 'cta-manager' ),
			], 404 );
		}

		$result = $data->delete_custom_icon( $icon_id );

		if ( ! $result ) {
			wp_send_json_error( [
				'message' => __( 'Failed to delete icon.', 'cta-manager' ),
			], 500 );
		}

		wp_send_json_success( [
			'message' => __( 'Icon deleted successfully.', 'cta-manager' ),
			'icons'   => $data->get_custom_icons(),
		] );
	}
}
