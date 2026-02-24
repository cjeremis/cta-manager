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

		// Sync remote support replies into local notifications before returning
		if ( class_exists( 'CTA_Support_AJAX' ) ) {
			CTA_Support_AJAX::get_instance()->sync_notifications_for_user( get_current_user_id() );
		}

		$notifications_db = CTA_Notifications::get_instance();
		$notifications    = $notifications_db->get_user_notifications();

		// Check if Pro is fully enabled (installed, active, AND licensed)
		$is_pro_enabled = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		// Check if we should show the hardcoded license CTA
		$show_license_cta = false;
		if ( ! $is_pro_enabled ) {
			$pro_plugin_file  = 'cta-manager-pro/cta-manager-pro.php';
			$pro_plugin_path  = WP_PLUGIN_DIR . '/' . $pro_plugin_file;
			$is_pro_installed = file_exists( $pro_plugin_path );

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$is_pro_active = $is_pro_installed && is_plugin_active( $pro_plugin_file );

			// Show license CTA if Pro is active but not fully enabled (missing license)
			$show_license_cta = $is_pro_active;
		}

		// Format notifications for JSON response
		$formatted_notifications = [];

		// Add hardcoded license CTA first if needed
		if ( $show_license_cta ) {
			$formatted_notifications[] = [
				'id'        => 'license-cta',
				'type'      => 'pro_api_key_missing',
				'title'     => __( 'Pro Plugin Installed', 'cta-manager' ),
				'message'   => __( 'Enter your CTA Manager Pro license key to unlock all premium features.', 'cta-manager' ),
				'icon'      => 'star-filled',
				'actions'   => [
					[
						'label'      => __( 'Add License Key', 'cta-manager' ),
						'url'        => admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ),
						'class'      => 'cta-add-api-key-button',
						'scrollTo'   => 'cta-pro-license-key',
						'focusField' => 'cta_pro_license_key',
					],
				],
				'deletable' => false,
			];
		}

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

		// Count only database notifications (don't count the license CTA)
		$count = count( $notifications );
		foreach ( $notifications as $notification ) {
			if ( 'pro_api_key_missing' === $notification['type'] ) {
				$count--;
			}
		}

		wp_send_json_success(
			[
				'notifications' => $formatted_notifications,
				'count'         => $count,
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

		if ( class_exists( 'CTA_Support_AJAX' ) ) {
			CTA_Support_AJAX::get_instance()->sync_notifications_for_user( get_current_user_id() );
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
