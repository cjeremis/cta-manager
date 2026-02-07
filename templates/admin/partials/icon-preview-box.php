<?php
/**
 * Icon Preview Box Partial
 *
 * Displays a preview box for custom icons.
 *
 * Variables:
 * - $preview_id (required) - Unique ID for the preview container
 * - $label (optional) - Label text (default: 'Preview')
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label    = $label ?? __( 'Preview', 'cta-manager' );
$group_id = $group_id ?? $preview_id . '-group';
?>
<div class="cta-form-group" id="<?php echo esc_attr( $group_id ); ?>" style="display: none;">
	<label><?php echo esc_html( $label ); ?></label>
	<div id="<?php echo esc_attr( $preview_id ); ?>" class="cta-icon-preview-box"></div>
</div>
