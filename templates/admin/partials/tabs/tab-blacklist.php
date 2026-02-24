<?php
/**
 * Admin Tab Partial Template - Tab Blacklist
 *
 * Handles markup rendering for the tab blacklist admin tab partial.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variable initialization
$blacklist_urls = $editing_cta['blacklist_urls'] ?? [];
if ( ! is_array( $blacklist_urls ) ) {
	$blacklist_urls = [];
}

?>
<div class="cta-form-section cta-no-mt">
	<div class="cta-form-row cta-full-width">
		<div class="cta-form-group">
			<div class="cta-label-row">
				<label for="cta-open-blacklist-modal">
					<?php esc_html_e( 'Hide CTA on matches', 'cta-manager' ); ?>
				</label>
				<div class="cta-label-actions" style="display:flex; align-items:center; gap:8px;">
					<span class="cta-count-badge-primary-dark" id="cta-blacklist-count"><?php echo esc_html( count( $blacklist_urls ) ); ?></span>
					<button type="button" class="cta-button-secondary" id="cta-open-blacklist-modal" <?php disabled( ! $is_pro ); ?>>
						<?php esc_html_e( 'Manage', 'cta-manager' ); ?>
					</button>
				</div>
			</div>
			<p class="cta-helper-text"><?php esc_html_e( 'Add paths, wildcards, or regex.', 'cta-manager' ); ?></p>
			<div id="cta-blacklist-hidden-container">
				<?php foreach ( $blacklist_urls as $pattern ) : ?>
					<input type="hidden" name="cta_blacklist_urls[]" value="<?php echo esc_attr( $pattern ); ?>" />
				<?php endforeach; ?>
			</div>
			<?php if ( ! $is_pro ) : ?>
				<?php
				$icon        = 'dismiss';
				$title       = __( 'Unlock Blacklist Controls', 'cta-manager' );
				$message     = __( 'Upgrade to Pro to manage per-CTA blacklist rules for hiding CTAs on specific URLs.', 'cta-manager' );
				$button_url  = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
				$button_text = __( 'Upgrade Now', 'cta-manager' );
				include CTA_PLUGIN_DIR . 'templates/admin/partials/upgrade-notice.php';
				unset( $icon, $title, $message, $button_url, $button_text );
				?>
			<?php endif; ?>
		</div>
	</div>
</div>
