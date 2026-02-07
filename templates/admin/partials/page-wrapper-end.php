<?php
/**
 * Page Wrapper End
 *
 * Closes the page wrapper container and includes the footer.
 * Pairs with page-wrapper-start.php to provide consistent structure.
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Close page content and include footer
include __DIR__ . '/footer.php';
?>
</div>

<?php
// Render notifications panel (content populated by AJAX)
if ( class_exists( 'CTA_Notifications' ) ) {
	include __DIR__ . '/notifications-panel.php';
}

// Render Import Demo Modal (globally available for onboarding and tools page)
ob_start();
include CTA_PLUGIN_DIR . 'templates/admin/modals/import-demo-modal.php';
$demo_modal_body_html = ob_get_clean();

ob_start();
?>
<div style="display: flex; justify-content: flex-end; gap: var(--cta-spacing-sm);">
	<button type="button" id="cta-import-demo-submit" class="cta-button cta-button-primary">
		<span class="dashicons dashicons-download"></span>
		<?php esc_html_e( 'Import Selected', 'cta-manager' ); ?>
	</button>
</div>
<?php
$demo_modal_footer_html = ob_get_clean();
$modal = [
	'id'          => 'cta-import-demo-modal',
	'title_html'  => '<span class="dashicons dashicons-download"></span>' . esc_html__( 'Import Demo Data', 'cta-manager' ),
	'body_html'   => $demo_modal_body_html,
	'footer_html' => $demo_modal_footer_html,
	'size_class'  => 'cta-modal-md',
	'display'     => 'none',
];
include __DIR__ . '/modal.php';
unset( $modal, $demo_modal_body_html, $demo_modal_footer_html );

// Render documentation modal
$modal = [
	'id'              => 'cta-docs-modal',
	'title_html'      => '<span class="dashicons dashicons-book-alt"></span>' . __( 'Documentation', 'cta-manager' ),
	'template'        => CTA_PLUGIN_DIR . 'templates/admin/modals/docs-modal.php',
	'size_class'      => 'cta-modal-xl',
	'extra_class'     => 'cta-docs-modal',
	'display'         => 'none',
	'close_in_header' => true,
];
include __DIR__ . '/modal.php';

// Render support ticket modal (globally available)
include __DIR__ . '/support-ticket-form.php';
?>
