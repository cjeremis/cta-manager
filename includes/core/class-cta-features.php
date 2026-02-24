<?php
/**
 * Features Registry Handler
 *
 * Handles feature registry definitions and feature metadata operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Features {

	/**
	 * Feature plan types
	 */
	const PLAN_FREE = 'free';
	const PLAN_PRO  = 'pro';

	/**
	 * Singleton instance.
	 *
	 * @var CTA_Features|null
	 */
	private static $instance = null;

	/**
	 * Cached features array.
	 *
	 * @var array|null
	 */
	private static $features_cache = null;

	/**
	 * Cached integrations array.
	 *
	 * @var array|null
	 */
	private static $integrations_cache = null;

	/**
	 * Cached categories array.
	 *
	 * @var array|null
	 */
	private static $categories_cache = null;

	/**
	 * Cached data file contents.
	 *
	 * @var array|null
	 */
	private static $data_cache = null;

	/**
	 * Cached labels array.
	 *
	 * @var array|null
	 */
	private static $labels_cache = null;

	/**
	 * Cached integrations meta.
	 *
	 * @var array|null
	 */
	private static $integrations_meta_cache = null;

	/**
	 * Load data file.
	 *
	 * @return array
	 */
	private static function load_data_file(): array {
		if ( null !== self::$data_cache ) {
			return self::$data_cache;
		}

		$file = CTA_PLUGIN_DIR . 'data/features.php';
		if ( file_exists( $file ) ) {
			$data = include $file;
			if ( is_array( $data ) ) {
				self::$data_cache = $data;
				return self::$data_cache;
			}
		}
		return [];
	}

	/**
	 * Normalize features to ensure required keys exist.
	 *
	 * @param array $all_features
	 * @param string $prefix_for_docs
	 * @return array
	 */
	private static function normalize_features( array $all_features, string $prefix_for_docs = 'feature-' ): array {
		$id_map = [
			__( 'Shortcode Usage', 'cta-manager' )          => 'shortcode-usage',
			__( 'Phone Call CTA', 'cta-manager' )           => 'phone',
			__( 'Link CTA', 'cta-manager' )                 => 'link',
			__( 'Email CTA', 'cta-manager' )                => 'email',
			__( 'Popup CTA', 'cta-manager' )                => 'popup',
			__( 'Slide-in CTA', 'cta-manager' )             => 'slide-in',
			__( 'Custom CTA Type', 'cta-manager' )          => 'custom',
			__( 'Button Layout', 'cta-manager' )            => 'button-layout',
			__( 'CTA Cards', 'cta-manager' )                => 'cta-cards',
			__( 'Customizable Cards', 'cta-manager' )       => 'customizable-cards',
			__( 'Advanced Styling', 'cta-manager' )         => 'advanced-styling',
			__( 'Button & Icon Animations', 'cta-manager' ) => 'animations',
			__( 'Custom CSS Editor', 'cta-manager' )        => 'custom-css',
			__( 'Rich Text Editing', 'cta-manager' )        => 'rich-text',
			__( 'Shortcode Support', 'cta-manager' )        => 'shortcodes',
			__( 'Basic Scheduling', 'cta-manager' )         => 'basic-scheduling',
			__( 'Business Hours', 'cta-manager' )           => 'business-hours',
			__( 'Device Detection', 'cta-manager' )         => 'device-detection',
			__( 'URL Blacklist/Whitelist', 'cta-manager' )  => 'blacklist-whitelist',
			__( 'User Agent Targeting', 'cta-manager' )     => 'user-agent',
			__( 'Screen Size Rules', 'cta-manager' )        => 'screen-size',
			__( 'Orientation Targeting', 'cta-manager' )    => 'orientation',
			__( 'A/B Testing', 'cta-manager' )              => 'ab-testing',
			__( 'Analytics (1 Day)', 'cta-manager' )        => 'analytics-7days',
			__( 'Analytics (7 Days)', 'cta-manager' )       => 'analytics-7days',
			__( 'Extended Analytics (90 Days)', 'cta-manager' ) => 'analytics-90days',
			__( 'Advanced Analytics', 'cta-manager' ) => 'analytics-advanced',
			__( 'Import & Export', 'cta-manager' )          => 'import-export',
			__( 'Analytics Export', 'cta-manager' )         => 'analytics-export',
			__( 'Custom Icons', 'cta-manager' )             => 'custom-icons',
			__( 'Advanced Embedding', 'cta-manager' )       => 'advanced-embedding',
		];

		foreach ( $all_features as $category => &$features ) {
			foreach ( $features as &$feature ) {
				if ( empty( $feature['id'] ) ) {
					$title_key     = $feature['title'] ?? '';
					$feature['id'] = $id_map[ $title_key ] ?? sanitize_title( $title_key ?: 'feature' );
				}
				if ( ! isset( $feature['plan'] ) ) {
					$feature['plan'] = self::PLAN_FREE;
				}
				if ( ! isset( $feature['implemented'] ) ) {
					$feature['implemented'] = true;
				}
				if ( empty( $feature['docs_page'] ) ) {
					$feature['docs_page'] = $prefix_for_docs . $feature['id'];
				}
			}
		}
		return $all_features;
	}

	/**
	 * Normalize integrations to ensure required keys exist.
	 *
	 * @param array $all_integrations
	 * @return array
	 */
	private static function normalize_integrations( array $all_integrations ): array {
		$id_map = [
			__( 'Google Analytics 4', 'cta-manager' )   => 'ga4',
			__( 'Google Tag Manager', 'cta-manager' )   => 'gtm',
			__( 'PostHog', 'cta-manager' )              => 'posthog',
			__( 'New Relic', 'cta-manager' )            => 'newrelic',
			__( 'Google Search Console', 'cta-manager' ) => 'gsc',
			__( 'VWO', 'cta-manager' )                  => 'vwo',
			__( 'Slack', 'cta-manager' )                => 'slack',
			__( 'Gmail', 'cta-manager' )                => 'gmail',
			__( 'Zoom', 'cta-manager' )                 => 'zoom',
			__( 'Jira', 'cta-manager' )                 => 'jira',
			__( 'Confluence', 'cta-manager' )           => 'confluence',
			__( 'GitHub', 'cta-manager' )               => 'github',
			__( 'Google Calendar', 'cta-manager' )      => 'gcal',
			__( 'Google reCAPTCHA', 'cta-manager' )     => 'recaptcha',
			__( 'Cloudflare', 'cta-manager' )           => 'cloudflare',
			__( 'Figma', 'cta-manager' )                => 'figma',
		];

		foreach ( $all_integrations as $category => &$items ) {
			foreach ( $items as &$integration ) {
				if ( empty( $integration['id'] ) ) {
					$title_key         = $integration['title'] ?? '';
					$integration['id'] = $id_map[ $title_key ] ?? sanitize_title( $title_key ?: 'integration' );
				}
				if ( ! isset( $integration['implemented'] ) ) {
					$integration['implemented'] = false;
				}
				if ( empty( $integration['docs_page'] ) ) {
					$integration['docs_page'] = 'integration-' . $integration['id'];
				}
			}
		}
		return $all_integrations;
	}

	/**
	 * Get singleton instance.
	 *
	 * @return CTA_Features
	 */
	public static function get_instance(): CTA_Features {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get all features grouped by category
	 *
	 * @return array Features grouped by category name
	 */
	public static function get_all_features(): array {
		if ( null !== self::$features_cache ) {
			return self::$features_cache;
		}

		$data = self::load_data_file();
		if ( ! empty( $data['all_features'] ) ) {
			self::$features_cache = self::normalize_features( $data['all_features'] );
			return self::$features_cache;
		}

		$fallback_features = [
			__( 'CTA Types', 'cta-manager' ) => [
				// Free - Available
				[
					'id'          => 'phone',
					'icon'        => '📞',
					'title'       => __( 'Phone Call CTA', 'cta-manager' ),
					'description' => __( 'Create click-to-call buttons for instant phone connections', 'cta-manager' ),
					'features'    => [
						__( 'One-tap mobile calling', 'cta-manager' ),
						__( 'Custom phone formatting', 'cta-manager' ),
						__( 'Call tracking ready', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-phone',
				],
				[
					'id'          => 'link',
					'icon'        => '🔗',
					'title'       => __( 'Link CTA', 'cta-manager' ),
					'description' => __( 'Direct users to any URL with customizable link buttons', 'cta-manager' ),
					'features'    => [
						__( 'Internal & external links', 'cta-manager' ),
						__( 'New tab option', 'cta-manager' ),
						__( 'Custom button text', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-link',
				],
				[
					'id'          => 'email',
					'icon'        => '✉️',
					'title'       => __( 'Email CTA', 'cta-manager' ),
					'description' => __( 'Enable one-click email composition with mailto links', 'cta-manager' ),
					'features'    => [
						__( 'Pre-filled subject lines', 'cta-manager' ),
						__( 'Default body text', 'cta-manager' ),
						__( 'Multiple recipients', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-email',
				],
				// Pro - Available
				[
					'id'          => 'popup',
					'icon'        => '💬',
					'title'       => __( 'Popup CTA', 'cta-manager' ),
					'description' => __( 'Create attention-grabbing popup modals', 'cta-manager' ),
					'features'    => [
						__( 'Customizable triggers', 'cta-manager' ),
						__( 'Exit-intent detection', 'cta-manager' ),
						__( 'Overlay backgrounds', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-popup',
				],
				[
					'id'          => 'slide-in',
					'icon'        => '📥',
					'title'       => __( 'Slide-in CTA', 'cta-manager' ),
					'description' => __( 'Animated slide-in panels from any edge', 'cta-manager' ),
					'features'    => [
						__( 'Multiple slide directions', 'cta-manager' ),
						__( 'Scroll-triggered display', 'cta-manager' ),
						__( 'Smooth animations', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-slide-in',
				],
				// Pro - Coming Soon
				[
					'id'          => 'custom',
					'icon'        => '🛠️',
					'title'       => __( 'Custom CTA Type', 'cta-manager' ),
					'description' => __( 'Build fully custom CTA behaviors', 'cta-manager' ),
					'features'    => [
						__( 'Custom HTML templates', 'cta-manager' ),
						__( 'JavaScript hooks', 'cta-manager' ),
						__( 'API integrations', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-custom',
				],
			],
			__( 'Layout & Display', 'cta-manager' ) => [
				// Free - Available
				[
					'id'          => 'button-layout',
					'icon'        => '🔘',
					'title'       => __( 'Button Layout', 'cta-manager' ),
					'description' => __( 'Simple button-only layout for clean, minimal CTAs', 'cta-manager' ),
					'features'    => [
						__( 'Compact footprint', 'cta-manager' ),
						__( 'Multiple positions', 'cta-manager' ),
						__( 'Mobile optimized', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-button-only',
				],
				// Pro - Available
				[
					'id'          => 'cta-cards',
					'icon'        => '🃏',
					'title'       => __( 'CTA Cards', 'cta-manager' ),
					'description' => __( 'Display CTAs as attractive card layouts', 'cta-manager' ),
					'features'    => [
						__( 'Card-style presentation', 'cta-manager' ),
						__( 'Text content support', 'cta-manager' ),
						__( 'Visual hierarchy', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-cta-cards',
				],
				[
					'id'          => 'customizable-cards',
					'icon'        => '🎴',
					'title'       => __( 'Customizable Cards', 'cta-manager' ),
					'description' => __( 'Card layouts with text above, below, left, or right', 'cta-manager' ),
					'features'    => [
						__( 'Flexible text positioning', 'cta-manager' ),
						__( 'Image support', 'cta-manager' ),
						__( 'Multiple card styles', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-customizable-cards',
				],
				[
					'id'          => 'advanced-styling',
					'icon'        => '🎨',
					'title'       => __( 'Advanced Styling', 'cta-manager' ),
					'description' => __( 'Custom colors, fonts, padding, and borders', 'cta-manager' ),
					'features'    => [
						__( 'Color picker', 'cta-manager' ),
						__( 'Font customization', 'cta-manager' ),
						__( 'Border & shadow controls', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-advanced-styling',
				],
				[
					'id'          => 'animations',
					'icon'        => '✨',
					'title'       => __( 'Button & Icon Animations', 'cta-manager' ),
					'description' => __( 'Eye-catching animations for buttons and icons', 'cta-manager' ),
					'features'    => [
						__( 'Hover effects', 'cta-manager' ),
						__( 'Pulse animations', 'cta-manager' ),
						__( 'Icon transitions', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-animations',
				],
				[
					'id'          => 'custom-css',
					'icon'        => '🎛️',
					'title'       => __( 'Custom CSS Editor', 'cta-manager' ),
					'description' => __( 'Add custom CSS for complete control', 'cta-manager' ),
					'features'    => [
						__( 'Syntax highlighting', 'cta-manager' ),
						__( 'Per-CTA styling', 'cta-manager' ),
						__( 'Global overrides', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-custom-css',
				],
				// Pro - Coming Soon
				[
					'id'          => 'rich-text',
					'icon'        => '📝',
					'title'       => __( 'Rich Text Editing', 'cta-manager' ),
					'description' => __( 'WYSIWYG editor for card content', 'cta-manager' ),
					'features'    => [
						__( 'Visual editor', 'cta-manager' ),
						__( 'Formatting toolbar', 'cta-manager' ),
						__( 'Media embedding', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-rich-text',
				],
			],
			__( 'Embedding & Scheduling', 'cta-manager' ) => [
				// Free - Available
				[
					'id'          => 'shortcodes',
					'icon'        => '📝',
					'title'       => __( 'Shortcode Support', 'cta-manager' ),
					'description' => __( 'Embed CTAs anywhere using simple shortcodes', 'cta-manager' ),
					'features'    => [
						__( 'Works in posts & pages', 'cta-manager' ),
						__( 'Widget compatible', 'cta-manager' ),
						__( 'Block editor support', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-shortcodes',
				],
				[
					'id'          => 'basic-scheduling',
					'icon'        => '📅',
					'title'       => __( 'Basic Scheduling', 'cta-manager' ),
					'description' => __( 'Set start and end dates for your CTAs', 'cta-manager' ),
					'features'    => [
						__( 'Date-based visibility', 'cta-manager' ),
						__( 'Timezone support', 'cta-manager' ),
						__( 'Campaign planning', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-scheduling-basic',
				],
				// Pro - Available
				[
					'id'          => 'business-hours',
					'icon'        => '⏰',
					'title'       => __( 'Business Hours', 'cta-manager' ),
					'description' => __( 'Show CTAs only during business hours', 'cta-manager' ),
					'features'    => [
						__( 'Day-of-week scheduling', 'cta-manager' ),
						__( 'Time range rules', 'cta-manager' ),
						__( 'Holiday exceptions', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-business-hours',
				],
			],
			__( 'Targeting & Rules', 'cta-manager' ) => [
				// Pro - Available
				[
					'id'          => 'device-detection',
					'icon'        => '📱',
					'title'       => __( 'Device Detection', 'cta-manager' ),
					'description' => __( 'Target mobile, desktop, or tablet users', 'cta-manager' ),
					'features'    => [
						__( 'Auto device detection', 'cta-manager' ),
						__( 'Device-specific CTAs', 'cta-manager' ),
						__( 'Responsive toggles', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-device-detection',
				],
				[
					'id'          => 'blacklist-whitelist',
					'icon'        => '🚫',
					'title'       => __( 'URL Blacklist/Whitelist', 'cta-manager' ),
					'description' => __( 'Control which pages display your CTAs', 'cta-manager' ),
					'features'    => [
						__( 'Page-level control', 'cta-manager' ),
						__( 'Pattern matching', 'cta-manager' ),
						__( 'Category exclusions', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-blacklist-whitelist',
				],
				// Pro - Coming Soon
				[
					'id'          => 'user-agent',
					'icon'        => '🔍',
					'title'       => __( 'User Agent Targeting', 'cta-manager' ),
					'description' => __( 'Target specific browsers and devices', 'cta-manager' ),
					'features'    => [
						__( 'Browser detection', 'cta-manager' ),
						__( 'OS targeting', 'cta-manager' ),
						__( 'Bot filtering', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-user-agent',
				],
				[
					'id'          => 'screen-size',
					'icon'        => '📐',
					'title'       => __( 'Screen Size Rules', 'cta-manager' ),
					'description' => __( 'Set custom viewport breakpoints', 'cta-manager' ),
					'features'    => [
						__( 'Custom breakpoints', 'cta-manager' ),
						__( 'Min/max width rules', 'cta-manager' ),
						__( 'Responsive visibility', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-screen-size',
				],
				[
					'id'          => 'orientation',
					'icon'        => '🔄',
					'title'       => __( 'Orientation Targeting', 'cta-manager' ),
					'description' => __( 'Target portrait or landscape orientation', 'cta-manager' ),
					'features'    => [
						__( 'Portrait mode CTAs', 'cta-manager' ),
						__( 'Landscape mode CTAs', 'cta-manager' ),
						__( 'Auto-switch behavior', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-orientation',
				],
			],
			__( 'Testing & Optimization', 'cta-manager' ) => [
				// Pro - Available
				[
					'id'          => 'ab-testing',
					'icon'        => '🧪',
					'title'       => __( 'A/B Testing', 'cta-manager' ),
					'description' => __( 'Test variants to optimize conversion rates', 'cta-manager' ),
					'features'    => [
						__( 'Multi-variant testing', 'cta-manager' ),
						__( 'Traffic splitting', 'cta-manager' ),
						__( 'Statistical significance', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-ab-testing',
				],
			],
			__( 'Analytics', 'cta-manager' ) => [
				// Free - Available
				[
					'id'          => 'analytics-7days',
					'icon'        => '📊',
					'title'       => __( 'Analytics (7 Days)', 'cta-manager' ),
					'description' => __( 'Track impressions and clicks for up to 7 days', 'cta-manager' ),
					'features'    => [
						__( 'Impression tracking', 'cta-manager' ),
						__( 'Click analytics', 'cta-manager' ),
						__( 'Basic reporting', 'cta-manager' ),
					],
					'plan'        => self::PLAN_FREE,
					'implemented' => true,
					'docs_page'   => 'feature-analytics-overview',
				],
				// Pro - Coming Soon
				[
					'id'          => 'analytics-90days',
					'icon'        => '📊',
					'title'       => __( 'Extended Analytics (90 Days)', 'cta-manager' ),
					'description' => __( 'Retain analytics data for up to 90 days', 'cta-manager' ),
					'features'    => [
						__( 'Historical data retention', 'cta-manager' ),
						__( 'Trend analysis', 'cta-manager' ),
						__( 'Data export options', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => false,
					'docs_page'   => 'feature-analytics-extended',
				],
				[
					'id'          => 'analytics-advanced',
					'icon'        => '📈',
					'title'       => __( 'Advanced Analytics', 'cta-manager' ),
					'description' => __( 'Detailed charts, trends, and insights', 'cta-manager' ),
					'features'    => [
						__( 'Interactive charts', 'cta-manager' ),
						__( 'Conversion funnels', 'cta-manager' ),
						__( 'Performance reports', 'cta-manager' ),
					],
					'plan'        => self::PLAN_PRO,
					'implemented' => true,
					'docs_page'   => 'feature-analytics-advanced',
				],
			],
		];

		self::$features_cache = $fallback_features;
		return self::$features_cache;
	}

	/**
	 * Get all integrations grouped by category
	 *
	 * @return array Integrations grouped by category name
	 */
	public static function get_all_integrations(): array {
		if ( null !== self::$integrations_cache ) {
			return self::$integrations_cache;
		}

		$data = self::load_data_file();
		if ( ! empty( $data['integrations'] ) ) {
			self::$integrations_cache = self::normalize_integrations( $data['integrations'] );
			return self::$integrations_cache;
		}

		$icons_path = CTA_PLUGIN_URL . 'assets/images/integrations/';

		$fallback_integrations = [
			__( 'Analytics & Tracking', 'cta-manager' ) => [
				[
					'id'          => 'ga4',
					'image'       => $icons_path . 'google-analytics.svg',
					'title'       => __( 'Google Analytics 4', 'cta-manager' ),
					'description' => __( 'Track CTA events, conversions, and user behavior directly in GA4', 'cta-manager' ),
					'features'    => [
						__( 'Automatic event tracking', 'cta-manager' ),
						__( 'Custom dimensions & metrics', 'cta-manager' ),
						__( 'Conversion attribution', 'cta-manager' ),
					],
					'implemented' => true,
					'docs_page'   => 'integration-ga4',
				],
				[
					'id'          => 'gtm',
					'image'       => $icons_path . 'google-tag-manager.svg',
					'title'       => __( 'Google Tag Manager', 'cta-manager' ),
					'description' => __( 'Push CTA events to GTM data layer for advanced tracking', 'cta-manager' ),
					'features'    => [
						__( 'Data layer push events', 'cta-manager' ),
						__( 'Custom trigger support', 'cta-manager' ),
						__( 'Enhanced e-commerce ready', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-gtm',
				],
				[
					'id'          => 'posthog',
					'image'       => $icons_path . 'posthog.svg',
					'title'       => __( 'PostHog', 'cta-manager' ),
					'description' => __( 'Product analytics with session recordings and feature flags', 'cta-manager' ),
					'features'    => [
						__( 'Session replay integration', 'cta-manager' ),
						__( 'Feature flag targeting', 'cta-manager' ),
						__( 'Funnel analysis', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-posthog',
				],
				[
					'id'          => 'newrelic',
					'image'       => $icons_path . 'new-relic.svg',
					'title'       => __( 'New Relic', 'cta-manager' ),
					'description' => __( 'Monitor CTA performance and page load impact', 'cta-manager' ),
					'features'    => [
						__( 'Real user monitoring', 'cta-manager' ),
						__( 'Performance alerting', 'cta-manager' ),
						__( 'Error tracking', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-newrelic',
				],
			],
			__( 'SEO & Optimization', 'cta-manager' ) => [
				[
					'id'          => 'gsc',
					'image'       => $icons_path . 'google-search-console.svg',
					'title'       => __( 'Google Search Console', 'cta-manager' ),
					'description' => __( 'Correlate CTA performance with organic search traffic', 'cta-manager' ),
					'features'    => [
						__( 'Search query insights', 'cta-manager' ),
						__( 'Landing page performance', 'cta-manager' ),
						__( 'CTR optimization tips', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-gsc',
				],
				[
					'id'          => 'vwo',
					'image'       => $icons_path . 'vwo.svg',
					'title'       => __( 'VWO', 'cta-manager' ),
					'description' => __( 'Advanced A/B testing and conversion optimization platform', 'cta-manager' ),
					'features'    => [
						__( 'Visual editor integration', 'cta-manager' ),
						__( 'Multivariate testing', 'cta-manager' ),
						__( 'Heatmap correlation', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-vwo',
				],
			],
			__( 'Communication', 'cta-manager' ) => [
				[
					'id'          => 'slack',
					'image'       => $icons_path . 'slack.svg',
					'title'       => __( 'Slack', 'cta-manager' ),
					'description' => __( 'Get real-time notifications for CTA conversions and milestones', 'cta-manager' ),
					'features'    => [
						__( 'Conversion alerts', 'cta-manager' ),
						__( 'Daily digest reports', 'cta-manager' ),
						__( 'Custom channel routing', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-slack',
				],
				[
					'id'          => 'gmail',
					'image'       => $icons_path . 'google-gmail.svg',
					'title'       => __( 'Gmail', 'cta-manager' ),
					'description' => __( 'Email notifications and lead capture integrations', 'cta-manager' ),
					'features'    => [
						__( 'Lead notifications', 'cta-manager' ),
						__( 'Auto-responder triggers', 'cta-manager' ),
						__( 'Contact sync', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-gmail',
				],
				[
					'id'          => 'zoom',
					'image'       => $icons_path . 'zoom.svg',
					'title'       => __( 'Zoom', 'cta-manager' ),
					'description' => __( 'Create CTAs that schedule and join Zoom meetings', 'cta-manager' ),
					'features'    => [
						__( 'One-click meeting join', 'cta-manager' ),
						__( 'Calendar scheduling CTA', 'cta-manager' ),
						__( 'Webinar registration', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-zoom',
				],
			],
			__( 'Project Management', 'cta-manager' ) => [
				[
					'id'          => 'jira',
					'image'       => $icons_path . 'jira.svg',
					'title'       => __( 'Jira', 'cta-manager' ),
					'description' => __( 'Create issues from CTA interactions for support workflows', 'cta-manager' ),
					'features'    => [
						__( 'Auto issue creation', 'cta-manager' ),
						__( 'Custom field mapping', 'cta-manager' ),
						__( 'Workflow automation', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-jira',
				],
				[
					'id'          => 'confluence',
					'image'       => $icons_path . 'confluence.svg',
					'title'       => __( 'Confluence', 'cta-manager' ),
					'description' => __( 'Embed dynamic CTAs in Confluence pages and spaces', 'cta-manager' ),
					'features'    => [
						__( 'Page macro support', 'cta-manager' ),
						__( 'Space-wide CTAs', 'cta-manager' ),
						__( 'Analytics dashboard', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-confluence',
				],
				[
					'id'          => 'github',
					'image'       => $icons_path . 'github.svg',
					'title'       => __( 'GitHub', 'cta-manager' ),
					'description' => __( 'Developer-focused CTAs with repository and issue integration', 'cta-manager' ),
					'features'    => [
						__( 'Star/fork CTA buttons', 'cta-manager' ),
						__( 'Issue submission forms', 'cta-manager' ),
						__( 'Sponsor link CTAs', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-github',
				],
			],
			__( 'Scheduling', 'cta-manager' ) => [
				[
					'id'          => 'gcal',
					'image'       => $icons_path . 'google-calendar.svg',
					'title'       => __( 'Google Calendar', 'cta-manager' ),
					'description' => __( 'Schedule meetings and events directly from CTAs', 'cta-manager' ),
					'features'    => [
						__( 'Appointment booking', 'cta-manager' ),
						__( 'Add to calendar CTAs', 'cta-manager' ),
						__( 'Availability display', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-gcal',
				],
			],
			__( 'Security', 'cta-manager' ) => [
				[
					'id'          => 'recaptcha',
					'image'       => $icons_path . 'google-recaptcha.svg',
					'title'       => __( 'Google reCAPTCHA', 'cta-manager' ),
					'description' => __( 'Protect form CTAs from spam and bot submissions', 'cta-manager' ),
					'features'    => [
						__( 'Invisible reCAPTCHA v3', 'cta-manager' ),
						__( 'Challenge-based v2', 'cta-manager' ),
						__( 'Score-based filtering', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-recaptcha',
				],
				[
					'id'          => 'cloudflare',
					'image'       => $icons_path . 'cloudflare.svg',
					'title'       => __( 'Cloudflare', 'cta-manager' ),
					'description' => __( 'CDN caching and Turnstile bot protection for CTAs', 'cta-manager' ),
					'features'    => [
						__( 'Turnstile integration', 'cta-manager' ),
						__( 'Edge caching rules', 'cta-manager' ),
						__( 'Bot score filtering', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-cloudflare',
				],
			],
			__( 'Design', 'cta-manager' ) => [
				[
					'id'          => 'figma',
					'image'       => $icons_path . 'figma.svg',
					'title'       => __( 'Figma', 'cta-manager' ),
					'description' => __( 'Import CTA designs directly from Figma files', 'cta-manager' ),
					'features'    => [
						__( 'Design token sync', 'cta-manager' ),
						__( 'Component import', 'cta-manager' ),
						__( 'Style guide matching', 'cta-manager' ),
					],
					'implemented' => false,
					'docs_page'   => 'integration-figma',
				],
			],
		];

		self::$integrations_cache = $fallback_integrations;
		return self::$integrations_cache;
	}

	/**
	 * Get all categories with metadata.
	 *
	 * Returns category definitions including icons, descriptions, and order.
	 *
	 * @return array Categories with metadata keyed by category name
	 */
	public static function get_categories(): array {
		if ( null !== self::$categories_cache ) {
			return self::$categories_cache;
		}

		$data = self::load_data_file();
		if ( ! empty( $data['categories'] ) ) {
			self::$categories_cache = $data['categories'];
			return self::$categories_cache;
		}

		// Default categories fallback
		$default_categories = [
			__( 'CTA Types', 'cta-manager' )              => [ 'icon' => 'phone', 'order' => 1 ],
			__( 'Layout & Display', 'cta-manager' )       => [ 'icon' => 'visibility', 'order' => 2 ],
			__( 'Embedding & Scheduling', 'cta-manager' ) => [ 'icon' => 'calendar-alt', 'order' => 3 ],
			__( 'Targeting & Rules', 'cta-manager' )      => [ 'icon' => 'filter', 'order' => 4 ],
			__( 'Testing & Optimization', 'cta-manager' ) => [ 'icon' => 'chart-line', 'order' => 5 ],
			__( 'Analytics', 'cta-manager' )              => [ 'icon' => 'chart-bar', 'order' => 6 ],
		];

		self::$categories_cache = $default_categories;
		return self::$categories_cache;
	}

	/**
	 * Get icon for a specific category.
	 *
	 * @param string $category_name Category name
	 * @return string Dashicon name (without 'dashicons-' prefix)
	 */
	public static function get_category_icon( string $category_name ): string {
		$categories = self::get_categories();
		if ( isset( $categories[ $category_name ]['icon'] ) ) {
			return $categories[ $category_name ]['icon'];
		}
		return 'list-view'; // Default fallback icon
	}

	/**
	 * Get integrations section metadata.
	 *
	 * Returns the icon, label, and description for the Integrations menu item.
	 *
	 * @return array Integrations metadata with icon, label, and description
	 */
	public static function get_integrations_meta(): array {
		$data = self::load_data_file();
		if ( ! empty( $data['integrations_meta'] ) ) {
			return $data['integrations_meta'];
		}

		// Default fallback
		return [
			'icon'        => 'admin-plugins',
			'label'       => __( 'Integrations', 'cta-manager' ),
			'description' => __( 'Connect CTA Manager with your favorite tools and services', 'cta-manager' ),
		];
	}

	/**
	 * Get all UI labels.
	 *
	 * Returns badge and status labels used across the features modal.
	 *
	 * @return array Labels keyed by label name
	 */
	public static function get_labels(): array {
		$data = self::load_data_file();
		if ( ! empty( $data['labels'] ) ) {
			return $data['labels'];
		}

		// Default fallback labels
		return [
			'badge_pro'         => __( 'Pro', 'cta-manager' ),
			'badge_free'        => __( 'Free', 'cta-manager' ),
			'badge_coming_soon' => __( 'Coming Soon', 'cta-manager' ),
			'badge_available'   => __( 'Available', 'cta-manager' ),
		];
	}

	/**
	 * Get a specific UI label.
	 *
	 * @param string $label_key Label key (e.g., 'badge_pro', 'badge_coming_soon')
	 * @return string Label text
	 */
	public static function get_label( string $label_key ): string {
		$labels = self::get_labels();
		return $labels[ $label_key ] ?? $label_key;
	}

	/**
	 * Get description for a specific category.
	 *
	 * @param string $category_name Category name
	 * @return string Category description
	 */
	public static function get_category_description( string $category_name ): string {
		$categories = self::get_categories();
		if ( isset( $categories[ $category_name ]['description'] ) ) {
			return $categories[ $category_name ]['description'];
		}
		return '';
	}

	/**
	 * Get features for documentation modal navigation structure
	 * Groups features by their docs category structure
	 *
	 * @return array Navigation structure for docs modal
	 */
	public static function get_docs_navigation(): array {
		$features     = self::get_all_features();
		$integrations = self::get_all_integrations();

		$nav = [
			'features'     => [],
			'integrations' => [],
		];

		// Build features nav grouped by category
		foreach ( $features as $category => $category_features ) {
			$nav['features'][ $category ] = [];
			foreach ( $category_features as $feature ) {
				$nav['features'][ $category ][] = [
					'id'          => $feature['id'],
					'title'       => $feature['title'],
					'docs_page'   => $feature['docs_page'],
					'plan'        => $feature['plan'],
					'implemented' => $feature['implemented'],
				];
			}
		}

		// Build integrations nav grouped by category
		foreach ( $integrations as $category => $category_integrations ) {
			$nav['integrations'][ $category ] = [];
			foreach ( $category_integrations as $integration ) {
				$nav['integrations'][ $category ][] = [
					'id'          => $integration['id'],
					'title'       => $integration['title'],
					'docs_page'   => $integration['docs_page'],
					'implemented' => $integration['implemented'],
				];
			}
		}

		return $nav;
	}

	/**
	 * Get documentation-specific navigation structure
	 *
	 * The docs modal organizes features differently from the features modal
	 * to optimize for documentation finding. This method provides that structure.
	 *
	 * @return array Documentation navigation structure
	 */
	public static function get_docs_sidebar_structure(): array {
		$all_features = self::get_features_flat();
		$all_integrations = self::get_integrations_flat();

		// Create lookup by ID
		$features_by_id = [];
		foreach ( $all_features as $feature ) {
			$features_by_id[ $feature['id'] ] = $feature;
		}

		$integrations_by_id = [];
		foreach ( $all_integrations as $integration ) {
			$integrations_by_id[ $integration['id'] ] = $integration;
		}

		// Documentation-specific feature categories
		// These may differ from the features modal categories for better docs organization
		$docs_features = [
			'cta_types' => [
				'label' => __( 'CTA Types', 'cta-manager' ),
				'items' => [ 'phone', 'link', 'email', 'popup', 'slide-in', 'custom' ],
			],
			'layout_display' => [
				'label' => __( 'Layout & Display', 'cta-manager' ),
				'items' => [ 'button-layout', 'cta-cards', 'customizable-cards' ],
			],
			'styling' => [
				'label' => __( 'Styling', 'cta-manager' ),
				'items' => [ 'advanced-styling', 'animations', 'custom-css', 'rich-text' ],
			],
			'analytics' => [
				'label' => __( 'Analytics', 'cta-manager' ),
				'items' => [ 'analytics-7days', 'analytics-90days', 'analytics-advanced' ],
			],
			'targeting_rules' => [
				'label' => __( 'Targeting & Rules', 'cta-manager' ),
				'items' => [ 'device-detection', 'blacklist-whitelist', 'screen-size', 'user-agent', 'orientation' ],
			],
			'scheduling' => [
				'label' => __( 'Scheduling', 'cta-manager' ),
				'items' => [ 'basic-scheduling', 'business-hours' ],
			],
			'embedding_testing' => [
				'label' => __( 'Embedding & Testing', 'cta-manager' ),
				'items' => [ 'shortcodes', 'ab-testing' ],
			],
		];

		// Build the docs features navigation from lookup
		$features_nav = [];
		foreach ( $docs_features as $section_key => $section ) {
			$features_nav[ $section_key ] = [
				'label' => $section['label'],
				'items' => [],
			];
			foreach ( $section['items'] as $item_id ) {
				if ( isset( $features_by_id[ $item_id ] ) ) {
					$feature = $features_by_id[ $item_id ];
					$features_nav[ $section_key ]['items'][] = [
						'id'          => $feature['id'],
						'title'       => $feature['title'],
						'docs_page'   => $feature['docs_page'],
						'plan'        => $feature['plan'],
						'implemented' => $feature['implemented'],
					];
				}
			}
		}

		// Integrations nav uses the same structure as the features modal
		$integrations_nav = [];
		foreach ( self::get_all_integrations() as $category => $items ) {
			$key = sanitize_key( $category );
			$integrations_nav[ $key ] = [
				'label' => $category,
				'items' => [],
			];
			foreach ( $items as $integration ) {
				$integrations_nav[ $key ]['items'][] = [
					'id'          => $integration['id'],
					'title'       => $integration['title'],
					'docs_page'   => $integration['docs_page'],
					'implemented' => $integration['implemented'],
				];
			}
		}

		return [
			'features'     => $features_nav,
			'integrations' => $integrations_nav,
		];
	}

	/**
	 * Get a flat list of all features
	 *
	 * @return array All features as a flat array
	 */
	public static function get_features_flat(): array {
		$features = self::get_all_features();
		$flat     = [];

		foreach ( $features as $category_features ) {
			$flat = array_merge( $flat, $category_features );
		}

		return $flat;
	}

	/**
	 * Get a flat list of all integrations
	 *
	 * @return array All integrations as a flat array
	 */
	public static function get_integrations_flat(): array {
		$integrations = self::get_all_integrations();
		$flat         = [];

		foreach ( $integrations as $category_integrations ) {
			$flat = array_merge( $flat, $category_integrations );
		}

		return $flat;
	}

	/**
	 * Get features by plan
	 *
	 * @param string $plan Plan type (free or pro)
	 * @return array Features for the specified plan
	 */
	public static function get_features_by_plan( string $plan ): array {
		$all_features = self::get_features_flat();

		return array_filter( $all_features, function ( $feature ) use ( $plan ) {
			return $feature['plan'] === $plan;
		} );
	}

	/**
	 * Get integrations by plan
	 *
	 * @param string $plan Plan type (free or pro)
	 * @return array Integrations for the specified plan
	 */
	public static function get_integrations_by_plan( string $plan ): array {
		$all_integrations = self::get_integrations_flat();

		return array_filter( $all_integrations, function ( $integration ) use ( $plan ) {
			return isset( $integration['plan'] ) && $integration['plan'] === $plan;
		} );
	}

	/**
	 * Get implemented features only
	 *
	 * @return array Only implemented features
	 */
	public static function get_implemented_features(): array {
		$all_features = self::get_features_flat();

		return array_filter( $all_features, function ( $feature ) {
			return $feature['implemented'] === true;
		} );
	}

	/**
	 * Get coming soon features
	 *
	 * @return array Features not yet implemented
	 */
	public static function get_coming_soon_features(): array {
		$all_features = self::get_features_flat();

		return array_filter( $all_features, function ( $feature ) {
			return $feature['implemented'] === false;
		} );
	}

	/**
	 * Get implemented integrations only
	 *
	 * @return array Only implemented integrations
	 */
	public static function get_implemented_integrations(): array {
		$all_integrations = self::get_integrations_flat();

		return array_filter( $all_integrations, function ( $integration ) {
			return $integration['implemented'] === true;
		} );
	}

	/**
	 * Get a feature by ID
	 *
	 * @param string $id Feature ID
	 * @return array|null Feature data or null if not found
	 */
	public static function get_feature_by_id( string $id ): ?array {
		$all_features = self::get_features_flat();

		foreach ( $all_features as $feature ) {
			if ( $feature['id'] === $id ) {
				return $feature;
			}
		}

		return null;
	}

	/**
	 * Get an integration by ID
	 *
	 * @param string $id Integration ID
	 * @return array|null Integration data or null if not found
	 */
	public static function get_integration_by_id( string $id ): ?array {
		$all_integrations = self::get_integrations_flat();

		foreach ( $all_integrations as $integration ) {
			if ( $integration['id'] === $id ) {
				return $integration;
			}
		}

		return null;
	}

	/**
	 * Clear cached data (useful after language switch)
	 */
	public static function clear_cache(): void {
		self::$features_cache     = null;
		self::$integrations_cache = null;
		self::$categories_cache   = null;
		self::$data_cache         = null;
	}

	/**
	 * Get feature count statistics
	 *
	 * @return array Count statistics
	 */
	public static function get_stats(): array {
		$features     = self::get_features_flat();
		$integrations = self::get_integrations_flat();

		return [
			'total_features'           => count( $features ),
			'free_features'            => count( array_filter( $features, fn( $f ) => $f['plan'] === self::PLAN_FREE ) ),
			'pro_features'             => count( array_filter( $features, fn( $f ) => $f['plan'] === self::PLAN_PRO ) ),
			'implemented_features'     => count( array_filter( $features, fn( $f ) => $f['implemented'] ) ),
			'coming_soon_features'     => count( array_filter( $features, fn( $f ) => ! $f['implemented'] ) ),
			'total_integrations'       => count( $integrations ),
			'implemented_integrations' => count( array_filter( $integrations, fn( $i ) => $i['implemented'] ) ),
			'coming_soon_integrations' => count( array_filter( $integrations, fn( $i ) => ! $i['implemented'] ) ),
		];
	}
}
