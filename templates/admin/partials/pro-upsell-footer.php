<?php
/**
 * Pro Upsell Footer Partial
 *
 * Reusable Pro upgrade section that appears at the bottom of all CTA Manager pages.
 * Shows different messages based on Pro plugin installation/activation status.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Allow custom state and messages to be passed in
$custom_state   = $custom_state ?? null;
$custom_title   = $custom_title ?? null;
$custom_message = $custom_message ?? null;
$custom_actions = $custom_actions ?? null;

// Check Pro plugin status (only if not using custom state)
if ( ! $custom_state ) {
	$is_pro_enabled = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

	// If Pro is fully enabled (installed, active, AND licensed), don't show upsell
	if ( $is_pro_enabled ) {
		return;
	}

	// Check if Pro plugin file exists (installed but maybe not active)
	$pro_plugin_file = 'cta-manager-pro/cta-manager-pro.php';
	$pro_plugin_path = WP_PLUGIN_DIR . '/' . $pro_plugin_file;
	$is_pro_installed = file_exists( $pro_plugin_path );

	// Check if Pro plugin is active
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$is_pro_active = is_plugin_active( $pro_plugin_file );

	// Determine the status message
	if ( ! $is_pro_installed ) {
		$status_message = __( 'CTA Manager Pro is not installed. Purchase and install the Pro version to unlock advanced features.', 'cta-manager' );
		$status_class = 'cta-pro-status-not-installed';
	} elseif ( ! $is_pro_active ) {
		$status_message = __( 'CTA Manager Pro is installed but not activated. Activate the plugin and enter your license key to unlock Pro features.', 'cta-manager' );
		$status_class = 'cta-pro-status-inactive';
	} else {
		// Pro is active - check if license key is missing
		$has_api_key = ! empty( CTA_Pro_Feature_Gate::get_license_key() );

		if ( ! $has_api_key ) {
			// Pro is active but license key is missing
			$status_message = __( 'CTA Manager Pro is installed and activated. Enter your license key to unlock Pro features.', 'cta-manager' );
			$status_class = 'cta-pro-status-no-api-key';
		} else {
			// Pro is active but not licensed (license key exists but validation failed)
			$status_message = __( 'CTA Manager Pro is active but requires a valid license key to unlock all features.', 'cta-manager' );
			$status_class = 'cta-pro-status-unlicensed';
		}
	}
} else {
	// Use custom state and message
	$status_class   = 'cta-pro-status-' . $custom_state;
	$status_message = $custom_message;
}

$title = $custom_title ?? __( 'CTA Manager Pro', 'cta-manager' );

// Determine button action based on Pro status
$show_add_api_key_button = false;
if ( ! $custom_state && ! $custom_actions ) {
	// Check if we should show "ADD PRO LICENSE KEY" button
	if ( 'cta-pro-status-no-api-key' === $status_class ) {
		$show_add_api_key_button = true;
	}
}
?>
<div class="cta-section cta-pro-upsell cta-pro-upsell-hero <?php echo esc_attr( $status_class ); ?>">
	<div class="cta-pro-upsell-glow"></div>
	<div class="cta-pro-upsell-header">
		<div class="cta-pro-upsell-icon">
			<span class="dashicons dashicons-star-filled cta-animate-slow"></span>
		</div>
		<div class="cta-pro-upsell-text">
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php cta_pro_badge_inline(); ?>
		</div>
	</div>
	<p class="cta-pro-upsell-status"><?php echo esc_html( $status_message ); ?></p>
	<div class="cta-pro-upsell-actions">
		<?php if ( $custom_actions ) : ?>
			<?php echo $custom_actions; // Allow custom HTML for actions ?>
		<?php elseif ( $show_add_api_key_button ) : ?>
			<?php
			$label       = __( 'Add Pro License Key', 'cta-manager' );
			$url         = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
			$variant     = 'primary';
			$extra_class = 'cta-add-api-key-button';
			$icon        = 'unlock';
			$extra_attrs = 'data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key"';
			include __DIR__ . '/pro-upgrade-button.php';
			unset( $label, $url, $variant, $extra_class, $icon, $extra_attrs );
			?>
		<?php else : ?>
			<?php
			$label       = __( 'Upgrade to Pro', 'cta-manager' );
			$url         = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
			$variant     = 'primary';
			$extra_class = 'cta-upgrade-button';
			$icon        = 'star-filled';
			include __DIR__ . '/pro-upgrade-button.php';
			unset( $label, $url, $variant, $extra_class, $icon );
			?>
		<?php endif; ?>
	</div>
</div>
