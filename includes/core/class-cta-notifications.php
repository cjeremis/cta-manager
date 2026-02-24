<?php
/**
 * Notifications Storage Handler
 *
 * Handles notification storage and notification retrieval operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTA_Database' ) ) {
	require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-database.php';
}

class CTA_Notifications {

	use CTA_Singleton;

	/**
	 * Get the full table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		return CTA_Database::table( CTA_Database::TABLE_NOTIFICATIONS );
	}

	/**
	 * Get notifications for current user
	 *
	 * @return array Array of notification objects
	 */
	public function get_user_notifications(): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$user_id  = get_current_user_id();
		$table    = self::get_table_name();
		$query    = "SELECT * FROM {$table} WHERE user_id = %d AND dismissed = 0 ORDER BY created_at DESC";
		$results  = CTA_Database::query( $query, [ $user_id ] );

		if ( empty( $results ) ) {
			return [];
		}

		$read_meta = get_user_meta( $user_id, 'cta_notifications_read', true );
		$read_meta = is_array( $read_meta ) ? $read_meta : [];

		// Attach is_read flag using user meta (no schema change)
		foreach ( $results as &$row ) {
			$row['is_read'] = in_array( (int) $row['id'], $read_meta, true );
		}
		unset( $row );

		return $results;
	}

	/**
	 * Add a notification
	 *
	 * @param string $type Notification type/identifier
	 * @param string $title Notification title
	 * @param string $message Notification message
	 * @param string $icon Dashicon name (without 'dashicons-' prefix)
	 * @param array  $actions Array of actions with 'label' and 'url' keys
	 * @param int    $user_id Optional user ID (defaults to current user)
	 *
	 * @return int|bool Notification ID on success, false on failure
	 */
	public function add_notification(
		string $type,
		string $title,
		string $message,
		string $icon = 'info',
		array $actions = [],
		int $user_id = 0
	) {
		if ( ! $this->table_exists() ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$table = self::get_table_name();

		$existing_rows = CTA_Database::query(
			"SELECT id FROM {$table} WHERE user_id = %d AND type = %s AND dismissed = 0",
			[ $user_id, $type ]
		);
		$existing = $existing_rows[0]['id'] ?? null;

		if ( $existing ) {
			return $existing; // Return existing ID if notification already exists
		}

		$result = CTA_Database::insert(
			$table,
			[
				'user_id'    => $user_id,
				'type'       => $type,
				'title'      => $title,
				'message'    => $message,
				'icon'       => $icon,
				'actions'    => wp_json_encode( $actions ),
				'dismissed'  => 0,
				'created_at' => current_time( 'mysql' ),
			]
		);

		return $result;
	}

	/**
	 * Delete/dismiss a notification
	 *
	 * @param int $notification_id Notification ID
	 * @param int $user_id Optional user ID for verification
	 *
	 * @return bool True on success, false on failure
	 */
	public function delete_notification( int $notification_id, int $user_id = 0 ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$table = self::get_table_name();

		$result = CTA_Database::update(
			$table,
			[ 'dismissed' => 1 ],
			[
				'id'      => $notification_id,
				'user_id' => $user_id,
			]
		);

		return false !== $result;
	}

	/**
	 * Check if notification type exists for user
	 *
	 * @param string $type Notification type
	 * @param int    $user_id Optional user ID
	 *
	 * @return bool True if notification exists and is not dismissed
	 */
	public function notification_exists( string $type, int $user_id = 0 ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$table  = self::get_table_name();
		$result = CTA_Database::query(
			"SELECT id FROM {$table} WHERE user_id = %d AND type = %s AND dismissed = 0 LIMIT 1",
			[ $user_id, $type ]
		);

		return ! empty( $result );
	}

	/**
	 * Clear all dismissed notifications for a user (cleanup)
	 *
	 * @param int $user_id Optional user ID
	 *
	 * @return int Number of rows deleted
	 */
	public function cleanup_dismissed_notifications( int $user_id = 0 ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$table = self::get_table_name();

		$deleted = CTA_Database::delete(
			$table,
			[
				'user_id'   => $user_id,
				'dismissed' => 1,
			]
		);

		return $deleted !== false ? (int) $deleted : 0;
	}

	/**
	 * Get notification count for current user
	 *
	 * @return int Number of active (non-dismissed) notifications
	 */
	public function get_notification_count(): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$user_id = get_current_user_id();
		$table   = self::get_table_name();
		$count   = CTA_Database::query(
			"SELECT COUNT(*) as total_count FROM {$table} WHERE user_id = %d AND dismissed = 0",
			[ $user_id ]
		);

		return (int) ( $count[0]['total_count'] ?? 0 );
	}

	/**
	 * Delete all demo notifications for a user
	 *
	 * Demo notifications are identified by types prefixed with 'demo_'.
	 *
	 * @param int $user_id Optional user ID (defaults to current user)
	 *
	 * @return int Number of notifications deleted
	 */
	public function delete_demo_notifications( int $user_id = 0 ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return 0;
		}

		global $wpdb;
		$table = self::get_table_name();

		// Delete notifications where type starts with 'demo_'
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE user_id = %d AND type LIKE %s",
				$user_id,
				'demo_%'
			)
		);

		return $deleted !== false ? (int) $deleted : 0;
	}

	/**
	 * Delete all pro_api_key_missing notifications (one-time cleanup).
	 *
	 * This notification type is now hardcoded in the panel and should not exist in the database.
	 *
	 * @return int Number of notifications deleted
	 */
	public function delete_pro_api_key_missing_notifications(): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		global $wpdb;
		$table = self::get_table_name();

		// Delete all pro_api_key_missing notifications across all users
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE type = %s",
				'pro_api_key_missing'
			)
		);

		return $deleted !== false ? (int) $deleted : 0;
	}

	/**
	 * Check whether the notifications table exists.
	 *
	 * @return bool
	 */
	private function table_exists(): bool {
		return CTA_Database::table_exists( self::get_table_name() );
	}
}
