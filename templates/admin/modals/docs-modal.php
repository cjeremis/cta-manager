<?php
/**
 * Documentation Modal Body
 *
 * Dynamically renders sidebar and content using the centralized features data.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Modals
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTA_Features' ) ) {
	require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-features.php';
}

$is_pro            = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$features          = CTA_Features::get_all_features();
$integrations      = CTA_Features::get_all_integrations();
$categories_meta   = CTA_Features::get_categories();
$integrations_meta = CTA_Features::get_integrations_meta();
$labels            = CTA_Features::get_labels();
$menu_coming_label = __( 'Soon', 'cta-manager' ); // Short label for menu navigation

// Hook categories for organization
$cta_hook_categories = [
	'core-events' => [
		'label' => __( 'Core Events', 'cta-manager' ),
		'icon'  => 'dashicons-admin-generic',
		'order' => 1,
	],
	'layout-events' => [
		'label' => __( 'Layout Events', 'cta-manager' ),
		'icon'  => 'dashicons-admin-appearance',
		'order' => 2,
	],
	'testing' => [
		'label' => __( 'Testing & Optimization', 'cta-manager' ),
		'icon'  => 'dashicons-chart-line',
		'order' => 3,
	],
	'admin-ui' => [
		'label' => __( 'Admin & UI', 'cta-manager' ),
		'icon'  => 'dashicons-admin-settings',
		'order' => 4,
	],
];

// Canonical list of front-end hooks for documentation/navigation.
$cta_docs_hooks = [
	[
		'id'          => 'cta-loaded',
		'title'       => __( 'CTA Loaded', 'cta-manager' ),
		'description' => __( 'Fires after CTAs finish initializing on the page.', 'cta-manager' ),
		'category'    => 'core-events',
		'payload'     => [
			[ 'key' => 'ctaId',   'summary' => __( 'CTA ID that finished booting', 'cta-manager' ) ],
			[ 'key' => 'layout',  'summary' => __( 'button|card|popup|slide-in', 'cta-manager' ) ],
			[ 'key' => 'pageUrl', 'summary' => __( 'Current page URL', 'cta-manager' ) ],
			[ 'key' => 'device',  'summary' => __( 'mobile|tablet|desktop', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Log all loaded CTAs', 'cta-manager' ),
				'description' => __( 'Useful to verify placement and ensure targeting wasn’t skipped.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:loaded', ({ detail }) => {\n  console.debug('CTA loaded', detail.ctaId, detail.layout, detail.device);\n});",
			],
		],
	],
	[
		'id'          => 'cta-impression',
		'title'       => __( 'CTA Impressions', 'cta-manager' ),
		'description' => __( 'Emitted when a CTA becomes visible and records an impression.', 'cta-manager' ),
		'category'    => 'core-events',
		'payload'     => [
			[ 'key' => 'ctaId',   'summary' => __( 'CTA ID shown', 'cta-manager' ) ],
			[ 'key' => 'layout',  'summary' => __( 'button/card/popup/slide-in', 'cta-manager' ) ],
			[ 'key' => 'pageUrl', 'summary' => __( 'URL where impression occurred', 'cta-manager' ) ],
			[ 'key' => 'device',  'summary' => __( 'mobile|tablet|desktop', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Push to dataLayer', 'cta-manager' ),
				'description' => __( 'Send impressions to GTM/GA4 without editing plugin code.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:impression', ({ detail }) => {\n  window.dataLayer = window.dataLayer || [];\n  dataLayer.push({\n    event: 'cta_impression',\n    ctaId: detail.ctaId,\n    layout: detail.layout,\n    device: detail.device,\n    page: detail.pageUrl,\n  });\n});",
			],
		],
	],
	[
		'id'          => 'cta-clicked',
		'title'       => __( 'CTA Clicked', 'cta-manager' ),
		'description' => __( 'Triggered on CTA click before navigation/handler runs.', 'cta-manager' ),
		'category'    => 'core-events',
		'payload'     => [
			[ 'key' => 'ctaId',     'summary' => __( 'CTA ID clicked', 'cta-manager' ) ],
			[ 'key' => 'targetUrl', 'summary' => __( 'Href/mailto/tel or popup action', 'cta-manager' ) ],
			[ 'key' => 'variant',   'summary' => __( 'A/B variant when present', 'cta-manager' ) ],
			[ 'key' => 'pageUrl',   'summary' => __( 'Page where click occurred', 'cta-manager' ) ],
			[ 'key' => 'device',    'summary' => __( 'mobile|tablet|desktop', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'GA4 event with parameters', 'cta-manager' ),
				'description' => __( 'Send click data to GA4 with CTA metadata.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:clicked', ({ detail }) => {\n  if (!window.gtag) return;\n  gtag('event', 'cta_click', {\n    cta_id: detail.ctaId,\n    target: detail.targetUrl,\n    variant: detail.variant || 'control',\n    page_location: detail.pageUrl,\n  });\n});",
			],
			[
				'label'       => __( 'Prevent certain clicks', 'cta-manager' ),
				'description' => __( 'Block navigation for specific CTAs (e.g., feature flags).', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:clicked', (event) => {\n  const { ctaId } = event.detail || {};\n  if ([101, 202].includes(Number(ctaId))) {\n    event.preventDefault?.();\n    alert('This CTA is currently disabled.');\n  }\n});",
			],
		],
	],
	[
		'id'          => 'cta-conversion',
		'title'       => __( 'CTA Conversion', 'cta-manager' ),
		'description' => __( 'Dispatched when a tracked conversion is recorded.', 'cta-manager' ),
		'category'    => 'core-events',
		'payload'     => [
			[ 'key' => 'ctaId',   'summary' => __( 'CTA ID that converted', 'cta-manager' ) ],
			[ 'key' => 'variant', 'summary' => __( 'A/B variant if test is running', 'cta-manager' ) ],
			[ 'key' => 'pageUrl', 'summary' => __( 'Conversion page URL', 'cta-manager' ) ],
			[ 'key' => 'meta',    'summary' => __( 'Additional conversion metadata (if any)', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Send to Segment', 'cta-manager' ),
				'description' => __( 'Forward conversions to Segment for downstream destinations.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:conversion', ({ detail }) => {\n  if (!window.analytics) return;\n  analytics.track('CTA Conversion', {\n    ctaId: detail.ctaId,\n    variant: detail.variant || 'control',\n    page: detail.pageUrl,\n    meta: detail.meta || {},\n  });\n});",
			],
		],
	],
	[
		'id'          => 'cta-popup',
		'title'       => __( 'CTA Popup', 'cta-manager' ),
		'description' => __( 'Popup lifecycle events (open/close).', 'cta-manager' ),
		'category'    => 'layout-events',
		'payload'     => [
			[ 'key' => 'ctaId', 'summary' => __( 'Popup CTA ID', 'cta-manager' ) ],
			[ 'key' => 'state', 'summary' => __( 'open | close', 'cta-manager' ) ],
			[ 'key' => 'pageUrl', 'summary' => __( 'Page URL where state changed', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Lock body scroll on open', 'cta-manager' ),
				'description' => __( 'Prevent background scrolling while popup is open.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:popup', ({ detail }) => {\n  if (detail.state === 'open') {\n    document.body.style.overflow = 'hidden';\n  } else {\n    document.body.style.overflow = '';\n  }\n});",
			],
			[
				'label'       => __( 'Start timer on open', 'cta-manager' ),
				'description' => __( 'Trigger countdowns or delayed actions when popup appears.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:popup', ({ detail }) => {\n  if (detail.state !== 'open') return;\n  setTimeout(() => {\n    console.log('Popup has been open for 5s', detail.ctaId);\n  }, 5000);\n});",
			],
		],
	],
	[
		'id'          => 'cta-slide-in',
		'title'       => __( 'CTA Slide In', 'cta-manager' ),
		'description' => __( 'Slide-in lifecycle events with position data.', 'cta-manager' ),
		'category'    => 'layout-events',
		'payload'     => [
			[ 'key' => 'ctaId',    'summary' => __( 'Slide-in CTA ID', 'cta-manager' ) ],
			[ 'key' => 'state',    'summary' => __( 'open | close', 'cta-manager' ) ],
			[ 'key' => 'position', 'summary' => __( 'e.g., bottom-right, top-left', 'cta-manager' ) ],
			[ 'key' => 'pageUrl',  'summary' => __( 'Page URL where state changed', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Log position/state', 'cta-manager' ),
				'description' => __( 'Capture slide-in behavior for UX analysis.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:slide_in', ({ detail }) => {\n  console.info('Slide-in', detail.state, detail.position, detail.ctaId);\n});",
			],
			[
				'label'       => __( 'Pause video while open', 'cta-manager' ),
				'description' => __( 'If a hero video is playing, pause while slide-in is open.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:slide_in', ({ detail }) => {\n  const video = document.querySelector('video#hero');\n  if (!video) return;\n  if (detail.state === 'open') {\n    video.pause();\n  } else {\n    video.play();\n  }\n});",
			],
		],
	],
	[
		'id'          => 'cta-ab-test',
		'title'       => __( 'CTA A/B Test', 'cta-manager' ),
		'description' => __( 'A/B test assignment and events.', 'cta-manager' ),
		'category'    => 'testing',
		'payload'     => [
			[ 'key' => 'experimentKey', 'summary' => __( 'Unique experiment identifier', 'cta-manager' ) ],
			[ 'key' => 'variant',       'summary' => __( 'Assigned variant (a|b)', 'cta-manager' ) ],
			[ 'key' => 'eventType',     'summary' => __( 'assigned | impression | click | conversion', 'cta-manager' ) ],
			[ 'key' => 'ctaId',         'summary' => __( 'CTA ID involved', 'cta-manager' ) ],
			[ 'key' => 'pageUrl',       'summary' => __( 'Page URL of the event', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Store assignment in localStorage', 'cta-manager' ),
				'description' => __( 'Keep visitor variant sticky for custom flows.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:ab_test', ({ detail }) => {\n  if (detail.eventType === 'assigned') {\n    localStorage.setItem('cta_ab_variant', detail.variant);\n  }\n});",
			],
			[
				'label'       => __( 'Send to Amplitude', 'cta-manager' ),
				'description' => __( 'Log variant events to product analytics.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:ab_test', ({ detail }) => {\n  if (!window.amplitude) return;\n  amplitude.getInstance().logEvent('CTA AB Test', {\n    experiment_key: detail.experimentKey,\n    variant: detail.variant,\n    event_type: detail.eventType,\n    cta_id: detail.ctaId,\n    page: detail.pageUrl,\n  });\n});",
			],
		],
	],
	[
		'id'          => 'cta-ga4',
		'title'       => __( 'CTA GA4', 'cta-manager' ),
		'description' => __( 'Helper event emitted prior to GA4 forwarding.', 'cta-manager' ),
		'integration' => true,
		'payload'     => [
			[ 'key' => 'ctaId',     'summary' => __( 'CTA ID related to GA4 event', 'cta-manager' ) ],
			[ 'key' => 'eventName', 'summary' => __( 'GA4 event name about to be sent', 'cta-manager' ) ],
			[ 'key' => 'params',    'summary' => __( 'Parameters object passed to gtag', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Augment GA4 params', 'cta-manager' ),
				'description' => __( 'Inject additional parameters before gtag fires.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:ga4', (event) => {\n  if (!event.detail?.params) return;\n  event.detail.params.site_section = 'marketing';\n});",
			],
		],
	],
	[
		'id'          => 'cta-tab-changed',
		'title'       => __( 'CTA Tab Changed', 'cta-manager' ),
		'description' => __( 'Admin/documentation tab change (includes target panel).', 'cta-manager' ),
		'category'    => 'admin-ui',
		'payload'     => [
			[ 'key' => 'tab',   'summary' => __( 'Tab slug that became active', 'cta-manager' ) ],
			[ 'key' => 'panel', 'summary' => __( 'DOM node of the target panel (may be null)', 'cta-manager' ) ],
		],
		'examples'    => [
			[
				'label'       => __( 'Remember docs tab', 'cta-manager' ),
				'description' => __( 'Persist last-open docs tab across reloads.', 'cta-manager' ),
				'code'        => "document.addEventListener('cta:tab:changed', ({ detail }) => {\n  sessionStorage.setItem('cta_docs_tab', detail.tab);\n});",
			],
		],
	],
];

// Group hooks by category
$hooks_by_category = [];
foreach ( $cta_docs_hooks as $hook ) {
	$category = $hook['category'] ?? 'core-events';
	$is_integration = ! empty( $hook['integration'] );

	if ( $is_integration ) {
		continue; // Integration hooks handled separately
	}

	if ( ! isset( $hooks_by_category[ $category ] ) ) {
		$hooks_by_category[ $category ] = [];
	}
	$hooks_by_category[ $category ][] = $hook;
}

// Sort categories by order
uksort(
	$hooks_by_category,
	function ( $a, $b ) use ( $cta_hook_categories ) {
		$order_a = $cta_hook_categories[ $a ]['order'] ?? 999;
		$order_b = $cta_hook_categories[ $b ]['order'] ?? 999;
		return $order_a <=> $order_b;
	}
);

// Sort hooks within each category alphabetically
foreach ( $hooks_by_category as $category => $hooks ) {
	usort(
		$hooks_by_category[ $category ],
		function ( $a, $b ) {
			return strcasecmp( $a['title'], $b['title'] );
		}
	);
}

// Get integration hooks separately
$integration_hooks = array_filter(
	$cta_docs_hooks,
	function ( $hook ) {
		return ! empty( $hook['integration'] );
	}
);

// Sort integration hooks alphabetically
usort(
	$integration_hooks,
	function ( $a, $b ) {
		return strcasecmp( $a['title'], $b['title'] );
	}
);

/**
 * Strip parenthetical text from a label.
 *
 * @param string $text
 * @return string
 */
$cta_docs_strip_paren = function ( string $text ): string {
	return trim( preg_replace( '/\s*\(.*?\)/', '', $text ) );
};

// Sort feature categories by declared order (fallback to current order).
$category_names = array_values(
	array_filter(
		array_keys( $features ),
		function( $name ) {
			// Keep hooks only under Hooks menu, not under Features
			$hooks = [
				__( 'PHP Hooks', 'cta-manager' ),
				__( 'JavaScript Hooks', 'cta-manager' ),
				__( 'JS Hooks', 'cta-manager' ),
			];
			return ! in_array( $name, $hooks, true );
		}
	)
);
if ( ! empty( $categories_meta ) ) {
	usort(
		$category_names,
		function ( $a, $b ) use ( $categories_meta ) {
			$ao = $categories_meta[ $a ]['order'] ?? 999;
			$bo = $categories_meta[ $b ]['order'] ?? 999;
			return $ao <=> $bo;
		}
	);
}

// Preserve integration category order as defined in data file.
$integration_category_names = array_keys( $integrations );
?>

<div class="cta-docs-layout">
	<!-- Sidebar -->
	<div class="cta-docs-sidebar">
		<!-- Search -->
		<div class="cta-docs-search">
			<input
				type="text"
				class="cta-docs-search-input"
				placeholder="<?php esc_attr_e( 'Search documentation...', 'cta-manager' ); ?>"
				data-docs-search
			>
		</div>

		<!-- Navigation -->
		<nav class="cta-docs-nav">
			<ul class="cta-docs-accordion" data-docs-accordion>
				<!-- Welcome -->
				<li class="cta-docs-accordion-item" data-search-title="Welcome Getting Started Overview">
					<button
						type="button"
						class="cta-docs-submenu-link is-active"
						data-docs-page="welcome"
						style="padding-left: 16px; font-weight: 600;"
					>
						<span class="dashicons dashicons-admin-home" style="margin-right: 8px;"></span>
						<?php esc_html_e( 'Welcome', 'cta-manager' ); ?>
					</button>
				</li>

				<!-- Features Section (dynamic) -->
				<li class="cta-docs-accordion-item" data-search-title="Features CTA Types Layout Display Analytics Targeting">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-features-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-star-filled"></span>
							<?php esc_html_e( 'Features', 'cta-manager' ); ?>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-features-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<!-- Shortcode Usage (direct link, not nested accordion) -->
							<li class="cta-docs-submenu-item" data-search-title="Shortcode Usage Shortcodes Embed">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="feature-shortcode-usage">
									<span class="dashicons dashicons-shortcode" style="margin-right: 6px; font-size: 14px;"></span>
									<?php esc_html_e( 'Shortcode Usage', 'cta-manager' ); ?>
								</button>
							</li>
							<?php foreach ( $category_names as $category_name ) :
								// Skip Shortcode Usage in the loop - it's rendered above as a direct link
								if ( __( 'Shortcode Usage', 'cta-manager' ) === $category_name ) {
									continue;
								}
							?>
								<?php
								$cat_icon = $categories_meta[ $category_name ]['icon'] ?? '';
								$cat_icon = $cat_icon ? 'dashicons-' . trim( $cat_icon ) : 'dashicons-admin-generic';
								$cat_label = $cta_docs_strip_paren( $category_name );
								$submenu_id = 'docs-submenu-' . sanitize_title( $category_name );
								?>
								<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php echo esc_attr( $cat_label ); ?>">
									<button
										type="button"
										class="cta-docs-submenu-header"
										aria-expanded="false"
										aria-controls="<?php echo esc_attr( $submenu_id ); ?>"
										data-submenu-trigger
									>
										<span class="cta-docs-submenu-header-text">
											<span class="dashicons <?php echo esc_attr( $cat_icon ); ?>"></span>
											<?php echo esc_html( $cat_label ); ?>
										</span>
										<span class="cta-docs-submenu-icon">
											<span class="dashicons dashicons-arrow-down-alt2"></span>
										</span>
									</button>
									<ul class="cta-docs-submenu-panel" id="<?php echo esc_attr( $submenu_id ); ?>" hidden>
										<?php foreach ( $features[ $category_name ] as $feature ) : ?>
											<?php
											$badge_html = '';
											$is_pro     = ( 'pro' === ( $feature['plan'] ?? 'free' ) );
											$is_soon    = empty( $feature['implemented'] );

											if ( $is_soon ) {
												// Only show Soon badge; skip Pro to avoid doubles.
												$badge_html .= '<span class="cta-docs-coming-badge">' . esc_html( $menu_coming_label ) . '</span>';
											} elseif ( $is_pro ) {
												$badge_html .= '<span class="cta-docs-pro-badge">' . esc_html( $labels['badge_pro'] ?? 'Pro' ) . '</span>';
											}

											$menu_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
											?>
											<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $menu_title ); ?>">
												<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $feature['docs_page'] ); ?>">
													<?php echo esc_html( $menu_title ); ?>
													<?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</button>
											</li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>

				<!-- Integrations Section (dynamic) -->
				<li class="cta-docs-accordion-item" data-search-title="Integrations">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-integrations-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-<?php echo esc_attr( $integrations_meta['icon'] ?? 'admin-plugins' ); ?>"></span>
							<?php echo esc_html( $integrations_meta['label'] ?? __( 'Integrations', 'cta-manager' ) ); ?>
							<span class="cta-docs-pro-badge">PRO</span>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-integrations-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<?php foreach ( $integration_category_names as $category_name ) : ?>
								<?php
								$cat_label = $cta_docs_strip_paren( $category_name );
								$submenu_id = 'docs-submenu-integration-' . sanitize_title( $category_name );
								?>
								<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php echo esc_attr( $cat_label ); ?>">
									<button
										type="button"
										class="cta-docs-submenu-header"
										aria-expanded="false"
										aria-controls="<?php echo esc_attr( $submenu_id ); ?>"
										data-submenu-trigger
									>
										<span class="cta-docs-submenu-header-text">
											<span class="dashicons dashicons-admin-generic"></span>
											<?php echo esc_html( $cat_label ); ?>
										</span>
										<span class="cta-docs-submenu-icon">
											<span class="dashicons dashicons-arrow-down-alt2"></span>
										</span>
									</button>
									<ul class="cta-docs-submenu-panel" id="<?php echo esc_attr( $submenu_id ); ?>" hidden>
										<?php foreach ( $integrations[ $category_name ] as $integration ) : ?>
											<?php
											$badge_html = '';
											if ( empty( $integration['implemented'] ) ) {
												$badge_html .= '<span class="cta-docs-coming-badge">' . esc_html( $menu_coming_label ) . '</span>';
											}
											$menu_title = $cta_docs_strip_paren( $integration['title'] ?? '' );
											?>
											<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $menu_title ); ?>">
												<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $integration['docs_page'] ); ?>">
													<?php echo esc_html( $menu_title ); ?>
													<?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</button>
											</li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>

				<!-- Hooks Section -->
				<li class="cta-docs-accordion-item" data-search-title="Hooks JavaScript Events">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-hooks-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-admin-plugins"></span>
							<?php esc_html_e( 'Hooks', 'cta-manager' ); ?>
							<span class="cta-docs-pro-badge"><?php esc_html_e( 'Pro', 'cta-manager' ); ?></span>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-hooks-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php esc_attr_e( 'JavaScript Hooks', 'cta-manager' ); ?>">
								<button
									type="button"
									class="cta-docs-submenu-header"
									aria-expanded="false"
									aria-controls="docs-submenu-js-hooks"
									data-submenu-trigger
								>
									<span class="cta-docs-submenu-header-text">
										<span class="dashicons dashicons-admin-plugins"></span>
										<?php esc_html_e( 'JavaScript Hooks', 'cta-manager' ); ?>
									</span>
									<span class="cta-docs-submenu-icon">
										<span class="dashicons dashicons-arrow-down-alt2"></span>
									</span>
								</button>
								<ul class="cta-docs-submenu-panel" id="docs-submenu-js-hooks" hidden>
									<li class="cta-docs-submenu-item" data-search-title="CTA JS Hooks overview">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="hooks-overview">
											<?php esc_html_e( 'CTA JS Hooks', 'cta-manager' ); ?>
										</button>
									</li>
									<li class="cta-docs-submenu-item" data-search-title="CTAProHooks implementation helpers">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="hooks-ctaprohooks">
											<?php esc_html_e( 'Helper Functions', 'cta-manager' ); ?>
										</button>
									</li>

									<?php
									// Render hook categories
									foreach ( $hooks_by_category as $category_key => $category_hooks ) :
										$category_meta = $cta_hook_categories[ $category_key ] ?? [];
										$category_label = $category_meta['label'] ?? $category_key;
										$category_icon = $category_meta['icon'] ?? 'dashicons-admin-generic';
										$submenu_id = 'docs-submenu-hooks-' . sanitize_title( $category_key );
										?>
										<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php echo esc_attr( $category_label ); ?>">
											<button
												type="button"
												class="cta-docs-submenu-header"
												aria-expanded="false"
												aria-controls="<?php echo esc_attr( $submenu_id ); ?>"
												data-submenu-trigger
											>
												<span class="cta-docs-submenu-header-text">
													<span class="dashicons <?php echo esc_attr( $category_icon ); ?>"></span>
													<?php echo esc_html( $category_label ); ?>
												</span>
												<span class="cta-docs-submenu-icon">
													<span class="dashicons dashicons-arrow-down-alt2"></span>
												</span>
											</button>
											<ul class="cta-docs-submenu-panel" id="<?php echo esc_attr( $submenu_id ); ?>" hidden>
												<?php foreach ( $category_hooks as $hook ) : ?>
													<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $hook['title'] ); ?>">
														<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
															<?php echo esc_html( $hook['title'] ); ?>
														</button>
													</li>
												<?php endforeach; ?>
											</ul>
										</li>
									<?php endforeach; ?>

									<?php if ( ! empty( $integration_hooks ) ) : ?>
										<!-- Integration Hooks Category -->
										<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php esc_attr_e( 'Integration Hooks', 'cta-manager' ); ?>">
											<button
												type="button"
												class="cta-docs-submenu-header"
												aria-expanded="false"
												aria-controls="docs-submenu-hooks-integrations"
												data-submenu-trigger
											>
												<span class="cta-docs-submenu-header-text">
													<span class="dashicons dashicons-admin-plugins"></span>
													<?php esc_html_e( 'Integration Hooks', 'cta-manager' ); ?>
												</span>
												<span class="cta-docs-submenu-icon">
													<span class="dashicons dashicons-arrow-down-alt2"></span>
												</span>
											</button>
											<ul class="cta-docs-submenu-panel" id="docs-submenu-hooks-integrations" hidden>
												<?php
												// Render integration hooks
												foreach ( $integration_hooks as $hook ) :
													?>
													<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $hook['title'] ); ?>">
														<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
															<?php echo esc_html( $hook['title'] ); ?>
														</button>
													</li>
												<?php endforeach; ?>
											</ul>
										</li>
									<?php endif; ?>
								</ul>
							</li>

							<!-- PHP Hooks Submenu -->
							<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php esc_attr_e( 'PHP Hooks', 'cta-manager' ); ?>">
								<button
									type="button"
									class="cta-docs-submenu-header"
									aria-expanded="false"
									aria-controls="docs-submenu-php-hooks"
									data-submenu-trigger
								>
									<span class="cta-docs-submenu-header-text">
										<span class="dashicons dashicons-editor-code"></span>
										<?php esc_html_e( 'PHP Hooks', 'cta-manager' ); ?>
									</span>
									<span class="cta-docs-submenu-icon">
										<span class="dashicons dashicons-arrow-down-alt2"></span>
									</span>
								</button>
								<ul class="cta-docs-submenu-panel" id="docs-submenu-php-hooks" hidden>
									<li class="cta-docs-submenu-item" data-search-title="PHP Hooks overview">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="php-hooks-overview">
											<?php esc_html_e( 'PHP Hooks Overview', 'cta-manager' ); ?>
										</button>
									</li>
									<li class="cta-docs-submenu-item" data-search-title="WordPress hook helpers functions">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="php-hooks-helpers">
											<?php esc_html_e( 'Helper Functions', 'cta-manager' ); ?>
										</button>
									</li>

									<?php
									// Get PHP hooks from PHP Hooks category
									$php_hooks_features = $features[ __( 'PHP Hooks', 'cta-manager' ) ] ?? [];
									$php_hooks_all = array_filter(
										$php_hooks_features,
										function ( $feature ) {
											return ! empty( $feature['hook_type'] );
										}
									);

									// Define PHP hook categories
									$php_hook_categories = [
										'rendering' => [
											'label' => __( 'Rendering & Display', 'cta-manager' ),
											'icon'  => 'dashicons-visibility',
											'order' => 1,
										],
										'database' => [
											'label' => __( 'Database Operations', 'cta-manager' ),
											'icon'  => 'dashicons-database',
											'order' => 2,
										],
										'permissions' => [
											'label' => __( 'Permissions & Access', 'cta-manager' ),
											'icon'  => 'dashicons-lock',
											'order' => 3,
										],
										'configuration' => [
											'label' => __( 'Configuration', 'cta-manager' ),
											'icon'  => 'dashicons-admin-settings',
											'order' => 4,
										],
									];

									// Group PHP hooks by category
									$php_hooks_by_category = [];
									foreach ( $php_hooks_all as $php_hook ) {
										$category = $php_hook['hook_category'] ?? 'other';
										if ( ! isset( $php_hooks_by_category[ $category ] ) ) {
											$php_hooks_by_category[ $category ] = [];
										}
										$php_hooks_by_category[ $category ][] = $php_hook;
									}

									// Sort categories by order
									uksort(
										$php_hooks_by_category,
										function ( $a, $b ) use ( $php_hook_categories ) {
											$order_a = $php_hook_categories[ $a ]['order'] ?? 999;
											$order_b = $php_hook_categories[ $b ]['order'] ?? 999;
											return $order_a <=> $order_b;
										}
									);

									// Sort hooks within each category by hook type (actions first, then filters) and title
									foreach ( $php_hooks_by_category as $category => $category_hooks ) {
										usort(
											$php_hooks_by_category[ $category ],
											function ( $a, $b ) {
												$type_priority = [ 'action' => 0, 'filter' => 1 ];
												$a_priority = $type_priority[ $a['hook_type'] ?? 'filter' ] ?? 2;
												$b_priority = $type_priority[ $b['hook_type'] ?? 'filter' ] ?? 2;

												if ( $a_priority === $b_priority ) {
													return strcasecmp( $a['title'], $b['title'] );
												}
												return $a_priority <=> $b_priority;
											}
										);
									}

									// Render hook categories
									foreach ( $php_hooks_by_category as $category_key => $category_hooks ) :
										$category_meta = $php_hook_categories[ $category_key ] ?? [];
										$category_label = $category_meta['label'] ?? $category_key;
										$category_icon = $category_meta['icon'] ?? 'dashicons-admin-generic';
										$submenu_id = 'docs-submenu-php-hooks-' . sanitize_title( $category_key );
										?>
										<li class="cta-docs-submenu-item cta-docs-submenu-accordion" data-search-title="<?php echo esc_attr( $category_label ); ?>">
											<button
												type="button"
												class="cta-docs-submenu-header"
												aria-expanded="false"
												aria-controls="<?php echo esc_attr( $submenu_id ); ?>"
												data-submenu-trigger
											>
												<span class="cta-docs-submenu-header-text">
													<span class="dashicons <?php echo esc_attr( $category_icon ); ?>"></span>
													<?php echo esc_html( $category_label ); ?>
												</span>
												<span class="cta-docs-submenu-icon">
													<span class="dashicons dashicons-arrow-down-alt2"></span>
												</span>
											</button>
											<ul class="cta-docs-submenu-panel" id="<?php echo esc_attr( $submenu_id ); ?>" hidden>
												<?php foreach ( $category_hooks as $php_hook ) :
													$page_slug = 'php-hook-' . sanitize_title( str_replace( [ 'cta_', 'cta_pro_', 'cta_db_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
													$hook_name = $php_hook['hook_name'] ?? '';
													$friendly_title = $php_hook['title'] ?? '';
													?>
													<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $hook_name . ' ' . $friendly_title ); ?>">
														<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
															<?php echo esc_html( $friendly_title ); ?>
														</button>
													</li>
												<?php endforeach; ?>
											</ul>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						</ul>
					</div>
				</li>

				<!-- Settings Section -->
				<li class="cta-docs-accordion-item" data-search-title="Settings Analytics Custom CSS Performance Custom Icons Data Management">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-settings-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-admin-settings"></span>
							<?php esc_html_e( 'Settings', 'cta-manager' ); ?>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-settings-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<li class="cta-docs-submenu-item" data-search-title="Analytics Tracking Data Retention">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-analytics">
									<?php esc_html_e( 'Analytics', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Custom CSS Styling Overrides">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-custom-css">
									<?php esc_html_e( 'Custom CSS', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Performance Footer Scripts">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-performance">
									<?php esc_html_e( 'Performance', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Custom Icons Upload SVG">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-custom-icons">
									<?php esc_html_e( 'Custom Icons', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Data Management Cleanup Uninstall">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-data-management">
									<?php esc_html_e( 'Data Management', 'cta-manager' ); ?>
								</button>
							</li>
						</ul>
					</div>
				</li>

				<!-- Tools Section -->
				<li class="cta-docs-accordion-item" data-search-title="Tools Export Import Demo Reset">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-tools-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Tools', 'cta-manager' ); ?>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-tools-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<li class="cta-docs-submenu-item" data-search-title="Export Data JSON Download">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-export">
									<?php esc_html_e( 'Export Data', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Import Data Merge Backup">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-import">
									<?php esc_html_e( 'Import Data', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Demo Data Import Delete">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-demo">
									<?php esc_html_e( 'Demo Data', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Reset Data Analytics CTAs">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-reset">
									<?php esc_html_e( 'Reset Data', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Debug Mode Logs Errors">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-debug">
									<?php esc_html_e( 'Debug Mode', 'cta-manager' ); ?>
								</button>
							</li>
						</ul>
					</div>
				</li>
			</ul>
		</nav>
	</div>

	<!-- Content Area -->
	<div class="cta-docs-content" data-docs-content>
		<!-- Welcome Page -->
		<div class="cta-docs-page is-active" data-docs-page-content="welcome">
			<div class="cta-docs-welcome">
				<div class="cta-docs-welcome-icon">
					<span class="dashicons dashicons-book-alt"></span>
				</div>
				<h2><?php esc_html_e( 'CTA Manager Documentation', 'cta-manager' ); ?></h2>
				<p><?php esc_html_e( 'Welcome to the CTA Manager documentation. Here you\'ll find comprehensive guides and documentation to help you get the most out of CTA Manager and start converting visitors into customers.', 'cta-manager' ); ?></p>
			</div>
		</div>

		<!-- Action Hooks Page -->
		<!-- Hooks: Using Hooks pages -->
		<div class="cta-docs-page" data-docs-page-content="hooks-overview">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title"><?php esc_html_e( 'CTA JS Hooks', 'cta-manager' ); ?></h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'Listen for CTA lifecycle events to integrate analytics, UX, and custom behaviors.', 'cta-manager' ); ?>
				</p>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'cta:loaded, cta:impression, cta:clicked, cta:conversion', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'cta:popup and cta:slide_in lifecycle events', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'cta:ab_test with variant payloads for experiments', 'cta-manager' ); ?></li>
					</ul>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'CTA Manager dispatches document-level CustomEvents with rich detail payloads (ctaId, variant, device, pageUrl, experimentKey) so you can forward data to analytics, trigger UI changes, or coordinate third-party widgets without editing plugin core.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Open Documentation → Hooks → JavaScript Hooks for payloads and examples.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Add listeners with addEventListener or CTAProHooks.on for auto-cleanup.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Inspect event.detail to access ctaId, layout, variant, device, and pageUrl.', 'cta-manager' ); ?></li>
					</ol>
				</div>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Available Events', 'cta-manager' ); ?></h4>
					<ul>
						<?php foreach ( $cta_docs_hooks as $hook ) : ?>
							<li>
								<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
									<strong><?php echo esc_html( $hook['title'] ); ?></strong>
								</a>
								— <?php echo esc_html( $hook['description'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="cta-docs-page" data-docs-page-content="hooks-event-listeners">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title"><?php esc_html_e( 'Event Listeners', 'cta-manager' ); ?></h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'All CTA events are CustomEvents dispatched on document. Listen with addEventListener to wire analytics or UI behaviors.', 'cta-manager' ); ?>
				</p>
<pre><code>document.addEventListener('cta:clicked', (event) =&gt; {
  const { ctaId, targetUrl, variant } = event.detail || {};
  console.log('CTA clicked', ctaId, targetUrl, variant);
});</code></pre>
			</div>
		</div>

		<div class="cta-docs-page" data-docs-page-content="hooks-ctaprohooks">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title"><?php esc_html_e( 'Helper Functions', 'cta-manager' ); ?></h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'If CTAProHooks is present, use its on/off helpers to manage listeners with less boilerplate.', 'cta-manager' ); ?>
				</p>
<pre><code>if (window.CTAProHooks) {
  CTAProHooks.on('cta:impression', ({ detail }) =&gt; {
    dataLayer.push({
      event: 'cta_impression',
      ctaId: detail.ctaId,
      pageUrl: detail.pageUrl,
      layout: detail.layout,
    });
  });
}</code></pre>
			</div>
		</div>

		<!-- PHP Hooks Overview Page -->
		<div class="cta-docs-page" data-docs-page-content="php-hooks-overview">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title">
					<span class="cta-docs-title-icon">🧩</span>
					<?php esc_html_e( 'PHP Hooks', 'cta-manager' ); ?>
				</h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'Server-side actions/filters to extend CTA rendering, targeting, and styling.', 'cta-manager' ); ?>
				</p>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Rendering hooks: cta_pro_shortcode_attributes, cta_pro_render_cta_type', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Targeting hooks: cta_pro_device_targeting, cta_pro_url_targeting', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Database hooks: cta_db_before_insert, cta_db_after_insert, cta_db_before_update, cta_db_after_update', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Permission hooks: cta_can_add_cta for access control', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Configuration hooks: cta_manager_pro_enabled for Pro status override', 'cta-manager' ); ?></li>
					</ul>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'PHP Hooks provide server-side extensibility points that allow developers to modify CTA Manager behavior without editing core plugin files. These WordPress filters and actions enable powerful customizations including injecting custom HTML attributes for JavaScript integration, swapping layouts for specific CTAs or conditions, refining device and URL targeting rules with custom logic, intercepting database operations for logging or validation, and integrating CTAs with other plugins or themes. PHP hooks execute during the WordPress request lifecycle, giving you control over what gets rendered and sent to the browser.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Add hooks in your theme\'s functions.php or custom plugin using standard WordPress filter/action syntax.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Use add_filter("hook_name", function($value, $params...) { return $modified_value; }, 10, $num_args) to modify values before they\'re used.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Use add_action("hook_name", function($params...) { /* your code */ }, 10, $num_args) to execute code at specific points in the plugin lifecycle.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Refer to the Documentation → Hooks → PHP Hooks section for detailed examples of each available filter and action with code samples.', 'cta-manager' ); ?></li>
					</ol>
				</div>
				<?php
				// Get PHP hooks from PHP Hooks category and separate by type
				$php_hooks_features_list = $features[ __( 'PHP Hooks', 'cta-manager' ) ] ?? [];
				$php_hooks_list = array_filter(
					$php_hooks_features_list,
					function ( $feature ) {
						return ! empty( $feature['hook_type'] );
					}
				);

				// Separate into filters and actions
				$filter_hooks = [];
				$action_hooks = [];
				foreach ( $php_hooks_list as $php_hook ) {
					if ( ( $php_hook['hook_type'] ?? 'filter' ) === 'filter' ) {
						$filter_hooks[] = $php_hook;
					} else {
						$action_hooks[] = $php_hook;
					}
				}
				?>

				<?php if ( ! empty( $filter_hooks ) ) : ?>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Filter Hooks', 'cta-manager' ); ?></h4>
					<ul>
						<?php foreach ( $filter_hooks as $php_hook ) :
							$page_slug = 'php-hook-' . sanitize_title( str_replace( [ 'cta_', 'cta_pro_', 'cta_db_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
							$hook_name = $php_hook['hook_name'] ?? '';
							?>
							<li>
								<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
									<strong><?php echo esc_html( $php_hook['title'] ); ?></strong>
									<?php if ( ! empty( $hook_name ) ) : ?>
										<code style="font-size: 0.85em;"><?php echo esc_html( $hook_name ); ?></code>
									<?php endif; ?>
								</a>
								— <?php echo esc_html( $php_hook['description'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $action_hooks ) ) : ?>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Action Hooks', 'cta-manager' ); ?></h4>
					<ul>
						<?php foreach ( $action_hooks as $php_hook ) :
							$page_slug = 'php-hook-' . sanitize_title( str_replace( [ 'cta_', 'cta_pro_', 'cta_db_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
							$hook_name = $php_hook['hook_name'] ?? '';
							?>
							<li>
								<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
									<strong><?php echo esc_html( $php_hook['title'] ); ?></strong>
									<?php if ( ! empty( $hook_name ) ) : ?>
										<code style="font-size: 0.85em;"><?php echo esc_html( $hook_name ); ?></code>
									<?php endif; ?>
								</a>
								— <?php echo esc_html( $php_hook['description'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- PHP Hooks Helper Functions Page -->
		<div class="cta-docs-page" data-docs-page-content="php-hooks-helpers">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title"><?php esc_html_e( 'Helper Functions', 'cta-manager' ); ?></h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'WordPress provides standard functions for working with hooks. Use these helpers to register and manage your hook callbacks.', 'cta-manager' ); ?>
				</p>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Adding Filter Hooks', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Use add_filter() to modify values before they\'re used. Filters must return a value.', 'cta-manager' ); ?></p>
<pre><code>// Basic filter syntax
add_filter('cta_pro_url_targeting', function($allowed, $cta, $url) {
  // Your logic here
  return $allowed; // Must return a value
}, 10, 3); // Priority 10, 3 arguments</code></pre>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Adding Action Hooks', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Use add_action() to execute code at specific points. Actions don\'t return values.', 'cta-manager' ); ?></p>
<pre><code>// Basic action syntax
add_action('cta_db_after_insert', function($result, $table, $data) {
  // Your side-effect code here (logging, notifications, etc.)
  // No return value needed
}, 10, 3); // Priority 10, 3 arguments</code></pre>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Removing Hooks', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Use remove_filter() or remove_action() to unhook callbacks. Must match the exact priority and function.', 'cta-manager' ); ?></p>
<pre><code>// Remove a filter
remove_filter('cta_pro_url_targeting', 'my_custom_function', 10);

// Remove an action
remove_action('cta_db_after_insert', 'my_custom_function', 10);</code></pre>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Priority & Execution Order', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Hooks with lower priority numbers execute first. Default priority is 10.', 'cta-manager' ); ?></p>
<pre><code>// Runs early (before default)
add_filter('cta_pro_url_targeting', 'my_early_function', 5, 3);

// Runs at default priority
add_filter('cta_pro_url_targeting', 'my_function', 10, 3);

// Runs late (after default)
add_filter('cta_pro_url_targeting', 'my_late_function', 20, 3);</code></pre>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Best Practices', 'cta-manager' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Place hooks in your theme\'s functions.php or a custom plugin - never modify CTA Manager core files.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Always specify the number of parameters your callback accepts (the 4th argument to add_filter/add_action).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'For filters, always return the value (even if unchanged) - never return null or void.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Check if required classes/functions exist before using them (e.g., if (class_exists("CTA_Data")) { ... }).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Use descriptive function names and add comments explaining what your hook does.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Test hooks thoroughly - especially database hooks that can affect data integrity.', 'cta-manager' ); ?></li>
					</ul>
				</div>

				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Available Hook Types', 'cta-manager' ); ?></h4>
					<ul>
						<li>
							<strong><?php esc_html_e( 'Filter Hooks (Modify Values)', 'cta-manager' ); ?></strong>
							— <?php esc_html_e( 'Use add_filter() to modify data before it\'s used. Must return a value.', 'cta-manager' ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'Action Hooks (Execute Code)', 'cta-manager' ); ?></strong>
							— <?php esc_html_e( 'Use add_action() to run code at specific points. No return value needed.', 'cta-manager' ); ?>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<!-- PHP Hooks: Individual hook pages -->
		<?php
		// Get PHP hooks from PHP Hooks category
		$php_hooks_features_pages = $features[ __( 'PHP Hooks', 'cta-manager' ) ] ?? [];
		$php_hooks = array_filter(
			$php_hooks_features_pages,
			function ( $feature ) {
				return ! empty( $feature['hook_type'] );
			}
		);

		foreach ( $php_hooks as $php_hook ) :
			// Generate page slug from hook name
			$page_slug = 'php-hook-' . sanitize_title( str_replace( [ 'cta_', 'cta_pro_', 'cta_db_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
			$hook_type_label = ( $php_hook['hook_type'] ?? 'filter' ) === 'filter' ? __( 'Filter', 'cta-manager' ) : __( 'Action', 'cta-manager' );
			$hook_name = $php_hook['hook_name'] ?? '';
			?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $page_slug ); ?>">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title">
						<?php if ( ! empty( $php_hook['icon'] ) ) : ?>
							<span class="cta-docs-title-icon"><?php echo esc_html( $php_hook['icon'] ); ?></span>
						<?php endif; ?>
						<?php echo esc_html( $php_hook['title'] ); ?>
						<?php if ( 'pro' === ( $php_hook['plan'] ?? 'free' ) ) : ?>
							<span class="cta-docs-pro-badge"><?php esc_html_e( 'Pro', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</h2>
					<?php if ( ! empty( $hook_name ) ) : ?>
						<p class="cta-docs-section-description">
							<strong><?php echo esc_html( $hook_type_label ); ?>:</strong>
							<code style="font-size: 1em; background: #f5f5f5; padding: 2px 8px; border-radius: 3px;"><?php echo esc_html( $hook_name ); ?></code>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $php_hook['description'] ) ) : ?>
						<p class="cta-docs-section-description">
							<?php echo esc_html( $php_hook['description'] ); ?>
						</p>
					<?php endif; ?>

					<?php if ( ! empty( $php_hook['parameters'] ) ) : ?>
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( 'Parameters', 'cta-manager' ); ?></h4>
							<ul>
								<?php foreach ( $php_hook['parameters'] as $param ) : ?>
									<li>
										<strong><?php echo esc_html( $param['name'] ); ?></strong>
										<code>(<?php echo esc_html( $param['type'] ); ?>)</code>
										<?php if ( ! empty( $param['description'] ) ) : ?>
											— <?php echo esc_html( $param['description'] ); ?>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $php_hook['features'] ) && is_array( $php_hook['features'] ) ) : ?>
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
							<ul>
								<?php foreach ( $php_hook['features'] as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $php_hook['details'] ) ) : ?>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
							<p><?php echo esc_html( $php_hook['details'] ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $php_hook['instructions'] ) && is_array( $php_hook['instructions'] ) ) : ?>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
							<ol class="cta-docs-steps">
								<?php foreach ( $php_hook['instructions'] as $step ) : ?>
									<li><?php echo esc_html( $step ); ?></li>
								<?php endforeach; ?>
							</ol>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $php_hook['examples'] ) ) : ?>
						<?php foreach ( $php_hook['examples'] as $example ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php echo esc_html( $example['label'] ); ?></h4>
								<?php if ( ! empty( $example['description'] ) ) : ?>
									<p><?php echo esc_html( $example['description'] ); ?></p>
								<?php endif; ?>
<pre><code><?php echo esc_html( $example['code'] ); ?></code></pre>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Hooks: individual action hook pages -->
		<?php foreach ( $cta_docs_hooks as $hook ) : ?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $hook['id'] ); ?>">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php echo esc_html( $hook['title'] ); ?></h2>
					<p class="cta-docs-section-description"><?php echo esc_html( $hook['description'] ); ?></p>
					<?php if ( ! empty( $hook['payload'] ) ) : ?>
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( 'Payload', 'cta-manager' ); ?></h4>
							<ul>
								<?php foreach ( $hook['payload'] as $field ) : ?>
									<li>
										<strong><?php echo esc_html( $field['key'] ); ?></strong>
										<?php if ( ! empty( $field['summary'] ) ) : ?>
											— <?php echo esc_html( $field['summary'] ); ?>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $hook['examples'] ) ) : ?>
						<?php foreach ( $hook['examples'] as $example ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php echo esc_html( $example['label'] ); ?></h4>
								<?php if ( ! empty( $example['description'] ) ) : ?>
									<p><?php echo esc_html( $example['description'] ); ?></p>
								<?php endif; ?>
<pre><code><?php echo esc_html( $example['code'] ); ?></code></pre>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Shortcode Usage Page (static, with copyable examples) -->
		<div class="cta-docs-page" data-docs-page-content="feature-shortcode-usage">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title">
					<span class="cta-docs-title-icon">📋</span>
					<?php esc_html_e( 'Shortcode Usage', 'cta-manager' ); ?>
				</h2>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'Display your CTAs anywhere on your site using shortcodes. Copy and paste these into any post, page, or widget.', 'cta-manager' ); ?>
				</p>

				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Copy and paste into any post, page, or widget', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Display by ID or by name', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Works with all CTA types', 'cta-manager' ); ?></li>
					</ul>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Shortcodes are the simplest way to embed CTAs in your WordPress content. Use the default shortcode to display your first CTA, or specify a particular CTA by its unique ID or name. Each CTA in the Manage CTAs list shows its unique shortcode that you can copy directly. Shortcodes work in posts, pages, widgets, and anywhere WordPress processes shortcode content.', 'cta-manager' ); ?></p>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Shortcode Examples', 'cta-manager' ); ?></h4>
					<div class="cta-shortcode-examples">
						<?php
						$title       = __( 'Display Default CTA', 'cta-manager' );
						$shortcode   = '[cta-manager]';
						$description = __( 'Displays your first/default CTA button.', 'cta-manager' );
						include CTA_PLUGIN_DIR . 'templates/admin/partials/shortcode-example.php';

						$title       = __( 'Display by ID', 'cta-manager' );
						$shortcode   = '[cta-manager id="123"]';
						$description = __( 'Display a specific CTA by its unique ID. Find the ID in the CTA list.', 'cta-manager' );
						include CTA_PLUGIN_DIR . 'templates/admin/partials/shortcode-example.php';

						$title       = __( 'Display by Name', 'cta-manager' );
						$shortcode   = '[cta-manager name="Support Line"]';
						$description = __( 'Display a CTA by its name. Use the exact name you gave the CTA.', 'cta-manager' );
						include CTA_PLUGIN_DIR . 'templates/admin/partials/shortcode-example.php';

						unset( $title, $shortcode, $description );
						?>
					</div>
				</div>

				<div class="cta-shortcode-tip" style="display: flex; align-items: flex-start; gap: 12px; padding: 16px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px; margin-top: 24px;">
					<span class="dashicons dashicons-lightbulb" style="color: #0073aa; margin-top: 2px;"></span>
					<p style="margin: 0;"><?php esc_html_e( 'Tip: Each CTA in the Manage CTAs list shows its unique shortcode that you can copy directly.', 'cta-manager' ); ?></p>
				</div>
			</div>
		</div>

		
			<!-- Settings Documentation Pages -->
			<div class="cta-docs-page" data-docs-page-content="settings-analytics">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Analytics tracks user interactions with your CTAs, including impressions (views) and engagement (clicks), so you can tune conversion performance.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Analytics?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Analytics tracks impressions, clicks, and conversions so you can understand CTA performance and optimize for conversions.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Settings', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Enable or disable analytics tracking globally', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Choose a data retention period (1 day, 1 week, or custom)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Custom retention days and indefinite retention for Pro users', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How Analytics Works', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Enable tracking to start collecting impressions and clicks', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Pick a retention window that balances storage and compliance', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'View analytics on the Analytics page for CTA performance insight', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Old data purges automatically based on your retention choice', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Disabling analytics stops new data collection but does not delete existing data. Use Tools → Reset Data to clear analytics.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="settings-custom-css">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Custom CSS', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Custom CSS lets you add site-wide styles for every CTA without editing theme files.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Custom CSS?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Add CSS rules that override CTA default styles to match your brand without editing theme files.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools (Pro Only)', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Write custom CSS directly inside CTA Manager', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Apply rules globally to every CTA', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Save manually to prevent accidental overrides', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Use Custom CSS', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Upgrade to Pro to enable Custom CSS', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Write CSS in the editor using standard syntax', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Target CTAs with class selectors like .cta-button', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click Save Custom CSS to apply changes', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Custom CSS overrides theme styles. Test thoroughly on multiple devices.', 'cta-manager' ); ?></p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="settings-performance">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Performance', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Performance settings optimize when CTA scripts load to keep your site fast.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Performance?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Load CTA scripts in the footer and defer assets so the page renders faster.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Settings (Pro Only)', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Load CTA scripts in the footer', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How Footer Scripts Improve Performance', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Browsers render content before executing CTA scripts', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Reduces render-blocking for a faster perceived load time', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'CTAs continue to function normally after the page loads', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Great for pages with many CTAs or rich content', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Footer loading is safe for most sites. Disable if you notice CTA issues.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="settings-custom-icons">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Custom Icons', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Upload your own SVG icons for CTA buttons to better match your brand.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What are Custom Icons?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Custom Icons let you upload SVG files and preview them for use in CTAs.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools (Pro Only)', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Upload SVG files from your computer', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Preview the icon before saving', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Delete unwanted icons safely', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Icons are optimized automatically', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Use Custom Icons', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Upgrade to Pro to unlock Custom Icons', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click Add Icon and select an SVG file', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Give the icon a descriptive name', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Use your custom icons when building CTAs', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Only SVG files are supported. Deleting an icon does not retroactively change CTAs already using it.', 'cta-manager' ); ?></p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="settings-data-management">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Data Management', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Data Management controls what CTA data is deleted when uninstalling.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Data Management?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Choose whether CTA data, analytics, CSS, and icons persist after uninstall.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Settings', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Remove data on uninstall', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Keep data with Pro for reinstall', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( 'What Data is Affected', 'cta-manager' ); ?></h4>
							<ul>
								<li><?php esc_html_e( 'All CTA configurations and settings', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Analytics impressions and engagement history', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Custom icons and CSS (Pro)', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Plugin preferences and defaults', 'cta-manager' ); ?></li>
							</ul>
						</div>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Use This Setting', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Leave enabled to remove data during uninstall', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Disable with Pro if you plan to reinstall and keep data', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Export settings on the Tools page before deleting the plugin', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Use Reset Data to clear data without uninstalling', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'This setting only runs when deleting the plugin. Deactivation does not delete data.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>


			<!-- Tools Documentation Pages -->


			<div class="cta-docs-page" data-docs-page-content="tools-export">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title">
						<span class="cta-docs-title-icon">⇪</span>
						<?php esc_html_e( 'Export Data', 'cta-manager' ); ?>
					</h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Export your CTAs, analytics, and settings to a JSON file for backups or migrations.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-download"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Export Data?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Export Data downloads a JSON snapshot of your entire CTA setup so you can restore it later.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Download JSON file', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'View JSON preview before saving', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Export', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Click Export Data on the Tools page', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Review the JSON preview to confirm the content', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Download the file for safekeeping or migrations', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Export before making destructive changes or resetting to ensure you can recover.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="tools-import">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Import Data', 'cta-manager' ); ?></h2>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Import Data?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Import Data restores CTA configurations from a valid export JSON file.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools', 'cta-manager' ); ?></h4>
						<ul>
							<li><strong><?php esc_html_e( 'File Upload', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Select a CTA Manager JSON file', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Mode Selection', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Choose Replace or Merge (Pro) mode', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Backup Toggle', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Automatically backup before importing (Pro)', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Import', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Upload the JSON file using the file picker', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Select Replace or Merge (requires Pro)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Toggle Backup to save current data before importing', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click Import Settings to complete', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Replace mode deletes existing CTAs. Export before importing in Replace mode.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="tools-demo">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Demo Data', 'cta-manager' ); ?></h2>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Demo Data?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Demo Data loads 15 pre-built CTAs to explore CTA Manager features.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools', 'cta-manager' ); ?></h4>
						<ul>
							<li><strong><?php esc_html_e( 'Import Demo Data', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Load 15 example CTAs', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Delete Demo Data', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Remove demo CTAs without affecting yours', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Status Indicator', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Shows how many demo CTAs are loaded', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( "What's Included in Demo Data", 'cta-manager' ); ?></h4>
							<ul>
								<li><?php esc_html_e( 'Phone, link, email, popup, and slide-in examples', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Button and card layouts with varied styling', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Examples marked with Demo badges', 'cta-manager' ); ?></li>
							</ul>
						</div>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Use Demo Data', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Import Demo Data to load all examples', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Browse demo CTAs on the Manage CTAs page', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Edit examples to learn configuration patterns', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Delete Demo Data when you are done learning', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Demo CTAs are safe to delete and do not affect live CTAs.', 'cta-manager' ); ?></p>
						</div>
					</div>
				</div>
			</div>


			<div class="cta-docs-page" data-docs-page-content="tools-reset">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Reset Data', 'cta-manager' ); ?></h2>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Reset Data?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Reset Data permanently deletes CTA configurations and analytics with granular control.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Tools', 'cta-manager' ); ?></h4>
						<ul>
							<li><strong><?php esc_html_e( 'Reset Data button', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Opens dialog with granular options', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Status indicator', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Shows whether data is available to reset', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<div class="cta-docs-feature-list">
							<h4><?php esc_html_e( 'Reset Options in Dialog', 'cta-manager' ); ?></h4>
							<ul>
								<li><strong><?php esc_html_e( 'CTAs and Analytics', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Delete all data', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'CTAs Only', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Keep analytics history', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Analytics Only', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Delete analytics but keep CTAs', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Include Demo Data', 'cta-manager' ); ?></strong> <?php esc_html_e( 'Toggle whether demo CTAs are removed', 'cta-manager' ); ?></li>
							</ul>
						</div>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to Reset Data', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Click Reset Data to open the modal', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Choose which datasets to delete', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Decide whether to include demo CTAs', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Confirm the action — it is permanent', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Reset is permanent. Export before proceeding.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="cta-docs-page" data-docs-page-content="tools-debug">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Debug Mode', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'When enabled, Debug Mode records additional logs, AJAX payloads, and validation responses to help troubleshoot CTA Manager behavior.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-admin-tools"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Debug Mode?', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Activating Debug Mode writes verbose results to your browser console and captures server responses for AJAX actions so you can spot configuration or integrity issues before going live.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'What it records', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'AJAX payloads for exports, imports, and demo data actions', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Server responses when saving CTA settings and toggles', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'JavaScript errors or warnings triggered by CTA Manager scripts', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to use Debug Mode', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Navigate to Tools → Debug Mode and enable the toggle', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Reproduce the workflow you want to inspect (importing data, toggling CTAs, etc.)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Open your browser console to review the logged requests and responses', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Disable Debug Mode once you have the information you need to avoid noise', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Debug Mode is only for troubleshooting and should be disabled on production sites to avoid logging sensitive data.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

<!-- Dynamic Feature Pages -->
<?php foreach ( $category_names as $category_name ) :
	// Skip Shortcode Usage in the loop - it's rendered above as a static page
	if ( __( 'Shortcode Usage', 'cta-manager' ) === $category_name ) {
		continue;
	}
?>
	<?php foreach ( $features[ $category_name ] as $feature ) : ?>
		<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $feature['docs_page'] ); ?>">
			<div class="cta-docs-section">
				<h2 class="cta-docs-section-title">
							<?php if ( ! empty( $feature['icon'] ) ) : ?>
								<span class="cta-docs-title-icon"><?php echo esc_html( $feature['icon'] ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( $feature['title'] ); ?>
							<?php if ( 'pro' === ( $feature['plan'] ?? 'free' ) ) : ?>
								<span class="cta-docs-pro-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
							<?php endif; ?>
							<?php if ( empty( $feature['implemented'] ) ) : ?>
								<span class="cta-docs-coming-badge"><?php echo esc_html( $labels['badge_coming_soon'] ?? 'Coming Soon' ); ?></span>
							<?php endif; ?>
						</h2>
						<?php if ( ! empty( $feature['description'] ) ) : ?>
							<p class="cta-docs-section-description">
								<?php echo esc_html( $feature['description'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $feature['features'] ) && is_array( $feature['features'] ) ) : ?>
							<div class="cta-docs-feature-list">
								<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
								<ul>
									<?php foreach ( $feature['features'] as $item ) : ?>
										<li><?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $feature['details'] ) ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
								<p><?php echo esc_html( $feature['details'] ); ?></p>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $feature['instructions'] ) && is_array( $feature['instructions'] ) ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
								<ol class="cta-docs-steps">
									<?php foreach ( $feature['instructions'] as $step ) : ?>
										<li><?php echo esc_html( $step ); ?></li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endforeach; ?>

		<!-- Dynamic Integration Pages -->
		<?php foreach ( $integrations as $category_name => $items ) : ?>
			<?php foreach ( $items as $integration ) : ?>
				<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $integration['docs_page'] ); ?>">
					<div class="cta-docs-section">
						<h2 class="cta-docs-section-title">
							<?php echo esc_html( $integration['title'] ); ?>
							<?php if ( empty( $integration['implemented'] ) ) : ?>
								<span class="cta-docs-coming-badge"><?php echo esc_html( $labels['badge_coming_soon'] ?? 'Coming Soon' ); ?></span>
							<?php endif; ?>
						</h2>
						<?php if ( ! empty( $integration['description'] ) ) : ?>
							<p class="cta-docs-section-description">
								<?php echo esc_html( $integration['description'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $integration['features'] ) && is_array( $integration['features'] ) ) : ?>
							<div class="cta-docs-feature-list">
								<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
								<ul>
									<?php foreach ( $integration['features'] as $item ) : ?>
										<li><?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
</div>

<script>
// Simple intra-modal navigation: clicking elements with data-docs-page triggers the matching submenu link.
(function() {
  document.addEventListener('click', function(e) {
    const link = e.target.closest('.cta-docs-link[data-docs-page]');
    if (!link) return;
    e.preventDefault();
    const target = link.getAttribute('data-docs-page');
    const navBtn = document.querySelector('.cta-docs-submenu-link[data-docs-page="' + target + '"]');
    if (!navBtn) return;

    // Find the parent top-level accordion
    const parentAccordionItem = navBtn.closest('.cta-docs-accordion-item');
    if (parentAccordionItem) {
      const parentTrigger = parentAccordionItem.querySelector('.cta-docs-accordion-trigger');
      const parentPanel = parentAccordionItem.querySelector('.cta-docs-accordion-panel');
      if (parentTrigger && parentPanel && parentPanel.hasAttribute('hidden')) {
        parentTrigger.click();
      }
    }

    // Find the parent submenu accordion if exists
    const parentSubmenuAccordion = navBtn.closest('.cta-docs-submenu-accordion');
    if (parentSubmenuAccordion) {
      const submenuTrigger = parentSubmenuAccordion.querySelector('[data-submenu-trigger]');
      const submenuPanel = parentSubmenuAccordion.querySelector('.cta-docs-submenu-panel');
      if (submenuTrigger && submenuPanel && submenuPanel.hasAttribute('hidden')) {
        submenuTrigger.click();
      }
    }

    // Activate target nav item
    document.querySelectorAll('.cta-docs-submenu-link.is-active').forEach(function(btn) {
      btn.classList.remove('is-active');
    });
    navBtn.classList.add('is-active');

    // Scroll nav into view
    const navItem = navBtn.closest('.cta-docs-submenu-item');
    if (navItem && navItem.scrollIntoView) {
      navItem.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    // Trigger page change
    navBtn.click();
  });
})();
</script>
