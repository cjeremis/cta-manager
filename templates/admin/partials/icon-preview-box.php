<?php
/**
 * Admin Partial Template - Icon Preview Box
 *
 * Handles markup rendering for the icon preview box admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
