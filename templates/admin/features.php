<?php
/**
 * Admin Page Template - Features
 *
 * Handles markup rendering for the features admin page template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables available in this template:
 *
 * @var bool $is_pro User's pro license status
 * @var string $license_expires Pro license expiration date (if applicable)
 */

// Page wrapper configuration
$current_page       = 'upgrade';
$header_title       = __( 'Unlock the Power of Pro', 'cta-manager' );
$header_description = __( 'CTA Manager Pro delivers premium features to grow your business.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';

// Get features and integrations from centralized registry
$all_features = CTA_Features::get_all_features();
$integrations = CTA_Features::get_all_integrations();

?>

<div class="cta-features-page">
	<!-- All Features Section (Free & Pro Combined) -->
	<div class="cta-features-section">
		<?php foreach ( $all_features as $category_name => $category_features ) : ?>
			<div class="cta-feature-group">
				<h4 class="cta-feature-group-title"><?php echo esc_html( $category_name ); ?></h4>
				<div class="cta-features-grid">
					<?php foreach ( $category_features as $feature ) : ?>
						<?php
						$icon           = $feature['icon'] ?? '';
						$title          = $feature['title'] ?? '';
						$description    = $feature['description'] ?? '';
						$features       = $feature['features'] ?? [];
						$plan           = $feature['plan'] ?? 'free'; // 'free' or 'pro'
						$is_implemented = ! empty( $feature['implemented'] );
						$is_pro         = 'pro' === $plan;

						// Determine badge styling:
						// - Implemented (available) items: no badge (shows "Available" for free, "Pro" for pro)
						// - Coming soon items: badge = 'primary' (shows "Coming Soon" or "Pro + Coming Soon")
						$badge      = ! $is_implemented ? 'primary' : '';
						$badge_text = '';

						include __DIR__ . '/partials/feature-card.php';
						unset( $icon, $title, $description, $features, $badge, $badge_text, $is_pro, $is_implemented, $plan );
						?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Integrations Section -->
	<div class="cta-features-section cta-integrations-section">
		<h2 class="cta-integrations-title"><?php esc_html_e( 'Integrations', 'cta-manager' ); ?></h2>

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
							<div class="cta-integration-footer">
								<?php if ( empty( $integration['implemented'] ) ) : ?>
									<span class="cta-badge cta-badge-primary cta-pulse-primary"><?php esc_html_e( 'Coming Soon', 'cta-manager' ); ?></span>
								<?php else : ?>
									<span class="cta-badge cta-badge-success"><?php esc_html_e( 'Available', 'cta-manager' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>

<?php include __DIR__ . '/partials/page-wrapper-end.php'; ?>
