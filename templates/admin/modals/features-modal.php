<?php
/**
 * Admin Modal Template - Features Modal
 *
 * Handles markup rendering for the features modal admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_features = $context['all_features'] ?? [];
$integrations = $context['integrations'] ?? [];

// Check if user has Pro enabled (for showing upgrade CTAs)
$user_has_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$hero_img = $user_has_pro ? esc_url( CTA_PLUGIN_URL . 'assets/images/cta-manager-pro-logo.png' ) : esc_url( CTA_PLUGIN_URL . 'assets/images/cta-manager-logo.png' );
$hero_plugin_name = $user_has_pro ? 'CTA Manager Pro' : 'CTA Manager';

$first_category = array_key_first( $all_features );
$first_category_slug = sanitize_title( $first_category );
$integrations_meta = CTA_Features::get_integrations_meta();
$labels = CTA_Features::get_labels();

$normalize_title_key = static function( string $title ): string {
	$title = strtolower( trim( wp_strip_all_tags( $title ) ) );
	$title = preg_replace( '/\s*\(.*?\)\s*/', ' ', $title );
	$title = preg_replace( '/[^a-z0-9]+/', '-', $title );
	return trim( $title, '-' );
};

$docs_lookup = [];
foreach ( $all_features as $feature_group ) {
	foreach ( $feature_group as $feature ) {
		$title_key = $normalize_title_key( $feature['title'] ?? '' );
		$docs_page = $feature['docs_page'] ?? '';
		if ( $title_key && $docs_page ) {
			$docs_lookup[ $title_key ] = $docs_page;
		}
	}
}
foreach ( $integrations as $integration_group ) {
	foreach ( $integration_group as $integration ) {
		$title_key = $normalize_title_key( $integration['title'] ?? '' );
		$docs_page = $integration['docs_page'] ?? '';
		if ( $title_key && $docs_page ) {
			$docs_lookup[ $title_key ] = $docs_page;
		}
	}
}

$resolve_docs_page = static function( array $titles, string $fallback ) use ( $docs_lookup, $normalize_title_key ): string {
	foreach ( $titles as $title ) {
		$title_key = $normalize_title_key( (string) $title );
		if ( $title_key && isset( $docs_lookup[ $title_key ] ) ) {
			return $docs_lookup[ $title_key ];
		}
	}
	return $fallback;
};
?>

<div class="cta-sidebar-modal-layout">
	<!-- Sidebar Toggle (docs-modal pattern) -->
	<button type="button" class="cta-features-sidebar-toggle" data-features-sidebar-toggle aria-label="<?php esc_attr_e( 'Toggle sidebar', 'cta-manager' ); ?>">
		<span class="dashicons dashicons-arrow-left-alt2"></span>
	</button>

	<!-- Sidebar -->
	<div class="cta-sidebar-modal-sidebar">
		<!-- Filter Controls -->
		<div class="cta-features-filter-controls">
			<div class="cta-features-filter-row">
				<span class="cta-status-filters" data-features-plan-filters>
					<button type="button" class="cta-status-filter is-active" data-features-plan="all"><?php esc_html_e( 'All', 'cta-manager' ); ?></button>
					<span class="cta-status-separator">|</span>
					<button type="button" class="cta-status-filter" data-features-plan="free"><?php esc_html_e( 'Free', 'cta-manager' ); ?></button>
					<span class="cta-status-separator">|</span>
					<button type="button" class="cta-status-filter" data-features-plan="pro"><?php esc_html_e( 'Pro', 'cta-manager' ); ?></button>
				</span>
				<label class="cta-features-soon-toggle">
					<span class="cta-features-soon-label"><?php esc_html_e( 'Soon', 'cta-manager' ); ?></span>
					<span class="cta-toggle cta-toggle--small">
						<input type="checkbox" checked data-features-soon-toggle />
						<span class="cta-toggle-track cta-toggle-track-small" aria-hidden="true">
							<span class="cta-toggle-thumb cta-toggle-thumb-small"></span>
						</span>
					</span>
				</label>
			</div>
		</div>
		<!-- Search -->
		<div class="cta-sidebar-modal-search">
			<div class="cta-search-wrapper">
				<input type="text" class="cta-search-input" data-features-search placeholder="<?php esc_attr_e( 'Search features...', 'cta-manager' ); ?>" />
				<button type="button" class="cta-search-icon" data-features-search-clear aria-label="<?php esc_attr_e( 'Clear search', 'cta-manager' ); ?>">
					<span class="dashicons dashicons-search"></span>
				</button>
			</div>
		</div>
		<!-- Navigation -->
		<nav class="cta-sidebar-modal-nav">
			<ul class="cta-sidebar-modal-menu" data-features-menu>
				<!-- Overview -->
				<li class="cta-sidebar-modal-menu-item">
					<button
						type="button"
						class="cta-sidebar-modal-menu-link is-active"
						data-features-page="overview"
					>
						<img src="<?php echo esc_url( $hero_img ); ?>" alt="" class="cta-sidebar-modal-menu-logo" loading="lazy" />
						<?php esc_html_e( 'Overview', 'cta-manager' ); ?>
					</button>
				</li>

				<!-- Integrations -->
				<li class="cta-sidebar-modal-menu-item has-divider">
					<button
						type="button"
						class="cta-sidebar-modal-menu-link"
						data-features-page="integrations"
					>
						<span class="dashicons dashicons-<?php echo esc_attr( $integrations_meta['icon'] ); ?>"></span>
						<?php echo esc_html( $integrations_meta['label'] ); ?>
						<span class="cta-sidebar-modal-pro-badge"><?php esc_html_e( 'PRO', 'cta-manager' ); ?></span>
						<span class="cta-sidebar-modal-menu-count"><?php echo array_sum( array_map( 'count', $integrations ) ); ?></span>
					</button>
				</li>

				<?php
				$categories_meta = CTA_Features::get_categories();
				$is_first_category = true;
				foreach ( $all_features as $category_name => $features ) :
					$category_slug = sanitize_title( $category_name );
					$icon = CTA_Features::get_category_icon( $category_name );
					$all_pro = ! empty( $features ) && 0 === count( array_filter( $features, function( $f ) { return ( $f['plan'] ?? 'free' ) !== 'pro'; } ) );
					$has_divider = $is_first_category || ! empty( $categories_meta[ $category_name ]['divider'] );
					$is_first_category = false;
					?>
					<li class="cta-sidebar-modal-menu-item<?php echo $has_divider ? ' has-divider' : ''; ?>">
						<button
							type="button"
							class="cta-sidebar-modal-menu-link"
							data-features-page="<?php echo esc_attr( $category_slug ); ?>"
						>
							<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
							<?php echo esc_html( $category_name ); ?>
							<?php if ( $all_pro ) : ?>
								<span class="cta-sidebar-modal-pro-badge"><?php esc_html_e( 'PRO', 'cta-manager' ); ?></span>
							<?php endif; ?>
							<span class="cta-sidebar-modal-menu-count"><?php echo count( $features ); ?></span>
						</button>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</nav>
	</div>

	<!-- Content Area -->
	<div class="cta-sidebar-modal-content" data-features-content>
		<!-- Sticky Header with Navigation -->
		<div class="cta-sidebar-modal-sticky-header" data-features-sticky-header>
			<div class="cta-sidebar-modal-sticky-header-left">
				<div class="cta-sidebar-modal-sticky-title">
					<span class="cta-sidebar-modal-sticky-icon" data-sticky-icon></span>
					<div class="cta-sidebar-modal-sticky-title-text">
						<h3 data-sticky-title><?php esc_html_e( 'Overview', 'cta-manager' ); ?></h3>
						</div>
					<span class="cta-sidebar-modal-sticky-badge" data-sticky-badge style="display: none;"></span>
				</div>
			</div>
			<div class="cta-sidebar-modal-sticky-header-right">
				<div class="cta-sidebar-modal-page-nav" data-page-nav>
					<button type="button" class="cta-sidebar-modal-nav-btn is-prev" data-nav-direction="prev" aria-label="<?php esc_attr_e( 'Previous', 'cta-manager' ); ?>" style="display: none;">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<span class="cta-nav-label" data-prev-label><?php esc_html_e( 'Prev', 'cta-manager' ); ?></span>
					</button>
					<button type="button" class="cta-sidebar-modal-nav-btn is-next" data-nav-direction="next" aria-label="<?php esc_attr_e( 'Next', 'cta-manager' ); ?>" style="display: none;">
						<span class="cta-nav-label" data-next-label><?php esc_html_e( 'Next', 'cta-manager' ); ?></span>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
			</div>
		</div>

		<!-- Overview Landing Page -->
		<div class="cta-sidebar-modal-page is-active" data-features-page-content="overview">
			<!-- Hero -->
			<div class="cta-overview-hero">
				<img src="<?php echo $hero_img; ?>" alt="<?php esc_attr_e( $hero_plugin_name, 'cta-manager' ); ?>" class="cta-overview-hero-logo" />
				<h2 class="cta-overview-hero-title"><?php esc_html_e( 'Your Conversion Command Center', 'cta-manager' ); ?></h2>
				<p class="cta-overview-hero-subtitle"><?php esc_html_e( 'Half the effort. Twice the insight. Maximum conversions.', 'cta-manager' ); ?></p>
				<p class="cta-overview-hero-desc"><?php esc_html_e( 'More than a button plugin — ' . $hero_plugin_name . ' is a complete conversion intelligence platform built for serious results.', 'cta-manager' ); ?></p>
			</div>

			<!-- Value Pillars -->
			<div class="cta-overview-pillars">
				<?php
				$overview_pillars = [
					[
						'icon'      => '🔗',
						'title'     => __( 'Integrations', 'cta-manager' ),
						'desc'      => __( '25+ connections to GA4, Salesforce, Slack, PostHog, HubSpot, and more. Your conversion data flows exactly where it needs to go.', 'cta-manager' ),
						'docs_page' => 'integrations-overview',
					],
					[
						'icon'      => '🤖',
						'title'     => __( 'AI & Automation', 'cta-manager' ),
						'desc'      => __( 'AI-powered insights surface what\'s working. Journey mapping traces every conversion path. Smart automation handles the busywork.', 'cta-manager' ),
						'docs_page' => 'category-ai-automation-overview',
					],
					[
						'icon'      => '📊',
						'title'     => __( 'Data & Analytics', 'cta-manager' ),
						'desc'      => __( 'Every impression, click, and conversion — tracked and visualized. 90-day extended analytics, A/B testing, and real-time event streams.', 'cta-manager' ),
						'docs_page' => 'category-analytics-overview',
					],
					[
						'icon'      => '🎨',
						'title'     => __( 'Customization', 'cta-manager' ),
						'desc'      => __( 'Point-and-click styling that covers 90% of use cases. For the other 10%, a full CSS editor and 30+ developer hooks.', 'cta-manager' ),
						'docs_page' => 'category-styling-overview',
					],
				];
				foreach ( $overview_pillars as $pillar ) :
					?>
					<div class="cta-overview-pillar">
						<div class="cta-overview-pillar-icon"><?php echo esc_html( $pillar['icon'] ); ?></div>
						<h4><?php echo esc_html( $pillar['title'] ); ?></h4>
						<p><?php echo esc_html( $pillar['desc'] ); ?></p>
						<button type="button" class="cta-learn-more-button cta-button-primary cta-overview-pillar-link" data-open-modal="#cta-docs-modal" data-docs-page="<?php echo esc_attr( $pillar['docs_page'] ); ?>" data-feature-title="<?php echo esc_attr( $pillar['title'] ); ?>">
							<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
						</button>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- VANGUARD-X -->
			<div class="cta-overview-vanguard">
				<div class="cta-overview-vanguard-header">
					<span class="cta-overview-vanguard-badge"><?php esc_html_e( 'VANGUARD-X', 'cta-manager' ); ?></span>
					<h3><?php esc_html_e( 'The Elite 10', 'cta-manager' ); ?></h3>
					<p><?php esc_html_e( 'The advance guard of CTA Manager Pro. Ten features engineered to give you an unfair advantage in conversion optimization.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-overview-vanguard-list">
					<?php
					$vanguard_features = [
						[ 'rank' => '01', 'icon' => '🧪', 'cat' => __( 'Experimentation', 'cta-manager' ), 'title' => __( 'A/B Testing', 'cta-manager' ), 'tagline' => __( 'Split-test variants and let the data pick the winner.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'A/B Testing', 'cta-manager' ) ], 'feature-ab-testing' ) ],
						[ 'rank' => '02', 'icon' => '🧠', 'cat' => __( 'AI', 'cta-manager' ), 'title' => __( 'AI Insights', 'cta-manager' ), 'tagline' => __( 'Machine-learning analysis of your conversion patterns.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'AI Insights', 'cta-manager' ) ], 'feature-ai-insights' ) ],
						[ 'rank' => '03', 'icon' => '🗺️', 'cat' => __( 'AI', 'cta-manager' ), 'title' => __( 'AI Journey Mapping', 'cta-manager' ), 'tagline' => __( 'Trace the full path from first touch to conversion.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'AI Journey Mapping', 'cta-manager' ) ], 'feature-ai-journey-mapping' ) ],
						[ 'rank' => '04', 'icon' => '📊', 'cat' => __( 'Data', 'cta-manager' ), 'title' => __( 'Extended Analytics', 'cta-manager' ), 'tagline' => __( '90-day deep analytics with granular event tracking.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'Extended Analytics (90 Days)', 'cta-manager' ), __( 'Advanced Analytics', 'cta-manager' ) ], 'category-analytics-overview' ) ],
						[ 'rank' => '05', 'icon' => '📱', 'cat' => __( 'Targeting', 'cta-manager' ), 'title' => __( 'Device & Screen Targeting', 'cta-manager' ), 'tagline' => __( 'Precision targeting by device, screen size, and orientation.', 'cta-manager' ), 'docs_page' => 'category-targeting-overview' ],
						[ 'rank' => '06', 'icon' => '🔄', 'cat' => __( 'Tools', 'cta-manager' ), 'title' => __( 'Convert-a-CTA', 'cta-manager' ), 'tagline' => __( 'Transform any CTA type into another with one click.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'Convert-a-CTA', 'cta-manager' ) ], 'feature-convert-a-cta' ) ],
						[ 'rank' => '07', 'icon' => '⏰', 'cat' => __( 'Scheduling', 'cta-manager' ), 'title' => __( 'Business Hours', 'cta-manager' ), 'tagline' => __( 'Show CTAs only when you\'re open for business.', 'cta-manager' ), 'docs_page' => $resolve_docs_page( [ __( 'Business Hours', 'cta-manager' ) ], 'feature-business-hours' ) ],
						[ 'rank' => '08', 'icon' => '🎨', 'cat' => __( 'Customization', 'cta-manager' ), 'title' => __( 'Custom Global CSS', 'cta-manager' ), 'tagline' => __( 'Site-wide styling overrides for CTA presentation.', 'cta-manager' ), 'docs_page' => 'settings-custom-css' ],
						[ 'rank' => '09', 'icon' => '🔗', 'cat' => __( 'Integrations', 'cta-manager' ), 'title' => __( '25+ Integrations', 'cta-manager' ), 'tagline' => __( 'Connect to GA4, Salesforce, Slack, HubSpot, and more.', 'cta-manager' ), 'docs_page' => 'integrations-overview' ],
						[ 'rank' => '10', 'icon' => '⚡', 'cat' => __( 'Developer', 'cta-manager' ), 'title' => __( 'Developer Hook System', 'cta-manager' ), 'tagline' => __( '30+ PHP & JS hooks for complete programmatic control.', 'cta-manager' ), 'docs_page' => 'php-hooks-overview' ],
					];
					foreach ( $vanguard_features as $vf ) :
					?>
						<div class="cta-overview-vanguard-item">
							<span class="cta-overview-vanguard-rank"><?php echo esc_html( $vf['rank'] ); ?></span>
							<span class="cta-overview-vanguard-icon"><?php echo esc_html( $vf['icon'] ); ?></span>
							<div class="cta-overview-vanguard-info">
								<div class="cta-overview-vanguard-meta">
									<h5><?php echo esc_html( $vf['title'] ); ?></h5>
									<span class="cta-overview-vanguard-cat"><?php echo esc_html( $vf['cat'] ); ?></span>
								</div>
								<p><?php echo esc_html( $vf['tagline'] ); ?></p>
							</div>
							<button type="button" class="cta-learn-more-button cta-button-primary cta-overview-vanguard-link" data-open-modal="#cta-docs-modal" data-docs-page="<?php echo esc_attr( $vf['docs_page'] ); ?>" data-feature-title="<?php echo esc_attr( $vf['title'] ); ?>">
								<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
							</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Plugin Ecosystem -->
			<div class="cta-overview-ecosystem">
				<div class="cta-overview-ecosystem-header">
					<span class="cta-overview-ecosystem-eyebrow"><?php esc_html_e( 'TopDevAmerica Suite', 'cta-manager' ); ?></span>
					<h3><?php esc_html_e( 'Build an Unstoppable Stack', 'cta-manager' ); ?></h3>
					<p><?php printf( esc_html__( '%s is powerful alone — but combine it with our other plugins and your WordPress site becomes a fully loaded conversion machine.', 'cta-manager' ), esc_html( $hero_plugin_name ) ); ?></p>
				</div>
				<div class="cta-overview-ecosystem-grid">
					<?php
					$plugins_img_url   = CTA_PLUGIN_URL . 'assets/images/plugins/';
					$ecosystem_plugins = [
						[
							'logo'       => CTA_PLUGIN_URL . 'assets/images/cta-manager-pro-logo.png',
							'title'      => __( 'CTA Manager Pro', 'cta-manager' ),
							'tagline'    => $user_has_pro ? __( 'You Are Here', 'cta-manager' ) : __( 'Unlock Pro Features', 'cta-manager' ),
							'desc'       => __( 'Create, manage, and track conversion-focused calls-to-action with targeting rules, A/B testing, and real-time analytics.', 'cta-manager' ),
							'url'        => '',
							'accent'     => 'orange',
							'combo'      => __( 'The conversion engine powering your entire growth strategy.', 'cta-manager' ),
							'open_modal' => ! $user_has_pro ? '#cta-features-modal' : '',
						],
						[
							'logo'    => $plugins_img_url . 'dashboard-widget-manager-logo.png',
							'title'   => __( 'Dashboard Widget Manager', 'cta-manager' ),
							'tagline' => __( 'Custom Dashboard Widgets', 'cta-manager' ),
							'desc'    => __( 'Build custom dashboard widgets with SQL queries, visual builder, chart support, and flexible caching.', 'cta-manager' ),
							'url'     => 'https://topdevamerica.com/plugins/dashboard-widget-manager',
							'accent'  => 'purple',
							'combo'   => __( 'Track CTA performance in real-time dashboard widgets.', 'cta-manager' ),
						],
						[
							'logo'    => $plugins_img_url . 'ai-chat-manager-logo.png',
							'title'   => __( 'AI Chat Manager', 'cta-manager' ),
							'tagline' => __( 'AI-Powered Chat', 'cta-manager' ),
							'desc'    => __( 'Add a fully customizable AI chat assistant to your WordPress site, powered by Claude or OpenAI.', 'cta-manager' ),
							'url'     => 'https://topdevamerica.com/plugins/ai-chat-manager',
							'accent'  => 'teal',
							'combo'   => __( 'AI-driven CTAs that respond to visitor conversations.', 'cta-manager' ),
						],
					];
					foreach ( $ecosystem_plugins as $ep ) :
					?>
						<div class="cta-overview-ecosystem-card cta-overview-ecosystem-card--<?php echo esc_attr( $ep['accent'] ); ?>">
							<div class="cta-overview-ecosystem-card-glow"></div>
							<div class="cta-overview-ecosystem-card-top">
								<div class="cta-overview-ecosystem-card-logo">
									<img src="<?php echo esc_url( $ep['logo'] ); ?>" alt="<?php echo esc_attr( $ep['title'] ); ?>" width="56" height="56" loading="lazy">
								</div>
								<div class="cta-overview-ecosystem-card-title">
									<span class="cta-overview-ecosystem-card-tagline"><?php echo esc_html( $ep['tagline'] ); ?></span>
									<h5><?php echo esc_html( $ep['title'] ); ?></h5>
								</div>
							</div>
							<p class="cta-overview-ecosystem-card-desc"><?php echo esc_html( $ep['desc'] ); ?></p>
							<div class="cta-overview-ecosystem-card-combo">
								<span>+</span>
								<span><?php echo esc_html( $ep['combo'] ); ?></span>
							</div>
							<?php if ( ! empty( $ep['open_modal'] ) ) : ?>
								<div class="cta-overview-ecosystem-card-footer">
									<button type="button" class="cta-overview-ecosystem-card-link" data-open-modal="<?php echo esc_attr( $ep['open_modal'] ); ?>">
										<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
										<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
									</button>
								</div>
							<?php elseif ( ! empty( $ep['url'] ) ) : ?>
								<div class="cta-overview-ecosystem-card-footer">
									<a href="<?php echo esc_url( $ep['url'] ); ?>" class="cta-overview-ecosystem-card-link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
										<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="cta-overview-ecosystem-tagline">
					<p><?php esc_html_e( 'All three plugins. One ecosystem. Zero limits.', 'cta-manager' ); ?></p>
				</div>
			</div>

			<?php if ( ! $user_has_pro ) : ?>
			<!-- Upgrade CTA -->
			<div class="cta-overview-upgrade">
				<span class="cta-features-pro-cta-glow"></span>
				<div class="cta-overview-upgrade-content">
					<span class="cta-overview-upgrade-icon-wrap">
						<span class="dashicons dashicons-star-filled cta-overview-upgrade-icon cta-animate-slow"></span>
					</span>
					<div class="cta-overview-upgrade-text">
						<div class="cta-overview-upgrade-topline">
							<strong><?php esc_html_e( 'Ready to deploy?', 'cta-manager' ); ?></strong>
							<span class="cta-pro-badge cta-pro-badge-inline" title="<?php esc_attr_e( 'Pro version available', 'cta-manager' ); ?>">
								<?php esc_html_e( 'PRO', 'cta-manager' ); ?>
							</span>
						</div>
						<span class="cta-overview-upgrade-message"><?php esc_html_e( 'Unlock the full arsenal with CTA Manager Pro.', 'cta-manager' ); ?></span>
					</div>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager&modal=pro-upgrade' ) ); ?>" class="cta-overview-upgrade-button">
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?>
				</a>
			</div>
			<?php endif; ?>
		</div>

		<?php
		$first = false;
		foreach ( $all_features as $category_name => $category_features ) :
			$category_slug = sanitize_title( $category_name );
			$active_class = $first ? ' is-active' : '';

			// Check if ALL features in this category are pro (matches sidebar PRO badge)
			$is_pro_category = ! empty( $category_features ) && 0 === count( array_filter( $category_features, function( $f ) { return ( $f['plan'] ?? 'free' ) !== 'pro'; } ) );
			?>
			<div class="cta-sidebar-modal-page<?php echo esc_attr( $active_class ); ?>" data-features-page-content="<?php echo esc_attr( $category_slug ); ?>">
				<div class="cta-sidebar-modal-page-header">
					<h3><?php echo esc_html( $category_name ); ?></h3>
					<p><?php echo esc_html( CTA_Features::get_category_description( $category_name ) ); ?></p>
				</div>

				<?php if ( $is_pro_category && ! $user_has_pro ) : ?>
					<div class="cta-features-pro-cta">
						<span class="cta-features-pro-cta-glow"></span>
						<span class="dashicons dashicons-star-filled cta-features-pro-cta-icon"></span>
						<strong class="cta-features-pro-cta-title"><?php esc_html_e( 'Unlock Pro', 'cta-manager' ); ?></strong>
						<span class="cta-pro-badge"><?php esc_html_e( 'PRO', 'cta-manager' ); ?></span>
						<span class="cta-features-pro-cta-message"><?php esc_html_e( 'Activate your Pro license for these features.', 'cta-manager' ); ?></span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-features-pro-cta-button" data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key">
							<span class="dashicons dashicons-unlock"></span>
							<?php esc_html_e( 'Add License Key', 'cta-manager' ); ?>
						</a>
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
						$docs_page      = $feature['docs_page'] ?? '';
						$plan           = $feature['plan'] ?? 'free';
						$is_implemented = ! empty( $feature['implemented'] );
						$is_pro         = 'pro' === $plan;
						$badge          = ! $is_implemented ? 'primary' : '';
						$badge_text     = '';

						include CTA_PLUGIN_DIR . 'templates/admin/partials/feature-card.php';
						unset( $icon, $title, $hook_name, $hook_type, $description, $features, $details, $instructions, $docs_page, $badge, $badge_text, $is_pro, $is_implemented, $plan );
					endforeach;
					?>
				</div>

				<?php if ( ! $is_pro_category && ! $user_has_pro ) : ?>
					<div class="cta-features-pro-cta cta-features-pro-cta--bottom">
						<span class="cta-features-pro-cta-glow"></span>
						<span class="dashicons dashicons-star-filled cta-features-pro-cta-icon"></span>
						<strong class="cta-features-pro-cta-title"><?php esc_html_e( 'Get Pro', 'cta-manager' ); ?></strong>
						<span class="cta-pro-badge"><?php esc_html_e( 'PRO', 'cta-manager' ); ?></span>
						<span class="cta-features-pro-cta-message"><?php esc_html_e( 'Unlock advanced features, integrations, and more with Pro.', 'cta-manager' ); ?></span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager&modal=pro-upgrade' ) ); ?>" class="cta-features-pro-cta-button">
							<span class="dashicons dashicons-star-filled"></span>
							<?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
			<?php
			$first = false;
		endforeach;
		?>

		<!-- Integrations Page -->
		<div class="cta-sidebar-modal-page" data-features-page-content="integrations">
			<?php if ( ! $user_has_pro ) : ?>
				<div class="cta-features-pro-cta">
					<span class="cta-features-pro-cta-glow"></span>
					<span class="dashicons dashicons-star-filled cta-features-pro-cta-icon"></span>
					<strong class="cta-features-pro-cta-title"><?php esc_html_e( 'Pro Integrations', 'cta-manager' ); ?></strong>
					<span class="cta-pro-badge"><?php esc_html_e( 'PRO', 'cta-manager' ); ?></span>
					<span class="cta-features-pro-cta-message"><?php esc_html_e( 'Activate your Pro license for integrations.', 'cta-manager' ); ?></span>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ) ); ?>" class="cta-features-pro-cta-button" data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key">
						<span class="dashicons dashicons-unlock"></span>
						<?php esc_html_e( 'Add License Key', 'cta-manager' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="cta-integrations-grid-wrapper">
				<?php foreach ( $integrations as $category_name => $items ) : ?>
					<div class="cta-integration-group">
						<h4 class="cta-feature-group-title"><?php echo esc_html( $category_name ); ?></h4>
						<div class="cta-integrations-grid">
							<?php foreach ( $items as $integration ) : ?>
								<div class="cta-integration-card<?php echo ! empty( $integration['implemented'] ) ? ' is-available' : ''; ?>" data-feature-plan="pro" data-feature-implemented="<?php echo ! empty( $integration['implemented'] ) ? '1' : '0'; ?>">
									<div class="cta-integration-header">
										<?php if ( ! empty( $integration['logos'] ) ) : ?>
											<div class="cta-integration-logos">
												<?php foreach ( $integration['logos'] as $logo_url ) : ?>
													<img src="<?php echo esc_url( $logo_url ); ?>" alt="" loading="lazy">
												<?php endforeach; ?>
											</div>
										<?php else : ?>
											<div class="cta-integration-icon">
												<?php if ( ! empty( $integration['image'] ) ) : ?>
													<img src="<?php echo esc_url( $integration['image'] ); ?>" alt="<?php echo esc_attr( $integration['title'] ); ?>" loading="lazy">
												<?php elseif ( ! empty( $integration['icon'] ) ) : ?>
													<?php echo esc_html( $integration['icon'] ); ?>
												<?php endif; ?>
											</div>
										<?php endif; ?>
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
										<span class="cta-pro-badge"><?php echo esc_html( $labels['badge_pro'] ); ?></span>
										<?php if ( empty( $integration['implemented'] ) ) : ?>
											<span class="cta-badge cta-badge-primary cta-pulse-primary"><?php echo esc_html( $labels['badge_coming_soon'] ); ?></span>
										<?php else : ?>
											<span class="cta-badge cta-badge-success"><?php echo esc_html( $labels['badge_available'] ); ?></span>
										<?php endif; ?>
											<button type="button" class="cta-learn-more-button cta-button-primary" data-open-modal="#cta-docs-modal" data-docs-page="<?php echo esc_attr( $integration['docs_page'] ?? '' ); ?>" data-feature-title="<?php echo esc_attr( $integration['title'] ); ?>" data-category-type="integration">
												<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
											</button>
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
	var layout = document.querySelector('.cta-features-modal .cta-sidebar-modal-layout');
	if (!layout) return;

	var searchInput   = layout.querySelector('[data-features-search]');
	var searchClear   = layout.querySelector('[data-features-search-clear]');
	var planBtns      = layout.querySelectorAll('[data-features-plan]');
	var soonToggle    = layout.querySelector('[data-features-soon-toggle]');
	var menuItems     = layout.querySelectorAll('[data-features-page]');
	var activePlan    = 'all';
	var showSoon      = true;
	var searchTerm    = '';
	var originalCounts = {};

	// Store original card counts per category
	menuItems.forEach(function(btn) {
		var slug = btn.dataset.featuresPage;
		var count = btn.querySelector('.cta-sidebar-modal-menu-count');
		if (count) originalCounts[slug] = parseInt(count.textContent, 10);
	});

	function applyFilters() {
		var pages = layout.querySelectorAll('[data-features-page-content]');
		var term = searchTerm.toLowerCase();

		pages.forEach(function(page) {
			var slug = page.dataset.featuresPageContent;
			if (slug === 'overview') return;

			// Handle both feature cards and integration cards
			var isIntegrations = slug === 'integrations';
			var cards = isIntegrations
				? page.querySelectorAll('.cta-integration-card')
				: page.querySelectorAll('.cta-feature-card');
			var visible = 0;

			cards.forEach(function(card) {
				var plan = card.dataset.featurePlan || (isIntegrations ? 'pro' : 'free');
				var implemented = card.dataset.featureImplemented === '1';
				var show = true;

				// Plan filter
				if (activePlan !== 'all' && plan !== activePlan) show = false;

				// Soon toggle
				if (!showSoon && !implemented) show = false;

				// Search (3+ chars)
				if (show && term.length >= 3) {
					var searchable = isIntegrations
						? (card.querySelector('h4') ? card.querySelector('h4').textContent.toLowerCase() : '')
						: (card.dataset.featureSearch || card.dataset.featureTitle || '');
					if (searchable.indexOf(term) === -1) show = false;
				}

				card.style.display = show ? '' : 'none';
				if (show) visible++;
			});

			// For integrations, also hide empty group headings
			if (isIntegrations) {
				var groups = page.querySelectorAll('.cta-integration-group');
				groups.forEach(function(group) {
					var groupCards = group.querySelectorAll('.cta-integration-card');
					var groupVisible = 0;
					groupCards.forEach(function(c) { if (c.style.display !== 'none') groupVisible++; });
					group.style.display = groupVisible > 0 ? '' : 'none';
				});
			}

			// Update sidebar count and hide menu items with 0 count
			var menuBtn = layout.querySelector('[data-features-page="' + slug + '"]');
			if (menuBtn) {
				var countEl = menuBtn.querySelector('.cta-sidebar-modal-menu-count');
				if (countEl) {
					if (slug === 'integrations' && typeof originalCounts.integrations !== 'undefined') {
						countEl.textContent = originalCounts.integrations;
					} else {
						countEl.textContent = visible;
					}
				}
				var menuItem = menuBtn.closest('.cta-sidebar-modal-menu-item');
				if (menuItem) {
					if (slug === 'integrations') {
						menuItem.style.display = '';
					} else {
						menuItem.style.display = visible > 0 ? '' : 'none';
					}
				}
			}
		});
	}

	function resetFilters() {
		searchTerm = '';
		if (searchInput) searchInput.value = '';
		updateSearchIcon();
		activePlan = 'all';
		planBtns.forEach(function(btn) {
			btn.classList.toggle('is-active', btn.dataset.featuresPlan === 'all');
		});
		if (soonToggle) {
			soonToggle.checked = true;
			showSoon = true;
		}
		applyFilters();
	}

	function updateSearchIcon() {
		if (!searchClear) return;
		var icon = searchClear.querySelector('.dashicons');
		if (!icon) return;
		if (searchInput && searchInput.value.length >= 3) {
			icon.className = 'dashicons dashicons-no-alt';
			searchClear.classList.add('has-value');
		} else {
			icon.className = 'dashicons dashicons-search';
			searchClear.classList.remove('has-value');
		}
	}

	// Search input
	if (searchInput) {
		var debounce;
		searchInput.addEventListener('input', function() {
			clearTimeout(debounce);
			debounce = setTimeout(function() {
				searchTerm = searchInput.value;
				updateSearchIcon();
				applyFilters();
			}, 200);
		});
	}

	// Search clear
	if (searchClear) {
		searchClear.addEventListener('click', function() {
			if (searchInput && searchInput.value.length > 0) {
				searchInput.value = '';
				searchTerm = '';
				updateSearchIcon();
				applyFilters();
				searchInput.focus();
			}
		});
	}

	// Plan filter buttons
	planBtns.forEach(function(btn) {
		btn.addEventListener('click', function() {
			activePlan = btn.dataset.featuresPlan;
			planBtns.forEach(function(b) { b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			applyFilters();
		});
	});

	// Soon toggle
	if (soonToggle) {
		soonToggle.addEventListener('change', function() {
			showSoon = soonToggle.checked;
			applyFilters();
		});
	}

	// Features modal navigation
	document.addEventListener('click', function(e) {
		var menuLink = e.target.closest('[data-features-page]');
		if (!menuLink) return;

		var page = menuLink.dataset.featuresPage;
		var modal = menuLink.closest('.cta-sidebar-modal-layout');
		if (!modal) return;

		modal.querySelectorAll('[data-features-page]').forEach(function(link) {
			link.classList.remove('is-active');
		});
		menuLink.classList.add('is-active');

		modal.querySelectorAll('[data-features-page-content]').forEach(function(content) {
			content.classList.remove('is-active');
		});
		var targetContent = modal.querySelector('[data-features-page-content="' + page + '"]');
		if (targetContent) {
			targetContent.classList.add('is-active');
		}
	});

	// Learn More button handler
	document.addEventListener('click', function(e) {
		var learnMoreBtn = e.target.closest('.cta-learn-more-button');
		if (!learnMoreBtn) return;
		e.preventDefault();
		e.stopPropagation();
		if (typeof e.stopImmediatePropagation === 'function') {
			e.stopImmediatePropagation();
		}

		var docsPage = learnMoreBtn.dataset.docsPage;
		var featureTitle = learnMoreBtn.dataset.featureTitle || '';
		var categoryType = learnMoreBtn.dataset.categoryType || 'feature';
		var targetPage = docsPage || 'welcome';
		var hasPageContent = function(pageId) {
			if (!pageId) return false;
			return !!document.querySelector('#cta-docs-modal [data-docs-page-content="' + pageId + '"]');
		};

		// Guard against invalid docs slugs leaving the modal on previous page state.
		if (!hasPageContent(targetPage)) {
			targetPage = 'welcome';
		}

		if (window.CTADocsModal) {
			var $docsModal = jQuery('#cta-docs-modal');
			var $targetLink = $docsModal.find('[data-docs-page="' + targetPage + '"]').first();

			// If page ID wasn't valid, try to resolve by feature title against docs nav labels.
			if ((!$targetLink.length || !hasPageContent(targetPage)) && featureTitle) {
				$docsModal.find('.cta-docs-submenu-link').each(function() {
					var text = (jQuery(this).text() || '').replace(/\s*(PRO|Soon|Coming Soon)\s*/gi, '').trim().toLowerCase();
					if (text === featureTitle.toLowerCase()) {
						var candidate = jQuery(this).attr('data-docs-page') || '';
						if (hasPageContent(candidate)) {
							targetPage = candidate;
							$targetLink = jQuery(this);
							return false;
						}
					}
				});
			}

			if (!hasPageContent(targetPage)) {
				targetPage = 'welcome';
				$targetLink = $docsModal.find('[data-docs-page="welcome"]').first();
			}

			window.CTADocsModal.collapseAllAccordions();
			window.CTADocsModal.showPage(targetPage);
			window.CTADocsModal.setActiveLink($targetLink.length ? $targetLink : $docsModal.find('[data-docs-page="welcome"]').first());
			if (window.ctaModalAPI && typeof window.ctaModalAPI.open === 'function') {
				window.ctaModalAPI.open($docsModal, { trigger: learnMoreBtn });
			} else {
				$docsModal.fadeIn(150);
				jQuery('body').addClass('cta-modal-open');
			}
		} else if (window.CTAManager && window.CTAManager.openDocumentationModalToPage) {
			if (docsPage && hasPageContent(docsPage)) {
				window.CTAManager.openDocumentationModalToPage(targetPage);
			} else if (window.CTAManager.openDocumentationModal && featureTitle) {
				window.CTAManager.openDocumentationModal(featureTitle, categoryType);
			} else {
				window.CTAManager.openDocumentationModalToPage('welcome');
			}
		}
	});

	// Reset on modal close
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(m) {
			if (m.type === 'attributes' && m.attributeName === 'class') {
				var modal = document.querySelector('.cta-features-modal');
				if (modal && !modal.classList.contains('active') && !modal.style.display !== 'none') {
					resetFilters();
				}
			}
		});
	});
	var featuresModalEl = document.querySelector('.cta-features-modal');
	if (featuresModalEl) {
		observer.observe(featuresModalEl, { attributes: true, attributeFilter: ['class', 'style'] });
	}
})();
</script>
