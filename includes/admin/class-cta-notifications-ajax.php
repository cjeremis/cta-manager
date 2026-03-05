<?php
/**
 * Notifications AJAX Handler
 *
 * Handles AJAX requests for notifications (fetch, delete, etc.)
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Notifications_AJAX {

	use CTA_Singleton;

	/**
	 * Check whether Pro live support reply sync is enabled.
	 *
	 * @return bool
	 */
	private function is_live_sync_enabled(): bool {
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( ! $is_pro ) {
			return false;
		}

		$settings = CTA_Data::get_instance()->get_settings();
		if ( isset( $settings['support']['live_notifications_enabled'] ) ) {
			return (bool) $settings['support']['live_notifications_enabled'];
		}

		return true;
	}

	/**
	 * Add one local release/update notification per version.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	private function maybe_add_version_notification( int $user_id ): void {
		$meta_key     = 'cta_last_seen_plugin_version';
		$seen_version = (string) get_user_meta( $user_id, $meta_key, true );

		if ( $seen_version === CTA_VERSION ) {
			return;
		}

		$privacy_page_id = (int) get_option( 'tda_shared_privacy_page_id' );
		$terms_page_id   = (int) get_option( 'tda_shared_terms_page_id' );
		$privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : '';
		$terms_url       = $terms_page_id ? get_permalink( $terms_page_id ) : '';
		$actions         = [];

		if ( $privacy_url ) {
			$actions[] = [ 'label' => __( 'Privacy Policy', 'cta-manager' ), 'url' => $privacy_url ];
		}
		if ( $terms_url ) {
			$actions[] = [ 'label' => __( 'Terms', 'cta-manager' ), 'url' => $terms_url ];
		}

		CTA_Notifications::get_instance()->add_notification(
			'plugin_update_' . sanitize_key( str_replace( '.', '_', CTA_VERSION ) ),
			sprintf( __( 'CTA Manager Updated to %s', 'cta-manager' ), CTA_VERSION ),
			__( 'New release notes and policy disclosures are available in your settings.', 'cta-manager' ),
			'update',
			$actions,
			$user_id
		);

		update_user_meta( $user_id, $meta_key, CTA_VERSION );
	}

	/**
	 * Get user notifications
	 *
	 * AJAX handler for fetching user notifications.
	 * Includes whether each notification is deletable.
	 *
	 * @return void
	 */
	public function ajax_get_notifications(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$user_id = get_current_user_id();
		$this->maybe_add_version_notification( $user_id );

		// Reply sync is Pro-only and user-controllable via settings.
		if ( $this->is_live_sync_enabled()
			&& class_exists( 'CTA_Pro_Extended_Support' )
			&& class_exists( 'CTA_Pro_Feature_Gate' )
			&& CTA_Pro_Feature_Gate::is_pro_enabled()
		) {
			CTA_Pro_Extended_Support::get_instance()->sync_notifications_for_user( get_current_user_id() );
		}

		$notifications_db = CTA_Notifications::get_instance();
		$notifications    = $notifications_db->get_user_notifications();

		// Format notifications for JSON response
		$formatted_notifications = [];

		foreach ( $notifications as $notification ) {
			// Skip pro_api_key_missing from DB since it's now hardcoded
			if ( 'pro_api_key_missing' === $notification['type'] ) {
				continue;
			}

			// CTA_Database::query() returns ARRAY_A format
			$formatted_notifications[] = [
				'id'         => $notification['id'],
				'type'       => $notification['type'],
				'title'      => $notification['title'],
				'message'    => $notification['message'],
				'icon'       => $notification['icon'],
				'actions'    => $notification['actions'] ? json_decode( $notification['actions'], true ) : [],
				'deletable'  => CTA_Notifications_Manager::is_notification_deletable( $notification['type'] ),
			];
		}

		wp_send_json_success(
			[
				'notifications' => $formatted_notifications,
				'count'         => count( $formatted_notifications ),
			]
		);
	}

	/**
	 * Delete a notification
	 *
	 * AJAX handler for dismissing a notification.
	 * Prevents deletion of undeletable notification types.
	 *
	 * @return void
	 */
	public function ajax_delete_notification(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedInput.InputNotSanitized
		$notification_id = isset( $_POST['notification_id'] ) ? (int) wp_unslash( $_POST['notification_id'] ) : 0;

		if ( ! $notification_id ) {
			wp_send_json_error( [ 'message' => 'Invalid notification ID' ], 400 );
		}

		// Verify the notification belongs to the current user and get its type
		$table = CTA_Notifications::get_table_name();
		$notification = CTA_Database::get_row(
			$table,
			[
				'id'      => $notification_id,
				'user_id' => get_current_user_id(),
			]
		);

		if ( ! $notification ) {
			wp_send_json_error( [ 'message' => 'Notification not found' ], 404 );
		}

		// Convert array to object for backward compatibility
		$notification = (object) $notification;

		// Check if notification is deletable
		if ( ! CTA_Notifications_Manager::is_notification_deletable( $notification->type ) ) {
			wp_send_json_error( [ 'message' => 'This notification cannot be deleted' ], 403 );
		}

		$notifications_db = CTA_Notifications::get_instance();
		$result           = $notifications_db->delete_notification( $notification_id );

		if ( $result ) {
			$count = $notifications_db->get_notification_count();
			wp_send_json_success( [ 'count' => $count ] );
		} else {
			wp_send_json_error( [ 'message' => 'Failed to delete notification' ], 500 );
		}
	}

	/**
	 * Get notification count
	 *
	 * AJAX handler for getting the current notification count.
	 *
	 * @return void
	 */
	public function ajax_get_notification_count(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$user_id = get_current_user_id();
		$this->maybe_add_version_notification( $user_id );

		if ( $this->is_live_sync_enabled()
			&& class_exists( 'CTA_Pro_Extended_Support' )
			&& class_exists( 'CTA_Pro_Feature_Gate' )
			&& CTA_Pro_Feature_Gate::is_pro_enabled()
		) {
			CTA_Pro_Extended_Support::get_instance()->sync_notifications_for_user( get_current_user_id() );
		}

		$notifications_db = CTA_Notifications::get_instance();
		$notifications    = $notifications_db->get_user_notifications();

		// Count notifications excluding pro_api_key_missing (which is now hardcoded)
		// License CTA is NOT counted as a notification
		$count = 0;
		foreach ( $notifications as $notification ) {
			if ( 'pro_api_key_missing' !== $notification['type'] ) {
				$count++;
			}
		}

		wp_send_json_success( [ 'count' => $count ] );
	}

	/**
	 * Mark notification as read
	 *
	 * AJAX handler for marking a notification as read.
	 *
	 * @return void
	 */
	public function ajax_mark_read(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedInput.InputNotSanitized
		$notification_id = isset( $_POST['notification_id'] ) ? (int) wp_unslash( $_POST['notification_id'] ) : 0;

		if ( ! $notification_id ) {
			wp_send_json_error( [ 'message' => 'Invalid notification ID' ], 400 );
		}

		$user_id = get_current_user_id();

		// Verify the notification belongs to the current user
		$table        = CTA_Notifications::get_table_name();
		$notification = CTA_Database::get_row(
			$table,
			[
				'id'      => $notification_id,
				'user_id' => $user_id,
			]
		);

		if ( ! $notification ) {
			wp_send_json_error( [ 'message' => 'Notification not found' ], 404 );
		}

		// Get current read notifications array
		$read_meta = get_user_meta( $user_id, 'cta_notifications_read', true );
		$read_meta = is_array( $read_meta ) ? $read_meta : [];

		// Add notification ID if not already in array
		if ( ! in_array( $notification_id, $read_meta, true ) ) {
			$read_meta[] = $notification_id;
			update_user_meta( $user_id, 'cta_notifications_read', $read_meta );
		}

		wp_send_json_success( [ 'message' => 'Notification marked as read' ] );
	}

	/**
	 * Mark notification as unread
	 *
	 * AJAX handler for marking a notification as unread.
	 *
	 * @return void
	 */
	public function ajax_mark_unread(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		if ( ! check_ajax_referer( 'cta_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedInput.InputNotSanitized
		$notification_id = isset( $_POST['notification_id'] ) ? (int) wp_unslash( $_POST['notification_id'] ) : 0;

		if ( ! $notification_id ) {
			wp_send_json_error( [ 'message' => 'Invalid notification ID' ], 400 );
		}

		$user_id = get_current_user_id();

		// Verify the notification belongs to the current user
		$table        = CTA_Notifications::get_table_name();
		$notification = CTA_Database::get_row(
			$table,
			[
				'id'      => $notification_id,
				'user_id' => $user_id,
			]
		);

		if ( ! $notification ) {
			wp_send_json_error( [ 'message' => 'Notification not found' ], 404 );
		}

		// Get current read notifications array
		$read_meta = get_user_meta( $user_id, 'cta_notifications_read', true );
		$read_meta = is_array( $read_meta ) ? $read_meta : [];

		// Remove notification ID from array
		$read_meta = array_diff( $read_meta, [ $notification_id ] );
		update_user_meta( $user_id, 'cta_notifications_read', array_values( $read_meta ) );

		wp_send_json_success( [ 'message' => 'Notification marked as unread' ] );
	}
}
