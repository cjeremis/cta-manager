<?php
/**
 * Admin Partial Template - Plugin Promo
 *
 * Handles markup rendering for the plugin promo section shown in the page footer.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugins_img_url = CTA_PLUGIN_URL . 'assets/images/plugins/';
$is_pro_active   = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

$promo_plugins = array(
	array(
		'slug'        => 'cta-manager-pro',
		'logo'        => CTA_PLUGIN_URL . 'assets/images/cta-manager-pro-logo.png',
		'title'       => __( 'CTA Manager Pro', 'cta-manager' ),
		'tagline'     => __( 'Convert More Visitors', 'cta-manager' ),
		'description' => __( 'Create, manage, and track conversion-focused calls-to-action with targeting rules, A/B testing, and real-time analytics.', 'cta-manager' ),
		'url'         => '',
		'color_class' => 'cta-promo-card--orange',
		'is_current'  => $is_pro_active,
		'open_modal'  => ! $is_pro_active ? '#cta-features-modal' : '',
	),
	array(
		'slug'        => 'dashboard-widget-manager',
		'logo'        => $plugins_img_url . 'dashboard-widget-manager-logo.png',
		'title'       => __( 'Dashboard Widget Manager', 'cta-manager' ),
		'tagline'     => __( 'Custom Dashboard Widgets', 'cta-manager' ),
		'description' => __( 'Build custom WP admin dashboard widgets with SQL queries, PHP rendering, chart support, and flexible caching.', 'cta-manager' ),
		'url'         => 'https://topdevamerica.com/plugins/dashboard-widget-manager',
		'color_class' => 'cta-promo-card--purple',
	),
	array(
		'slug'        => 'ai-chat-manager',
		'logo'        => $plugins_img_url . 'ai-chat-manager-logo.png',
		'title'       => __( 'AI Chat Manager', 'cta-manager' ),
		'tagline'     => __( 'AI-Powered Chat', 'cta-manager' ),
		'description' => __( 'Add a fully customizable AI chat assistant to your WordPress site, powered by Claude or OpenAI.', 'cta-manager' ),
		'url'         => 'https://topdevamerica.com/plugins/ai-chat-manager',
		'color_class' => 'cta-promo-card--teal',
	),
);
?>

<div class="cta-plugin-promo-section">
	<div class="cta-plugin-promo-header">
		<span class="cta-plugin-promo-eyebrow"><?php esc_html_e( 'TopDevAmerica', 'cta-manager' ); ?></span>
		<h3 class="cta-plugin-promo-title"><?php esc_html_e( 'More Tools for WordPress', 'cta-manager' ); ?></h3>
		<p class="cta-plugin-promo-subtitle"><?php esc_html_e( 'Extend your WordPress workflow with our growing suite of developer-focused plugins.', 'cta-manager' ); ?></p>
	</div>

	<div class="cta-plugin-promo-grid">
		<?php foreach ( $promo_plugins as $plugin ) : ?>
			<div class="cta-promo-card-wrap <?php echo esc_attr( $plugin['color_class'] ); ?>">
				<div class="cta-promo-card">
					<div class="cta-promo-card-overlay <?php echo esc_attr( $plugin['color_class'] ); ?>"></div>
					<div class="cta-promo-card-logo">
						<img src="<?php echo esc_url( $plugin['logo'] ); ?>" alt="<?php echo esc_attr( $plugin['title'] ); ?>" width="52" height="52" loading="lazy">
					</div>
					<div class="cta-promo-card-body">
						<p class="cta-promo-card-tagline"><?php echo esc_html( $plugin['tagline'] ); ?></p>
						<h4 class="cta-promo-card-name"><?php echo esc_html( $plugin['title'] ); ?></h4>
						<p class="cta-promo-card-desc"><?php echo esc_html( $plugin['description'] ); ?></p>
					</div>
					<div class="cta-promo-card-footer">
						<?php if ( ! empty( $plugin['is_current'] ) ) : ?>
							<span class="cta-promo-card-current"><?php esc_html_e( 'You Are Here', 'cta-manager' ); ?></span>
						<?php elseif ( ! empty( $plugin['open_modal'] ) ) : ?>
							<button type="button" class="cta-promo-card-btn" data-open-modal="<?php echo esc_attr( $plugin['open_modal'] ); ?>">
								<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
							</button>
						<?php else : ?>
							<a href="<?php echo esc_url( $plugin['url'] ); ?>" class="cta-promo-card-btn" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
								<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
