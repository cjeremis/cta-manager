<?php
/**
 * Admin Modal Template - Cta Preview
 *
 * Handles markup rendering for the cta preview admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-preview-modal-card">
	<p class="cta-preview-description"><?php esc_html_e( 'This is how your CTA will appear to visitors on your website.', 'cta-manager' ); ?></p>
	<div class="cta-frontend-preview">
		<!-- Preview content will be injected here -->
	</div>
</div>
