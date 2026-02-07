<?php
/**
 * Features Modal Body
 *
 * Displays feature cards and integrations with left sidebar navigation.
 * Uses shared sidebar-modal-layout classes for consistent styling.
 *
 * @var array $context Modal context with all_features and integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_features = $context['all_features'] ?? [];
$integrations = $context['integrations'] ?? [];

// Check if user has Pro enabled (for showing upgrade CTAs)
$user_has_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

$first_category = array_key_first( $all_features );
$first_category_slug = sanitize_title( $first_category );
$integrations_meta = CTA_Features::get_integrations_meta();
$labels = CTA_Features::get_labels();
?>

<div class="cta-sidebar-modal-layout">
	<!-- Sidebar -->
	<div class="cta-sidebar-modal-sidebar">
		<!-- Navigation -->
		<nav class="cta-sidebar-modal-nav">
			<ul class="cta-sidebar-modal-menu" data-features-menu>
				<?php
				$first = true;
				foreach ( $all_features as $category_name => $features ) :
					$category_slug = sanitize_title( $category_name );
					$icon = CTA_Features::get_category_icon( $category_name );
					$active_class = $first ? ' is-active' : '';
					?>
					<li class="cta-sidebar-modal-menu-item">
						<button
							type="button"
							class="cta-sidebar-modal-menu-link<?php echo esc_attr( $active_class ); ?>"
							data-features-page="<?php echo esc_attr( $category_slug ); ?>"
						>
							<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
							<?php echo esc_html( $category_name ); ?>
							<span class="cta-sidebar-modal-menu-count"><?php echo count( $features ); ?></span>
						</button>
					</li>
					<?php
					$first = false;
				endforeach;
				?>

				<!-- Integrations -->
				<li class="cta-sidebar-modal-menu-item has-divider">
					<button
						type="button"
						class="cta-sidebar-modal-menu-link"
						data-features-page="integrations"
					>
						<span class="dashicons dashicons-<?php echo esc_attr( $integrations_meta['icon'] ); ?>"></span>
						<?php echo esc_html( $integrations_meta['label'] ); ?>
						<span class="cta-sidebar-modal-menu-count"><?php echo array_sum( array_map( 'count', $integrations ) ); ?></span>
					</button>
				</li>
			</ul>
		</nav>
	</div>

	<!-- Content Area -->
	<div class="cta-sidebar-modal-content" data-features-content>
		<?php
		$first = true;
		foreach ( $all_features as $category_name => $category_features ) :
			$category_slug = sanitize_title( $category_name );
			$active_class = $first ? ' is-active' : '';

			// Check if this category contains Pro or Coming Soon features
			$has_pro_features = false;
			$has_coming_soon = false;
			foreach ( $category_features as $feature ) {
				$feature_plan = $feature['plan'] ?? 'free';
				$feature_implemented = ! empty( $feature['implemented'] );
				if ( 'pro' === $feature_plan ) {
					$has_pro_features = true;
				}
				if ( ! $feature_implemented ) {
					$has_coming_soon = true;
				}
			}
			?>
			<div class="cta-sidebar-modal-page<?php echo esc_attr( $active_class ); ?>" data-features-page-content="<?php echo esc_attr( $category_slug ); ?>">
				<div class="cta-sidebar-modal-page-header">
					<h3><?php echo esc_html( $category_name ); ?></h3>
					<p><?php echo esc_html( CTA_Features::get_category_description( $category_name ) ); ?></p>
				</div>

				<?php if ( $has_pro_features && ! $user_has_pro ) : ?>
					<!-- Pro Upgrade CTA for Pro Features -->
					<div class="cta-notification-item cta-notification-item--license-cta" data-notification-id="pro-features-cta" data-notification-type="pro_features_upgrade">
						<div class="cta-notification-license-glow"></div>
						<div class="cta-notification-license-header">
							<div class="cta-notification-license-icon">
								<span class="dashicons dashicons-star-filled cta-animate-slow"></span>
							</div>
							<div class="cta-notification-license-text">
								<h4 class="cta-notification-license-title"><?php esc_html_e( 'Unlock Pro', 'cta-manager' ); ?></h4>
								<?php cta_pro_badge_inline(); ?>
							</div>
						</div>
						<p class="cta-notification-license-message"><?php esc_html_e( 'Unlock powerful Pro features in this category by activating your CTA Manager Pro license.', 'cta-manager' ); ?></p>
						<div class="cta-notification-license-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-button cta-pro-upgrade-button cta-pro-upgrade-button--primary cta-button-primary" data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key">
								<span class="dashicons dashicons-unlock"></span>
								<span class="cta-pro-upgrade-button__label"><?php esc_html_e( 'Add License Key', 'cta-manager' ); ?></span>
							</a>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $has_coming_soon && ! $has_pro_features ) : ?>
					<!-- Coming Soon CTA for Free Features -->
					<div class="cta-notification-item cta-notification-item--license-cta" data-notification-id="coming-soon-cta" data-notification-type="coming_soon_features">
						<div class="cta-notification-license-glow"></div>
						<div class="cta-notification-license-header">
							<div class="cta-notification-license-icon">
								<span class="dashicons dashicons-calendar cta-animate-slow"></span>
							</div>
							<div class="cta-notification-license-text">
								<h4 class="cta-notification-license-title"><?php esc_html_e( 'Coming Soon Features', 'cta-manager' ); ?></h4>
							</div>
						</div>
						<p class="cta-notification-license-message"><?php esc_html_e( 'This category includes features currently in development. Stay tuned for updates!', 'cta-manager' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="cta-features-grid">
					<?php
					// Ensure hooks show Action above Filter within the category
					usort(
						$category_features,
						function( $a, $b ) {
							$priority = [ 'action' => 0, 'filter' => 1 ];
							$a_type   = $a['hook_type'] ?? '';
							$b_type   = $b['hook_type'] ?? '';
							$a_rank   = $priority[ $a_type ] ?? 2;
							$b_rank   = $priority[ $b_type ] ?? 2;

							if ( $a_rank === $b_rank ) {
								return 0;
							}
							return ( $a_rank < $b_rank ) ? -1 : 1;
						}
					);

					foreach ( $category_features as $feature ) :
						// Skip Hooks Overview cards in features modal (docs modal handles overview)
						$feature_title_lower = isset( $feature['title'] ) ? strtolower( $feature['title'] ) : '';
						if (
							in_array( $category_name, [ __( 'PHP Hooks', 'cta-manager' ), __( 'JavaScript Hooks', 'cta-manager' ) ], true )
							&& $feature_title_lower === 'overview'
						) {
							continue;
						}
						$icon           = $feature['icon'] ?? '';
						$title          = $feature['title'] ?? '';
						$hook_name      = $feature['hook_name'] ?? '';
						$hook_type      = $feature['hook_type'] ?? '';
						$description    = $feature['description'] ?? '';
						$features       = $feature['features'] ?? [];
						$details        = $feature['details'] ?? '';
						$instructions   = $feature['instructions'] ?? [];
						$plan           = $feature['plan'] ?? 'free';
						$is_implemented = ! empty( $feature['implemented'] );
						$is_pro         = 'pro' === $plan;
						$badge          = ! $is_implemented ? 'primary' : '';
						$badge_text     = '';

						include CTA_PLUGIN_DIR . 'templates/admin/partials/feature-card.php';
						unset( $icon, $title, $hook_name, $hook_type, $description, $features, $details, $instructions, $badge, $badge_text, $is_pro, $is_implemented, $plan );
					endforeach;
					?>
				</div>
			</div>
			<?php
			$first = false;
		endforeach;
		?>

		<!-- Integrations Page -->
		<div class="cta-sidebar-modal-page" data-features-page-content="integrations">
			<div class="cta-sidebar-modal-page-header">
				<h3><?php echo esc_html( $integrations_meta['label'] ); ?></h3>
				<p><?php echo esc_html( $integrations_meta['description'] ); ?></p>
			</div>

			<?php if ( ! $user_has_pro ) : ?>
				<!-- Pro Upgrade CTA for Integrations -->
				<div class="cta-notification-item cta-notification-item--license-cta" data-notification-id="integrations-pro-cta" data-notification-type="integrations_upgrade">
					<div class="cta-notification-license-glow"></div>
					<div class="cta-notification-license-header">
						<div class="cta-notification-license-icon">
							<span class="dashicons dashicons-star-filled cta-animate-slow"></span>
						</div>
						<div class="cta-notification-license-text">
							<h4 class="cta-notification-license-title"><?php esc_html_e( 'Pro Integrations Available', 'cta-manager' ); ?></h4>
							<?php cta_pro_badge_inline(); ?>
						</div>
					</div>
					<p class="cta-notification-license-message"><?php esc_html_e( 'Connect your CTAs with powerful third-party services and analytics platforms by activating CTA Manager Pro.', 'cta-manager' ); ?></p>
					<div class="cta-notification-license-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-button cta-pro-upgrade-button cta-pro-upgrade-button--primary cta-button-primary" data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key">
							<span class="dashicons dashicons-unlock"></span>
							<span class="cta-pro-upgrade-button__label"><?php esc_html_e( 'Add License Key', 'cta-manager' ); ?></span>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<div class="cta-integrations-grid-wrapper">
				<?php foreach ( $integrations as $category_name => $items ) : ?>
					<div class="cta-integration-group">
						<h4 class="cta-feature-group-title"><?php echo esc_html( $category_name ); ?></h4>
						<div class="cta-integrations-grid">
							<?php foreach ( $items as $integration ) : ?>
								<div class="cta-integration-card<?php echo ! empty( $integration['implemented'] ) ? ' is-available' : ''; ?>">
									<div class="cta-integration-header">
										<div class="cta-integration-icon">
											<?php if ( ! empty( $integration['image'] ) ) : ?>
												<img src="<?php echo esc_url( $integration['image'] ); ?>" alt="<?php echo esc_attr( $integration['title'] ); ?>" loading="lazy">
											<?php elseif ( ! empty( $integration['icon'] ) ) : ?>
												<?php echo esc_html( $integration['icon'] ); ?>
											<?php endif; ?>
										</div>
									</div>
									<h4><?php echo esc_html( $integration['title'] ); ?></h4>
									<?php if ( ! empty( $integration['description'] ) ) : ?>
										<p class="cta-integration-description"><?php echo esc_html( $integration['description'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $integration['features'] ) ) : ?>
										<ul class="cta-integration-features">
											<?php foreach ( $integration['features'] as $feature ) : ?>
												<li><?php echo esc_html( $feature ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
									<?php if ( ! empty( $integration['details'] ) || ! empty( $integration['instructions'] ) ) : ?>
										<div class="cta-integration-learn-more">
											<button type="button" class="cta-learn-more-button" data-feature-title="<?php echo esc_attr( $integration['title'] ); ?>" data-category-type="integration">
												<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
											</button>
										</div>
									<?php endif; ?>
									<div class="cta-integration-footer">
										<?php if ( empty( $integration['implemented'] ) ) : ?>
											<span class="cta-badge cta-badge-primary cta-pulse-primary"><?php echo esc_html( $labels['badge_coming_soon'] ); ?></span>
										<?php else : ?>
											<span class="cta-badge cta-badge-success"><?php echo esc_html( $labels['badge_available'] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<script>
(function() {
	// Features modal navigation
	document.addEventListener('click', function(e) {
		const menuLink = e.target.closest('[data-features-page]');
		if (!menuLink) return;

		const page = menuLink.dataset.featuresPage;
		const modal = menuLink.closest('.cta-sidebar-modal-layout');
		if (!modal) return;

		// Update menu active state
		modal.querySelectorAll('[data-features-page]').forEach(function(link) {
			link.classList.remove('is-active');
		});
		menuLink.classList.add('is-active');

		// Update content active state
		modal.querySelectorAll('[data-features-page-content]').forEach(function(content) {
			content.classList.remove('is-active');
		});
		const targetContent = modal.querySelector('[data-features-page-content="' + page + '"]');
		if (targetContent) {
			targetContent.classList.add('is-active');
		}
	});

	// Learn More button handler
	document.addEventListener('click', function(e) {
		const learnMoreBtn = e.target.closest('.cta-learn-more-button');
		if (!learnMoreBtn) return;

		const featureTitle = learnMoreBtn.dataset.featureTitle;
		const categoryType = learnMoreBtn.dataset.categoryType || 'feature';

		// Close features modal
		const featuresModal = document.querySelector('[data-modal="features"]');
		if (featuresModal && window.CTAManager && window.CTAManager.closeModal) {
			window.CTAManager.closeModal('features');
		}

		// Open documentation modal with specific feature
		setTimeout(function() {
			if (window.CTAManager && window.CTAManager.openDocumentationModal) {
				window.CTAManager.openDocumentationModal(featureTitle, categoryType);
			}
		}, 300); // Small delay to allow features modal to close
	});
})();
</script>
