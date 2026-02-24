<?php
/**
 * Plugin Activation Handler
 *
 * Handles activation tasks and initialization operations for CTA Manager.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_MANAGER_ACTIVATOR {

	/**
	 * Activation routine
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		self::create_database_tables();

		// Schedule analytics cleanup cron job
		self::schedule_analytics_cleanup();

		// Add activation notification
		self::add_activation_notification();

		// Flush rewrite rules to ensure any custom permalink structures
		// or REST API endpoints registered by the plugin are recognized
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables
	 *
	 * Delegates to CTA_Database for actual table creation.
	 *
	 * @return void
	 */
	private static function create_database_tables(): void {
		// Ensure database class is loaded
		if ( ! class_exists( 'CTA_Database' ) ) {
			require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-database.php';
		}

		// Delegate to centralized database manager
		CTA_Database::create_tables();
	}

	/**
	 * Schedule daily analytics cleanup cron job
	 *
	 * @return void
	 */
	private static function schedule_analytics_cleanup(): void {
		if ( ! wp_next_scheduled( 'cta_cleanup_analytics' ) ) {
			wp_schedule_event( time(), 'daily', 'cta_cleanup_analytics' );
		}
	}

	/**
	 * Add activation notification
	 *
	 * Adds appropriate notification based on Pro status when plugin is activated.
	 * Called during plugin activation.
	 *
	 * @return void
	 */
	private static function add_activation_notification(): void {
		// Queue the notification manager to add the activation notification
		// This is done via a hooked action to ensure classes are loaded
		add_action(
			'admin_init',
			function() {
				if ( class_exists( 'CTA_Notifications_Manager' ) ) {
					CTA_Notifications_Manager::get_instance()->add_activation_notification();
				}
			},
			1 // Priority 1 ensures this runs early
		);
	}
}
