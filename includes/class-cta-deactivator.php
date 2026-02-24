<?php
/**
 * Plugin Deactivation Handler
 *
 * Handles deactivation tasks and cleanup operations for CTA Manager.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Deactivator {

	/**
	 * Deactivation routine
	 *
	 * Cleanup Strategy:
	 * - Removes transient cache data to prevent stale data
	 * - Flushes rewrite rules to remove custom endpoints
	 * - Does NOT delete CTAs, settings, or permanent data
	 * - User data is preserved for reactivation
	 * - For complete removal, use uninstall.php
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Clear cache data
		delete_transient( 'cta_click_cache' );

		// Remove scheduled cron jobs
		$timestamp = wp_next_scheduled( 'cta_cleanup_analytics' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cta_cleanup_analytics' );
		}

		// Remove custom rewrite rules
		flush_rewrite_rules();
	}
}
