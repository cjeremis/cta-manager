<?php
/**
 * Status Badge Partial
 *
 * Displays a colored badge for status indicators (no icons).
 *
 * Variables:
 * - $variant (required) - Badge style: 'primary', 'secondary', 'success', 'warning', 'danger', 'inactive'
 * - $text (required) - Badge text content
 * - $pulse_class (optional) - Pulse animation class (e.g., 'cta-pulse-warning')
 * - $extra_styles (optional) - Additional inline styles
 * - $extra_attrs (optional) - Additional HTML attributes
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pulse_class  = $pulse_class ?? '';
$extra_styles = $extra_styles ?? '';
$extra_attrs  = $extra_attrs ?? '';
?>
<span class="cta-badge cta-badge-<?php echo esc_attr( $variant ); ?><?php echo $pulse_class ? ' ' . esc_attr( $pulse_class ) : ''; ?>" <?php echo $extra_styles ? 'style="' . esc_attr( $extra_styles ) . '"' : ''; ?> <?php echo $extra_attrs ? $extra_attrs : ''; ?>><?php echo esc_html( $text ); ?></span>
