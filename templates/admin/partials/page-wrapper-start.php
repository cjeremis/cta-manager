<?php
/**
 * Page Wrapper Start
 *
 * Unified template wrapper that provides consistent header, navigation, and container structure
 * across all CTA Manager admin pages. Enables multi-plugin compatibility.
 *
 * @package CTAManager
 * @since 1.0.0
 *
 * Required Variables:
 * @var string $current_page Current page identifier for navigation highlighting (e.g., '', 'analytics', 'settings', 'cta', 'tools')
 * @var string $header_title Page title displayed in header
 * @var string $header_description Page description displayed below title
 *
 * Optional Variables:
 * @var array $topbar_actions Array of HTML strings for action buttons in topbar (default: [])
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Set defaults for optional variables
$topbar_actions = $topbar_actions ?? [];

?>
<div class="cta-admin-container">
	<?php
	$support_url = apply_filters( 'cta_support_url', home_url( '/cta-manager-pro/' ) );
	?>
	<div class="cta-support-toolbar">
		<?php if ( CTA_Onboarding::should_show() ) : ?>
			<a class="cta-support-link cta-help-trigger" href="#cta-dashboard-help-modal" data-target="#cta-dashboard-help-modal" title="<?php esc_attr_e( 'Get Started', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-lightbulb cta-glow-warm-slow"></span>
			</a>
		<?php endif; ?>
		<a class="cta-support-link" href="#cta-features-modal" data-open-modal="#cta-features-modal" title="<?php esc_attr_e( 'CTA Manager Features', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-awards"></span>
		</a>
		<?php
		// Include notifications button with server-side count calculation
		$count = 0;
		if ( class_exists( 'CTA_Notifications' ) ) {
			$notifications = CTA_Notifications::get_instance()->get_user_notifications();

			// Count notifications excluding pro_api_key_missing (hardcoded CTA, not a notification)
			foreach ( $notifications as $notification ) {
				if ( 'pro_api_key_missing' !== ( $notification['type'] ?? '' ) ) {
					$count++;
				}
			}

			// License CTA is hardcoded and should NOT count as a notification
		}
		include __DIR__ . '/notifications-button.php';
		?>
		<a class="cta-support-link" href="#cta-new-ticket-modal" data-open-modal="#cta-new-ticket-modal" title="<?php esc_attr_e( 'Support', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-phone"></span>
		</a>
		<a class="cta-support-link" href="#cta-docs-modal" data-open-docs-modal title="<?php esc_attr_e( 'Documentation', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-book-alt"></span>
		</a>
		<?php if ( ! ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() ) ) : ?>
			<?php
			$label       = __( 'Upgrade to Pro', 'cta-manager' );
			$url         = '#';
			$variant     = 'ghost';
			$extra_class = 'cta-support-upgrade cta-topbar-upgrade';
			$icon        = 'star-filled';
			$extra_attrs = '';
			include __DIR__ . '/pro-upgrade-button.php';
			unset( $label, $url, $variant, $extra_class, $icon, $extra_attrs );
			?>
		<?php endif; ?>
	</div>
	<?php
	// Include navigation topbar
	include __DIR__ . '/topbar.php';
	?>

	<?php
	// Include page header
	include __DIR__ . '/header.php';
	// Content starts here (provided by including template)
	?>

