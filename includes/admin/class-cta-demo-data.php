<?php
/**
 * Demo Data Import/Delete handler
 *
 * @package CTAManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Demo_Data {

	use CTA_Singleton;

	/**
	 * Load demo data file
	 *
	 * @return array|null Decoded demo data or null if not found/invalid
	 */
	private function load_demo_data() {
		$demo_file = CTA_PLUGIN_DIR . 'data/demo-data.json';

		if ( ! file_exists( $demo_file ) ) {
			return null;
		}

		$demo_contents = file_get_contents( $demo_file );
		$demo_data     = json_decode( $demo_contents, true );

		return null === $demo_data ? null : $demo_data;
	}

	/**
	 * Check if demo data file exists
	 *
	 * @return bool
	 */
	public function demo_data_exists(): bool {
		$demo_file = CTA_PLUGIN_DIR . 'data/demo-data.json';
		return file_exists( $demo_file );
	}

	/**
	 * Handle demo data import request
	 *
	 * @return void
	 */
	public function handle_import_demo_data(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_admin_nonce', 'nonce' ) ) {
			wp_safe_redirect( add_query_arg( 'message', 'invalid_nonce', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$scope          = isset( $_POST['import_scope'] ) ? sanitize_text_field( wp_unslash( $_POST['import_scope'] ) ) : 'all';
		$allowed_scopes = [ 'all', 'ctas', 'analytics', 'settings' ];
		if ( ! in_array( $scope, $allowed_scopes, true ) ) {
			$scope = 'all';
		}

		$demo_data = $this->load_demo_data();

		if ( null === $demo_data ) {
			wp_safe_redirect( add_query_arg( 'message', 'demo_file_missing', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$import_data = [];

		if ( in_array( $scope, [ 'all', 'ctas' ], true ) && isset( $demo_data['ctas'] ) ) {
			$ctas_with_flag = [];
			foreach ( $demo_data['ctas'] as $cta ) {
				$cta['_demo']     = true;
				$ctas_with_flag[] = $cta;
			}
			$import_data['ctas'] = $ctas_with_flag;
		}

		if ( in_array( $scope, [ 'all', 'analytics' ], true ) && isset( $demo_data['analytics'] ) ) {
			$import_data['analytics'] = $demo_data['analytics'];
		}

		$errors = CTA_Validator::validate_import_data( $import_data );
		if ( ! empty( $errors ) ) {
			wp_safe_redirect( add_query_arg( 'message', 'demo_file_invalid', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		// Ensure database tables exist
		CTA_Database::maybe_create_tables();

		$data   = CTA_Data::get_instance();
		$result = $data->import_all( $import_data, true, false );

		if ( ! $result ) {
			wp_safe_redirect( add_query_arg( 'message', 'import_failed', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		wp_safe_redirect(
			add_query_arg(
				'message',
				'demo_imported',
				CTA_Admin_Menu::get_admin_url( 'tools' )
			)
		);
		exit;
	}

	/**
	 * Handle delete demo data request
	 *
	 * Deletes demo CTAs, analytics, notifications, and restores original settings.
	 *
	 * @return void
	 */
	public function handle_delete_demo_data(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_admin_nonce', 'nonce' ) ) {
			wp_safe_redirect( add_query_arg( 'message', 'invalid_nonce', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$scope          = isset( $_POST['delete_scope'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_scope'] ) ) : 'all';
		$allowed_scopes = [ 'all', 'ctas', 'analytics', 'notifications', 'settings' ];
		if ( ! in_array( $scope, $allowed_scopes, true ) ) {
			$scope = 'all';
		}

		$data     = CTA_Data::get_instance();
		$all_ctas = $data->get_ctas();
		$result   = true;

		$demo_cta_ids = [];
		foreach ( $all_ctas as $cta ) {
			if ( isset( $cta['_demo'] ) && true === $cta['_demo'] ) {
				$demo_cta_ids[] = (int) $cta['id'];
			}
		}

		if ( in_array( $scope, [ 'all', 'ctas' ], true ) ) {
			$repo = CTA_Repository::get_instance();
			$repo->delete_demo_ctas();
			$result = true;
		}

		if ( in_array( $scope, [ 'all', 'analytics' ], true ) ) {
			// Delete analytics events from the events table for demo CTAs
			if ( ! empty( $demo_cta_ids ) ) {
				$events_repo = CTA_Events_Repository::get_instance();
				if ( $events_repo->table_exists() ) {
					$events_repo->delete_by_cta_ids( $demo_cta_ids );
				}
			}
		}

		// Delete demo notifications (types prefixed with 'demo_')
		if ( in_array( $scope, [ 'all', 'notifications' ], true ) ) {
			if ( class_exists( 'CTA_Notifications' ) ) {
				$notifications = CTA_Notifications::get_instance();
				$notifications->delete_demo_notifications();
			}
		}

		// Restore original settings from backup
		if ( in_array( $scope, [ 'all', 'settings' ], true ) ) {
			$this->restore_settings_from_backup();
		}

		$message = ( $result || empty( $demo_cta_ids ) ) ? 'demo_deleted' : 'delete_failed';

		wp_safe_redirect(
			add_query_arg(
				'message',
				$message,
				CTA_Admin_Menu::get_admin_url( 'tools' )
			)
		);
		exit;
	}

	/**
	 * AJAX handler for selective demo data import
	 *
	 * Imports demo data based on user-selected toggles (settings, ctas, analytics, notifications).
	 * Automatically selects free or pro tier data based on active plugin version.
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public function ajax_import_demo_data_selective(): void {
		// Verify nonce
		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'cta-manager' ) ], 403 );
		}

		// Check capability
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized access.', 'cta-manager' ) ], 403 );
		}

		// Get toggle states from POST
		$import_settings      = isset( $_POST['import_settings'] ) && filter_var( $_POST['import_settings'], FILTER_VALIDATE_BOOLEAN );
		$import_ctas          = isset( $_POST['import_ctas'] ) && filter_var( $_POST['import_ctas'], FILTER_VALIDATE_BOOLEAN );
		$import_analytics     = isset( $_POST['import_analytics'] ) && filter_var( $_POST['import_analytics'], FILTER_VALIDATE_BOOLEAN );
		$import_notifications = isset( $_POST['import_notifications'] ) && filter_var( $_POST['import_notifications'], FILTER_VALIDATE_BOOLEAN );

		// Validate at least one option is selected
		if ( ! $import_settings && ! $import_ctas && ! $import_analytics && ! $import_notifications ) {
			wp_send_json_error( [ 'message' => __( 'Please select at least one data type to import.', 'cta-manager' ) ], 400 );
		}

		$demo_data = $this->load_demo_data();
		if ( null === $demo_data ) {
			wp_send_json_error( [ 'message' => __( 'Demo data file not found.', 'cta-manager' ) ], 404 );
		}

		// Determine if Pro is active
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		// Ensure database tables exist (use direct table creation, not full activate)
		CTA_Database::maybe_create_tables();

		// Track import counts
		$counts = [
			'settings'      => 0,
			'ctas'          => 0,
			'analytics'     => 0,
			'notifications' => 0,
		];

		// Import settings if enabled
		if ( $import_settings && isset( $demo_data['settings'] ) ) {
			$counts['settings'] = $this->import_demo_settings( $demo_data['settings'], $is_pro );
		}

		// Import CTAs if enabled
		if ( $import_ctas && isset( $demo_data['ctas'] ) ) {
			$counts['ctas'] = $this->import_demo_ctas( $demo_data['ctas'], $is_pro );
		}

		// Import analytics if enabled
		if ( $import_analytics && isset( $demo_data['analytics'] ) ) {
			$counts['analytics'] = $this->import_demo_analytics( $demo_data['analytics'], $is_pro );
		}

		// Import notifications if enabled
		if ( $import_notifications && isset( $demo_data['notifications'] ) ) {
			$counts['notifications'] = $this->import_demo_notifications( $demo_data['notifications'] );
		}

		// Build success message
		$parts = [];
		if ( $counts['settings'] > 0 ) {
			$parts[] = sprintf( _n( '%d setting', '%d settings', $counts['settings'], 'cta-manager' ), $counts['settings'] );
		}
		if ( $counts['ctas'] > 0 ) {
			$parts[] = sprintf( _n( '%d CTA', '%d CTAs', $counts['ctas'], 'cta-manager' ), $counts['ctas'] );
		}
		if ( $counts['analytics'] > 0 ) {
			$parts[] = sprintf( _n( '%d analytics event', '%d analytics events', $counts['analytics'], 'cta-manager' ), $counts['analytics'] );
		}
		if ( $counts['notifications'] > 0 ) {
			$parts[] = sprintf( _n( '%d notification', '%d notifications', $counts['notifications'], 'cta-manager' ), $counts['notifications'] );
		}

		$message = ! empty( $parts )
			? sprintf( __( 'Import complete: %s imported.', 'cta-manager' ), implode( ', ', $parts ) )
			: __( 'No data was imported.', 'cta-manager' );

		wp_send_json_success( [
			'message' => $message,
			'counts'  => $counts,
		] );
	}

	/**
	 * Settings key for storing settings backup before demo import
	 */
	private const SETTINGS_BACKUP_KEY = 'demo_settings_backup';

	/**
	 * Import settings from demo data
	 *
	 * Backs up current settings before importing demo settings to allow
	 * restoration when demo data is deleted.
	 *
	 * @since 1.3.0
	 *
	 * @param array $settings_data Settings data with free and pro sections
	 * @param bool  $is_pro        Whether Pro version is active
	 *
	 * @return int 1 on success, 0 on failure
	 */
	private function import_demo_settings( array $settings_data, bool $is_pro ): int {
		// Select appropriate tier
		$tier_key = $is_pro ? 'pro' : 'free';

		if ( ! isset( $settings_data[ $tier_key ] ) || ! is_array( $settings_data[ $tier_key ] ) ) {
			return 0;
		}

		$settings_to_import = $settings_data[ $tier_key ];

		// Sanitize settings using the nested sanitizer
		$sanitized = CTA_Sanitizer::sanitize_settings_nested( $settings_to_import );

		if ( empty( $sanitized ) ) {
			return 0;
		}

		// Backup current settings before importing demo data
		$this->backup_settings_before_demo();

		// Use CTA_Data to replace settings (not merge) for consistent demo state
		$data   = CTA_Data::get_instance();
		$result = $data->update_settings( $sanitized, true );

		return $result ? 1 : 0;
	}

	/**
	 * Backup current settings before demo import
	 *
	 * Only creates a backup if one doesn't already exist to preserve
	 * the original settings across multiple demo imports.
	 *
	 * @since 1.3.0
	 *
	 * @return bool True on success or if backup already exists
	 */
	private function backup_settings_before_demo(): bool {
		$repo = CTA_Settings_Repository::get_instance();

		// Don't overwrite existing backup
		if ( $repo->exists( self::SETTINGS_BACKUP_KEY ) ) {
			return true;
		}

		$data             = CTA_Data::get_instance();
		$current_settings = $data->get_settings();

		return $repo->set(
			self::SETTINGS_BACKUP_KEY,
			$current_settings,
			CTA_Settings_Repository::GROUP_BACKUP,
			false
		);
	}

	/**
	 * Restore settings from backup after demo data deletion
	 *
	 * @since 1.3.0
	 *
	 * @return bool True on success, false if no backup exists or restore failed
	 */
	private function restore_settings_from_backup(): bool {
		$repo   = CTA_Settings_Repository::get_instance();
		$backup = $repo->get( self::SETTINGS_BACKUP_KEY );

		if ( null === $backup || ! is_array( $backup ) ) {
			return false;
		}

		$data   = CTA_Data::get_instance();
		$result = $data->update_settings( $backup );

		if ( $result ) {
			$repo->delete( self::SETTINGS_BACKUP_KEY );
		}

		return $result;
	}

	/**
	 * Import notifications from demo data
	 *
	 * Demo notifications are prefixed with 'demo_' in their type to allow
	 * easy identification and deletion later.
	 *
	 * @since 1.3.0
	 *
	 * @param array $notifications_data Array of notification objects
	 *
	 * @return int Count of notifications added
	 */
	private function import_demo_notifications( array $notifications_data ): int {
		if ( ! class_exists( 'CTA_Notifications' ) ) {
			return 0;
		}

		$notifications = CTA_Notifications::get_instance();
		$count         = 0;

		foreach ( $notifications_data as $notification ) {
			// Validate required fields
			if ( empty( $notification['type'] ) || empty( $notification['title'] ) || empty( $notification['message'] ) ) {
				continue;
			}

			// Prefix type with 'demo_' for identification during deletion
			$original_type = sanitize_text_field( $notification['type'] );
			$type          = 'demo_' . $original_type;
			$title         = sanitize_text_field( $notification['title'] );
			$message       = sanitize_text_field( $notification['message'] );
			$icon          = isset( $notification['icon'] ) ? sanitize_text_field( $notification['icon'] ) : 'info';
			$actions       = isset( $notification['actions'] ) && is_array( $notification['actions'] ) ? $notification['actions'] : [];

			// Sanitize actions
			$sanitized_actions = [];
			foreach ( $actions as $action ) {
				if ( isset( $action['label'] ) ) {
					$sanitized_actions[] = [
						'label' => sanitize_text_field( $action['label'] ),
						'url'   => isset( $action['url'] ) ? esc_url_raw( $action['url'] ) : '',
					];
				}
			}

			// Check if notification already exists to avoid duplicates
			if ( $notifications->notification_exists( $type ) ) {
				continue;
			}

			// Add the notification
			$result = $notifications->add_notification( $type, $title, $message, $icon, $sanitized_actions );

			if ( $result ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Import CTAs from demo data
	 *
	 * @since 1.3.0
	 *
	 * @param array $ctas_data CTAs data with free and pro sections
	 * @param bool  $is_pro    Whether Pro version is active
	 *
	 * @return int Count of CTAs imported
	 */
	private function import_demo_ctas( array $ctas_data, bool $is_pro ): int {
		// Select appropriate tier
		$tier_key = $is_pro ? 'pro' : 'free';

		if ( ! isset( $ctas_data[ $tier_key ] ) || ! is_array( $ctas_data[ $tier_key ] ) ) {
			return 0;
		}

		$ctas_to_import = $ctas_data[ $tier_key ];
		$data           = CTA_Data::get_instance();
		$count          = 0;

		foreach ( $ctas_to_import as $cta ) {
			// Remove original ID to create new entry
			unset( $cta['id'] );

			// Mark as demo data
			$cta['_demo'] = true;

			// Sanitize key fields
			if ( isset( $cta['type'] ) ) {
				$cta['type'] = CTA_Sanitizer::sanitize_type( $cta['type'] );
			}
			if ( isset( $cta['layout'] ) ) {
				$cta['layout'] = CTA_Sanitizer::sanitize_layout( $cta['layout'] );
			}
			if ( isset( $cta['visibility'] ) ) {
				$cta['visibility'] = CTA_Sanitizer::sanitize_visibility( $cta['visibility'] );
			}
			if ( isset( $cta['name'] ) ) {
				$cta['name'] = sanitize_text_field( $cta['name'] );
			}
			if ( isset( $cta['phone_number'] ) ) {
				$cta['phone_number'] = CTA_Sanitizer::sanitize_phone_number( $cta['phone_number'] );
			}
			if ( isset( $cta['email_to'] ) ) {
				$cta['email_to'] = sanitize_email( $cta['email_to'] );
			}
			if ( isset( $cta['link_url'] ) ) {
				$cta['link_url'] = esc_url_raw( $cta['link_url'] );
			}

			// Create the CTA
			$new_id = $data->create_cta( $cta );

			if ( $new_id ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Import analytics events from demo data
	 *
	 * @since 1.3.0
	 *
	 * @param array $analytics_data Analytics data with free and pro sections
	 * @param bool  $is_pro         Whether Pro version is active
	 *
	 * @return int Count of events imported
	 */
	private function import_demo_analytics( array $analytics_data, bool $is_pro ): int {
		// Select appropriate tier
		$tier_key = $is_pro ? 'pro' : 'free';

		if ( ! isset( $analytics_data[ $tier_key ]['events'] ) || ! is_array( $analytics_data[ $tier_key ]['events'] ) ) {
			return 0;
		}

		$events_to_import = $analytics_data[ $tier_key ]['events'];

		// Check if events table exists
		if ( ! class_exists( 'CTA_Events_Repository' ) ) {
			return 0;
		}

		$events_repo = CTA_Events_Repository::get_instance();
		if ( ! $events_repo->table_exists() ) {
			return 0;
		}

		// Get current retention setting to filter events
		$data           = CTA_Data::get_instance();
		$settings       = $data->get_settings();
		$retention      = $settings['analytics']['retention'] ?? '7';
		$retention_days = $data->get_retention_days( $retention, $settings, $is_pro );

		// Calculate cutoff date
		$cutoff_timestamp = strtotime( '-' . $retention_days . ' days' );

		// Prepare events for bulk insert
		$events_for_insert = [];

		foreach ( $events_to_import as $event ) {
			// Parse relative datetime to MySQL format
			$occurred_at = $this->parse_relative_datetime( $event['occurred_at'] ?? '' );
			$event_timestamp = strtotime( $occurred_at );

			// Skip events outside retention window
			if ( $event_timestamp < $cutoff_timestamp ) {
				continue;
			}

			// Build page URL (convert relative to absolute if needed)
			$page_url = $event['page_url'] ?? '';
			if ( $page_url && strpos( $page_url, 'http' ) !== 0 ) {
				$page_url = home_url( $page_url );
			}

			$events_for_insert[] = [
				'event_type'  => sanitize_text_field( $event['event_type'] ?? 'impression' ),
				'cta_id'      => absint( $event['cta_id'] ?? 0 ),
				'cta_title'   => sanitize_text_field( $event['cta_title'] ?? '' ),
				'page_url'    => esc_url_raw( $page_url ),
				'page_title'  => sanitize_text_field( $event['page_title'] ?? '' ),
				'referrer'    => esc_url_raw( $event['referrer'] ?? '' ),
				'device'      => sanitize_text_field( $event['device'] ?? 'desktop' ),
				'occurred_at' => $occurred_at,
			];
		}

		// Bulk insert events
		if ( empty( $events_for_insert ) ) {
			return 0;
		}

		return $events_repo->bulk_insert( $events_for_insert );
	}

	/**
	 * Parse relative datetime string to MySQL datetime format
	 *
	 * Supports formats like:
	 * - "-6 days 09:15:00" (relative to today)
	 * - "today 07:00:00" (today at specific time)
	 * - "2026-01-08 12:00:00" (absolute datetime)
	 *
	 * @since 1.3.0
	 *
	 * @param string $datetime_str Datetime string to parse
	 *
	 * @return string MySQL datetime format (Y-m-d H:i:s)
	 */
	private function parse_relative_datetime( string $datetime_str ): string {
		$datetime_str = trim( $datetime_str );

		if ( empty( $datetime_str ) ) {
			return current_time( 'mysql' );
		}

		// If it's already a valid datetime, return it
		if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime_str ) ) {
			return $datetime_str;
		}

		// Parse relative formats: "-6 days 09:15:00"
		if ( preg_match( '/^(-?\d+\s+days?)\s+(\d{2}:\d{2}:\d{2})$/i', $datetime_str, $matches ) ) {
			$date_part = gmdate( 'Y-m-d', strtotime( $matches[1] ) );
			return $date_part . ' ' . $matches[2];
		}

		// Parse "today 07:00:00" format
		if ( preg_match( '/^today\s+(\d{2}:\d{2}:\d{2})$/i', $datetime_str, $matches ) ) {
			return gmdate( 'Y-m-d' ) . ' ' . $matches[1];
		}

		// Try to parse with strtotime as fallback
		$timestamp = strtotime( $datetime_str );
		if ( $timestamp !== false ) {
			return gmdate( 'Y-m-d H:i:s', $timestamp );
		}

		// Default to current time if parsing fails
		return current_time( 'mysql' );
	}
}
