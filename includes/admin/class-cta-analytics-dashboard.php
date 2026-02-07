<?php
/**
 * Analytics Dashboard page
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Analytics_Dashboard {

	use CTA_Singleton;

	/**
	 * Render analytics dashboard page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$data      = CTA_Data::get_instance();
		$ctas      = $data->get_ctas();
		$settings  = $data->get_settings();
		$analytics = $data->get_analytics();
		$is_pro    = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		$retention_days = $data->get_reporting_retention_days();
		$retention_start_date = $data->get_reporting_start_date();

		$cta_list = [];
		foreach ( $ctas as $cta ) {
			$cta_list[] = [
				'id'      => $cta['id'] ?? 0,
				'name'    => $cta['name'] ?? $cta['button_text'] ?? __( 'Unnamed CTA', 'cta-manager' ),
				'type'    => $cta['type'] ?? 'phone',
				'color'   => $cta['color'] ?? '#3b82f6',
				'is_demo' => ! empty( $cta['_demo'] ),
			];
		}

		$nonce = wp_create_nonce( 'cta_analytics_nonce' );

		// Check if any analytics data exists to render empty states immediately
		$has_analytics_data = $this->has_analytics_data();

		include CTA_PLUGIN_DIR . 'templates/admin/analytics.php';
	}

	/**
	 * Check if any analytics data exists in the database
	 *
	 * @return bool True if events exist, false otherwise
	 */
	private function has_analytics_data(): bool {
		$data = CTA_Data::get_instance();
		return $data->has_analytics_data_for_reporting();
	}
}
