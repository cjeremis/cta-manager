<?php
/**
 * Uninstall handler - removes all plugin data
 *
 * Respects the user's data_management.delete_on_uninstall preference.
 * When enabled, truncates and drops all CTA Manager and CTA Manager Pro tables.
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! current_user_can( 'delete_plugins' ) ) {
	exit;
}

global $wpdb;

/**
 * All CTA Manager table names (without prefix)
 * Includes both free and pro tables for complete cleanup
 */
$cta_tables = array(
	'cta_manager',
	'cta_manager_events',
	'cta_manager_notifications',
	'cta_manager_settings',
	'cta_manager_ab_tests',
	'cta_manager_ab_events',
);

/**
 * Read the delete_on_uninstall setting from the settings table
 *
 * Settings are stored in cta_manager_settings table with nested JSON structure:
 * - setting_key: 'general'
 * - value_json: { "data_management": { "delete_on_uninstall": true/false } }
 *
 * @return bool Whether to delete all data on uninstall (default: false for safety)
 */
function cta_get_delete_on_uninstall_setting(): bool {
	global $wpdb;

	$table = $wpdb->prefix . 'cta_manager_settings';

	// Check if table exists
	$table_exists = $wpdb->get_var(
		$wpdb->prepare( 'SHOW TABLES LIKE %s', $table )
	);

	if ( ! $table_exists ) {
		// Table doesn't exist, default to false (preserve data)
		return false;
	}

	// Read the general settings
	$result = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT value_json FROM {$table} WHERE setting_key = %s LIMIT 1",
			'general'
		)
	);

	if ( ! $result ) {
		return false;
	}

	$settings = json_decode( $result, true );

	if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $settings ) ) {
		return false;
	}

	// Check nested path: data_management.delete_on_uninstall
	if ( isset( $settings['data_management']['delete_on_uninstall'] ) ) {
		return (bool) $settings['data_management']['delete_on_uninstall'];
	}

	// Default to false if setting not found (preserve data by default)
	return false;
}

/**
 * Truncate a table if it exists
 *
 * @param string $table Full table name with prefix.
 * @return bool
 */
function cta_truncate_table( string $table ): bool {
	global $wpdb;

	// Check if table exists first
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return true; // Table doesn't exist, nothing to truncate
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "TRUNCATE TABLE {$table}" );
	return $result !== false;
}

/**
 * Drop a table if it exists
 *
 * @param string $table Full table name with prefix.
 * @return bool
 */
function cta_drop_table( string $table ): bool {
	global $wpdb;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	return $result !== false;
}

// Get user's preference for data deletion
$delete_data = cta_get_delete_on_uninstall_setting();

// Only delete data if the setting is enabled
if ( $delete_data ) {
	// Step 1: Truncate all CTA Manager tables (clears all data)
	foreach ( $cta_tables as $table_name ) {
		$full_table_name = $wpdb->prefix . $table_name;
		cta_truncate_table( $full_table_name );
	}

	// Step 2: Drop all CTA Manager tables (removes table structure)
	foreach ( $cta_tables as $table_name ) {
		$full_table_name = $wpdb->prefix . $table_name;
		cta_drop_table( $full_table_name );
	}
}

// Always clean up transients and scheduled hooks (regardless of data deletion setting)
delete_transient( 'cta_click_cache' );
delete_transient( 'cta_manager_license_check' );
delete_transient( 'cta_manager_pro_license_check' );

// Clear all CTA Manager scheduled hooks
wp_clear_scheduled_hook( 'cta_cleanup_analytics' );
wp_clear_scheduled_hook( 'cta_daily_analytics_cleanup' );
wp_clear_scheduled_hook( 'cta_manager_daily_cleanup' );
wp_clear_scheduled_hook( 'cta_pro_daily_cleanup' );

// Clean up any wp_options entries (if any were created)
delete_option( 'cta_manager_version' );
delete_option( 'cta_manager_pro_version' );
delete_option( 'cta_manager_db_version' );
delete_option( 'cta_manager_pro_db_version' );
delete_option( 'cta_manager_activated' );
delete_option( 'cta_manager_pro_activated' );
