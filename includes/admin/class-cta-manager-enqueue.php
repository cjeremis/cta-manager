<?php
/**
 * Admin Assets Enqueue Handler
 *
 * Handles admin script and style enqueueing for CTA Manager pages.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Manager_Enqueue {

	use CTA_Singleton;

	/**
	 * Enqueue admin bar and global styles
	 *
	 * Loads on all admin pages to ensure admin bar styling is applied.
	 *
	 * @return void
	 */
	public function enqueue_admin_bar_styles(): void {
		// Load shared styles (admin bar, animations, etc.) on all pages (admin + frontend)
		wp_enqueue_style(
			'cta-shared-admin-bar',
			CTA_PLUGIN_URL . 'assets/minimized/css/shared/shared.min.css',
			[],
			CTA_VERSION,
			'all'
		);

		// Enqueue site-wide minimized admin bundle; includes admin-bar-responsive module.
		wp_enqueue_script(
			'cta-admin',
			CTA_PLUGIN_URL . 'assets/minimized/js/admin/admin.min.js',
			[ 'jquery' ],
			CTA_VERSION,
			true
		);

		// Provide a minimal localization object for non-CTA admin screens.
		// Full CTA page-specific localization is still provided in enqueue_scripts().
		wp_localize_script(
			'cta-admin',
			'ctaAdminVars',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'cta_admin_nonce' ),
				'i18n'    => [
					'next' => __( 'Next', 'cta-manager' ),
					'skip' => __( 'Skip', 'cta-manager' ),
				],
			]
		);
	}

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
			CTA_PLUGIN_URL . 'assets/minimized/css/admin/global.min.css',
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
			CTA_PLUGIN_URL . 'assets/minimized/css/shared/shared.min.css',
			[],
			CTA_VERSION,
			'all'
		);

		// Load dashboard page-specific styles
		if ( 'cta-manager' === $page ) {
			wp_enqueue_style(
				'cta-dashboard',
				CTA_PLUGIN_URL . 'assets/minimized/css/admin/dashboard.min.css',
				[ 'cta-global' ],
				CTA_VERSION,
				'all'
			);
		}

		// Manage CTAs page styles (minimized - compiled from SCSS)
		// Includes: manage_ctas page-specific styles
		if ( 'cta-manager-cta' === $page ) {
			wp_enqueue_style(
				'cta-manage-ctas',
				CTA_PLUGIN_URL . 'assets/minimized/css/admin/manage_ctas.min.css',
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
				CTA_PLUGIN_URL . 'assets/minimized/css/admin/settings.min.css',
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
				CTA_PLUGIN_URL . 'assets/minimized/css/admin/tools.min.css',
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

		// Global admin bundle - includes: tabs, modals, toast, notifications, onboarding, pro-upsell, docs-modal, admin-bar-responsive
		wp_enqueue_script(
			'cta-admin',
			CTA_PLUGIN_URL . 'assets/minimized/js/admin/admin.min.js',
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
				CTA_PLUGIN_URL . 'assets/minimized/js/admin/manage-ctas.min.js',
				[ 'jquery', 'cta-admin' ],
				CTA_VERSION,
				true
			);
		}

		// Settings page bundle
		if ( 'cta-manager-settings' === $page ) {
			wp_enqueue_script(
				'cta-settings',
				CTA_PLUGIN_URL . 'assets/minimized/js/admin/settings.min.js',
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

		// Tools page bundle
		if ( 'cta-manager-tools' === $page ) {
			$tools_script_path = CTA_PLUGIN_DIR . 'assets/minimized/js/admin/tools.min.js';
			$tools_script_ver  = file_exists( $tools_script_path ) ? (string) filemtime( $tools_script_path ) : CTA_VERSION;

			wp_enqueue_script(
				'cta-tools',
				CTA_PLUGIN_URL . 'assets/minimized/js/admin/tools.min.js',
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

		// Features page bundle
		if ( 'cta-manager-features' === $page ) {
			wp_enqueue_script(
				'cta-features',
				CTA_PLUGIN_URL . 'assets/minimized/js/admin/features.min.js',
				[ 'jquery', 'cta-admin' ],
				CTA_VERSION,
				true
			);
		}

		// Support page bundle
		if ( 'cta-manager-support' === $page ) {
			wp_enqueue_script(
				'cta-support',
				CTA_PLUGIN_URL . 'assets/minimized/js/admin/support.min.js',
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
	 * Add action links to plugin page.
	 *
	 * Reads submenu items dynamically so the links stay in sync with the
	 * registered admin menu. The Pro upgrade / license link is appended last
	 * (before the WP-provided Deactivate link) with the same gold colour used
	 * in the admin sidebar.
	 *
	 * @param array $links Existing action links (contains Deactivate).
	 * @return array
	 */
	public function add_action_links( array $links ): array {
		global $submenu;

		$new_links  = [];
		$parent     = CTA_Admin_Menu::MENU_SLUG;
		$pro_slug   = CTA_Admin_Menu::PRO_MENU_SLUG;
		$pro_config = CTA_Admin_Menu::get_pro_menu_config();

		// Shorter labels for the Plugins page action links row.
		$label_overrides = [
			'Manage CTAs' => 'Manage',
		];

		// Build links from the live submenu — same order as the WP admin sidebar.
		if ( ! empty( $submenu[ $parent ] ) ) {
			foreach ( $submenu[ $parent ] as $item ) {
				$item_slug  = $item[2] ?? '';
				$item_label = strip_tags( $item[0] ?? '' );

				// Skip empty, missing capability, or the Pro placeholder page.
				if ( ! $item_slug || ! $item_label || $item_slug === $pro_slug ) {
					continue;
				}

				$item_label = $label_overrides[ $item_label ] ?? $item_label;

				$new_links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'admin.php?page=' . $item_slug ) ),
					esc_html( $item_label )
				);
			}
		}

		// Pro upgrade / license link — bold, gold to match the admin sidebar colour.
		if ( $pro_config ) {
			$link_classes = [ 'cta-pro-action-link' ];
			$link_classes[] = 'add_license' === $pro_config['type'] ? 'cta-add-api-key-button' : 'cta-upgrade-button';

			$data_attrs = '';
			if ( ! empty( $pro_config['scroll_to'] ) ) {
				$data_attrs .= ' data-scroll-to="' . esc_attr( $pro_config['scroll_to'] ) . '"';
			}
			if ( ! empty( $pro_config['focus_field'] ) ) {
				$data_attrs .= ' data-focus-field="' . esc_attr( $pro_config['focus_field'] ) . '"';
			}

			$pro_label = 'add_license' === $pro_config['type']
				? __( 'Add License', 'cta-manager' )
				: $pro_config['label'];

			$new_links[] = sprintf(
				'<a href="%s" class="%s" style="color:#f0b849;font-weight:bold;"%s>%s</a>',
				esc_url( $pro_config['target_url'] ),
				esc_attr( implode( ' ', $link_classes ) ),
				$data_attrs,
				esc_html( $pro_label )
			);
		}

		return array_merge( $new_links, $links );
	}

}
