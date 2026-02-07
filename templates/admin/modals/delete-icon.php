<?php
/**
 * Delete icon confirmation modal body
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'Are you sure you want to delete this icon?', 'cta-manager' ); ?></p>
<p class="cta-confirm-icon-name" style="font-weight: 600; margin-top: var(--cta-spacing-sm);"></p>
<p class="cta-warning-text" style="color: var(--cta-color-danger); margin-top: var(--cta-spacing-md);">
	<span class="dashicons dashicons-warning"></span>
	<?php esc_html_e( 'This action cannot be undone. CTAs using this icon will fall back to "None".', 'cta-manager' ); ?>
</p>
<input type="hidden" id="cta-delete-icon-id" value="" />

<!-- MODAL_FOOTER -->
<button type="button" class="cta-button-secondary" data-close-modal><?php esc_html_e( 'Cancel', 'cta-manager' ); ?></button>
<?php
$button_text  = __( 'Delete Icon', 'cta-manager' );
$button_class = 'cta-button-danger';
$button_id    = 'cta-confirm-delete-icon-btn';
$button_type  = 'button';
include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-spinner.php';
?>
