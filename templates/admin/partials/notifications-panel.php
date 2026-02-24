<?php
/**
 * Admin Partial Template - Notifications Panel
 *
 * Handles markup rendering for the notifications panel admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if Pro is fully enabled (installed, active, AND licensed)
$is_pro_enabled = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

// Check if Pro is active but missing license key (show hardcoded license CTA)
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

// Get initial notifications server-side for instant load
$notifications_db = CTA_Notifications::get_instance();
$notifications    = $notifications_db->get_user_notifications();

// Filter out pro_api_key_missing from DB since it's now hardcoded
$notifications = array_filter( $notifications, function( $notification ) {
	return ( $notification['type'] ?? '' ) !== 'pro_api_key_missing';
} );
$notifications = array_values( $notifications );

// License CTA is NOT counted as a notification (it's hardcoded, not a real notification)
$count = count( $notifications );
?>

<div class="cta-notifications-panel" id="cta-notifications-panel" data-initial-count="<?php echo esc_attr( $count ); ?>">
	<!-- Panel Header -->
	<div class="cta-notifications-header">
		<h3 class="cta-notifications-title">
			<span class="dashicons dashicons-megaphone"></span>
			<?php esc_html_e( 'Notifications', 'cta-manager' ); ?>
			<?php if ( $count > 0 ) : ?>
				<span class="cta-notifications-count"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</h3>
		<button class="cta-notifications-close" id="cta-notifications-close" type="button">
			<span class="dashicons dashicons-no-alt"></span>
		</button>
	</div>

	<!-- Panel Body - Initial content rendered server-side -->
	<div class="cta-notifications-body">
		<?php if ( $show_license_cta ) : ?>
			<!-- Hardcoded License CTA - Pro active but missing license key -->
			<!-- This is NOT a notification, just a persistent call-to-action -->
			<div class="cta-notification-item cta-notification-item--license-cta" data-notification-id="license-cta" data-notification-type="pro_api_key_missing">
				<div class="cta-notification-license-glow"></div>
				<div class="cta-notification-license-header">
					<div class="cta-notification-license-icon">
						<span class="dashicons dashicons-star-filled cta-animate-slow"></span>
					</div>
					<div class="cta-notification-license-text">
						<h4 class="cta-notification-license-title"><?php esc_html_e( 'Pro Plugin Installed', 'cta-manager' ); ?></h4>
						<?php cta_pro_badge_inline(); ?>
					</div>
				</div>
				<p class="cta-notification-license-message"><?php esc_html_e( 'Enter your CTA Manager Pro license key to unlock all premium features.', 'cta-manager' ); ?></p>
				<div class="cta-notification-license-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-button cta-pro-upgrade-button cta-pro-upgrade-button--primary cta-button-primary cta-add-api-key-button" data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key">
						<span class="dashicons dashicons-unlock"></span>
						<span class="cta-pro-upgrade-button__label"><?php esc_html_e( 'Add License Key', 'cta-manager' ); ?></span>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<ul class="cta-notifications-list" id="cta-notifications-list"<?php echo ( empty( $notifications ) ) ? ' style="display:none;"' : ''; ?>>
			<?php if ( ! empty( $notifications ) ) : ?>
				<?php foreach ( $notifications as $notification ) :
					$notification_id = $notification['id'] ?? 0;
					$icon            = $notification['icon'] ?? 'info';
					$title           = $notification['title'] ?? '';
					$message         = $notification['message'] ?? '';
					$type            = $notification['type'] ?? '';
					$actions         = ! empty( $notification['actions'] ) ? json_decode( $notification['actions'], true ) : [];
					$is_deletable    = CTA_Notifications_Manager::is_notification_deletable( $type );
				?>
					<li class="cta-notification-item" data-notification-id="<?php echo esc_attr( $notification_id ); ?>" data-notification-type="<?php echo esc_attr( $type ); ?>">
						<div class="cta-notification-content">
							<div class="cta-notification-icon cta-notification-icon--<?php echo esc_attr( $icon ); ?>">
								<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
							</div>
							<div class="cta-notification-text">
								<h4 class="cta-notification-title"><?php echo esc_html( $title ); ?></h4>
								<p class="cta-notification-message"><?php echo esc_html( $message ); ?></p>
								<?php if ( ! empty( $actions ) ) : ?>
									<div class="cta-notification-actions">
										<?php foreach ( $actions as $action ) :
											$action_class = 'cta-button sm outline';
											if ( ! empty( $action['class'] ) ) {
												$action_class .= ' ' . $action['class'];
											}
											$data_attrs = '';
											if ( ! empty( $action['scrollTo'] ) ) {
												$data_attrs .= ' data-scroll-to="' . esc_attr( $action['scrollTo'] ) . '"';
											}
											if ( ! empty( $action['focusField'] ) ) {
												$data_attrs .= ' data-focus-field="' . esc_attr( $action['focusField'] ) . '"';
											}
										?>
											<a href="<?php echo esc_url( $action['url'] ?? '#' ); ?>" class="<?php echo esc_attr( $action_class ); ?>"<?php echo $data_attrs; ?>>
												<?php echo esc_html( $action['label'] ?? 'Action' ); ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php if ( $is_deletable ) : ?>
							<button class="cta-notification-delete" type="button" data-notification-id="<?php echo esc_attr( $notification_id ); ?>" title="<?php esc_attr_e( 'Dismiss notification', 'cta-manager' ); ?>">
								<span class="dashicons dashicons-no-alt"></span>
							</button>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>

		<!-- Checking for new notifications message -->
		<div class="cta-notifications-loading" id="cta-notifications-loading" style="display: none;">
			<span class="dashicons dashicons-update"></span>
			<p><?php esc_html_e( 'Checking for new notifications...', 'cta-manager' ); ?></p>
		</div>

		<!-- Empty state - shows when there are no real notifications -->
		<div class="cta-notifications-empty" id="cta-notifications-empty"<?php echo ( ! empty( $notifications ) ) ? ' style="display:none;"' : ''; ?>>
			<span class="dashicons dashicons-megaphone"></span>
			<p><?php esc_html_e( 'No notifications', 'cta-manager' ); ?></p>
		</div>
	</div>

	<!-- Panel Footer -->
	<div class="cta-notifications-footer">
		<p class="cta-notifications-info">
			<?php esc_html_e( 'Notifications from TopDevAmerica', 'cta-manager' ); ?>
		</p>
	</div>
</div>

<!-- Overlay for panel -->
<div class="cta-notifications-overlay" id="cta-notifications-overlay"></div>
