<?php
/**
 * Import Demo Modal Body Template
 *
 * Modal body content for selective demo data import.
 * Contains toggle switches for Settings, CTAs, Analytics, and Notifications.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Modals
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
?>
<div class="cta-import-demo-options">
	<p class="cta-import-intro">
		<?php esc_html_e( 'Select which demo data to import.', 'cta-manager' ); ?>
	</p>

	<div class="cta-import-toggle-group">
		<!-- Settings Toggle -->
		<div class="cta-import-toggle-row">
			<label class="cta-toggle cta-toggle--filter">
				<input type="checkbox" id="import-toggle-settings" checked />
				<span class="cta-toggle-track">
					<span class="cta-toggle-thumb"></span>
				</span>
			</label>
			<div class="cta-toggle-content">
				<span class="cta-toggle-label"><?php esc_html_e( 'Settings', 'cta-manager' ); ?></span>
				<span class="cta-toggle-description">
					<?php esc_html_e( 'Import Settings with recommended defaults', 'cta-manager' ); ?>
				</span>
			</div>
		</div>

		<!-- CTAs Toggle -->
		<div class="cta-import-toggle-row">
			<label class="cta-toggle cta-toggle--filter">
				<input type="checkbox" id="import-toggle-ctas" checked />
				<span class="cta-toggle-track">
					<span class="cta-toggle-thumb"></span>
				</span>
			</label>
			<div class="cta-toggle-content">
				<span class="cta-toggle-label"><?php esc_html_e( 'CTAs', 'cta-manager' ); ?></span>
				<span class="cta-toggle-description">
					<?php esc_html_e( 'Import CTAs with various configurations', 'cta-manager' ); ?>
				</span>
			</div>
		</div>

		<!-- Analytics Toggle -->
		<div class="cta-import-toggle-row">
			<label class="cta-toggle cta-toggle--filter">
				<input type="checkbox" id="import-toggle-analytics" checked />
				<span class="cta-toggle-track">
					<span class="cta-toggle-thumb"></span>
				</span>
			</label>
			<div class="cta-toggle-content">
				<span class="cta-toggle-label"><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></span>
				<span class="cta-toggle-description">
					<?php esc_html_e( 'Import Analytics to test reports', 'cta-manager' ); ?>
				</span>
			</div>
		</div>

		<!-- Notifications Toggle -->
		<div class="cta-import-toggle-row">
			<label class="cta-toggle cta-toggle--filter">
				<input type="checkbox" id="import-toggle-notifications" checked />
				<span class="cta-toggle-track">
					<span class="cta-toggle-thumb"></span>
				</span>
			</label>
			<div class="cta-toggle-content">
				<span class="cta-toggle-label"><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></span>
				<span class="cta-toggle-description">
					<?php esc_html_e( 'Import Notifications to preview the panel', 'cta-manager' ); ?>
				</span>
			</div>
		</div>
	</div>
	<div class="cta-import-error" style="display: none;"></div>

	<p class="cta-import-hint">
		<span class="dashicons dashicons-info"></span>
		<?php esc_html_e( 'Demo data can be removed anytime from the Tools page.', 'cta-manager' ); ?>
	</p>

</div>