<?php
/**
 * Pro-gated inline message + badge link
 *
 * Expected variables:
 * - string $error_id       Required. ID for the error element (controls show/hide).
 * - string $error_message  Required. Message text.
 * - string $badge_id       Required. ID for the badge element (controls show/hide).
 * - string $features_url Optional. Upgrade URL (defaults to Pro features page).
 *
 * @package CTA_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$error_id        = $error_id ?? '';
$error_message   = $error_message ?? '';
$badge_id        = $badge_id ?? '';
$coming_soon_url = $coming_soon_url ?? admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
?>

<div class="cta-pro-gated-meta">
	<?php if ( $error_id && $error_message ) : ?>
		<p
			class="cta-error-text"
			id="<?php echo esc_attr( $error_id ); ?>"
			style="display:none; color:#dc3232; margin-top:8px; font-weight:500; align-items:center; gap:6px;"
		>
			<span class="dashicons dashicons-warning" style="font-size:16px; vertical-align:middle;"></span>
			<?php echo esc_html( $error_message ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $badge_id ) : ?>
		<a
			id="<?php echo esc_attr( $badge_id ); ?>"
			class="cta-pro-badge-link"
			href="<?php echo esc_url( $coming_soon_url ); ?>"
			target="_blank"
			rel="noopener"
			style="display:none;"
		>
			<?php esc_html_e( 'PRO', 'cta-manager' ); ?>
		</a>
	<?php endif; ?>
</div>
