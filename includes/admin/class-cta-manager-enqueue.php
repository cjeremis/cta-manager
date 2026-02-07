<?php
/**
 * Admin controller class
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Manager_Enqueue {

	use CTA_Singleton;

	/**
	 * Enqueue admin styles
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		if ( ! CTA_Admin_Menu::is_plugin_page() ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		// Load global styles on all pages (minimized - compiled from SCSS)
		// Includes: variables, mixins, reset, buttons, forms, toggles, info-box, toast, empty-state, modals, badges, card, tabs, elements, support, components, help-icon, notifications
		wp_enqueue_style(
			'cta-global',
			CTA_PLUGIN_URL . 'assets/css/minimized/admin/global.min.css',
			[],
			CTA_VERSION,
			'all'
		);

		// Inject logo URL as CSS variable (filterable for Pro customization)
		$default_logo_url = CTA_PLUGIN_URL . 'assets/images/cta-manager-logo.png';
		$logo_url         = apply_filters( 'cta_logo_url', $default_logo_url );
		wp_add_inline_style( 'cta-global', ':root { --cta-logo-url: url("' . esc_url( $logo_url ) . '"); }' );

		// Load shared animations CSS
		wp_enqueue_style(
			'cta-shared',
			CTA_PLUGIN_URL . 'assets/css/minimized/shared/shared.min.css',
			[],
			CTA_VERSION,
			'all'
		);

		// Load dashboard page-specific styles (preview/filters modals, features page)
		wp_enqueue_style(
			'cta-dashboard',
			CTA_PLUGIN_URL . 'assets/css/minimized/admin/dashboard.min.css',
			[ 'cta-global' ],
			CTA_VERSION,
			'all'
		);

		// Manage CTAs page styles (minimized - compiled from SCSS)
		// Includes: manage_ctas page-specific styles
		if ( 'cta-manager-cta' === $page ) {
			wp_enqueue_style(
				'cta-manage-ctas',
				CTA_PLUGIN_URL . 'assets/css/minimized/admin/manage_ctas.min.css',
				[ 'cta-global' ],
				CTA_VERSION,
				'all'
			);
		}

		// Analytics dashboard styles
		if ( 'cta-manager-analytics' === $page ) {
			wp_enqueue_style(
				'cta-analytics',
				CTA_PLUGIN_URL . 'assets/css/minimized/admin/analytics.min.css',
				[ 'cta-global' ],
				CTA_VERSION,
				'all'
			);
		}

		// Settings page styles (minimized - compiled from SCSS)
		// Includes: settings page styles, license-manager, custom-icons, and pro-lockout
		if ( 'cta-manager-settings' === $page ) {
			wp_enqueue_style(
				'cta-settings',
				CTA_PLUGIN_URL . 'assets/css/minimized/admin/settings.min.css',
				[ 'cta-global' ],
				CTA_VERSION,
				'all'
			);
		}

		// Tools page styles (minimized - compiled from SCSS)
		// Includes: tools page styles (export/import, demo data, backup), and pro-lockout
		if ( 'cta-manager-tools' === $page ) {
			wp_enqueue_style(
				'cta-tools',
				CTA_PLUGIN_URL . 'assets/css/minimized/admin/tools.min.css',
				[ 'cta-global' ],
				CTA_VERSION,
				'all'
			);
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * Uses webpack-compiled minimized bundles.
	 * - admin.js: Global admin bundle (tabs, modals, toast, notifications, onboarding, pro-upsell, docs)
	 * - Page-specific bundles: manage-ctas.js, settings.js, analytics.js, tools.js, features.js, support.js
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( ! CTA_Admin_Menu::is_plugin_page() ) {
			return;
		}

		// Global admin bundle - includes: tabs, modals, toast, notifications, onboarding, pro-upsell, docs-modal
		wp_enqueue_script(
			'cta-admin',
			CTA_PLUGIN_URL . 'assets/js/minimized/admin/admin.min.js',
			[ 'jquery' ],
			CTA_VERSION,
			true
		);

		// Check if demo data exists for onboarding modal
		$demo_cta_ids = CTA_Repository::get_instance()->get_demo_cta_ids();
		$has_demo_data = ! empty( $demo_cta_ids );

		// Check if debug mode is enabled
		$debug_enabled = CTA_Debug::is_enabled();

		wp_localize_script(
			'cta-admin',
			'ctaAdminVars',
			[
				'nonce'            => wp_create_nonce( 'cta_admin_nonce' ),
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'proActivateNonce' => wp_create_nonce( 'cta_activate_pro_plugin' ),
				'hasDemoData'      => $has_demo_data,
				'debug'            => $debug_enabled,
				'i18n'             => [
					'next' => __( 'Next', 'cta-manager' ),
					'skip' => __( 'Skip', 'cta-manager' ),
				],
			]
		);

		$pro_menu_config = CTA_Admin_Menu::get_pro_menu_config();
		if ( $pro_menu_config ) {
			$menu_script_config = [
				'targetUrl'   => $pro_menu_config['target_url'],
				'type'        => $pro_menu_config['type'],
				'initialHref' => $pro_menu_config['menu_href'],
				'scrollTo'    => $pro_menu_config['scroll_to'] ?? '',
				'focusField'  => $pro_menu_config['focus_field'] ?? '',
			];
			wp_add_inline_script(
				'cta-admin',
				'window.ctaProMenuLinkConfig = ' . wp_json_encode( $menu_script_config ) . ';',
				'before'
			);
			wp_add_inline_script(
				'cta-admin',
				<<<'JS'
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    var config = window.ctaProMenuLinkConfig;
    if (!config || !config.targetUrl || !config.initialHref) {
      return;
    }
    var menuLink = document.querySelector('#toplevel_page_cta-manager .wp-submenu a[href="' + config.initialHref + '"]');
    if (!menuLink) {
      menuLink = document.querySelector('#toplevel_page_cta-manager .wp-submenu a[href*="cta-manager-pro-upgrade"]');
    }
    if (!menuLink) {
      return;
    }
    menuLink.setAttribute('href', config.targetUrl);
    if (config.scrollTo) {
      menuLink.setAttribute('data-scroll-to', config.scrollTo);
    }
    if (config.focusField) {
      menuLink.setAttribute('data-focus-field', config.focusField);
    }

    if (config.type === 'add_license') {
      menuLink.classList.add('cta-add-api-key-button');
    } else {
      menuLink.classList.add('cta-upgrade-button');
    }
  });
})();
JS
			);
		}

		// Global support vars for ticket modal (available on all pages)
		wp_localize_script(
			'cta-admin',
			'cta_support_vars',
			[
				'nonce'           => wp_create_nonce( 'cta_support_nonce' ),
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'submit_text'     => __( 'Submit Ticket', 'cta-manager' ),
				'submitting_text' => __( 'Submitting...', 'cta-manager' ),
				'send_text'       => __( 'Send Reply', 'cta-manager' ),
				'sending_text'    => __( 'Sending...', 'cta-manager' ),
			]
		);

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		// Manage CTAs page bundle - includes: format-modal, filters, preview-modal
		if ( 'cta-manager-cta' === $page ) {
			wp_enqueue_script(
				'cta-manage-ctas',
				CTA_PLUGIN_URL . 'assets/js/minimized/admin/manage-ctas.min.js',
				[ 'jquery', 'cta-admin' ],
				CTA_VERSION,
				true
			);
		}

		// Settings page bundle
		if ( 'cta-manager-settings' === $page ) {
			wp_enqueue_script(
				'cta-settings',
				CTA_PLUGIN_URL . 'assets/js/minimized/admin/settings.min.js',
				[ 'jquery', 'cta-admin' ],
				CTA_VERSION,
				true
			);

			$data = CTA_Data::get_instance();

			wp_localize_script(
				'cta-settings',
				'ctaSettingsVars',
				[
					'nonce'           => wp_create_nonce( 'cta_admin_nonce' ),
					'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
					'currentSettings' => $data->get_settings(),
				]
			);
		}


		// Analytics dashboard bundle
		if ( 'cta-manager-analytics' === $page ) {
			$chart_script_path = CTA_PLUGIN_DIR . 'assets/js/lib/chart.js';
			$chart_script_url  = CTA_PLUGIN_URL . 'assets/js/lib/chart.js';
			if ( ! file_exists( $chart_script_path ) ) {
				$chart_script_url = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
			}

			$analytics_script_path = CTA_PLUGIN_DIR . 'assets/js/minimized/admin/analytics.min.js';
			$analytics_script_ver  = file_exists( $analytics_script_path ) ? (string) filemtime( $analytics_script_path ) : CTA_VERSION;

			wp_enqueue_script(
				'chart-js',
				$chart_script_url,
				[],
				'3.9.1',
				true
			);

			wp_enqueue_script(
				'cta-analytics',
				CTA_PLUGIN_URL . 'assets/js/minimized/admin/analytics.min.js',
				[ 'jquery', 'chart-js', 'cta-admin' ],
				$analytics_script_ver,
				true
			);
		}

		// Tools page bundle
		if ( 'cta-manager-tools' === $page ) {
			$tools_script_path = CTA_PLUGIN_DIR . 'assets/js/minimized/admin/tools.min.js';
			$tools_script_ver  = file_exists( $tools_script_path ) ? (string) filemtime( $tools_script_path ) : CTA_VERSION;

			wp_enqueue_script(
				'cta-tools',
				CTA_PLUGIN_URL . 'assets/js/minimized/admin/tools.min.js',
				[ 'jquery', 'cta-admin' ],
				$tools_script_ver,
				true
			);

			wp_localize_script(
				'cta-tools',
				'ctaExportImportVars',
				[
					'nonce'   => wp_create_nonce( 'cta_admin_nonce' ),
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'i18n'    => [
						'exporting'    => __( 'Exporting...', 'cta-manager' ),
						'exportFailed' => __( 'Export failed', 'cta-manager' ),
						'exportLabel'  => __( 'Download JSON File', 'cta-manager' ),
						'download'     => __( 'Download JSON File', 'cta-manager' ),
						'importing'    => __( 'Importing...', 'cta-manager' ),
						'importFailed' => __( 'Import failed', 'cta-manager' ),
						'imported'     => __( 'Import successful', 'cta-manager' ),
						'importLabel'  => __( 'Import Settings', 'cta-manager' ),
						'chooseFile'   => __( 'Please choose a file', 'cta-manager' ),
						'invalidJson'  => __( 'Invalid JSON file', 'cta-manager' ),
						'copySuccess'  => __( 'JSON copied to clipboard!', 'cta-manager' ),
						'copyFailed'   => __( 'Unable to copy JSON', 'cta-manager' ),
					],
				]
			);

			wp_localize_script(
				'cta-tools',
				'ctaDemoDataVars',
				[
					'nonce'   => wp_create_nonce( 'cta_demo_data_nonce' ),
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'i18n'    => [
						'validating'        => __( 'Validating...', 'cta-manager' ),
						'validationSuccess' => __( 'Validation successful!', 'cta-manager' ),
						'validationFailed'  => __( 'Validation failed', 'cta-manager' ),
						'uploading'         => __( 'Uploading...', 'cta-manager' ),
						'uploadSuccess'     => __( 'Upload successful!', 'cta-manager' ),
						'uploadFailed'      => __( 'Upload failed', 'cta-manager' ),
						'invalidFileType'   => __( 'Please select a valid JSON file', 'cta-manager' ),
						'confirmUpload'     => __( 'Are you sure you want to upload this demo data file?', 'cta-manager' ),
						'confirmRestore'    => __( 'Are you sure you want to restore this backup?', 'cta-manager' ),
						'confirmDelete'     => __( 'Are you sure you want to delete this backup?', 'cta-manager' ),
					],
				]
			);
		}

		// Support page bundle
		if ( 'cta-manager-support' === $page ) {
			wp_enqueue_script(
				'cta-support',
				CTA_PLUGIN_URL . 'assets/js/minimized/admin/support.min.js',
				[ 'jquery', 'cta-admin' ],
				CTA_VERSION,
				true
			);

			wp_localize_script(
				'cta-support',
				'cta_support_vars',
				[
					'nonce'           => wp_create_nonce( 'cta_support_nonce' ),
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'submit_text'     => __( 'Submit Ticket', 'cta-manager' ),
					'submitting_text' => __( 'Submitting...', 'cta-manager' ),
					'send_text'       => __( 'Send Reply', 'cta-manager' ),
					'sending_text'    => __( 'Sending...', 'cta-manager' ),
				]
			);
		}
	}

	/**
	 * Add action links to plugin page
	 *
	 * @param array $links Action links
	 *
	 * @return array
	 */
	public function add_action_links( array $links ): array {
		$new_links = [];

		$pro_config = CTA_Admin_Menu::get_pro_menu_config();
		if ( $pro_config ) {
			$link_classes = [ 'cta-pro-action-link' ];
			if ( 'add_license' === $pro_config['type'] ) {
				$link_classes[] = 'cta-add-api-key-button';
			} else {
				$link_classes[] = 'cta-upgrade-button';
			}

			$data_attrs = '';
			if ( ! empty( $pro_config['scroll_to'] ) ) {
				$data_attrs .= ' data-scroll-to="' . esc_attr( $pro_config['scroll_to'] ) . '"';
			}
			if ( ! empty( $pro_config['focus_field'] ) ) {
				$data_attrs .= ' data-focus-field="' . esc_attr( $pro_config['focus_field'] ) . '"';
			}

			$new_links[] = sprintf(
				'<a href="%s" class="%s"%s>%s</a>',
				esc_url( $pro_config['target_url'] ),
				esc_attr( implode( ' ', $link_classes ) ),
				$data_attrs,
				esc_html( $pro_config['label'] )
			);
		}

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( CTA_Admin_Menu::get_admin_url( 'settings' ) ),
			esc_html__( 'Settings', 'cta-manager' )
		);
		$new_links[] = $settings_link;

		return array_merge( $new_links, $links );
	}

}
