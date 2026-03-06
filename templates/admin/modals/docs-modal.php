<?php
/**
 * Admin Modal Template - Docs Modal
 *
 * Handles markup rendering for the docs modal admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
$docs_hero_img     = $is_pro ? esc_url( CTA_PLUGIN_URL . 'assets/images/cta-manager-pro-logo.png' ) : esc_url( CTA_PLUGIN_URL . 'assets/images/cta-manager-logo.png' );
$docs_plugin_name  = $is_pro ? 'CTA Manager Pro' : 'CTA Manager';

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
		'label' => __( 'Experimentation', 'cta-manager' ),
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

$cta_php_hook_categories = [
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

$php_hooks_features = $features[ __( 'PHP Hooks', 'cta-manager' ) ] ?? [];
$php_hooks_all = array_filter(
	$php_hooks_features,
	function ( $feature ) {
		return ! empty( $feature['hook_type'] );
	}
);

$php_hooks_by_category = [];
foreach ( $php_hooks_all as $php_hook ) {
	$category = $php_hook['hook_category'] ?? 'other';
	if ( ! isset( $php_hooks_by_category[ $category ] ) ) {
		$php_hooks_by_category[ $category ] = [];
	}
	$php_hooks_by_category[ $category ][] = $php_hook;
}

uksort(
	$php_hooks_by_category,
	function ( $a, $b ) use ( $cta_php_hook_categories ) {
		$order_a = $cta_php_hook_categories[ $a ]['order'] ?? 999;
		$order_b = $cta_php_hook_categories[ $b ]['order'] ?? 999;
		return $order_a <=> $order_b;
	}
);

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

$feature_category_overview_slug = static function( string $category_name ): string {
	return 'category-' . sanitize_title( $category_name ) . '-overview';
};

$integration_category_overview_slug = static function( string $category_name ): string {
	return 'integration-category-' . sanitize_title( $category_name ) . '-overview';
};

$hook_category_overview_slug = static function( string $hook_type, string $category_key ): string {
	return 'hook-category-' . sanitize_title( $hook_type ) . '-' . sanitize_title( $category_key ) . '-overview';
};

$cta_docs_category_icon = static function( string $category_name ): string {
	$map = [
		__( 'Embedding CTAs', 'cta-manager' )   => '🧩',
		__( 'CTA Types', 'cta-manager' )        => '🎯',
		__( 'Presentation', 'cta-manager' )     => '🪄',
		__( 'Styling', 'cta-manager' )          => '🎨',
		__( 'Scheduling', 'cta-manager' )       => '🗓️',
		__( 'Targeting', 'cta-manager' )        => '🎯',
		__( 'Experimentation', 'cta-manager' )  => '🧪',
		__( 'Analytics', 'cta-manager' )        => '📊',
		__( 'AI & Automation', 'cta-manager' )  => '🤖',
		__( 'Goals & KPIs', 'cta-manager' )     => '🏁',
		__( 'Data Management', 'cta-manager' )  => '🗃️',
		__( 'Performance', 'cta-manager' )      => '⚡',
		__( 'JS Hooks', 'cta-manager' )         => '🪝',
		__( 'PHP Hooks', 'cta-manager' )        => '🧩',
	];
	return $map[ $category_name ] ?? '✨';
};

$cta_docs_integration_category_icon = static function( string $category_name ): string {
	$map = [
		__( 'Analytics & Tracking', 'cta-manager' ) => '📈',
		__( 'SEO & Optimization', 'cta-manager' )   => '🔎',
		__( 'Communication', 'cta-manager' )        => '💬',
		__( 'Project Management', 'cta-manager' )   => '📋',
		__( 'Scheduling', 'cta-manager' )           => '🗓️',
		__( 'Security', 'cta-manager' )             => '🛡️',
		__( 'Development', 'cta-manager' )          => '🧑‍💻',
		__( 'CRM', 'cta-manager' )                  => '🤝',
		__( 'Forms', 'cta-manager' )                => '📝',
		__( 'Finance', 'cta-manager' )              => '💳',
		__( 'Infrastructure', 'cta-manager' )       => '🏗️',
		__( 'Database', 'cta-manager' )             => '🗄️',
	];
	return $map[ $category_name ] ?? '🔌';
};

$embedding_category_name = __( 'Embedding CTAs', 'cta-manager' );
$embedding_items         = $features[ $embedding_category_name ] ?? [];

$cta_docs_category_summary = static function( array $items ): array {
	$total     = count( $items );
	$pro       = 0;
	$coming    = 0;
	$available = 0;

	foreach ( $items as $item ) {
		if ( 'pro' === ( $item['plan'] ?? 'free' ) ) {
			$pro++;
		}
		if ( empty( $item['implemented'] ) ) {
			$coming++;
		} else {
			$available++;
		}
	}

	return [
		'total'     => $total,
		'pro'       => $pro,
		'coming'    => $coming,
		'available' => $available,
	];
};
?>

<div class="cta-docs-layout">
	<!-- Sidebar -->
	<div class="cta-docs-sidebar">
		<!-- Filter Controls -->
		<div class="cta-docs-filter-controls">
			<div class="cta-docs-filter-row">
				<span class="cta-docs-plan-filters" data-docs-plan-filters>
					<button type="button" class="cta-docs-plan-filter is-active" data-docs-plan-filter="all"><?php esc_html_e( 'All', 'cta-manager' ); ?></button>
					<span class="cta-docs-plan-separator">|</span>
					<button type="button" class="cta-docs-plan-filter" data-docs-plan-filter="free"><?php esc_html_e( 'Free', 'cta-manager' ); ?></button>
					<span class="cta-docs-plan-separator">|</span>
					<button type="button" class="cta-docs-plan-filter" data-docs-plan-filter="pro"><?php esc_html_e( 'Pro', 'cta-manager' ); ?></button>
				</span>
				<label class="cta-docs-soon-toggle">
					<span class="cta-docs-soon-label"><?php esc_html_e( 'Soon', 'cta-manager' ); ?></span>
					<span class="cta-toggle cta-toggle--small">
						<input type="checkbox" checked data-docs-soon-toggle />
						<span class="cta-toggle-track cta-toggle-track-small" aria-hidden="true">
							<span class="cta-toggle-thumb cta-toggle-thumb-small"></span>
						</span>
					</span>
				</label>
			</div>
		</div>

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
						class="cta-docs-welcome-link is-active"
						data-docs-page="welcome"
					>
						<span class="dashicons dashicons-welcome-learn-more"></span>
						<?php esc_html_e( 'Welcome', 'cta-manager' ); ?>
					</button>
				</li>
				<li class="cta-docs-accordion-item" data-search-title="Getting Started Install Installation Guide Upgrade Glossary Onboarding Wizard Creating Editing Scheduling">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-getting-started-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-lightbulb"></span>
							<?php esc_html_e( 'Getting Started', 'cta-manager' ); ?>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-getting-started-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<li class="cta-docs-submenu-item" data-search-title="Installing CTA Manager plugin zip upload FTP setup requirements activate configure manual install">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-installing">
									<?php esc_html_e( 'Installation Guide', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Upgrading to Pro download install activate">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-upgrading-pro">
									<?php esc_html_e( 'Upgrading to Pro', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="CTA Manager Glossary terms categories subcategories menus">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-glossary">
									<?php esc_html_e( 'CTA Manager Glossary', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Onboarding wizard walkthrough steps welcome import demo data getting help setup">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-onboarding">
									<?php esc_html_e( 'Onboarding Wizard', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Creating New CTAs modal tabs fields walkthrough">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-creating-ctas">
									<?php esc_html_e( 'Creating New CTAs', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Editing CTAs what happens and limits">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-editing-ctas">
									<?php esc_html_e( 'Editing CTAs', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Scheduling CTAs all schedule types and setup">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="getting-started-scheduling-ctas">
									<?php esc_html_e( 'Scheduling CTAs', 'cta-manager' ); ?>
								</button>
							</li>
						</ul>
					</div>
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
							<li class="cta-docs-submenu-item" data-search-title="Feature Overview All Features">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="features-overview">
									<?php esc_html_e( 'Feature Overview', 'cta-manager' ); ?>
								</button>
							</li>
							<?php foreach ( $category_names as $category_name ) :
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
										<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $cat_label . ' Overview' ); ?>">
											<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $feature_category_overview_slug( $category_name ) ); ?>">
												<?php echo esc_html( $cat_label . ' Overview' ); ?>
											</button>
										</li>
										<?php foreach ( $features[ $category_name ] as $feature ) : ?>
											<?php
											$badge_html = '';
											$is_pro     = ( 'pro' === ( $feature['plan'] ?? 'free' ) );
											$is_soon    = empty( $feature['implemented'] );

										if ( $is_pro ) {
											$badge_html .= '<span class="cta-docs-pro-badge cta-docs-accordion-badge">' . esc_html( $labels['badge_pro'] ?? 'Pro' ) . '</span>';
										}
										if ( $is_soon ) {
											$badge_html .= '<span class="cta-docs-coming-badge">' . esc_html( $menu_coming_label ) . '</span>';
										}

										$menu_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
											?>
											<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $menu_title ); ?>" data-docs-plan="<?php echo esc_attr( $feature['plan'] ?? 'free' ); ?>" data-docs-implemented="<?php echo empty( $feature['implemented'] ) ? '0' : '1'; ?>">
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
							<span class="cta-docs-pro-badge cta-docs-accordion-badge">PRO</span>
						</span>
						<span class="cta-docs-accordion-icon">
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
					</button>
					<div id="docs-integrations-panel" class="cta-docs-accordion-panel" hidden>
						<ul class="cta-docs-submenu">
							<li class="cta-docs-submenu-item" data-search-title="<?php esc_attr_e( 'Integrations Overview', 'cta-manager' ); ?>">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="integrations-overview">
									<?php esc_html_e( 'Integrations Overview', 'cta-manager' ); ?>
								</button>
							</li>
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
											<span class="dashicons dashicons-<?php echo esc_attr( CTA_Features::get_integration_category_icon( $category_name ) ); ?>"></span>
											<?php echo esc_html( $cat_label ); ?>
										</span>
										<span class="cta-docs-submenu-icon">
											<span class="dashicons dashicons-arrow-down-alt2"></span>
										</span>
									</button>
									<ul class="cta-docs-submenu-panel" id="<?php echo esc_attr( $submenu_id ); ?>" hidden>
										<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $cat_label . ' Overview' ); ?>">
											<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $integration_category_overview_slug( $category_name ) ); ?>">
												<?php echo esc_html( $cat_label . ' Overview' ); ?>
											</button>
										</li>
										<?php foreach ( $integrations[ $category_name ] as $integration ) : ?>
											<?php
											$badge_html = '';
											if ( empty( $integration['implemented'] ) ) {
												$badge_html .= '<span class="cta-docs-coming-badge">' . esc_html( $menu_coming_label ) . '</span>';
											}
											$menu_title = $cta_docs_strip_paren( $integration['title'] ?? '' );
											?>
											<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( $menu_title ); ?>" data-docs-plan="pro" data-docs-implemented="<?php echo empty( $integration['implemented'] ) ? '0' : '1'; ?>">
												<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $integration['docs_page'] ); ?>">
													<?php if ( ! empty( $integration['image'] ) ) : ?>
														<img src="<?php echo esc_url( $integration['image'] ); ?>" alt="" style="width:16px;height:16px;vertical-align:middle;margin-right:8px;">
													<?php endif; ?>
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
				<li class="cta-docs-accordion-item" data-search-title="Hooks JavaScript Events" data-docs-plan="pro">
					<button
						type="button"
						class="cta-docs-accordion-trigger"
						aria-expanded="false"
						aria-controls="docs-hooks-panel"
					>
						<span class="cta-docs-accordion-trigger-text">
							<span class="dashicons dashicons-admin-plugins"></span>
							<?php esc_html_e( 'Hooks', 'cta-manager' ); ?>
							<span class="cta-docs-pro-badge cta-docs-accordion-badge"><?php esc_html_e( 'Pro', 'cta-manager' ); ?></span>
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
									<li class="cta-docs-submenu-item" data-search-title="JS Hooks Overview">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="category-js-hooks-overview">
											<?php esc_html_e( 'JS Hooks Overview', 'cta-manager' ); ?>
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
												<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( sprintf( __( '%s overview', 'cta-manager' ), $category_label ) ); ?>">
													<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $hook_category_overview_slug( 'js', $category_key ) ); ?>">
														<?php echo esc_html( sprintf( __( '%s Overview', 'cta-manager' ), $category_label ) ); ?>
													</button>
												</li>
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
												<li class="cta-docs-submenu-item" data-search-title="<?php esc_attr_e( 'Integration Hooks Overview', 'cta-manager' ); ?>">
													<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $hook_category_overview_slug( 'js', 'integrations' ) ); ?>">
														<?php esc_html_e( 'Integration Hooks Overview', 'cta-manager' ); ?>
													</button>
												</li>
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
									<li class="cta-docs-submenu-item" data-search-title="PHP Hooks Overview">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="category-php-hooks-overview">
											<?php esc_html_e( 'PHP Hooks Overview', 'cta-manager' ); ?>
										</button>
									</li>
									<li class="cta-docs-submenu-item" data-search-title="WordPress hook helpers functions">
										<button type="button" class="cta-docs-submenu-link" data-docs-page="php-hooks-helpers">
											<?php esc_html_e( 'Helper Functions', 'cta-manager' ); ?>
										</button>
									</li>

									<?php
									// Render hook categories
									foreach ( $php_hooks_by_category as $category_key => $category_hooks ) :
										$category_meta = $cta_php_hook_categories[ $category_key ] ?? [];
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
												<li class="cta-docs-submenu-item" data-search-title="<?php echo esc_attr( sprintf( __( '%s overview', 'cta-manager' ), $category_label ) ); ?>">
													<button type="button" class="cta-docs-submenu-link" data-docs-page="<?php echo esc_attr( $hook_category_overview_slug( 'php', $category_key ) ); ?>">
														<?php echo esc_html( sprintf( __( '%s Overview', 'cta-manager' ), $category_label ) ); ?>
													</button>
												</li>
												<?php foreach ( $category_hooks as $php_hook ) :
													$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
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
				<li class="cta-docs-accordion-item" data-search-title="Settings Analytics Custom Global CSS Performance Custom Icons Data Management">
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
							<li class="cta-docs-submenu-item" data-search-title="Settings Overview">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-overview">
									<?php esc_html_e( 'Settings Overview', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Business Hours Schedule Timezone Closed Message">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-business-hours">
									<?php esc_html_e( 'Business Hours', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Analytics Tracking Data Retention">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-analytics">
									<?php esc_html_e( 'Analytics', 'cta-manager' ); ?>
								</button>
							</li>
							<li class="cta-docs-submenu-item" data-search-title="Custom Global CSS Styling Overrides">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="settings-custom-css">
									<?php esc_html_e( 'Custom Global CSS', 'cta-manager' ); ?>
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
				<li class="cta-docs-accordion-item" data-search-title="Tools Export Import Demo Reset Debug">
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
							<li class="cta-docs-submenu-item" data-search-title="Tools Overview">
								<button type="button" class="cta-docs-submenu-link" data-docs-page="tools-overview">
									<?php esc_html_e( 'Tools Overview', 'cta-manager' ); ?>
								</button>
							</li>
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

	<!-- Sidebar Toggle -->
	<button type="button" class="cta-docs-sidebar-toggle" data-docs-sidebar-toggle aria-label="<?php esc_attr_e( 'Toggle sidebar', 'cta-manager' ); ?>">
		<span class="dashicons dashicons-arrow-left-alt2"></span>
	</button>

	<!-- Content Area -->
	<div class="cta-docs-content" data-docs-content>
		<!-- Welcome Page -->
		<div class="cta-docs-page is-active" data-docs-page-content="welcome">
			<div class="cta-overview-hero">
				<img src="<?php echo $docs_hero_img; ?>" alt="<?php echo esc_attr( $docs_plugin_name ); ?>" class="cta-overview-hero-logo" />
				<h2 class="cta-overview-hero-title"><?php esc_html_e( 'Your Conversion Command Center', 'cta-manager' ); ?></h2>
				<p class="cta-overview-hero-subtitle"><?php esc_html_e( 'Half the effort. Twice the insight. Maximum conversions.', 'cta-manager' ); ?></p>
				<p class="cta-overview-hero-desc"><?php printf( esc_html__( 'More than a button plugin — %s is a complete conversion intelligence platform built for serious results.', 'cta-manager' ), esc_html( $docs_plugin_name ) ); ?></p>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'Support Shortcuts', 'cta-manager' ); ?></h4>
				<div class="cta-docs-mode-grid">
					<button type="button" class="cta-docs-mode-card cta-welcome-page-card" data-open-modal="#cta-dashboard-help-modal">
						<span class="dashicons dashicons-lightbulb"></span>
						<span class="cta-docs-mode-card-body">
							<strong><?php esc_html_e( 'Onboarding', 'cta-manager' ); ?></strong>
							<span><?php esc_html_e( 'Launch faster with a guided setup that gets your first CTA live without missing key steps.', 'cta-manager' ); ?></span>
						</span>
					</button>
					<button type="button" class="cta-docs-mode-card cta-welcome-page-card" data-open-modal="#cta-features-modal">
						<span class="dashicons dashicons-awards"></span>
						<span class="cta-docs-mode-card-body">
							<strong><?php esc_html_e( 'Features', 'cta-manager' ); ?></strong>
							<span><?php esc_html_e( 'Compare capabilities by category so you can pick the right tools to improve conversions.', 'cta-manager' ); ?></span>
						</span>
					</button>
					<button type="button" class="cta-docs-mode-card cta-welcome-page-card" data-cta-open-notifications>
						<span class="dashicons dashicons-megaphone"></span>
						<span class="cta-docs-mode-card-body">
							<strong><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></strong>
							<span><?php esc_html_e( 'Stay ahead of updates, alerts, and action items so your CTA system stays healthy and optimized.', 'cta-manager' ); ?></span>
						</span>
					</button>
					<button type="button" class="cta-docs-mode-card cta-welcome-page-card" data-open-modal="#cta-new-ticket-modal">
						<span class="dashicons dashicons-phone"></span>
						<span class="cta-docs-mode-card-body">
							<strong><?php esc_html_e( 'Support', 'cta-manager' ); ?></strong>
							<span><?php esc_html_e( 'Get expert help quickly when you are blocked so setup issues do not slow growth.', 'cta-manager' ); ?></span>
						</span>
					</button>
				</div>
			</div>

			<?php if ( ! $is_pro ) : ?>
				<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upsell-footer.php'; ?>
			<?php endif; ?>

			<!-- Plugin Ecosystem -->
			<div class="cta-overview-ecosystem cta-docs-welcome-ecosystem">
				<div class="cta-overview-ecosystem-header">
					<span class="cta-overview-ecosystem-eyebrow"><?php esc_html_e( 'TopDevAmerica Suite', 'cta-manager' ); ?></span>
					<h3><?php esc_html_e( 'Build an Unstoppable Stack', 'cta-manager' ); ?></h3>
					<p><?php printf( esc_html__( '%s is powerful alone — but combine it with our other plugins and your WordPress site becomes a fully loaded conversion machine.', 'cta-manager' ), esc_html( $docs_plugin_name ) ); ?></p>
				</div>
				<div class="cta-overview-ecosystem-grid">
					<?php
					$docs_plugins_img_url   = CTA_PLUGIN_URL . 'assets/images/plugins/';
					$docs_ecosystem_plugins = [
						[
							'logo'       => CTA_PLUGIN_URL . 'assets/images/cta-manager-pro-logo.png',
							'title'      => __( 'CTA Manager Pro', 'cta-manager' ),
							'tagline'    => $is_pro ? __( 'You Are Here', 'cta-manager' ) : __( 'Unlock Pro Features', 'cta-manager' ),
							'desc'       => __( 'Create, manage, and track conversion-focused calls-to-action with targeting rules, A/B testing, and real-time analytics.', 'cta-manager' ),
							'url'        => '',
							'accent'     => 'orange',
							'combo'      => __( 'The conversion engine powering your entire growth strategy.', 'cta-manager' ),
							'open_modal' => ! $is_pro ? '#cta-features-modal' : '',
						],
						[
							'logo'    => $docs_plugins_img_url . 'dashboard-widget-manager-logo.png',
							'title'   => __( 'Dashboard Widget Manager', 'cta-manager' ),
							'tagline' => __( 'Custom Dashboard Widgets', 'cta-manager' ),
							'desc'    => __( 'Build custom dashboard widgets with SQL queries, visual builder, chart support, and flexible caching.', 'cta-manager' ),
							'url'     => 'https://topdevamerica.com/plugins/dashboard-widget-manager',
							'accent'  => 'purple',
							'combo'   => __( 'Track CTA performance in real-time dashboard widgets.', 'cta-manager' ),
						],
						[
							'logo'    => $docs_plugins_img_url . 'ai-chat-manager-logo.png',
							'title'   => __( 'AI Chat Manager', 'cta-manager' ),
							'tagline' => __( 'AI-Powered Chat', 'cta-manager' ),
							'desc'    => __( 'Add a fully customizable AI chat assistant to your WordPress site, powered by Claude or OpenAI.', 'cta-manager' ),
							'url'     => 'https://topdevamerica.com/plugins/ai-chat-manager',
							'accent'  => 'teal',
							'combo'   => __( 'AI-driven CTAs that respond to visitor conversations.', 'cta-manager' ),
						],
					];
					foreach ( $docs_ecosystem_plugins as $ep ) :
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
		</div>

		<div class="cta-docs-page" data-docs-page-content="getting-started-installing">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">🚀</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Installing CTA Manager', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description"><?php esc_html_e( 'Manual installation and setup guide for CTA Manager. Follow these steps if you prefer to configure the plugin yourself instead of using the onboarding wizard.', 'cta-manager' ); ?></p>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Requirements', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'WordPress', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Version 5.8 or higher.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'PHP', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Version 7.4 or higher.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'MySQL', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Version 5.7 or higher (or MariaDB 10.3+).', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 1: Install the Plugin', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Choose one of the following methods to install CTA Manager on your WordPress site.', 'cta-manager' ); ?></p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'WordPress Plugin Upload', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Go to Plugins > Add New > Upload Plugin. Select the cta-manager.zip file and click Install Now.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'FTP / File Manager', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Extract the zip file and upload the cta-manager folder to wp-content/plugins/ on your server.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 2: Activate the Plugin', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Navigate to Plugins > Installed Plugins in your WordPress admin.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Find CTA Manager in the list and click Activate.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'On activation, the plugin automatically creates its database tables and default settings.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 3: Configure Settings', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'After activation, go to CTA Manager > Settings to configure your defaults.', 'cta-manager' ); ?></p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Default Position', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Choose where CTAs appear on the page by default (bottom-right, bottom-left, etc.).', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Default Display Trigger', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Set when CTAs show (on page load, after scroll, after delay, etc.).', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Enable or disable built-in impression and click tracking.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 4: Import Data (Optional)', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'If you have an existing CTA Manager JSON export or want to start with demo content, you can import data manually.', 'cta-manager' ); ?></p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'From a Backup', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Go to CTA Manager > Settings, open the Import/Export section, select your JSON file, choose which sections to import, and click Import.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Demo Data', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Use the Import Demo Data option on the Settings page to load sample CTAs, analytics, and notifications so you can explore the plugin with working examples.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Step 5: Create Your First CTA', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Go to CTA Manager and click the Create CTA button.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'In General, set CTA name, status, and schedule options.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'In Display, choose layout and device visibility; in Action, set CTA type destination fields.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'In Button (and Content for card/pro layouts), configure your copy and visual styling. If using slide-ins, configure trigger and position in the Slides tab (Pro).', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click Save & Publish to make your CTA live on the site.', 'cta-manager' ); ?></li>
						</ol>
					</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 6: Verify Your Setup', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Visit your site frontend and confirm the CTA renders in the expected position.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Click the CTA button and check that the configured action fires correctly (link, scroll, form, etc.).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Return to CTA Manager and check the Analytics tab to confirm impression and click events are recording.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Prefer a Guided Setup?', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'CTA Manager includes a built-in onboarding wizard that walks you through these same steps interactively. Click the Get Started trigger in the CTA Manager admin to launch it at any time.', 'cta-manager' ); ?></p>
					<button type="button" class="cta-docs-mode-card" data-docs-page="getting-started-onboarding"><span class="cta-docs-title-icon">🧭</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Onboarding Wizard Guide', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Step-by-step documentation of the onboarding modal, including each step, field, and navigation control.', 'cta-manager' ); ?></span></span></button>
				</div>
			</div>
		</div>

		<div class="cta-docs-page" data-docs-page-content="getting-started-upgrading-pro">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">👑</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Upgrading to Pro', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description"><?php esc_html_e( 'Walkthrough for downloading CTA Manager Pro, installing it, activating the plugin, and enabling your license.', 'cta-manager' ); ?></p>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What You Get with Pro', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'A/B Testing', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Create split-test campaigns with automatic variant serving and winner detection.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Advanced Analytics', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Extended reporting with conversion funnels, date-range comparisons, and per-CTA breakdowns.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Integrations', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'GA4, Google Tag Manager, and PostHog event forwarding with per-CTA toggle control.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Advanced Scheduling', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Date-range scheduling, day-of-week rules, and time-window targeting.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Custom Code', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Per-CTA custom CSS and JavaScript injection for full presentation control.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Priority Support', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Direct ticket-based support with faster response times.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 1: Download CTA Manager Pro', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Log in to your TopDevAmerica account.', 'cta-manager' ); ?></li>
							<li><?php printf( esc_html__( 'Navigate to the %1$sCTA Manager Pro download page%2$s.', 'cta-manager' ), '<a href="https://topdevamerica.com/cta-manager-pro" target="_blank" rel="noopener noreferrer">', '</a>' ); ?></li>
						<li><?php esc_html_e( 'Download the latest cta-manager-pro.zip file to your computer.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 2: Install the Plugin', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'CTA Manager (free) must already be installed and active before installing Pro.', 'cta-manager' ); ?></p>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'In your WordPress admin, go to Plugins > Add New > Upload Plugin.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Select the cta-manager-pro.zip file you downloaded and click Install Now.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Once installed, click Activate Plugin.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 3: Activate Your License', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Go to CTA Manager > Settings in your WordPress admin.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Locate the License Key field and paste your Pro license key from your account portal.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Click Activate License. The status should change to Active.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 4: Verify Pro Is Active', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Admin Menu', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Pro-only menu items (A/B Testing, Integrations) appear under CTA Manager.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'CTA Editor', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Pro tabs (Tracking, Custom Styling, Custom JS) are unlocked in the CTA form modal.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Settings Page', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'License status shows Active with your license key and expiration date.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Plugin Header', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'The admin topbar displays CTA Manager Pro branding when Pro is active.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Troubleshooting', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Pro menu items missing', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Confirm CTA Manager Pro is activated on the Plugins page and your license is active in Settings.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'License won\'t activate', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Check that the key is copied exactly with no leading/trailing spaces. Ensure your domain is not on the blocked domains list.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Features still locked', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Deactivate and reactivate CTA Manager Pro to force a feature gate refresh.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				</div>
			</div>

		<div class="cta-docs-page" data-docs-page-content="getting-started-glossary">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">📚</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'CTA Manager Glossary', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description"><?php esc_html_e( 'Reference terms for CTA Manager categories, features, and admin navigation.', 'cta-manager' ); ?></p>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Main Menu Terms', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Dashboard', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'High-level CTA performance and management overview.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Manage CTAs', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Create, edit, schedule, and organize CTA records.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Impressions, clicks, conversions, and breakdown reporting.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Integrations', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Connections to analytics, CRM, and automation platforms.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Settings', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Global plugin behavior, defaults, and configuration.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Tools', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Import/export, reset, debug, and maintenance utilities.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Support Menu Terms', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Get Started', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Onboarding flow for initial setup.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Features', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Feature modal and category browsing.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Operational notices, alerts, and system messages.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Support', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Ticket submission and support communication.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Documentation', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Docs modal with category and feature pages.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Pro feature unlock and licensing path.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Feature Categories & Subcategories', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<?php foreach ( $category_names as $category_name ) : ?>
								<?php
								$cat_terms = [];
								foreach ( ( $features[ $category_name ] ?? [] ) as $feature ) {
									$cat_terms[] = $cta_docs_strip_paren( $feature['title'] ?? '' );
								}
								?>
								<li>
									<strong><?php echo esc_html( $cta_docs_strip_paren( $category_name ) ); ?></strong>
									<?php if ( ! empty( $cat_terms ) ) : ?>
										— <?php echo esc_html( implode( ', ', $cat_terms ) ); ?>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				</div>
			</div>

		<div class="cta-docs-page" data-docs-page-content="getting-started-onboarding">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">🧭</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Onboarding Wizard', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description"><?php esc_html_e( 'This page documents the full CTA Manager onboarding modal exactly as it works in the plugin, including each step, field, button, and what data each option changes.', 'cta-manager' ); ?></p>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How the Onboarding Modal Is Triggered', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'The onboarding wizard contains 4 steps and opens from the Help trigger (Get Started) in CTA Manager.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'It is designed to appear when your site has no active non-demo CTAs, so new installs or reset environments can quickly get configured.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'When you click Finish or Maybe Later, onboarding is marked complete and dismissed in plugin onboarding state.', 'cta-manager' ); ?></li>
					</ol>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Modal Navigation and Global Controls', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Breadcrumb Steps (1-4)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Top progress indicator. You can click a step number to jump directly to that step.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Back', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Visible on steps 2-4. Returns to the previous step.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Next', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Moves to the next step. On steps 2 and 3, this label changes to Skip until import is completed or demo data already exists.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Maybe Later', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Visible on step 1 only. Closes the wizard and marks onboarding complete/dismissed.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Finish', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Visible on step 4 only. Closes wizard and saves completion state.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 1: Welcome', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Header Title', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Shows CTA Manager or CTA Manager Pro based on whether Pro is active.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Feature Cards', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Highlights Unlimited CTAs, Flexible Layouts, Analytics, and Integrations as a quick capability overview.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'View Documentation Button', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Closes onboarding and opens the Docs modal immediately so users can read guides before setup.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 2: Import Existing Data', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'This step restores a CTA Manager JSON export. Import mode is replace and backup is disabled in onboarding, so imported sections overwrite current data for those sections.', 'cta-manager' ); ?></p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Settings Toggle', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Imports plugin configuration/default values from the file.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'CTAs Toggle', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Imports CTA records (your CTA items and their saved setup).', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Analytics Toggle', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Imports visitor/analytics event history contained in the export.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Notifications Toggle', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Imports saved CTA Manager notification entries.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Drop or Choose JSON File', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'File input supports click-to-select and drag-and-drop.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'File Validation', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Client checks valid JSON and requires plugin marker "cta-manager"; invalid files show an inline error.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Import Now Button', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Appears only after a file is selected; sends selected sections to AJAX import endpoint.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Import Status Panel', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Shows loading, success, or error messages. On success, step marks complete and auto-advances to Step 3.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 3: Import Demo Data', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Use this when you want sample content instead of restoring from your own backup file.', 'cta-manager' ); ?></p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Auto-Detection: Demo Already Loaded', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'If demo CTAs already exist, this step displays a success info box and CTA count instead of import controls.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Demo Content Included', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Sample CTAs, demo analytics, demo notifications, and recommended defaults.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Import Demo Data Button', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Imports settings, CTAs, analytics, and notifications from bundled demo data for your license tier (free/pro).', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Demo Import Status', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Shows in-progress state and final result message with import counts.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Post-Import Behavior', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Replaces demo option area with success confirmation, then automatically moves to Step 4.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Step 4: Getting Help', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<ul>
							<li><strong><?php esc_html_e( 'Features', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Links conceptually to feature discovery so users understand available capabilities.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Explains where plugin alerts/tips are surfaced in the admin experience.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Support', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Shows users where to request help from the support team.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Documentation', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Highlights that the docs modal contains in-depth setup and usage references.', 'cta-manager' ); ?></li>
							<li><strong><?php esc_html_e( 'Get Support Button', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Opens the support ticket modal and closes onboarding.', 'cta-manager' ); ?></li>
						</ul>
					</div>
				</div>

				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Recommended First-Run Workflow', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Open onboarding from Get Started and review Step 1 orientation.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'If you have a prior export, complete Step 2 and import only the sections you trust and need.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'If you do not have prior data, use Step 3 to load demo data and explore working CTA examples quickly.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Use Step 4 to launch support/docs as needed, then click Finish to save onboarding completion.', 'cta-manager' ); ?></li>
					</ol>
				</div>
			</div>
		</div>

				<div class="cta-docs-page" data-docs-page-content="getting-started-creating-ctas">
					<div class="cta-docs-section">
						<div class="cta-docs-section-title-wrapper">
							<span class="cta-docs-title-icon">✨</span>
							<h2 class="cta-docs-section-title"><?php esc_html_e( 'Creating New CTAs', 'cta-manager' ); ?></h2>
						</div>
						<p class="cta-docs-section-description"><?php esc_html_e( 'Field-level guide for every tab in the New CTA modal.', 'cta-manager' ); ?></p>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'General Tab', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'CTA Name', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Internal label used in lists, filters, and shortcode lookup by name.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Status', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Draft/Published/Scheduled lifecycle state.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Schedule Type', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Date Range in free; Business Hours appears when Pro is active.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Include Times', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Adds hour/minute/AM-PM controls to date boundaries.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Display Tab', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'Layout', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Button and card layout variants (pro layouts when enabled).', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Visibility', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'All Devices, Mobile Only, Desktop Only, or Tablet targeting.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Action Tab', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'CTA Type', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Phone, Email, Link in free; popup/slide-in and other types in Pro.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Phone Number / Email To / Link URL', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Destination fields that appear based on selected CTA type.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Open in New Tab', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Link-only behavior toggle for `target=\"_blank\"`.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Content Tab (Pro)', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'Title / Tagline / Body', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Displayed in card/popup/slide contexts where content regions are available.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Formatting Controls', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Format toolbar updates hidden formatting inputs used at render time.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Character Limits', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Limits protect layout integrity across mobile and desktop variants.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Button Tab', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'Button Text + Formatting', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Primary call-to-action copy and text emphasis controls.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Width / Alignment', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Auto/Full width and left/center/right alignment group.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Background', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Solid, gradient, or transparent modes with gradient direction and color stops.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Border + Radius + Padding', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Style, color, width, side linking, corner radius, and per-side spacing.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Icon + Animations', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Icon selector plus button/icon animation settings.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Tracking Tab (Pro)', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'ARIA Fields', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Label, Role, Description, Controls, and Expanded state for accessibility semantics.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Data Attributes', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Repeatable key/value hooks for analytics, JS selectors, and integration tags.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'GTM / GA4 / PostHog Sections', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Per-CTA event names, toggles, conversion flags, and custom payload fields.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Custom Styling Tab (Pro)', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'Wrapper ID / Classes', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Outer container hooks for theme-level selectors.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'CTA ID / Classes', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Direct element hooks for one CTA or class groups.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Custom CSS', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Per-CTA CSS overrides injected only for that CTA.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Pro Numbers', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Quick-select for saved number presets when available.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Custom JS Tab (Pro)', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Per-CTA JavaScript for custom behavior, instrumentation, or integration triggers.', 'cta-manager' ); ?></p>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Blacklist Tab (Pro)', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Path rules (including wildcard/regex patterns) to suppress CTA rendering on selected URLs.', 'cta-manager' ); ?></p>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Slides Tab (Pro, Slide-In Type)', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'Trigger Method', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Time delay or scroll-percent trigger.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Position', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Nine placement options across top/center/bottom and left/center/right.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Animations', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Show/hide transition selection.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Auto-dismiss + Dismiss Controls', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Auto close delay plus manual close behavior options.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
					</div>
				</div>

				<div class="cta-docs-page" data-docs-page-content="getting-started-editing-ctas">
					<div class="cta-docs-section">
						<div class="cta-docs-section-title-wrapper">
							<span class="cta-docs-title-icon">🛠️</span>
							<h2 class="cta-docs-section-title"><?php esc_html_e( 'Editing CTAs', 'cta-manager' ); ?></h2>
						</div>
						<p class="cta-docs-section-description"><?php esc_html_e( 'Field-by-field editing reference for existing CTAs, including what updates immediately on save.', 'cta-manager' ); ?></p>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Tab Coverage in Edit Mode', 'cta-manager' ); ?></h4>
							<div class="cta-docs-feature-list cta-docs-feature-list--plain"><ul>
								<li><strong><?php esc_html_e( 'General', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Update name, status, schedule type, and include-times controls.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Display', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Change layout and device visibility options.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Action', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Switch CTA type and destination fields (phone/email/link/new-tab).', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Content (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Edit title/tagline/body and retained formatting states.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Button', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Adjust text, width/alignment, background modes, gradient, border, spacing, icon, and animations.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Tracking (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Update ARIA fields, data attributes, and GTM/GA4/PostHog per-CTA settings.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Custom Styling (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Update wrapper/CTA IDs/classes, custom CSS, and Pro Numbers.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Custom JS (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Update per-CTA JavaScript code.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Blacklist (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Adjust URL blocking paths, wildcard rules, and regex patterns.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Slides (Pro)', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Update trigger method, delay/scroll threshold, position, animations, and dismiss behavior.', 'cta-manager' ); ?></li>
							</ul></div>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'What Happens on Update', 'cta-manager' ); ?></h4>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'CTA ID stays the same, so existing shortcode/block placements remain valid.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Rendered output updates on next page load using the new field values.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Tracking continues for the same CTA record; future events use the updated settings.', 'cta-manager' ); ?></li>
							</ol>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Safe Edit Checklist', 'cta-manager' ); ?></h4>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Review Live Preview after tab changes, especially layout and button styling.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'If changing destinations or tracking payloads, test on a staging page first.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'After Update CTA, validate frontend behavior and analytics event capture.', 'cta-manager' ); ?></li>
							</ol>
						</div>
					</div>
				</div>

			<div class="cta-docs-page" data-docs-page-content="getting-started-scheduling-ctas">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">📅</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Scheduling CTAs', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Use scheduling to control when CTAs are visible and interactive.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Basic Scheduling', 'cta-manager' ); ?></h4>
						<p><?php esc_html_e( 'Set start/end date or status timing in the CTA form to limit visibility windows.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Advanced Scheduling (Pro)', 'cta-manager' ); ?></h4>
						<div class="cta-docs-feature-list cta-docs-feature-list--plain">
							<ul>
								<li><strong><?php esc_html_e( 'Business Hours', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Show CTAs only in defined day/time windows.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Date Ranges', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Run campaign windows with hard start/end boundaries.', 'cta-manager' ); ?></li>
								<li><strong><?php esc_html_e( 'Slide-In Timing', 'cta-manager' ); ?></strong> — <?php esc_html_e( 'Combine scheduling with trigger delay/scroll and auto-dismiss behavior.', 'cta-manager' ); ?></li>
							</ul>
						</div>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Verification Checklist', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Confirm site timezone in WordPress Settings > General.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Test inside and outside the configured schedule window.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Verify analytics still capture valid impressions/clicks during active windows.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>

		<!-- Feature Overview (all categories) -->
		<div class="cta-docs-page" data-docs-page-content="features-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">✨</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Feature Overview', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description"><?php esc_html_e( 'Everything CTA Manager can do — at a glance.', 'cta-manager' ); ?></p>
				<div class="cta-docs-section-block">
					<p><?php esc_html_e( 'Features are the building blocks of your conversion strategy. From embedding and styling CTAs to scheduling, targeting, analytics, and AI-powered automation — each feature category below groups related capabilities that work together to help you create, manage, and optimize high-performing calls to action.', 'cta-manager' ); ?></p>
				</div>

				<?php foreach ( $category_names as $overview_cat_name ) :
					$overview_cat_icon = $cta_docs_category_icon( $overview_cat_name );
					$overview_cat_label = $cta_docs_strip_paren( $overview_cat_name );
					$overview_cat_desc = $categories_meta[ $overview_cat_name ]['description'] ?? '';
					$overview_cat_features = $features[ $overview_cat_name ] ?? [];
					$overview_cat_overview_slug = $feature_category_overview_slug( $overview_cat_name );
				?>
				<div class="cta-docs-section-block cta-docs-features-overview-category">
					<h3 class="cta-docs-features-overview-category-title">
						<span class="cta-docs-title-icon"><?php echo esc_html( $overview_cat_icon ); ?></span>
						<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $overview_cat_overview_slug ); ?>">
							<?php echo esc_html( $overview_cat_label ); ?>
						</a>
					</h3>
					<?php if ( $overview_cat_desc ) : ?>
						<p><?php echo esc_html( $overview_cat_desc ); ?></p>
					<?php endif; ?>

					<div class="cta-docs-mode-grid">
						<?php foreach ( $overview_cat_features as $ov_feature ) :
							$ov_title = $cta_docs_strip_paren( $ov_feature['title'] ?? '' );
							$ov_slug  = $ov_feature['docs_page'] ?? '';
							$ov_is_pro  = 'pro' === ( $ov_feature['plan'] ?? 'free' );
							$ov_is_soon = empty( $ov_feature['implemented'] );
						?>
							<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $ov_slug ); ?>">
								<span class="cta-docs-title-icon"><?php echo esc_html( $ov_feature['icon'] ?? '•' ); ?></span>
								<span class="cta-docs-mode-card-body">
									<strong>
										<?php echo esc_html( $ov_title ); ?>
										<?php if ( $ov_is_pro ) : ?>
											<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
										<?php endif; ?>
										<?php if ( $ov_is_soon ) : ?>
											<span class="cta-docs-coming-badge cta-docs-card-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</strong>
									<span><?php echo esc_html( $ov_feature['description'] ?? '' ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="cta-docs-feature-list cta-docs-feature-list--groups">
						<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
						<div class="cta-docs-feature-groups-grid">
							<?php foreach ( $overview_cat_features as $ov_feature ) :
								$ov_title = $cta_docs_strip_paren( $ov_feature['title'] ?? '' );
								$ov_slug  = $ov_feature['docs_page'] ?? '';
								$ov_is_pro  = 'pro' === ( $ov_feature['plan'] ?? 'free' );
								$ov_is_soon = empty( $ov_feature['implemented'] );
								$ov_bullets = ! empty( $ov_feature['features'] ) && is_array( $ov_feature['features'] )
									? $ov_feature['features']
									: [ $ov_feature['description'] ?? '' ];
							?>
								<div class="cta-docs-feature-group">
									<div class="cta-docs-feature-group-title">
										<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $ov_slug ); ?>">
											<?php echo esc_html( $ov_title ); ?>
										</a>
										<?php if ( $ov_is_pro ) : ?>
											<span class="cta-docs-pro-badge cta-docs-feature-list-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
										<?php endif; ?>
										<?php if ( $ov_is_soon ) : ?>
											<span class="cta-docs-coming-badge cta-docs-feature-list-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</div>
									<ul>
										<?php foreach ( $ov_bullets as $ov_bullet ) : ?>
											<li><?php echo esc_html( $ov_bullet ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="cta-docs-page" data-docs-page-content="category-embedding-ctas-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon"><?php echo esc_html( $cta_docs_category_icon( $embedding_category_name ) ); ?></span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Embedding CTAs Overview', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description">
					<?php echo esc_html( $categories_meta[ $embedding_category_name ]['description'] ?? __( 'Display CTAs anywhere using shortcodes', 'cta-manager' ) ); ?>
				</p>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Embedding CTAs', 'cta-manager' ); ?></h4>
					<div class="cta-docs-mode-grid">
						<?php foreach ( $embedding_items as $feature ) : ?>
							<?php
							$feature_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
							$feature_slug  = $feature['docs_page'] ?? '';
							$is_pro        = 'pro' === ( $feature['plan'] ?? 'free' );
							$is_soon       = empty( $feature['implemented'] );
							?>
							<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $feature_slug ); ?>">
								<span class="cta-docs-title-icon"><?php echo esc_html( $feature['icon'] ?? '•' ); ?></span>
								<span class="cta-docs-mode-card-body">
									<strong>
										<?php echo esc_html( $feature_title ); ?>
										<?php if ( $is_pro ) : ?>
											<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
										<?php endif; ?>
										<?php if ( $is_soon ) : ?>
											<span class="cta-docs-coming-badge cta-docs-card-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</strong>
									<span><?php echo esc_html( $feature['description'] ?? '' ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-feature-list cta-docs-feature-list--groups">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-groups-grid">
						<?php foreach ( $embedding_items as $feature ) : ?>
							<?php
							$feature_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
							$feature_slug  = $feature['docs_page'] ?? '';
							$is_pro        = 'pro' === ( $feature['plan'] ?? 'free' );
							$is_soon       = empty( $feature['implemented'] );
							$feature_bullets = ! empty( $feature['features'] ) && is_array( $feature['features'] )
								? $feature['features']
								: [ $feature['description'] ?? '' ];
							?>
							<div class="cta-docs-feature-group">
								<div class="cta-docs-feature-group-title">
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $feature_slug ); ?>">
										<?php echo esc_html( $feature_title ); ?>
									</a>
									<?php if ( $is_pro ) : ?>
										<span class="cta-docs-pro-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
									<?php endif; ?>
									<?php if ( $is_soon ) : ?>
										<span class="cta-docs-coming-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
									<?php endif; ?>
								</div>
								<ul>
									<?php foreach ( $feature_bullets as $bullet ) : ?>
										<li><?php echo esc_html( $bullet ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'Embedding options let you place CTAs in posts, pages, templates, and existing on-page elements while keeping tracking and management centralized in CTA Manager.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Choose the embedding method that matches your workflow (shortcode, block, or advanced element conversion).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Configure and save the CTA in Manage CTAs.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Insert or connect the CTA on your target page and preview output.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Verify impressions/clicks in Analytics after publishing.', 'cta-manager' ); ?></li>
					</ol>
				</div>
			</div>
		</div>

		<!-- JS Hooks Category Overview Page -->
		<div class="cta-docs-page" data-docs-page-content="category-js-hooks-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon"><?php echo esc_html( $cta_docs_category_icon( __( 'JS Hooks', 'cta-manager' ) ) ); ?></span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'JS Hooks Overview', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description">
					<?php echo esc_html( $categories_meta[ __( 'JS Hooks', 'cta-manager' ) ]['description'] ?? __( 'JavaScript event hooks for CTA lifecycle and interactions', 'cta-manager' ) ); ?>
				</p>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Available Hooks', 'cta-manager' ); ?></h4>
					<div class="cta-docs-mode-grid">
						<?php
						$js_hooks_items = $features[ __( 'JS Hooks', 'cta-manager' ) ] ?? [];
						foreach ( $js_hooks_items as $hook ) :
							$hook_title = $cta_docs_strip_paren( $hook['title'] ?? '' );
							$hook_slug  = $hook['docs_page'] ?? '';
							$is_pro     = 'pro' === ( $hook['plan'] ?? 'free' );
							$is_soon    = empty( $hook['implemented'] );
						?>
							<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $hook_slug ); ?>">
								<span class="cta-docs-title-icon"><?php echo esc_html( $hook['icon'] ?? '•' ); ?></span>
								<span class="cta-docs-mode-card-body">
									<strong>
										<?php echo esc_html( $hook_title ); ?>
										<?php if ( $is_pro ) : ?>
											<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
										<?php endif; ?>
										<?php if ( $is_soon ) : ?>
											<span class="cta-docs-coming-badge cta-docs-card-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</strong>
									<span><?php echo esc_html( $hook['description'] ?? '' ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-feature-list cta-docs-feature-list--groups">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-groups-grid">
						<?php foreach ( $js_hooks_items as $hook ) :
							$hook_title = $cta_docs_strip_paren( $hook['title'] ?? '' );
							$hook_slug  = $hook['docs_page'] ?? '';
							$is_pro     = 'pro' === ( $hook['plan'] ?? 'free' );
							$is_soon    = empty( $hook['implemented'] );
							$hook_bullets = ! empty( $hook['features'] ) && is_array( $hook['features'] )
								? $hook['features']
								: [ $hook['description'] ?? '' ];
						?>
							<div class="cta-docs-feature-group">
								<div class="cta-docs-feature-group-title">
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $hook_slug ); ?>">
										<?php echo esc_html( $hook_title ); ?>
									</a>
									<?php if ( $is_pro ) : ?>
										<span class="cta-docs-pro-badge cta-docs-feature-list-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
									<?php endif; ?>
									<?php if ( $is_soon ) : ?>
										<span class="cta-docs-coming-badge cta-docs-feature-list-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
									<?php endif; ?>
								</div>
								<ul>
									<?php foreach ( $hook_bullets as $bullet ) : ?>
										<li><?php echo esc_html( $bullet ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'JavaScript hooks are document-level CustomEvents dispatched by CTA Manager with rich detail payloads. These events allow you to listen for CTA lifecycle events including loading, impressions, clicks, conversions, and A/B test variant assignments to integrate with analytics platforms, trigger UI changes, or coordinate third-party widgets.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Choose the hook that matches your use case (cta:loaded, cta:impression, cta:clicked, cta:conversion, etc.).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Add an event listener with document.addEventListener to listen for the event on your page.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Access hook payload data via event.detail to retrieve ctaId, variant, device, pageUrl, and other contextual information.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Integrate with your analytics platform, trigger custom logic, or coordinate with other page elements.', 'cta-manager' ); ?></li>
					</ol>
				</div>
			</div>
		</div>

		<!-- PHP Hooks Category Overview Page -->
		<div class="cta-docs-page" data-docs-page-content="category-php-hooks-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon"><?php echo esc_html( $cta_docs_category_icon( __( 'PHP Hooks', 'cta-manager' ) ) ); ?></span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'PHP Hooks Overview', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description">
					<?php echo esc_html( $categories_meta[ __( 'PHP Hooks', 'cta-manager' ) ]['description'] ?? __( 'Server-side filters and actions for CTA customization', 'cta-manager' ) ); ?>
				</p>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'Available Hooks', 'cta-manager' ); ?></h4>
					<div class="cta-docs-mode-grid">
						<?php
						$php_hooks_items = $features[ __( 'PHP Hooks', 'cta-manager' ) ] ?? [];
						foreach ( $php_hooks_items as $hook ) :
							$hook_title = $cta_docs_strip_paren( $hook['title'] ?? '' );
							$hook_slug  = ! empty( $hook['docs_page'] ) ? $hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $hook['hook_name'] ?? $hook['title'] ) );
							$hook_type  = $hook['hook_type'] ?? 'filter';
							$type_label = 'filter' === $hook_type ? 'Filter' : 'Action';
							$is_soon    = empty( $hook['implemented'] );
						?>
							<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $hook_slug ); ?>">
								<span class="cta-docs-title-icon"><?php echo esc_html( $hook['icon'] ?? '•' ); ?></span>
								<span class="cta-docs-mode-card-body">
									<strong>
										<?php echo esc_html( $hook_title ); ?>
										<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $type_label ); ?></span>
										<?php if ( $is_soon ) : ?>
											<span class="cta-docs-coming-badge cta-docs-card-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</strong>
									<span><?php echo esc_html( $hook['description'] ?? '' ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-feature-list cta-docs-feature-list--groups">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-groups-grid">
						<?php foreach ( $php_hooks_items as $hook ) :
							$hook_title = $cta_docs_strip_paren( $hook['title'] ?? '' );
							$hook_slug  = ! empty( $hook['docs_page'] ) ? $hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $hook['hook_name'] ?? $hook['title'] ) );
							$is_soon    = empty( $hook['implemented'] );
							$hook_bullets = ! empty( $hook['features'] ) && is_array( $hook['features'] )
								? $hook['features']
								: [ $hook['description'] ?? '' ];
						?>
							<div class="cta-docs-feature-group">
								<div class="cta-docs-feature-group-title">
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $hook_slug ); ?>">
										<?php echo esc_html( $hook_title ); ?>
									</a>
									<?php if ( $is_soon ) : ?>
										<span class="cta-docs-coming-badge cta-docs-feature-list-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
									<?php endif; ?>
								</div>
								<ul>
									<?php foreach ( $hook_bullets as $bullet ) : ?>
										<li><?php echo esc_html( $bullet ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
					<p><?php esc_html_e( 'PHP hooks are server-side extensibility points that allow developers to modify CTA Manager behavior without editing core plugin files. These WordPress filters and actions enable customizations including swapping layouts for specific CTAs, refining targeting rules with custom logic, intercepting database operations for logging, and integrating CTAs with other plugins or themes.', 'cta-manager' ); ?></p>
				</div>
				<div class="cta-docs-section-block">
					<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
					<ol class="cta-docs-steps">
						<li><?php esc_html_e( 'Choose the hook that matches your customization need (filters to modify values, actions to execute code).', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Add your hook callback in your theme\'s functions.php or custom plugin using standard WordPress filter/action syntax.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Use add_filter() for filters to modify values, or add_action() for actions to execute code at specific points.', 'cta-manager' ); ?></li>
						<li><?php esc_html_e( 'Test your implementation to ensure your customizations work as expected alongside other plugins and themes.', 'cta-manager' ); ?></li>
					</ol>
				</div>
			</div>
		</div>

		<?php foreach ( $hooks_by_category as $category_key => $category_hooks ) : ?>
			<?php
			$category_meta  = $cta_hook_categories[ $category_key ] ?? [];
			$category_label = $category_meta['label'] ?? ucfirst( str_replace( '-', ' ', $category_key ) );
			$category_slug  = $hook_category_overview_slug( 'js', $category_key );
			?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $category_slug ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🪝</span>
						<h2 class="cta-docs-section-title"><?php echo esc_html( sprintf( __( '%s Overview', 'cta-manager' ), $category_label ) ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Open any hook below to view payload fields, examples, and implementation notes.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php echo esc_html( $category_label ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $category_hooks as $hook ) : ?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
									<span class="cta-docs-title-icon"><?php echo esc_html( $hook['icon'] ?? '•' ); ?></span>
									<span class="cta-docs-mode-card-body">
										<strong><?php echo esc_html( $hook['title'] ?? '' ); ?></strong>
										<span><?php echo esc_html( $hook['description'] ?? '' ); ?></span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Available Hooks', 'cta-manager' ); ?></h4>
						<ul>
							<?php foreach ( $category_hooks as $hook ) : ?>
								<li>
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
										<?php echo esc_html( $hook['title'] ?? '' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php if ( ! empty( $integration_hooks ) ) : ?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $hook_category_overview_slug( 'js', 'integrations' ) ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🔌</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Integration Hooks Overview', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Integration hooks emit specialized events used by analytics and external service connectors.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Integration Hooks', 'cta-manager' ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $integration_hooks as $hook ) : ?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $hook['id'] ); ?>">
									<span class="cta-docs-title-icon"><?php echo esc_html( $hook['icon'] ?? '•' ); ?></span>
									<span class="cta-docs-mode-card-body">
										<strong><?php echo esc_html( $hook['title'] ?? '' ); ?></strong>
										<span><?php echo esc_html( $hook['description'] ?? '' ); ?></span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php foreach ( $php_hooks_by_category as $category_key => $category_hooks ) : ?>
			<?php
			$category_meta  = $cta_php_hook_categories[ $category_key ] ?? [];
			$category_label = $category_meta['label'] ?? ucfirst( str_replace( '-', ' ', $category_key ) );
			$category_slug  = $hook_category_overview_slug( 'php', $category_key );
			?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $category_slug ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🧩</span>
						<h2 class="cta-docs-section-title"><?php echo esc_html( sprintf( __( '%s Overview', 'cta-manager' ), $category_label ) ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'These server-side hooks let you alter CTA behavior during render, validation, permissions, and persistence workflows.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php echo esc_html( $category_label ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $category_hooks as $php_hook ) : ?>
								<?php
								$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
								$hook_type = ( $php_hook['hook_type'] ?? 'filter' ) === 'action' ? __( 'Action', 'cta-manager' ) : __( 'Filter', 'cta-manager' );
								?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
									<span class="cta-docs-title-icon"><?php echo esc_html( $php_hook['icon'] ?? '•' ); ?></span>
									<span class="cta-docs-mode-card-body">
										<strong>
											<?php echo esc_html( $php_hook['title'] ?? '' ); ?>
											<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $hook_type ); ?></span>
										</strong>
										<span><?php echo esc_html( $php_hook['description'] ?? '' ); ?></span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Available Hooks', 'cta-manager' ); ?></h4>
						<ul>
							<?php foreach ( $category_hooks as $php_hook ) : ?>
								<?php $page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) ); ?>
								<li>
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
										<?php echo esc_html( $php_hook['title'] ?? '' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Action Hooks Page -->
		<!-- Hooks: Using Hooks pages -->
		<div class="cta-docs-page" data-docs-page-content="hooks-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">🪝</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'CTA JS Hooks', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'Listen for CTA lifecycle events to integrate analytics, UX, and custom behaviors.', 'cta-manager' ); ?>
				</p>
				<div class="cta-docs-section-block">
					<p><?php esc_html_e( 'JavaScript hooks are document-level CustomEvents dispatched by CTA Manager with rich detail payloads. These events allow you to listen for CTA lifecycle events including loading, impressions, clicks, conversions, and A/B test variant assignments. Use these events to integrate with analytics platforms, trigger UI changes, or coordinate third-party widgets without modifying plugin code.', 'cta-manager' ); ?></p>
				</div>

				<!-- JS Hooks Mode Cards -->
				<div class="cta-docs-mode-grid">
					<?php foreach ( $cta_docs_hooks as $js_hook ) :
						$js_title = $cta_docs_strip_paren( $js_hook['title'] ?? '' );
						$js_slug  = $js_hook['id'] ?? '';
						?>
						<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $js_slug ); ?>">
							<span class="cta-docs-title-icon"><?php echo esc_html( $js_hook['icon'] ?? '•' ); ?></span>
							<span class="cta-docs-mode-card-body">
								<strong><?php echo esc_html( $js_title ); ?></strong>
								<span><?php echo esc_html( $js_hook['description'] ?? '' ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- JS Hooks Key Features -->
				<div class="cta-docs-feature-list cta-docs-feature-list--groups">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-groups-grid">
						<?php foreach ( $cta_docs_hooks as $js_hook ) :
							$js_title = $cta_docs_strip_paren( $js_hook['title'] ?? '' );
							$js_slug  = $js_hook['id'] ?? '';
							$js_bullets = ! empty( $js_hook['features'] ) && is_array( $js_hook['features'] )
								? $js_hook['features']
								: [ $js_hook['description'] ?? '' ];
							?>
							<div class="cta-docs-feature-group">
								<div class="cta-docs-feature-group-title">
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $js_slug ); ?>">
										<?php echo esc_html( $js_title ); ?>
									</a>
								</div>
								<ul>
									<?php foreach ( $js_bullets as $js_bullet ) : ?>
										<li><?php echo esc_html( $js_bullet ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
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

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'Why Use Helper Functions?', 'cta-manager' ); ?></h4>
				<p><?php esc_html_e( 'While document.addEventListener() works great for simple cases, CTAProHooks helpers provide built-in benefits: automatic listener cleanup on page unload (preventing memory leaks), less boilerplate code, and cleaner listener management. Use helpers when you\'re adding many listeners or building complex integrations that need to clean up after themselves.', 'cta-manager' ); ?></p>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'CTAProHooks.on()', 'cta-manager' ); ?></h4>
				<p><?php esc_html_e( 'Register an event listener with automatic cleanup. The listener is automatically removed when the page unloads, preventing memory leaks.', 'cta-manager' ); ?></p>
<pre><code>// Basic usage - listen to multiple events
if (window.CTAProHooks) {
  CTAProHooks.on('cta:impression', ({ detail }) =&gt; {
    console.log('CTA impression:', detail.ctaId, detail.layout);
  });

  // Send clicks to analytics
  CTAProHooks.on('cta:clicked', ({ detail }) =&gt; {
    dataLayer.push({
      event: 'cta_click',
      cta_id: detail.ctaId,
      layout: detail.layout,
      target: detail.targetUrl,
    });
  });
}</code></pre>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'CTAProHooks.off()', 'cta-manager' ); ?></h4>
				<p><?php esc_html_e( 'Manually remove a listener registered with CTAProHooks.on(). Useful when you need to stop listening for specific events before page unload.', 'cta-manager' ); ?></p>
<pre><code>// Define a handler function
const handleImpression = ({ detail }) =&gt; {
  console.log('Impression tracked:', detail.ctaId);
};

// Register the listener
if (window.CTAProHooks) {
  CTAProHooks.on('cta:impression', handleImpression);

  // Later, remove it
  CTAProHooks.off('cta:impression', handleImpression);
}</code></pre>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'Best Practices', 'cta-manager' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Always check if window.CTAProHooks exists before using it - it\'s loaded via JS hooks, not guaranteed.', 'cta-manager' ); ?></li>
					<li><?php esc_html_e( 'Use document.addEventListener() for simple, one-off listeners; use CTAProHooks for complex integrations.', 'cta-manager' ); ?></li>
					<li><?php esc_html_e( 'CTAProHooks listeners auto-cleanup on unload, so manual cleanup is optional in most cases.', 'cta-manager' ); ?></li>
					<li><?php esc_html_e( 'Define handler functions separately if you plan to remove listeners with CTAProHooks.off().', 'cta-manager' ); ?></li>
					<li><?php esc_html_e( 'Access event data via event.detail (same payload as document.addEventListener).', 'cta-manager' ); ?></li>
					<li><?php esc_html_e( 'Test listeners in the browser console to verify they\'re working correctly.', 'cta-manager' ); ?></li>
				</ul>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'addEventListener() vs CTAProHooks', 'cta-manager' ); ?></h4>
				<p style="font-size: 12px; color: #6b7280; margin-bottom: 12px;"><?php esc_html_e( 'Quick comparison to help you choose the right approach:', 'cta-manager' ); ?></p>
				<table style="width: 100%; border-collapse: collapse; font-size: 12px;">
					<tr style="border-bottom: 1px solid #e2e8f0; background: #f9fafb;">
						<th style="text-align: left; padding: 10px; font-weight: 700;"><?php esc_html_e( 'Feature', 'cta-manager' ); ?></th>
						<th style="text-align: left; padding: 10px; font-weight: 700;">addEventListener()</th>
						<th style="text-align: left; padding: 10px; font-weight: 700;">CTAProHooks</th>
					</tr>
					<tr style="border-bottom: 1px solid #e2e8f0;">
						<td style="padding: 10px;"><?php esc_html_e( 'Setup', 'cta-manager' ); ?></td>
						<td style="padding: 10px;">document.addEventListener('event-name', handler)</td>
						<td style="padding: 10px;">CTAProHooks.on('event-name', handler)</td>
					</tr>
					<tr style="border-bottom: 1px solid #e2e8f0;">
						<td style="padding: 10px;"><?php esc_html_e( 'Auto-cleanup', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Manual removal required', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Automatic on page unload', 'cta-manager' ); ?></td>
					</tr>
					<tr style="border-bottom: 1px solid #e2e8f0;">
						<td style="padding: 10px;"><?php esc_html_e( 'Memory leaks', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Possible if not removed', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Prevented (auto-cleanup)', 'cta-manager' ); ?></td>
					</tr>
					<tr>
						<td style="padding: 10px;"><?php esc_html_e( 'Best for', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Simple one-off listeners', 'cta-manager' ); ?></td>
						<td style="padding: 10px;"><?php esc_html_e( 'Multiple listeners, complex code', 'cta-manager' ); ?></td>
					</tr>
				</table>
			</div>

			<div class="cta-docs-section-block">
				<h4><?php esc_html_e( 'Complete Integration Example', 'cta-manager' ); ?></h4>
<pre><code>// Full analytics integration with CTAProHooks
if (window.CTAProHooks && window.dataLayer) {
  // Define handlers
  const handleImpression = ({ detail }) =&gt; {
    dataLayer.push({
      event: 'cta_impression',
      cta_id: detail.ctaId,
      layout: detail.layout,
      device: detail.device,
    });
  };

  const handleClick = ({ detail }) =&gt; {
    dataLayer.push({
      event: 'cta_click',
      cta_id: detail.ctaId,
      target: detail.targetUrl,
      variant: detail.variant || 'control',
    });
  };

  // Register listeners
  CTAProHooks.on('cta:impression', handleImpression);
  CTAProHooks.on('cta:clicked', handleClick);

  // Optional: unsubscribe from events later
  // CTAProHooks.off('cta:impression', handleImpression);
  // CTAProHooks.off('cta:clicked', handleClick);
}</code></pre>
			</div>
		</div>

		<!-- PHP Hooks Overview Page -->
		<div class="cta-docs-page" data-docs-page-content="php-hooks-overview">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<span class="cta-docs-title-icon">🧩</span>
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'PHP Hooks', 'cta-manager' ); ?></h2>
				</div>
				<p class="cta-docs-section-description">
					<?php esc_html_e( 'Server-side actions/filters to extend CTA rendering, targeting, and styling.', 'cta-manager' ); ?>
				</p>
				<div class="cta-docs-section-block">
					<p><?php esc_html_e( 'PHP hooks are server-side extensibility points that allow developers to modify CTA Manager behavior without editing core plugin files. These WordPress filters and actions enable customizations including swapping layouts for specific CTAs, refining targeting rules with custom logic, intercepting database operations for logging, and integrating CTAs with other plugins or themes. Hooks execute during the WordPress request lifecycle, giving you control over what gets rendered and sent to the browser.', 'cta-manager' ); ?></p>
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

				<!-- PHP Hooks Mode Cards -->
				<div class="cta-docs-mode-grid">
					<?php foreach ( $php_hooks_list as $php_hook ) :
						$php_title = $cta_docs_strip_paren( $php_hook['title'] ?? '' );
						$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
						$hook_type_label = ( $php_hook['hook_type'] ?? 'filter' ) === 'filter' ? 'Filter' : 'Action';
						?>
						<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
							<span class="cta-docs-title-icon"><?php echo esc_html( $php_hook['icon'] ?? '•' ); ?></span>
							<span class="cta-docs-mode-card-body">
								<strong>
									<?php echo esc_html( $php_title ); ?>
									<span class="cta-docs-pro-badge cta-docs-card-badge"><?php echo esc_html( $hook_type_label ); ?></span>
								</strong>
								<span><?php echo esc_html( $php_hook['description'] ?? '' ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>

				<!-- PHP Hooks Key Features -->
				<div class="cta-docs-feature-list cta-docs-feature-list--groups">
					<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
					<div class="cta-docs-feature-groups-grid">
						<?php foreach ( $php_hooks_list as $php_hook ) :
							$php_title = $cta_docs_strip_paren( $php_hook['title'] ?? '' );
							$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
							$php_bullets = ! empty( $php_hook['features'] ) && is_array( $php_hook['features'] )
								? $php_hook['features']
								: [ $php_hook['description'] ?? '' ];
							?>
							<div class="cta-docs-feature-group">
								<div class="cta-docs-feature-group-title">
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $page_slug ); ?>">
										<?php echo esc_html( $php_title ); ?>
									</a>
								</div>
								<ul>
									<?php foreach ( $php_bullets as $php_bullet ) : ?>
										<li><?php echo esc_html( $php_bullet ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
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

				<?php if ( ! empty( $filter_hooks ) ) : ?>
				<div class="cta-docs-feature-list">
					<h4><?php esc_html_e( 'Filter Hooks', 'cta-manager' ); ?></h4>
					<ul>
						<?php foreach ( $filter_hooks as $php_hook ) :
							$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
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
							$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
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
			$page_slug = ! empty( $php_hook['docs_page'] ) ? $php_hook['docs_page'] : 'php-hook-' . sanitize_title( str_replace( [ 'cta_pro_', 'cta_db_', 'cta_' ], '', $php_hook['hook_name'] ?? $php_hook['title'] ) );
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

		<!-- Embedding CTAs Page (static, with copyable examples) -->
		<div class="cta-docs-page" data-docs-page-content="feature-shortcode-usage">
			<div class="cta-docs-section">
				<div class="cta-docs-section-title-wrapper">
					<h2 class="cta-docs-section-title">
						<?php esc_html_e( 'Embedding CTAs', 'cta-manager' ); ?>
					</h2>
					<span class="cta-docs-title-icon">📋</span>
				</div>
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
			<div class="cta-docs-page" data-docs-page-content="settings-overview">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">⚙️</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Settings Overview', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Configure analytics, styling, performance, icons, and data policies from CTA Manager settings.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Settings', 'cta-manager' ); ?></h4>
						<div class="cta-docs-mode-grid">
							<button type="button" class="cta-docs-mode-card" data-docs-page="settings-analytics"><span class="cta-docs-title-icon">📊</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Tracking behavior, retention windows, and reporting defaults.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="settings-custom-css"><span class="cta-docs-title-icon">🎨</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Custom Global CSS', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Global CSS overrides for CTA presentation.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="settings-performance"><span class="cta-docs-title-icon">⚡</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Performance', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Script loading, caching, and optimization controls.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="settings-custom-icons"><span class="cta-docs-title-icon">🖼️</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Custom Icons', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Upload and manage icon assets used by CTA buttons.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="settings-data-management"><span class="cta-docs-title-icon">🗃️</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Data Management', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Retention and cleanup behavior for CTA data.', 'cta-manager' ); ?></span></span></button>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Configure analytics collection and retention', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Apply global styling and custom icon libraries', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Tune performance behavior for faster rendering', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Control long-term data footprint and cleanup', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
						<p><?php esc_html_e( 'Settings centralize site-wide CTA behavior so analytics, performance, visual defaults, and data policies remain consistent across all CTAs.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Open CTA Manager → Settings and choose the settings page you need.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Adjust options for your environment and save changes.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Validate behavior using CTA preview or live test pages.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Revisit settings after feature rollouts or major campaign changes.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>
			<div class="cta-docs-page" data-docs-page-content="settings-business-hours">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🕘</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Business Hours', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Configure a site-wide business hours schedule for CTA visibility, including timezone, closed-state behavior, and day-level active windows.', 'cta-manager' ); ?>
					</p>
					<div class="cta-docs-feature-list cta-docs-feature-list--plain">
						<h4><?php esc_html_e( 'Available Settings', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Show CTAs only during business hours (global toggle)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Timezone selector (defaults to your WordPress timezone)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Show message when closed toggle + custom closed message', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Per-day open/close times with day-level active switches', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Important Behavior', 'cta-manager' ); ?></h4>
						<p><?php esc_html_e( 'Enabling the global business-hours setting applies a site-wide visibility gate. CTA output is restricted by the configured schedule windows. If you need per-CTA control, configure schedule behavior on each CTA individually instead of relying only on the global gate.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Open Settings → Business Hours and enable the global toggle if you want a site-wide schedule gate.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Set your timezone first, then configure closed behavior and optional closed message.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Set open/close times for each day and use Active switches to include/exclude days.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Save settings, then test a CTA during open and closed windows to verify expected behavior.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>
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
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Custom Global CSS', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Custom Global CSS lets you add site-wide styles for every CTA without editing theme files.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-info"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'What is Custom Global CSS?', 'cta-manager' ); ?></p>
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
						<h4><?php esc_html_e( 'How to Use Custom Global CSS', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Upgrade to Pro to enable Custom Global CSS', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Write CSS in the editor using standard syntax', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Target CTAs with class selectors like .cta-button', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click Save Custom Global CSS to apply changes', 'cta-manager' ); ?></li>
						</ol>
					</div>
					<div class="cta-info-box cta-info-box--warning" style="margin-top: 16px;">
						<span class="cta-info-box__icon dashicons dashicons-warning"></span>
						<div>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Custom Global CSS overrides theme styles. Test thoroughly on multiple devices.', 'cta-manager' ); ?></p>
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
			<div class="cta-docs-page" data-docs-page-content="tools-overview">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🧰</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Tools Overview', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Use maintenance and migration tools to export, import, seed demo data, reset records, and troubleshoot CTA Manager.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Tools', 'cta-manager' ); ?></h4>
						<div class="cta-docs-mode-grid">
							<button type="button" class="cta-docs-mode-card" data-docs-page="tools-export"><span class="cta-docs-title-icon">📤</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Export Data', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Export CTA data and settings for backup or migration.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="tools-import"><span class="cta-docs-title-icon">📥</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Import Data', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Import previously exported CTA datasets.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="tools-demo"><span class="cta-docs-title-icon">🧪</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Demo Data', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Load sample CTAs for testing and exploration.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="tools-reset"><span class="cta-docs-title-icon">🧹</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Reset Data', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Reset selected CTA records and analytics datasets.', 'cta-manager' ); ?></span></span></button>
							<button type="button" class="cta-docs-mode-card" data-docs-page="tools-debug"><span class="cta-docs-title-icon">🐞</span><span class="cta-docs-mode-card-body"><strong><?php esc_html_e( 'Debug Mode', 'cta-manager' ); ?></strong><span><?php esc_html_e( 'Troubleshoot issues with logging and diagnostics.', 'cta-manager' ); ?></span></span></button>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'Portable data export/import workflows', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Rapid testing with seeded demo datasets', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Selective reset utilities for cleanup', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Debug utilities for faster issue diagnosis', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
						<p><?php esc_html_e( 'Tools provide operational controls for moving data between sites, validating configurations, cleaning environments, and diagnosing plugin behavior.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Choose the tool that matches your task (backup, migration, reset, or troubleshooting).', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Review warnings and confirm scope before running destructive actions.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Run the tool and verify success notifications/log output.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Re-check CTAs and analytics after completion to confirm expected state.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>

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

			<div class="cta-docs-page" data-docs-page-content="tools-clear-caches">
				<div class="cta-docs-section">
					<h2 class="cta-docs-section-title"><?php esc_html_e( 'Clear Caches', 'cta-manager' ); ?></h2>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Flush internal caches used by CTA Manager so the latest data, settings, and debug state are loaded fresh on the next page view.', 'cta-manager' ); ?>
					</p>
					<div class="cta-info-box cta-info-box--info">
						<span class="cta-info-box__icon dashicons dashicons-update"></span>
						<div>
							<p class="cta-info-box__title"><?php esc_html_e( 'When to clear caches', 'cta-manager' ); ?></p>
							<p class="cta-info-box__body">
								<?php esc_html_e( 'Clear caches after bulk imports, manual database edits, toggling debug mode, or when CTA data appears stale on the front end.', 'cta-manager' ); ?>
							</p>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'What gets cleared', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'CTA data cache (forces fresh database reads for all CTA records)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Settings cache (reloads plugin options from the settings table)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Debug log cache (ensures debug state reflects current toggle)', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Feature registry cache (refreshes category and feature metadata)', 'cta-manager' ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to clear caches', 'cta-manager' ); ?></h4>
						<ol style="margin: 16px 0; padding-left: 24px;">
							<li><?php esc_html_e( 'Navigate to Tools → Clear Caches', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Click the "Clear All Caches" button', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Wait for the confirmation toast indicating caches were flushed', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Reload any admin or front-end page to verify updated data', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>

			<div class="cta-docs-page" data-docs-page-content="integrations-overview">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon">🔌</span>
						<h2 class="cta-docs-section-title"><?php esc_html_e( 'Integrations Overview', 'cta-manager' ); ?></h2>
					</div>
					<p class="cta-docs-section-description">
						<?php esc_html_e( 'Browse integration categories and open individual integration setup guides.', 'cta-manager' ); ?>
					</p>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'Integration Categories', 'cta-manager' ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $integration_category_names as $integration_category_name ) : ?>
								<?php
								$integration_overview_slug = $integration_category_overview_slug( $integration_category_name );
								$integration_count = count( $integrations[ $integration_category_name ] ?? [] );
								?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $integration_overview_slug ); ?>">
									<span class="cta-docs-title-icon"><?php echo esc_html( $cta_docs_integration_category_icon( $integration_category_name ) ); ?></span>
									<span class="cta-docs-mode-card-body">
										<strong><?php echo esc_html( $cta_docs_strip_paren( $integration_category_name ) ); ?></strong>
										<span>
											<?php
											echo esc_html(
												sprintf(
													_n( '%d integration', '%d integrations', $integration_count, 'cta-manager' ),
													$integration_count
												)
											);
											?>
										</span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Integration Categories', 'cta-manager' ); ?></h4>
						<ul>
							<?php foreach ( $integration_category_names as $integration_category_name ) : ?>
								<?php
								$integration_overview_slug = $integration_category_overview_slug( $integration_category_name );
								$integration_count = count( $integrations[ $integration_category_name ] ?? [] );
								?>
								<li>
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $integration_overview_slug ); ?>">
										<?php echo esc_html( $cta_docs_strip_paren( $integration_category_name ) ); ?>
									</a>
									<?php echo esc_html( sprintf( _n( ' (%d integration)', ' (%d integrations)', $integration_count, 'cta-manager' ), $integration_count ) ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
						<p><?php esc_html_e( 'Integrations connect CTA engagement data to your existing analytics, CRM, communication, and automation stack so CTA events can trigger real workflows across your business.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Open an integration category overview below and choose the tool you want to connect.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Follow that integration page to add credentials/API keys and complete authorization.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Enable the integration and define which CTA events should be sent.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Run a test CTA interaction and confirm events appear in the target platform.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>

<?php foreach ( $category_names as $category_name ) :
	if ( __( 'Embedding CTAs', 'cta-manager' ) === $category_name ) {
		continue;
	}
	$category_page_slug = $feature_category_overview_slug( $category_name );
	$category_label     = $cta_docs_strip_paren( $category_name );
	$category_desc      = $categories_meta[ $category_name ]['description'] ?? '';
	$category_emoji     = $cta_docs_category_icon( $category_name );
	$category_items     = $features[ $category_name ] ?? [];
	$category_summary   = $cta_docs_category_summary( $category_items );
	?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $category_page_slug ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon"><?php echo esc_html( $category_emoji ); ?></span>
						<h2 class="cta-docs-section-title"><?php echo esc_html( sprintf( __( '%s Overview', 'cta-manager' ), $category_label ) ); ?></h2>
					</div>
					<?php if ( ! empty( $category_desc ) ) : ?>
						<p class="cta-docs-section-description"><?php echo esc_html( $category_desc ); ?></p>
					<?php else : ?>
						<p class="cta-docs-section-description"><?php esc_html_e( 'Open any feature below to view details and implementation guidance.', 'cta-manager' ); ?></p>
					<?php endif; ?>
					<div class="cta-docs-section-block">
						<h4><?php echo esc_html( $category_label ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $category_items as $feature ) : ?>
								<?php
								$feature_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
								$feature_slug  = $feature['docs_page'] ?? '';
								$is_pro        = 'pro' === ( $feature['plan'] ?? 'free' );
								$is_soon       = empty( $feature['implemented'] );
								?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $feature_slug ); ?>">
									<span class="cta-docs-title-icon"><?php echo esc_html( $feature['icon'] ?? '•' ); ?></span>
									<span class="cta-docs-mode-card-body">
										<strong>
											<?php echo esc_html( $feature_title ); ?>
											<?php if ( $is_pro ) : ?>
												<span class="cta-docs-pro-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
											<?php endif; ?>
											<?php if ( $is_soon ) : ?>
												<span class="cta-docs-coming-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
											<?php endif; ?>
										</strong>
										<span><?php echo esc_html( $feature['description'] ?? '' ); ?></span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-feature-list cta-docs-feature-list--groups">
						<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
						<div class="cta-docs-feature-groups-grid">
							<?php foreach ( $category_items as $feature ) : ?>
								<?php
								$feature_title = $cta_docs_strip_paren( $feature['title'] ?? '' );
								$feature_slug  = $feature['docs_page'] ?? '';
								$is_pro        = 'pro' === ( $feature['plan'] ?? 'free' );
								$is_soon       = empty( $feature['implemented'] );
								$feature_bullets = ! empty( $feature['features'] ) && is_array( $feature['features'] )
									? $feature['features']
									: [ $feature['description'] ?? '' ];
								?>
								<div class="cta-docs-feature-group">
									<div class="cta-docs-feature-group-title">
										<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $feature_slug ); ?>">
											<?php echo esc_html( $feature_title ); ?>
										</a>
										<?php if ( $is_pro ) : ?>
											<span class="cta-docs-pro-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
										<?php endif; ?>
										<?php if ( $is_soon ) : ?>
											<span class="cta-docs-coming-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
										<?php endif; ?>
									</div>
									<ul>
										<?php foreach ( $feature_bullets as $bullet ) : ?>
											<li><?php echo esc_html( $bullet ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: category label, 2: feature count */
									__( '%1$s centralizes related CTA capabilities into one workflow so you can configure, launch, and optimize that part of your conversion system from a single place. This category currently includes %2$d documented features.', 'cta-manager' ),
									$category_label,
									$category_summary['total']
								)
							);
							?>
						</p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php echo esc_html( sprintf( __( 'Start with the %s feature cards above and open the feature most relevant to your current CTA objective.', 'cta-manager' ), $category_label ) ); ?></li>
							<li><?php esc_html_e( 'Apply the feature-level configuration steps on the linked page and save your CTA changes.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Preview the CTA to confirm behavior and styling match your intended outcome.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Use analytics and A/B testing where available to validate performance and iterate.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>
<?php endforeach; ?>

<?php foreach ( $integration_category_names as $integration_category_name ) : ?>
	<?php
	$integration_page_slug  = $integration_category_overview_slug( $integration_category_name );
	$integration_page_label = $cta_docs_strip_paren( $integration_category_name );
	$integration_items      = $integrations[ $integration_category_name ] ?? [];
	$integration_summary    = $cta_docs_category_summary( $integration_items );
	?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $integration_page_slug ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<span class="cta-docs-title-icon"><?php echo esc_html( $cta_docs_integration_category_icon( $integration_category_name ) ); ?></span>
						<h2 class="cta-docs-section-title"><?php echo esc_html( sprintf( __( '%s Integrations', 'cta-manager' ), $integration_page_label ) ); ?></h2>
					</div>
					<p class="cta-docs-section-description"><?php esc_html_e( 'Select an integration below to open configuration instructions and capabilities.', 'cta-manager' ); ?></p>
					<div class="cta-docs-section-block">
						<h4><?php echo esc_html( $integration_page_label ); ?></h4>
						<div class="cta-docs-mode-grid">
							<?php foreach ( $integration_items as $integration ) : ?>
								<?php
								$integration_title = $cta_docs_strip_paren( $integration['title'] ?? '' );
								$integration_slug  = $integration['docs_page'] ?? '';
								$is_soon           = empty( $integration['implemented'] );
								?>
								<button type="button" class="cta-docs-mode-card" data-docs-page="<?php echo esc_attr( $integration_slug ); ?>">
									<?php if ( ! empty( $integration['image'] ) ) : ?>
										<img src="<?php echo esc_url( $integration['image'] ); ?>" alt="" style="width:20px;height:20px;flex-shrink:0;margin-top:2px;">
									<?php else : ?>
										<span class="dashicons dashicons-admin-links"></span>
									<?php endif; ?>
									<span class="cta-docs-mode-card-body">
										<strong>
											<?php echo esc_html( $integration_title ); ?>
											<?php if ( $is_soon ) : ?>
												<span class="cta-docs-coming-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
											<?php endif; ?>
										</strong>
										<span><?php echo esc_html( $integration['description'] ?? '' ); ?></span>
									</span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Available Integrations', 'cta-manager' ); ?></h4>
						<ul>
							<?php foreach ( $integration_items as $integration ) : ?>
								<?php
								$integration_title = $cta_docs_strip_paren( $integration['title'] ?? '' );
								$integration_slug  = $integration['docs_page'] ?? '';
								?>
								<li>
									<a href="#" class="cta-docs-link" data-docs-page="<?php echo esc_attr( $integration_slug ); ?>">
										<?php echo esc_html( $integration_title ); ?>
									</a>
									<?php if ( empty( $integration['implemented'] ) ) : ?>
										<span class="cta-docs-coming-badge"><?php echo esc_html( $menu_coming_label ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="cta-docs-feature-list">
						<h4><?php esc_html_e( 'Key Features', 'cta-manager' ); ?></h4>
						<ul>
							<li><?php echo esc_html( sprintf( _n( '%d integration in this category', '%d integrations in this category', $integration_summary['total'], 'cta-manager' ), $integration_summary['total'] ) ); ?></li>
							<li><?php echo esc_html( sprintf( _n( '%d available now', '%d available now', $integration_summary['available'], 'cta-manager' ), $integration_summary['available'] ) ); ?></li>
							<li><?php echo esc_html( sprintf( _n( '%d marked Coming Soon', '%d marked Coming Soon', $integration_summary['coming'], 'cta-manager' ), $integration_summary['coming'] ) ); ?></li>
						</ul>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: integration category name, 2: integration count */
									__( '%1$s integrations let CTA Manager exchange data with external tools so CTA interactions can power reporting, alerts, attribution, and automation workflows. This category currently includes %2$d integrations.', 'cta-manager' ),
									$integration_page_label,
									$integration_summary['total']
								)
							);
							?>
						</p>
					</div>
					<div class="cta-docs-section-block">
						<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
						<ol class="cta-docs-steps">
							<li><?php esc_html_e( 'Open one integration from the cards above and complete its credential/auth setup.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Map CTA events and fields to the destination tool where applicable.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Enable the integration and save settings.', 'cta-manager' ); ?></li>
							<li><?php esc_html_e( 'Run a live test CTA interaction and verify data appears in that platform.', 'cta-manager' ); ?></li>
						</ol>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Dynamic Feature Pages -->
		<?php foreach ( $category_names as $category_name ) :
		?>
		<?php foreach ( $features[ $category_name ] as $feature ) : ?>
			<div class="cta-docs-page" data-docs-page-content="<?php echo esc_attr( $feature['docs_page'] ); ?>">
				<div class="cta-docs-section">
					<div class="cta-docs-section-title-wrapper">
						<?php if ( ! empty( $feature['icon'] ) ) : ?>
							<span class="cta-docs-title-icon"><?php echo esc_html( $feature['icon'] ); ?></span>
						<?php endif; ?>
						<h2 class="cta-docs-section-title">
							<?php echo esc_html( $feature['title'] ); ?>
						</h2>
						<?php if ( 'pro' === ( $feature['plan'] ?? 'free' ) ) : ?>
							<span class="cta-docs-pro-badge cta-docs-page-title-badge"><?php echo esc_html( $labels['badge_pro'] ?? 'Pro' ); ?></span>
						<?php endif; ?>
						<?php if ( empty( $feature['implemented'] ) ) : ?>
							<span class="cta-docs-coming-badge cta-docs-page-title-badge"><?php echo esc_html( $labels['badge_coming_soon'] ?? 'Coming Soon' ); ?></span>
						<?php endif; ?>
					</div>
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
					<?php if ( 'feature-advanced-embedding' === ( $feature['docs_page'] ?? '' ) ) : ?>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Custom Wrapper IDs & Classes', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Where to add it: New/Edit CTA → Custom Styling tab. Use Wrapper ID for one unique selector and Wrapper Classes for reusable selectors across templates.', 'cta-manager' ); ?></p>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Set Wrapper ID only when you need a unique element target (example: homepage-hero-cta).', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Use Wrapper Classes for shared styling/behavior (example: marketing-cta, dark-section).', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Save the CTA, inspect frontend markup, then target wrapper selectors in CSS/JS.', 'cta-manager' ); ?></li>
							</ol>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Data-* Attributes For Selectors', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Where to add it: New/Edit CTA → Tracking tab → Data Attributes. Add unlimited key/value pairs for analytics, JS behavior, and CSS attribute selectors.', 'cta-manager' ); ?></p>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Click Add Attribute and enter a key and value (example key: data-gtm-category, value: lead-gen).', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'If you omit the data- prefix, CTA Manager adds it automatically.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Read attributes in frontend scripts (dataset) or target them in CSS selectors.', 'cta-manager' ); ?></li>
							</ol>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'Per-CTA CSS Hooks', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Where to add it: Custom Styling tab. CTA ID and CTA Classes are applied directly to the CTA element and let you style one CTA without affecting others.', 'cta-manager' ); ?></p>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Add CTA ID for one unique hook (example: pricing-cta-primary).', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Add CTA Classes for reusable hook groups (example: cta-pill, cta-shadow-lg).', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Use those selectors in Custom CSS, theme stylesheets, or JS listeners.', 'cta-manager' ); ?></li>
							</ol>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'ARIA Attribute Support', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Where to add it: Tracking tab. Use dedicated ARIA Label, ARIA Description, ARIA Controls, ARIA Expanded, and Role fields for accessible CTA semantics.', 'cta-manager' ); ?></p>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Set ARIA Label to define an explicit screen-reader name for the CTA.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Use ARIA Description and ARIA Controls when the CTA affects another UI region.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Set ARIA Expanded to true/false/mixed when the CTA toggles expandable content.', 'cta-manager' ); ?></li>
							</ol>
						</div>
						<div class="cta-docs-section-block">
							<h4><?php esc_html_e( 'GTM Integration Ready', 'cta-manager' ); ?></h4>
							<p><?php esc_html_e( 'Where to add it: Tracking tab → Data Attributes. Add GTM-friendly keys like data-gtm-event, data-gtm-category, data-gtm-action, and data-gtm-label for clean trigger rules.', 'cta-manager' ); ?></p>
							<ol class="cta-docs-steps">
								<li><?php esc_html_e( 'Add GTM attributes as data keys on the CTA so they are available in the rendered markup.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'In GTM, configure click triggers that match your CTA selectors/attributes.', 'cta-manager' ); ?></li>
								<li><?php esc_html_e( 'Publish changes and verify in GTM Preview that the expected attribute payload is present.', 'cta-manager' ); ?></li>
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
						<div class="cta-docs-section-title-wrapper">
							<?php if ( ! empty( $integration['image'] ) ) : ?>
								<span class="cta-docs-title-icon">
									<img src="<?php echo esc_url( $integration['image'] ); ?>" alt="" style="width:28px;height:28px;display:block;">
								</span>
							<?php elseif ( ! empty( $integration['icon'] ) ) : ?>
								<span class="cta-docs-title-icon"><?php echo esc_html( $integration['icon'] ); ?></span>
							<?php else : ?>
								<span class="cta-docs-title-icon">🔌</span>
							<?php endif; ?>
							<h2 class="cta-docs-section-title">
								<?php echo esc_html( $integration['title'] ); ?>
							</h2>
							<?php if ( empty( $integration['implemented'] ) ) : ?>
								<span class="cta-docs-coming-badge cta-docs-page-title-badge"><?php echo esc_html( $labels['badge_coming_soon'] ?? 'Coming Soon' ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $integration['description'] ) ) : ?>
							<p class="cta-docs-section-description">
								<?php echo esc_html( $integration['description'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $integration['implemented'] ) ) :
							$int_id        = $integration['id'] ?? '';
							$int_is_active = false;
							$has_pro_class = false;
							if ( 'ga4' === $int_id && class_exists( 'TDA_GA4' ) ) {
								$has_pro_class = true;
								$ga4_s         = TDA_GA4::get_instance()->get_settings();
								$int_is_active = ! empty( $ga4_s['ga4_enabled'] ) && ! empty( $ga4_s['ga4_measurement_id'] );
							}
							if ( 'posthog' === $int_id && class_exists( 'TDA_PostHog' ) ) {
								$has_pro_class = true;
								$ph_s          = TDA_PostHog::get_instance()->get_settings();
								$int_is_active = ! empty( $ph_s['posthog_enabled'] ) && ! empty( $ph_s['posthog_api_key'] );
							}
						?>
							<?php if ( $has_pro_class ) : ?>
								<?php if ( $int_is_active ) : ?>
									<div class="cta-docs-integration-status is-active">
										<span class="cta-docs-integration-status-dot"></span>
										<span class="cta-docs-integration-status-text">
											<?php esc_html_e( 'Active', 'cta-manager' ); ?>
										</span>
									</div>
								<?php else : ?>
									<div class="cta-info-box cta-info-box--info" style="margin: 12px 0 20px;">
										<span class="cta-info-box__icon dashicons dashicons-info"></span>
										<span class="cta-info-box__body" style="flex: 1;">
											<?php
											printf(
												/* translators: %s: integration title */
												esc_html__( '%s is ready to use. Configure it on the Integrations page to get started.', 'cta-manager' ),
												esc_html( $integration['title'] )
											);
											?>
										</span>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager-integrations&activate=' . $int_id ) ); ?>" class="cta-docs-activate-btn" data-activate-integration="<?php echo esc_attr( $int_id ); ?>">
											<?php esc_html_e( 'Activate', 'cta-manager' ); ?>
										</a>
									</div>
								<?php endif; ?>
							<?php else : ?>
								<div class="cta-docs-integration-status is-locked">
									<span class="cta-docs-integration-status-dot"></span>
									<span class="cta-docs-integration-status-text">
										<?php esc_html_e( 'Requires Pro', 'cta-manager' ); ?>
									</span>
								</div>
							<?php endif; ?>
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
						<?php if ( ! empty( $integration['details'] ) ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php esc_html_e( 'What this does', 'cta-manager' ); ?></h4>
								<p><?php echo esc_html( $integration['details'] ); ?></p>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $integration['instructions'] ) && is_array( $integration['instructions'] ) ) : ?>
							<div class="cta-docs-section-block">
								<h4><?php esc_html_e( 'How to configure & use it', 'cta-manager' ); ?></h4>
								<ol class="cta-docs-steps">
									<?php foreach ( $integration['instructions'] as $step ) : ?>
										<li><?php echo esc_html( $step ); ?></li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endforeach; ?>
</div>
</div>

<style>
	#cta-docs-modal .cta-docs-header-tools {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		margin-left: auto;
		min-width: 0;
	}

	#cta-docs-modal .cta-docs-page-summary {
		display: inline-flex;
		flex-direction: column;
		min-width: 0;
		flex: 1 1 auto;
		margin-right: 4px;
	}

	#cta-docs-modal .cta-docs-sticky-title {
		font-size: 20px;
		font-weight: 700;
		line-height: 1.2;
		color: #111827;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 420px;
	}

	#cta-docs-modal .cta-docs-sticky-description {
		font-size: 13px;
		line-height: 1.3;
		color: #64748b;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 520px;
	}

	#cta-docs-modal .cta-docs-sticky-source-hidden {
		display: none !important;
	}

	#cta-docs-modal .cta-docs-category-badge {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		height: 24px;
		padding: 0 8px;
		border-radius: 999px;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #475569;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: 0.3px;
		text-transform: uppercase;
		max-width: 145px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	#cta-docs-modal .cta-docs-category-badge .dashicons {
		font-size: 14px;
		width: 14px;
		height: 14px;
		color: #64748b;
	}

	#cta-docs-modal .cta-docs-page-nav {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	#cta-docs-modal .cta-docs-page-nav-btn {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		height: 24px;
		padding: 0 8px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #ffffff;
		color: #475569;
		font-size: 10px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.15s ease;
		max-width: 150px;
	}

	#cta-docs-modal .cta-docs-page-nav-btn:hover:not(:disabled) {
		border-color: #cbd5e1;
		background: #f8fafc;
		color: #334155;
	}

	#cta-docs-modal .cta-docs-page-nav-btn:disabled {
		opacity: 0.45;
		cursor: not-allowed;
	}

	#cta-docs-modal .cta-docs-page-nav-btn .dashicons {
		font-size: 14px;
		width: 14px;
		height: 14px;
	}

	#cta-docs-modal .cta-docs-page-nav-btn .cta-docs-nav-label {
		display: inline-block;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 102px;
	}

	/* Normalize docs badge sizing across menu/card/feature-list contexts */
	#cta-docs-modal .cta-docs-card-badge,
	#cta-docs-modal .cta-docs-feature-list-badge,
	#cta-docs-modal .cta-docs-accordion-badge {
		font-size: 10px;
		padding: 2px 8px;
		line-height: 1;
		letter-spacing: 0.4px;
		margin-left: 6px;
		vertical-align: middle;
	}

	#cta-docs-modal .cta-docs-feature-group-title .cta-docs-feature-list-badge,
	#cta-docs-modal .cta-docs-mode-card-body strong .cta-docs-card-badge {
		margin-left: 0;
	}

	#cta-docs-modal .cta-docs-page-static-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		border-bottom: 2px solid #e2e8f0;
		padding: 20px 0 15px 0;
		margin-bottom: 15px;
		position: sticky;
		top: 0;
		background: #fff;
		z-index: 5;
	}

	#cta-docs-modal .cta-docs-section-title-wrapper .cta-docs-header-tools,
	#cta-docs-modal .cta-docs-page-static-header .cta-docs-header-tools {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex: 1;
		min-width: 0;
		gap: 8px;
	}

	#cta-docs-modal .cta-modal-maximize {
		position: absolute;
		top: 50%;
		right: 70px;
		transform: translateY(-50%);
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 1px solid rgba(255, 255, 255, 0.32);
		background: rgba(255, 255, 255, 0.08);
		border-radius: 8px;
		color: #ffffff;
		cursor: pointer;
		transition: background 0.15s ease, transform 0.15s ease;
	}

	#cta-docs-modal .cta-modal-maximize:hover {
		background: rgba(255, 255, 255, 0.22);
		transform: translateY(calc(-50% - 1px));
	}

	#cta-docs-modal .cta-modal-maximize .dashicons {
		font-size: 17px;
		width: 17px;
		height: 17px;
	}

	#cta-docs-modal .cta-modal-header {
		position: relative;
	}

	#cta-docs-modal .cta-modal-close {
		top: 50% !important;
		right: 12px !important;
		transform: translateY(-50%) !important;
	}

	#cta-docs-modal.is-maximized .cta-modal-content {
		width: 100vw !important;
		max-width: 100vw !important;
		height: 100vh !important;
		max-height: 100vh !important;
		margin: 0 !important;
		border-radius: 0 !important;
	}

	#cta-docs-modal.is-maximized .cta-modal-header {
		padding-top: 10px !important;
		padding-bottom: 10px !important;
		min-height: 48px;
	}

	#cta-docs-modal.is-maximized .cta-modal-header h2 {
		font-size: 21px;
		line-height: 1.2;
	}

	#cta-docs-modal.is-maximized .cta-modal-close {
		top: 50% !important;
		right: 12px !important;
		transform: translateY(-50%) !important;
	}

	#cta-docs-modal.is-maximized .cta-modal-maximize {
		top: 50%;
		right: 62px;
		transform: translateY(-50%);
	}

	#cta-docs-modal .cta-docs-header-tools .cta-docs-collapsed-search {
		display: none;
		position: static !important;
		top: auto !important;
		right: auto !important;
		transform: none !important;
		margin: 0 !important;
		width: 28px;
		height: 28px;
		align-items: center;
		justify-content: center;
	}

	@media (max-width: 680px) {
		#cta-docs-modal .cta-docs-header-tools {
			width: 100%;
			gap: 6px;
			flex-wrap: nowrap;
		}

		#cta-docs-modal .cta-docs-category-badge {
			max-width: 108px;
			padding: 0 8px;
			font-size: 10px;
		}

		#cta-docs-modal .cta-docs-page-nav {
			flex: 1;
			justify-content: flex-end;
		}

		#cta-docs-modal .cta-docs-page-nav-btn {
			padding: 0 7px;
			max-width: 102px;
		}

		#cta-docs-modal .cta-docs-page-nav-btn .cta-docs-nav-label {
			max-width: 58px;
		}

		#cta-docs-modal .cta-modal-maximize {
			top: 50%;
			right: 58px;
			transform: translateY(-50%);
			width: 28px;
			height: 28px;
			border-radius: 7px;
		}
	}

	@media (max-width: 1180px) {
		#cta-docs-modal .cta-docs-page-nav-btn .cta-docs-nav-label {
			max-width: 70px;
		}
	}
</style>

<script>
// Simple intra-modal navigation: clicking elements with data-docs-page triggers the matching submenu link.
(function() {
  const docsModalSelector = '#cta-docs-modal';

  function closeDocsModal() {
    if (window.ctaModalAPI && typeof window.ctaModalAPI.close === 'function') {
      window.ctaModalAPI.close(docsModalSelector);
      return;
    }
    const docsModal = document.querySelector(docsModalSelector);
    if (docsModal) {
      docsModal.style.display = 'none';
      document.body.classList.remove('cta-modal-open');
    }
  }

  function openTargetModal(target, triggerEl) {
    if (!target || target === '#cta-docs-modal') return;

    if (window.ctaModalAPI && typeof window.ctaModalAPI.open === 'function') {
      window.ctaModalAPI.open(target, { trigger: triggerEl });
      return;
    }

    const modalEl = document.querySelector(target);
    if (modalEl) {
      modalEl.style.display = 'block';
      document.body.classList.add('cta-modal-open');
    }
  }

  function getDocsModal() {
    return document.querySelector(docsModalSelector);
  }

  function ensureMaximizeButton() {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }
    const header = docsModal.querySelector('.cta-modal-header');
    const closeBtn = docsModal.querySelector('.cta-modal-close');
    if (!header || !closeBtn || header.querySelector('.cta-modal-maximize')) {
      return;
    }

    const maximizeBtn = document.createElement('button');
    maximizeBtn.type = 'button';
    maximizeBtn.className = 'cta-modal-maximize';
    maximizeBtn.setAttribute('aria-label', 'Maximize docs modal');
    maximizeBtn.setAttribute('title', 'Maximize');
    maximizeBtn.innerHTML = '<span class="dashicons dashicons-editor-expand" aria-hidden="true"></span>';

    header.insertBefore(maximizeBtn, closeBtn);
  }

  function syncMaximizeButtonState() {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }
    const maximizeBtn = docsModal.querySelector('.cta-modal-maximize');
    if (!maximizeBtn) {
      return;
    }

    const isMaximized = docsModal.classList.contains('is-maximized');
    const icon = maximizeBtn.querySelector('.dashicons');
    if (icon) {
      icon.className = 'dashicons ' + (isMaximized ? 'dashicons-editor-contract minimize' : 'dashicons-editor-expand');
    }
    maximizeBtn.setAttribute('aria-label', isMaximized ? 'Restore docs modal size' : 'Maximize docs modal');
    maximizeBtn.setAttribute('title', isMaximized ? 'Restore' : 'Maximize');
  }

  function setMaximizedState(enabled) {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }
    docsModal.classList.toggle('is-maximized', !!enabled);
    syncMaximizeButtonState();
  }

  function bindMaximizeEvents() {
    const docsModal = getDocsModal();
    if (!docsModal || docsModal.__ctaMaximizeBound) {
      return;
    }

    function isDocsModalEventTarget($modal, modalId) {
      if (modalId === 'cta-docs-modal') {
        return true;
      }

      if (!$modal) {
        return false;
      }

      if (window.jQuery && $modal.jquery) {
        return $modal.is(docsModalSelector);
      }

      if ($modal.nodeType === 1) {
        return $modal.matches(docsModalSelector);
      }

      if (typeof $modal.length === 'number' && $modal.length > 0 && $modal[0] && $modal[0].matches) {
        return $modal[0].matches(docsModalSelector);
      }

      return false;
    }

    docsModal.addEventListener('click', function(e) {
      const maximizeBtn = e.target.closest('.cta-modal-maximize');
      if (!maximizeBtn) {
        return;
      }
      e.preventDefault();
      setMaximizedState(!docsModal.classList.contains('is-maximized'));
    });

    docsModal.addEventListener('click', function(e) {
      if (!e.target.closest('[data-docs-sidebar-toggle]')) {
        return;
      }
      setTimeout(function() {
        renderHeaderNavigation(getCurrentPageId());
      }, 20);
    });

    document.addEventListener('keydown', function(e) {
      if (e.key !== 'Escape') {
        return;
      }
      const modalVisible = docsModal.style.display !== 'none' && docsModal.offsetParent !== null;
      if (!modalVisible) {
        return;
      }
      if (docsModal.classList.contains('is-maximized')) {
        setMaximizedState(false);
      }
    });

    docsModal.addEventListener('click', function(e) {
      if (e.target.closest('.cta-modal-close') || e.target.classList.contains('cta-modal-overlay')) {
        setMaximizedState(false);
      }
    });

    document.addEventListener('ctaModalClosed', function(e, $modal, modalId) {
      if (isDocsModalEventTarget($modal, modalId)) {
        setMaximizedState(false);
      }
    });

    document.addEventListener('ctaModalOpened', function(e, $modal, modalId) {
      if (isDocsModalEventTarget($modal, modalId)) {
        syncMaximizeButtonState();
      }
    });

    docsModal.__ctaMaximizeBound = true;
  }

  function getCleanLabel(rawText) {
    return (rawText || '')
      .replace(/\s*(PRO|Soon|Coming Soon)\s*/gi, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function getDashiconClass(iconEl, fallback) {
    const classes = (iconEl && iconEl.className ? iconEl.className : '').split(/\s+/);
    const icon = classes.find((className) => className.indexOf('dashicons-') === 0 && className !== 'dashicons');
    return icon ? icon : fallback;
  }

  function shouldHideNavLink(link) {
    if (!link) {
      return true;
    }
    if (link.closest('.is-hidden') || link.closest('.is-plan-filtered')) {
      return true;
    }
    const pageId = link.getAttribute('data-docs-page');
    return !pageId || !document.querySelector(docsModalSelector + ' [data-docs-page-content="' + pageId + '"]');
  }

  function getOrderedNavEntries() {
    const navLinks = document.querySelectorAll(
      docsModalSelector + ' .cta-docs-sidebar .cta-docs-welcome-link[data-docs-page], ' +
      docsModalSelector + ' .cta-docs-sidebar .cta-docs-submenu-link[data-docs-page]'
    );
    const seen = new Set();
    const entries = [];

    navLinks.forEach((link) => {
      if (shouldHideNavLink(link)) {
        return;
      }
      const pageId = link.getAttribute('data-docs-page');
      if (!pageId || seen.has(pageId)) {
        return;
      }
      seen.add(pageId);
      entries.push({
        pageId,
        title: getCleanLabel(link.textContent) || pageId,
        link
      });
    });

    return entries;
  }

  function getCurrentPageId() {
    const activePage = document.querySelector(docsModalSelector + ' [data-docs-page-content].is-active');
    return activePage ? activePage.getAttribute('data-docs-page-content') : 'welcome';
  }

  function getSidebarLink(pageId) {
    if (!pageId) {
      return null;
    }
    return document.querySelector(docsModalSelector + ' .cta-docs-sidebar [data-docs-page="' + pageId + '"]');
  }

  function getCategoryMeta(link, pageId) {
    if (pageId === 'welcome') {
      const welcomeLink = document.querySelector(docsModalSelector + ' .cta-docs-welcome-link[data-docs-page="welcome"]');
      const welcomeIcon = welcomeLink ? welcomeLink.querySelector('.dashicons') : null;
      return {
        title: 'Welcome',
        iconClass: getDashiconClass(welcomeIcon, 'dashicons-book-alt')
      };
    }

    if (!link) {
      return {
        title: 'Documentation',
        iconClass: 'dashicons-book-alt'
      };
    }

    const accordionItem = link.closest('.cta-docs-accordion-item');
    if (!accordionItem) {
      return {
        title: 'Documentation',
        iconClass: 'dashicons-book-alt'
      };
    }

    const triggerText = accordionItem.querySelector('.cta-docs-accordion-trigger-text');
    const iconEl = triggerText ? triggerText.querySelector('.dashicons') : null;
    const title = triggerText ? getCleanLabel(triggerText.textContent) : 'Documentation';

    return {
      title: title || 'Documentation',
      iconClass: getDashiconClass(iconEl, 'dashicons-book-alt')
    };
  }

  function getPageEmoji(pageId) {
    const exact = {
      'category-js-hooks-overview': '',
      'category-php-hooks-overview': '',
      'hooks-overview': '🧠',
      'hooks-event-listeners': '👂',
      'hooks-ctaprohooks': '🛟',
      'php-hooks-overview': '🧬',
      'php-hooks-helpers': '🧱',
      'settings-overview': '⚙️',
      'settings-business-hours': '🕘',
      'settings-analytics': '📊',
      'settings-custom-css': '🎨',
      'settings-performance': '⚡',
      'settings-custom-icons': '🖼️',
      'settings-data-management': '🗃️',
      'tools-overview': '🧰',
      'tools-export': '📤',
      'tools-import': '📥',
      'tools-demo': '🧪',
      'tools-reset': '🧹',
      'tools-debug': '🐞',
      'tools-clear-caches': '🧼'
    };

    if (exact[pageId]) {
      return exact[pageId];
    }

    if (pageId.indexOf('hook-category-js-') === 0) {
      return '🪝';
    }
    if (pageId.indexOf('hook-category-php-') === 0) {
      return '🧩';
    }
    if (pageId.indexOf('php-hook-') === 0) {
      return '🧬';
    }
    if (pageId.indexOf('cta-') === 0) {
      return '⚡';
    }
    if (pageId.indexOf('settings-') === 0) {
      return '⚙️';
    }
    if (pageId.indexOf('tools-') === 0) {
      return '🧰';
    }

    return '';
  }

  function isProPageId(pageId, pageEl) {
    if (!pageId) {
      return false;
    }

    const explicitProPages = new Set([
      'category-js-hooks-overview',
      'category-php-hooks-overview',
      'hooks-overview',
      'hooks-event-listeners',
      'hooks-ctaprohooks',
      'php-hooks-overview',
      'php-hooks-helpers',
      'settings-business-hours',
      'settings-custom-css',
      'settings-performance',
      'settings-custom-icons'
    ]);

    if (explicitProPages.has(pageId)) {
      return true;
    }

    if (
      pageId.indexOf('hook-category-js-') === 0 ||
      pageId.indexOf('hook-category-php-') === 0 ||
      pageId.indexOf('php-hook-') === 0 ||
      pageId.indexOf('cta-') === 0
    ) {
      return true;
    }

    if (pageEl && pageEl.getAttribute('data-docs-plan') === 'pro') {
      return true;
    }

    const link = getSidebarLink(pageId);
    if (link && link.closest('[data-docs-plan="pro"]')) {
      return true;
    }

    return false;
  }

  function ensureMenuPlanBadges() {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }

    const links = docsModal.querySelectorAll('.cta-docs-submenu-link[data-docs-page], .cta-docs-welcome-link[data-docs-page]');
    links.forEach(function(link) {
      const pageId = link.getAttribute('data-docs-page');
      if (!isProPageId(pageId)) {
        return;
      }
      if (link.querySelector('.cta-docs-pro-badge')) {
        return;
      }

      const badge = document.createElement('span');
      badge.className = 'cta-docs-pro-badge cta-docs-accordion-badge cta-docs-menu-badge';
      badge.textContent = 'PRO';
      link.appendChild(badge);
    });
  }

  function ensureTitlePlanBadge(wrapper, pageId, pageEl) {
    if (!wrapper || !pageId) {
      return;
    }

    const existingManualBadge = wrapper.querySelector('.cta-docs-pro-badge.cta-docs-page-title-badge:not(.cta-docs-auto-plan-badge)');
    let autoBadge = wrapper.querySelector('.cta-docs-pro-badge.cta-docs-page-title-badge.cta-docs-auto-plan-badge');
    const isPro = isProPageId(pageId, pageEl);

    if (!isPro) {
      if (autoBadge) {
        autoBadge.remove();
      }
      return;
    }

    if (existingManualBadge) {
      return;
    }

    if (!autoBadge) {
      autoBadge = document.createElement('span');
      autoBadge.className = 'cta-docs-pro-badge cta-docs-page-title-badge cta-docs-auto-plan-badge';
      autoBadge.textContent = 'PRO';
      const title = wrapper.querySelector('.cta-docs-section-title');
      if (title && title.nextSibling) {
        wrapper.insertBefore(autoBadge, title.nextSibling);
      } else {
        wrapper.appendChild(autoBadge);
      }
    }
  }

  function shouldSuppressCategoryEmoji(pageId) {
    return pageId === 'category-js-hooks-overview' || pageId === 'category-php-hooks-overview';
  }

  function ensurePageTitleWrapper(pageEl, pageId) {
    const section = pageEl.querySelector('.cta-docs-section');
    if (!section) {
      return null;
    }

    let wrapper = section.querySelector('.cta-docs-section-title-wrapper');
    let titleEl = section.querySelector('.cta-docs-section-title') || section.querySelector('h2');
    if (!titleEl) {
      return null;
    }

    if (!titleEl.classList.contains('cta-docs-section-title')) {
      titleEl.classList.add('cta-docs-section-title');
    }

    if (!wrapper) {
      wrapper = document.createElement('div');
      wrapper.className = 'cta-docs-section-title-wrapper cta-docs-section-title-wrapper--auto';
      section.insertBefore(wrapper, titleEl);
      wrapper.appendChild(titleEl);
    }

    const desiredEmoji = getPageEmoji(pageId);
    let iconEl = wrapper.querySelector('.cta-docs-title-icon');
    if (shouldSuppressCategoryEmoji(pageId)) {
      if (iconEl) {
        iconEl.remove();
      }
      return wrapper;
    }

    if (desiredEmoji) {
      if (!iconEl) {
        iconEl = document.createElement('span');
        iconEl.className = 'cta-docs-title-icon';
        wrapper.insertBefore(iconEl, wrapper.firstChild);
      }
      iconEl.textContent = desiredEmoji;
    } else if (iconEl && wrapper.classList.contains('cta-docs-section-title-wrapper--auto')) {
      iconEl.remove();
    }

    ensureTitlePlanBadge(wrapper, pageId, pageEl);

    return wrapper;
  }

  function ensureStaticHeader(pageEl) {
    let staticHeader = pageEl.querySelector('.cta-docs-page-static-header');
    if (!staticHeader) {
      staticHeader = document.createElement('div');
      staticHeader.className = 'cta-docs-page-static-header';
      pageEl.insertBefore(staticHeader, pageEl.firstChild);
    }
    return staticHeader;
  }

  function ensureHeaderTools(hostEl) {
    let tools = hostEl.querySelector('.cta-docs-header-tools');
    if (!tools) {
      tools = document.createElement('div');
      tools.className = 'cta-docs-header-tools';
      tools.innerHTML = '' +
        '<span class="cta-docs-page-summary">' +
          '<span class="cta-docs-sticky-title">Welcome</span>' +
          '<span class="cta-docs-sticky-description" style="display:none;"></span>' +
        '</span>' +
        '<span class="cta-docs-category-badge">' +
          '<span class="dashicons dashicons-book-alt" aria-hidden="true"></span>' +
          '<span class="cta-docs-category-text">Welcome</span>' +
        '</span>' +
        '<span class="cta-docs-page-nav">' +
          '<button type="button" class="cta-docs-page-nav-btn is-prev" data-docs-nav-direction="prev">' +
            '<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>' +
            '<span class="cta-docs-nav-label">Previous</span>' +
          '</button>' +
          '<button type="button" class="cta-docs-page-nav-btn is-next" data-docs-nav-direction="next">' +
            '<span class="cta-docs-nav-label">Next</span>' +
            '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>' +
          '</button>' +
        '</span>';
      hostEl.appendChild(tools);
    }
    return tools;
  }

  function getStickyPageTitle(pageEl, pageId, fallbackTitle) {
    const titleEl = pageEl.querySelector('.cta-docs-section-title') || pageEl.querySelector('.cta-docs-section h2');
    const title = titleEl ? titleEl.textContent.trim() : '';
    if (title) {
      return title;
    }
    if (pageId === 'welcome') {
      return 'Welcome';
    }
    return fallbackTitle || 'Documentation';
  }

  function getStickyPageDescription(pageEl) {
    const explicitDesc = pageEl.querySelector('.cta-docs-section-description');
    if (explicitDesc && explicitDesc.textContent.trim()) {
      return explicitDesc.textContent.trim();
    }

    const section = pageEl.querySelector('.cta-docs-section');
    if (!section) {
      return '';
    }

    const children = Array.from(section.children);
    for (const child of children) {
      if (child.tagName && child.tagName.toLowerCase() === 'p') {
        const text = child.textContent.trim();
        if (text) {
          return text;
        }
      }
    }
    return '';
  }

  function syncStickySourceVisibility(pageEl) {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }

    docsModal.querySelectorAll('.cta-docs-sticky-source-hidden').forEach(function(el) {
      el.classList.remove('cta-docs-sticky-source-hidden');
    });

    const titleWrapper = pageEl.querySelector('.cta-docs-section-title-wrapper');
    if (titleWrapper) {
      titleWrapper.querySelectorAll('.cta-docs-title-icon, .cta-docs-section-title, .cta-docs-page-title-badge').forEach(function(el) {
        el.classList.add('cta-docs-sticky-source-hidden');
      });
    }

    const descEl = pageEl.querySelector('.cta-docs-section-description');
    if (descEl) {
      descEl.classList.add('cta-docs-sticky-source-hidden');
    }
  }

  function renderHeaderNavigation(pageId) {
    const docsModal = getDocsModal();
    if (!docsModal) {
      return;
    }

    const targetPage = docsModal.querySelector('[data-docs-page-content="' + pageId + '"]');
    if (!targetPage) {
      return;
    }

    const wrapper = ensurePageTitleWrapper(targetPage, pageId) || targetPage.querySelector('.cta-docs-section-title-wrapper');
    const host = wrapper || ensureStaticHeader(targetPage);
    const tools = ensureHeaderTools(host);
    const collapsedSearchBtn =
      targetPage.querySelector('.cta-docs-collapsed-search') ||
      docsModal.querySelector('.cta-docs-collapsed-search');

    if (!host.contains(tools)) {
      host.appendChild(tools);
    }
    const layout = docsModal.querySelector('.cta-docs-layout');
    const isSidebarCollapsed = !!(layout && layout.classList.contains('is-sidebar-collapsed'));
    if (collapsedSearchBtn) {
      if (isSidebarCollapsed) {
        if (!tools.contains(collapsedSearchBtn)) {
          collapsedSearchBtn.classList.remove('is-floating');
          tools.insertBefore(collapsedSearchBtn, tools.firstChild);
        }
        collapsedSearchBtn.style.display = 'inline-flex';
      } else {
        collapsedSearchBtn.style.display = 'none';
      }
    }

    const entries = getOrderedNavEntries();
    if (!entries.length) {
      tools.style.display = 'none';
      return;
    }
    tools.style.display = 'inline-flex';

    const currentIndex = Math.max(
      entries.findIndex((entry) => entry.pageId === pageId),
      0
    );
    const currentEntry = entries[currentIndex];
    const welcomeIndex = entries.findIndex((entry) => entry.pageId === 'welcome');

    const categoryMeta = getCategoryMeta(currentEntry.link, pageId);
    const badgeIcon = tools.querySelector('.cta-docs-category-badge .dashicons');
    const badgeText = tools.querySelector('.cta-docs-category-text');
    const stickyTitle = tools.querySelector('.cta-docs-sticky-title');
    const stickyDescription = tools.querySelector('.cta-docs-sticky-description');
    if (badgeIcon) {
      badgeIcon.className = 'dashicons ' + categoryMeta.iconClass;
    }
    if (badgeText) {
      badgeText.textContent = categoryMeta.title;
    }
    if (stickyTitle) {
      stickyTitle.textContent = getStickyPageTitle(targetPage, pageId, categoryMeta.title);
    }
    if (stickyDescription) {
      const descText = getStickyPageDescription(targetPage);
      stickyDescription.textContent = descText;
      stickyDescription.style.display = descText ? '' : 'none';
    }
    syncStickySourceVisibility(targetPage);

    const prevButton = tools.querySelector('.cta-docs-page-nav-btn.is-prev');
    const nextButton = tools.querySelector('.cta-docs-page-nav-btn.is-next');
    const prevLabel = prevButton ? prevButton.querySelector('.cta-docs-nav-label') : null;
    const nextLabel = nextButton ? nextButton.querySelector('.cta-docs-nav-label') : null;

    let prevEntry = null;
    if (pageId !== 'welcome' && entries.length > 1) {
      const prevIndex = (currentIndex - 1 + entries.length) % entries.length;
      prevEntry = entries[prevIndex];
    }

    let nextEntry = null;
    if (entries.length > 1) {
      if (currentIndex === entries.length - 1 && welcomeIndex >= 0) {
        nextEntry = entries[welcomeIndex];
      } else {
        nextEntry = entries[(currentIndex + 1) % entries.length];
      }
    }

    if (prevButton) {
      if (!prevEntry) {
        prevButton.disabled = true;
        prevButton.removeAttribute('data-docs-nav-target');
      } else {
        prevButton.disabled = false;
        prevButton.setAttribute('data-docs-nav-target', prevEntry.pageId);
      }
    }
    if (nextButton) {
      if (!nextEntry) {
        nextButton.disabled = true;
        nextButton.removeAttribute('data-docs-nav-target');
      } else {
        nextButton.disabled = false;
        nextButton.setAttribute('data-docs-nav-target', nextEntry.pageId);
      }
    }
    if (prevLabel) {
      prevLabel.textContent = prevEntry ? prevEntry.title : 'Welcome';
    }
    if (nextLabel) {
      nextLabel.textContent = nextEntry ? nextEntry.title : currentEntry.title;
    }
  }

  function navigateToPage(pageId) {
    if (!pageId) {
      return;
    }

    const navBtn = getSidebarLink(pageId);
    if (!navBtn) {
      return;
    }

    if (window.CTADocsModal && typeof window.CTADocsModal.showPage === 'function' && typeof window.CTADocsModal.setActiveLink === 'function' && window.jQuery) {
      if (typeof window.CTADocsModal.collapseAllAccordions === 'function') {
        window.CTADocsModal.collapseAllAccordions();
      }
      window.CTADocsModal.showPage(pageId);
      window.CTADocsModal.setActiveLink(window.jQuery(navBtn));
      return;
    }

    navBtn.click();
  }

  function installHeaderNavigationEnhancements() {
    if (!window.CTADocsModal || window.CTADocsModal.__ctaHeaderNavInstalled) {
      return !!window.CTADocsModal;
    }

    const originalShowPage = window.CTADocsModal.showPage ? window.CTADocsModal.showPage.bind(window.CTADocsModal) : null;
    if (originalShowPage) {
      window.CTADocsModal.showPage = function(pageId) {
        originalShowPage(pageId);
        renderHeaderNavigation(pageId || getCurrentPageId());
      };
    }

    const originalApplyDocFilters = window.CTADocsModal.applyDocFilters ? window.CTADocsModal.applyDocFilters.bind(window.CTADocsModal) : null;
    if (originalApplyDocFilters) {
      window.CTADocsModal.applyDocFilters = function() {
        originalApplyDocFilters();
        renderHeaderNavigation(getCurrentPageId());
      };
    }

    const docsModal = getDocsModal();
    if (docsModal) {
      docsModal.addEventListener('click', function(e) {
        const navButton = e.target.closest('.cta-docs-page-nav-btn[data-docs-nav-target]');
        if (!navButton || navButton.disabled) {
          return;
        }
        e.preventDefault();
        navigateToPage(navButton.getAttribute('data-docs-nav-target'));
      });
    }

    window.CTADocsModal.__ctaHeaderNavInstalled = true;
    renderHeaderNavigation(getCurrentPageId());
    return true;
  }

  function waitForDocsModalEnhancements(attempts) {
    ensureMaximizeButton();
    bindMaximizeEvents();
    syncMaximizeButtonState();
    ensureMenuPlanBadges();

    if (installHeaderNavigationEnhancements()) {
      return;
    }
    if (attempts <= 0) {
      return;
    }
    setTimeout(function() {
      waitForDocsModalEnhancements(attempts - 1);
    }, 120);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      waitForDocsModalEnhancements(50);
    });
  } else {
    waitForDocsModalEnhancements(50);
  }

  document.addEventListener('click', function(e) {
    const modalShortcut = e.target.closest(docsModalSelector + ' [data-open-modal]');
    if (modalShortcut) {
      const targetModal = modalShortcut.getAttribute('data-open-modal');
      if (targetModal && targetModal !== docsModalSelector) {
        e.preventDefault();
        openTargetModal(targetModal, modalShortcut);
        return;
      }
    }

    const notificationsShortcut = e.target.closest('[data-cta-open-notifications]');
    if (notificationsShortcut) {
      e.preventDefault();
      closeDocsModal();
      setTimeout(function() {
        const notificationsButton = document.querySelector('#cta-notification-button') || document.querySelector('.cta-support-toolbar .cta-notification-button');
        if (notificationsButton) {
          notificationsButton.click();
        }
      }, 180);
      return;
    }

    const link = e.target.closest('.cta-docs-link[data-docs-page]');
    if (!link) return;
    e.preventDefault();
    const target = link.getAttribute('data-docs-page');
    const navBtn = getSidebarLink(target);
    if (!navBtn) return;

    navigateToPage(target);
  });

  // Integration Activate button handler — close docs modal and navigate to integrations page
  document.addEventListener('click', function(e) {
    const activateBtn = e.target.closest('[data-activate-integration]');
    if (!activateBtn) return;
    e.preventDefault();

    const integrationId = activateBtn.dataset.activateIntegration;
    if (!integrationId) return;

    // Close docs modal
    if (window.ctaModalAPI && typeof window.ctaModalAPI.close === 'function') {
      window.ctaModalAPI.close(docsModalSelector);
    }

    // Navigate to integrations page with activate param
    window.location.href = activateBtn.href;
  });
})();
</script>
