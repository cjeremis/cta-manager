<?php
/**
 * Onboarding modal body
 *
 * Steps:
 * 1. Welcome - Introduction to CTA Manager
 * 2. Import Data - Optional import of existing CTA data from JSON file
 * 3. Demo Data - Optional import of demo CTAs and analytics
 * 4. Support Overview - Explanation of support toolbar icons
 *
 * @var array $context Contains onboarding state and completed steps
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$completed_steps = $context['completed_steps'] ?? [];
$is_pro_active = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$welcome_message = $is_pro_active
	? __( 'CTA Manager Pro', 'cta-manager' )
	: __( 'CTA Manager', 'cta-manager' );

// Check if demo data is already loaded
$data = CTA_Data::get_instance();
$all_ctas = $data->get_ctas();
$demo_ctas = array_filter( $all_ctas, function( $cta ) {
	return isset( $cta['_demo'] ) && $cta['_demo'] === true;
} );
$has_demo = count( $demo_ctas ) > 0;
?>

<div class="cta-onboarding-wizard">
	<div class="cta-onboarding-container">
		<!-- Horizontal Breadcrumb Step Indicator -->
		<div class="cta-onboarding-breadcrumb">
			<div class="cta-breadcrumb-step cta-breadcrumb-step-active" data-step="1">
				<span class="cta-breadcrumb-number">1</span>
			</div>
			<div class="cta-breadcrumb-step" data-step="2">
				<span class="cta-breadcrumb-number">2</span>
			</div>
			<div class="cta-breadcrumb-step" data-step="3">
				<span class="cta-breadcrumb-number">3</span>
			</div>
			<div class="cta-breadcrumb-step" data-step="4">
				<span class="cta-breadcrumb-number">4</span>
			</div>
		</div>

		<!-- Step Content -->
		<div class="cta-onboarding-content">
			<!-- Step 1: Welcome -->
			<div class="cta-onboarding-step cta-step-1 cta-step-active">
				<div class="cta-step-title">
					<div class="cta-step-icon">
						<span class="dashicons dashicons-megaphone"></span>
					</div>
					<h2><?php echo esc_html( $welcome_message ); ?></h2>
				</div>
				<p class="cta-step-description">
					<?php esc_html_e( 'This wizard helps you get started with CTA Manager in just a few steps.', 'cta-manager' ); ?>
				</p>

				<div class="cta-support-icons-guide cta-onboarding-feature-grid">
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-megaphone"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Unlimited CTAs', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Create and manage unlimited CTAs to attract leads.', 'cta-manager' ); ?></p>
						</div>
					</div>
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-layout"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Flexible Layouts', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Choose from multiple layouts and customization options.', 'cta-manager' ); ?></p>
						</div>
					</div>
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-chart-line"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Track CTA performance with built-in analytics.', 'cta-manager' ); ?></p>
						</div>
					</div>
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-admin-network"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Integrations', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Upgrade to Pro for more integrations and features.', 'cta-manager' ); ?></p>
						</div>
					</div>
				</div>

				<div class="cta-onboarding-step-cta">
					<button type="button" class="cta-button cta-button-primary" id="cta-onboarding-docs-btn">
						<span class="dashicons dashicons-book-alt"></span>
						<?php esc_html_e( 'View Documentation', 'cta-manager' ); ?>
					</button>
				</div>
			</div>

			<!-- Step 2: Import Existing Data -->
			<div class="cta-onboarding-step cta-step-2" style="display: none;">
				<div class="cta-step-title">
					<div class="cta-step-icon">
						<span class="dashicons dashicons-upload"></span>
					</div>
					<h2><?php esc_html_e( 'Import Existing Data', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-step-description">
					<?php esc_html_e( 'Import your JSON backup file to restore all of your CTAs and data.', 'cta-manager' ); ?>
				</p>

			<div class="cta-onboarding-toggle-list cta-support-icons-guide">
				<div class="cta-onboarding-toggle-row cta-support-icon-item">
					<div class="cta-support-icon-preview">
						<span class="dashicons dashicons-admin-settings"></span>
					</div>
					<div class="cta-support-icon-info">
						<strong><?php esc_html_e( 'Settings', 'cta-manager' ); ?></strong>
						<p><?php esc_html_e( 'Configuration and defaults', 'cta-manager' ); ?></p>
					</div>
					<label class="cta-toggle cta-toggle--filter">
						<input type="checkbox" id="onboarding-import-toggle-settings" checked />
						<span class="cta-toggle-track">
							<span class="cta-toggle-thumb"></span>
						</span>
					</label>
				</div>

				<div class="cta-onboarding-toggle-row cta-support-icon-item">
					<div class="cta-support-icon-preview">
						<span class="dashicons dashicons-portfolio"></span>
					</div>
					<div class="cta-support-icon-info">
						<strong><?php esc_html_e( 'CTAs', 'cta-manager' ); ?></strong>
						<p><?php esc_html_e( 'All CTA items', 'cta-manager' ); ?></p>
					</div>
					<label class="cta-toggle cta-toggle--filter">
						<input type="checkbox" id="onboarding-import-toggle-ctas" checked />
						<span class="cta-toggle-track">
							<span class="cta-toggle-thumb"></span>
						</span>
					</label>
				</div>

				<div class="cta-onboarding-toggle-row cta-support-icon-item">
					<div class="cta-support-icon-preview">
						<span class="dashicons dashicons-chart-area"></span>
					</div>
					<div class="cta-support-icon-info">
						<strong><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></strong>
						<p><?php esc_html_e( 'Performance and reports', 'cta-manager' ); ?></p>
					</div>
					<label class="cta-toggle cta-toggle--filter">
						<input type="checkbox" id="onboarding-import-toggle-analytics" checked />
						<span class="cta-toggle-track">
							<span class="cta-toggle-thumb"></span>
						</span>
					</label>
				</div>

				<div class="cta-onboarding-toggle-row cta-support-icon-item">
					<div class="cta-support-icon-preview">
						<span class="dashicons dashicons-megaphone"></span>
					</div>
					<div class="cta-support-icon-info">
						<strong><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></strong>
						<p><?php esc_html_e( 'System notification messages', 'cta-manager' ); ?></p>
					</div>
					<label class="cta-toggle cta-toggle--filter">
						<input type="checkbox" id="onboarding-import-toggle-notifications" checked />
						<span class="cta-toggle-track">
							<span class="cta-toggle-thumb"></span>
						</span>
					</label>
				</div>
			</div>

				<div class="cta-onboarding-import-section">
					<div class="cta-import-file-area" id="cta-onboarding-import-area">
						<?php
						$input_id         = 'cta-onboarding-import-file';
						$input_name       = 'import_file';
						$wrapper_id       = 'cta-onboarding-file-wrapper';
						$selected_id      = 'cta-onboarding-file-selected';
						$file_name_id     = 'cta-onboarding-file-name';
						$file_size_id     = 'cta-onboarding-file-size';
						$remove_button_id = 'cta-onboarding-file-remove';
						$label_text       = __( 'Drop or Choose JSON File', 'cta-manager' );
						include CTA_PLUGIN_DIR . 'templates/admin/partials/file-input-wrapper.php';
						unset( $input_id, $input_name, $wrapper_id, $selected_id, $file_name_id, $file_size_id, $remove_button_id, $label_text );
						?>
					</div>

					<div class="cta-onboarding-import-actions" id="cta-onboarding-import-actions" style="display: none;">
						<button type="button" class="cta-button cta-button-primary" id="cta-onboarding-import-btn">
							<span class="dashicons dashicons-upload"></span>
							<?php esc_html_e( 'Import Now', 'cta-manager' ); ?>
						</button>
					</div>

					<div class="cta-onboarding-import-status" id="cta-onboarding-import-status" style="display: none;"></div>
				</div>
			</div>

			<!-- Step 3: Demo Data -->
			<div class="cta-onboarding-step cta-step-3" style="display: none;">
				<div class="cta-step-title">
					<div class="cta-step-icon">
						<span class="dashicons dashicons-welcome-learn-more"></span>
					</div>
					<h2><?php esc_html_e( 'Import Demo Data', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-step-description">
					<?php esc_html_e( 'Load sample CTAs, analytics, and settings to explore CTA Manager.', 'cta-manager' ); ?>
				</p>

				<?php if ( $has_demo ) : ?>
					<div class="cta-info-box cta-info-box--success">
						<span class="cta-info-box__icon dashicons dashicons-yes-alt"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'Demo Data Already Loaded', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php
								printf(
									/* translators: %d: number of demo CTAs */
									esc_html( _n( 'You have %d demo CTA loaded.', 'You have %d demo CTAs loaded.', count( $demo_ctas ), 'cta-manager' ) ),
									count( $demo_ctas )
								);
								?>
							</p>
						</div>
					</div>
				<?php else : ?>
					<div class="cta-onboarding-demo-options">
						<div class="cta-support-icons-guide cta-onboarding-feature-grid">
							<div class="cta-support-icon-item">
								<div class="cta-support-icon-preview">
									<span class="dashicons dashicons-portfolio"></span>
								</div>
								<div class="cta-support-icon-info">
									<strong><?php esc_html_e( 'Sample CTAs', 'cta-manager' ); ?></strong>
									<p><?php esc_html_e( 'Prebuilt CTAs with varied configurations.', 'cta-manager' ); ?></p>
								</div>
							</div>
							<div class="cta-support-icon-item">
								<div class="cta-support-icon-preview">
									<span class="dashicons dashicons-chart-area"></span>
								</div>
								<div class="cta-support-icon-info">
									<strong><?php esc_html_e( 'Demo Analytics', 'cta-manager' ); ?></strong>
									<p><?php esc_html_e( 'Sample analytics data to test reports.', 'cta-manager' ); ?></p>
								</div>
							</div>
							<div class="cta-support-icon-item">
								<div class="cta-support-icon-preview">
									<span class="dashicons dashicons-megaphone"></span>
								</div>
								<div class="cta-support-icon-info">
									<strong><?php esc_html_e( 'Demo Notifications', 'cta-manager' ); ?></strong>
									<p><?php esc_html_e( 'Preview notifications in the panel.', 'cta-manager' ); ?></p>
								</div>
							</div>
							<div class="cta-support-icon-item">
								<div class="cta-support-icon-preview">
									<span class="dashicons dashicons-admin-settings"></span>
								</div>
								<div class="cta-support-icon-info">
									<strong><?php esc_html_e( 'Recommended Defaults', 'cta-manager' ); ?></strong>
									<p><?php esc_html_e( 'Plugin settings ready to explore.', 'cta-manager' ); ?></p>
								</div>
							</div>
						</div>

						<div class="cta-onboarding-demo-actions">
							<button type="button" class="cta-button cta-button-primary" id="cta-onboarding-demo-btn">
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Import Demo Data', 'cta-manager' ); ?>
							</button>
						</div>

						<div class="cta-onboarding-demo-status" id="cta-onboarding-demo-status" style="display: none;"></div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Step 4: Support Toolbar Overview -->
			<div class="cta-onboarding-step cta-step-4" style="display: none;">
				<div class="cta-step-title">
					<div class="cta-step-icon">
						<span class="dashicons dashicons-phone"></span>
					</div>
					<h2><?php esc_html_e( 'Getting Help', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-step-description">
					<?php esc_html_e( 'Use the support toolbar in the top-right of CTA Manager.', 'cta-manager' ); ?>
				</p>

				<div class="cta-support-icons-guide">
					<!-- Features -->
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-awards"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Features', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Learn about existing and upcoming features.', 'cta-manager' ); ?></p>
						</div>
					</div>

					<!-- Notifications -->
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-megaphone"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Stay updated with plugin alerts, tips, and important messages.', 'cta-manager' ); ?></p>
						</div>
					</div>

					<!-- Support -->
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-phone"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Support', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Get help from our support team when you need assistance.', 'cta-manager' ); ?></p>
						</div>
					</div>

					<!-- Documentation -->
					<div class="cta-support-icon-item">
						<div class="cta-support-icon-preview">
							<span class="dashicons dashicons-book-alt"></span>
						</div>
						<div class="cta-support-icon-info">
							<strong><?php esc_html_e( 'Documentation', 'cta-manager' ); ?></strong>
							<p><?php esc_html_e( 'Access guides, tutorials, and detailed documentation.', 'cta-manager' ); ?></p>
						</div>
					</div>
				</div>

				<div class="cta-onboarding-step-cta">
					<button type="button" class="cta-button cta-button-primary" id="cta-onboarding-support-btn">
						<span class="dashicons dashicons-phone"></span>
						<?php esc_html_e( 'Get Support', 'cta-manager' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- MODAL_FOOTER -->

<div class="cta-pro-modal-actions">
	<button type="button" class="cta-button-secondary cta-onboarding-maybe-later" id="cta-onboarding-maybe-later" style="display: none;">
		<?php esc_html_e( 'Maybe Later', 'cta-manager' ); ?>
	</button>
	<button type="button" class="cta-button-secondary cta-onboarding-prev" style="display: none;">
		<?php esc_html_e( 'Back', 'cta-manager' ); ?>
	</button>

	<button type="button" class="cta-button-primary cta-onboarding-next">
		<?php esc_html_e( 'Next', 'cta-manager' ); ?>
	</button>

	<button type="button" class="cta-button-primary cta-onboarding-finish" style="display: none;">
		<?php esc_html_e( 'Finish', 'cta-manager' ); ?>
	</button>
</div>
