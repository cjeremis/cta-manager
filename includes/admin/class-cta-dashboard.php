<?php
/**
 * Main dashboard page
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Dashboard {

	use CTA_Singleton;

	/**
	 * Render dashboard page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$data = CTA_Data::get_instance();
		$analytics = $data->get_analytics();
		$settings = $data->get_settings();
		$ctas = $data->get_ctas();
		$stats = $data->get_dashboard_stats();
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		include CTA_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}
}
