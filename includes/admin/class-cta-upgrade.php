<?php
/**
 * Upgrade Page Handler
 *
 * Handles rendering for the CTA Manager upgrade page.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Upgrade {

	use CTA_Singleton;

	/**
	 * Render upgrade page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		// Check if Pro is active
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && method_exists( 'CTA_Pro_Feature_Gate', 'is_pro_enabled' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		// Get license expiration if Pro is active
		$license_expires = null;
		if ( $is_pro && class_exists( 'CTA_Pro_License' ) && method_exists( 'CTA_Pro_License', 'get_license_info' ) ) {
			$license_info = CTA_Pro_License::get_license_info();
			if ( isset( $license_info['expires'] ) ) {
				$license_expires = $license_info['expires'];
			}
		}

		// Pro features list
		$pro_features = [
			[
				'icon'        => '📊',
				'title'       => __( 'Advanced Analytics', 'cta-manager' ),
				'description' => __( 'Track button views, clicks, and call conversions with detailed analytics and custom date ranges', 'cta-manager' ),
			],
			[
				'icon'        => '🎨',
				'title'       => __( 'Custom Colors & Styling', 'cta-manager' ),
				'description' => __( 'Customize button colors, fonts, and styles to match your brand perfectly', 'cta-manager' ),
			],
			[
				'icon'        => '⏱️',
				'title'       => __( 'Time-Based Scheduling', 'cta-manager' ),
				'description' => __( 'Show/hide buttons based on time of day, day of week, or specific date ranges', 'cta-manager' ),
			],
			[
				'icon'        => '🌍',
				'title'       => __( 'Multiple Phone Numbers', 'cta-manager' ),
				'description' => __( 'Set different numbers for different pages, categories, or countries with routing rules', 'cta-manager' ),
			],
			[
				'icon'        => '🔊',
				'title'       => __( 'Sound & Notifications', 'cta-manager' ),
				'description' => __( 'Customize button sounds, add attention-grabbing notifications and animations', 'cta-manager' ),
			],
			[
				'icon'        => '🔗',
				'title'       => __( 'Advanced Integrations', 'cta-manager' ),
				'description' => __( 'Integration with CRM platforms, Google Analytics, Facebook Pixel, and more', 'cta-manager' ),
			],
			[
				'icon'        => '📱',
				'title'       => __( 'Advanced Targeting', 'cta-manager' ),
				'description' => __( 'Show CTAs based on user behavior, location, device type, and custom conditions', 'cta-manager' ),
			],
			[
				'icon'        => '🎯',
				'title'       => __( 'A/B Testing', 'cta-manager' ),
				'description' => __( 'Test different button designs, colors, and text to optimize conversion rates', 'cta-manager' ),
			],
			[
				'icon'        => '🔐',
				'title'       => __( 'White Label Options', 'cta-manager' ),
				'description' => __( 'Remove CTA Manager branding and customize with your own agency branding', 'cta-manager' ),
			],
			[
				'icon'        => '⚡',
				'title'       => __( 'Priority Support', 'cta-manager' ),
				'description' => __( 'Get direct access to our support team with priority response times', 'cta-manager' ),
			],
			[
				'icon'        => '🔄',
				'title'       => __( 'Unlimited CTAs', 'cta-manager' ),
				'description' => __( 'Create unlimited call-to-action buttons across all your pages and posts', 'cta-manager' ),
			],
			[
				'icon'        => '💾',
				'title'       => __( 'Extended Analytics Retention', 'cta-manager' ),
				'description' => __( 'Store analytics data for custom time periods or unlimited storage', 'cta-manager' ),
			],
		];

		include CTA_PLUGIN_DIR . 'templates/admin/features.php';
	}
}
