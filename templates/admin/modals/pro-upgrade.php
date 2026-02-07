<?php
/**
 * Pro upgrade modal body
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-step-title">
	<div class="cta-step-icon cta-step-icon--pro">
		<span class="dashicons dashicons-star-filled cta-animate-slow"></span>
	</div>
	<h2><?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?></h2>
</div>

<p class="cta-step-description"><?php esc_html_e( 'Unlock advanced features to scale your conversions.', 'cta-manager' ); ?></p>

<ul class="cta-pro-features-list">
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Popups and slide-in CTAs', 'cta-manager' ); ?></li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Smart scheduling controls', 'cta-manager' ); ?></li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'A/B testing + analytics', 'cta-manager' ); ?></li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Custom CSS and HTML', 'cta-manager' ); ?></li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Device targeting rules', 'cta-manager' ); ?></li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'GA4 integration + subdomains', 'cta-manager' ); ?></li>
</ul>

<!-- MODAL_FOOTER -->
<div class="cta-pro-modal-actions">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-button-primary cta-upgrade-button">
		<span class="dashicons dashicons-star-filled cta-animate-fast"></span>
		<?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?>
	</a>
	<button type="button" class="cta-button-secondary cta-modal-cancel" data-close-modal>
		<?php esc_html_e( 'Maybe Later', 'cta-manager' ); ?>
	</button>
</div>
