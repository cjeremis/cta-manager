<?php
/**
 * Notifications Manager
 *
 * Handles internal notification triggers and notification lifecycle events.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Notifications_Manager {

	use CTA_Singleton;

	/**
	 * List of notification types that cannot be dismissed by the user
	 * Note: License CTA is now hardcoded in the panel template, not stored in DB
	 */
	const UNDELETABLE_NOTIFICATIONS = [];

	/**
	 * Initialize the notifications manager
	 */
	private function __construct() {
		// Notifications are triggered from the activator and admin settings
	}

	/**
	 * Check if a notification type is deletable
	 *
	 * @param string $type Notification type
	 *
	 * @return bool True if deletable, false if undeletable
	 */
	public static function is_notification_deletable( string $type ): bool {
		return ! in_array( $type, self::UNDELETABLE_NOTIFICATIONS, true );
	}

	/**
	 * Add activation notification
	 *
	 * Called when the plugin is activated to notify the user about Pro features.
	 * This should be called on each activation to ensure the notification exists.
	 *
	 * @return void
	 */
	public function add_activation_notification(): void {
		$notifications_db = CTA_Notifications::get_instance();
		$user_id          = get_current_user_id();

		if ( ! $user_id ) {
			return;
		}

		// Determine notification based on Pro status
		$pro_plugin_file = 'cta-manager-pro/cta-manager-pro.php';
		$pro_plugin_path = WP_PLUGIN_DIR . '/' . $pro_plugin_file;
		$is_pro_installed = file_exists( $pro_plugin_path );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$is_pro_active = is_plugin_active( $pro_plugin_file );

		// Always remove the old activation notification first
		$table = CTA_Notifications::get_table_name();
		CTA_Database::delete(
			$table,
			[
				'user_id' => $user_id,
				'type'    => 'plugin_activated',
			],
			[ '%d', '%s' ]
		);

		if ( ! $is_pro_installed ) {
			// Pro not installed - show upgrade prompt with modal trigger
			$notifications_db->add_notification(
				'plugin_activated',
				__( 'Upgrade to Pro', 'cta-manager' ),
				__( 'Unlock advanced features with CTA Manager Pro. Get advanced analytics, scheduling, and more.', 'cta-manager' ),
				'star-filled',
				[
					[
						'label' => __( 'Upgrade to Pro', 'cta-manager' ),
						'url'   => '#',
						'class' => 'cta-upgrade-button',
					],
				],
				$user_id
			);
		} elseif ( ! $is_pro_active ) {
			// Pro installed but not active - show activate button with AJAX
			$notifications_db->add_notification(
				'plugin_activated',
				__( 'CTA Manager Pro Installed', 'cta-manager' ),
				__( 'CTA Manager Pro is installed. Activate the plugin to enter your license key and unlock Pro features.', 'cta-manager' ),
				'admin-plugins',
				[
					[
						'label' => __( 'Activate Plugin', 'cta-manager' ),
						'url'   => '#',
						'class' => 'cta-activate-pro-notification',
					],
				],
				$user_id
			);
		}
		// Note: License CTA notification is now hardcoded in the panel template
	}

	/**
	 * Add Pro license key notification
	 *
	 * Called when Pro plugin is activated to notify about adding license key.
	 *
	 * @return void
	 */
	public function add_pro_activated_notification(): void {
		$notifications_db = CTA_Notifications::get_instance();
		$user_id          = get_current_user_id();

		if ( ! $user_id ) {
			return;
		}

		// Remove any existing Pro activated notification
		$table = CTA_Notifications::get_table_name();
		CTA_Database::delete(
			$table,
			[
				'user_id' => $user_id,
				'type'    => 'pro_activated',
			],
			[ '%d', '%s' ]
		);

		$notifications_db->add_notification(
			'pro_activated',
			__( 'CTA Manager Pro Activated!', 'cta-manager' ),
			__( 'Enter your license key to unlock advanced features and analytics.', 'cta-manager' ),
			'unlock',
			[
				[
					'label'      => __( 'Add License Key', 'cta-manager' ),
					'url'        => admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ),
					'class'      => 'cta-add-api-key-button',
					'scrollTo'   => 'cta-pro-license-key',
					'focusField' => 'cta_pro_license_key',
				],
			],
			$user_id
		);
	}

	/**
	 * Add Pro license key validated notification
	 *
	 * Called when Pro license key is successfully validated.
	 * Adds a congratulations notification (deletable by user).
	 * Note: The license CTA is hardcoded in the panel template and will
	 * automatically hide when Pro is fully enabled.
	 *
	 * @return void
	 */
	public function add_pro_api_key_validated_notification(): void {
		$notifications_db = CTA_Notifications::get_instance();
		$user_id          = get_current_user_id();

		if ( ! $user_id ) {
			return;
		}

		$table = CTA_Notifications::get_table_name();

		// Remove any existing Pro key validation notifications
		CTA_Database::delete(
			$table,
			[
				'user_id' => $user_id,
				'type'    => 'pro_api_key_validated',
			],
			[ '%d', '%s' ]
		);

		// Add the congratulations notification (this one is deletable)
		$notifications_db->add_notification(
			'pro_api_key_validated',
			__( 'Congratulations!', 'cta-manager' ),
			__( 'Your CTA Manager Pro is now fully activated. Explore premium features to maximize your CTAs.', 'cta-manager' ),
			'star-filled',
			[
				[
					'label' => __( 'Go to CTA Manager Pro', 'cta-manager' ),
					'url'   => admin_url( 'admin.php?page=cta-manager-pro' ),
				],
			],
			$user_id
		);
	}
}
