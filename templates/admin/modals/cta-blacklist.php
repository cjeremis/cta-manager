<?php
/**
 * Admin Modal Template - Cta Blacklist
 *
 * Handles markup rendering for the cta blacklist admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="cta-helper-text"><?php esc_html_e( 'Use full/relative URLs, wildcards, or regex. One per row.', 'cta-manager' ); ?></p>
<div id="cta-blacklist-rows">
	<p class="cta-blacklist-empty-state" style="color:var(--cta-text-secondary);font-style:italic;margin:8px 0;"><?php esc_html_e( 'No patterns added yet. Click "Add URL" to get started.', 'cta-manager' ); ?></p>
</div>
<button type="button" class="cta-button-secondary" id="cta-add-blacklist-row">
	<?php esc_html_e( 'Add URL', 'cta-manager' ); ?>
</button>

<!-- MODAL_FOOTER -->
<button type="button" class="cta-button-secondary" data-action="cancel"><?php esc_html_e( 'Cancel', 'cta-manager' ); ?></button>
<button type="button" class="cta-button-primary" data-action="save"><?php esc_html_e( 'Save', 'cta-manager' ); ?></button>
