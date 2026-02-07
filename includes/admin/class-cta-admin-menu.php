<?php
/**
 * Admin menu registration
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Admin_Menu {

	use CTA_Singleton;

	public const MENU_SLUG     = 'cta-manager';
	public const PRO_MENU_SLUG = 'cta-manager-pro-upgrade';
	public const REQUIRED_CAP = 'manage_options';

	/**
	 * Register admin menus
	 *
	 * @return void
	 */
	public function register_menus(): void {
		$icon_svg = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1NzAgNTcwIiB3aWR0aD0iNTcwIiBoZWlnaHQ9IjU3MCI+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCw1NzApIHNjYWxlKDAuMSwtMC4xKSIgZmlsbD0iI2ZmZmZmZiI+CiAgICA8cGF0aCBkPSJNMjcwNiA1NTIwIGMtOCAtMTkgLTQ1IC0xMTQgLTgwIC0yMTAgLTM2IC05NiAtNzEgLTE5MSAtODAgLTIxMSBsLTE1IC0zNyAtOTggLTYgYy01NCAtMyAtMTc3IC0xMSAtMjczIC0xNyAtOTYgLTYgLTE3NiAtMTIgLTE3OCAtMTQgLTQgLTMgMzYzIC0yNzkgNDA2IC0zMDYgMTIgLTggMjIgLTE5IDIyIC0yNSAwIC02IC0zNiAtMTE0IC03OSAtMjQwIC00NCAtMTI2IC03OCAtMjMxIC03NSAtMjMzIDIgLTIgMTAyIDYxIDIyMSAxNDEgMTIwIDc5IDIyMyAxNDcgMjI5IDE1MSA2IDMgMTE2IC02MyAyNDUgLTE0OCAxMjggLTg0IDIzNSAtMTUzIDIzNiAtMTUxIDIgMiAtMzMgMTA2IC03OCAyMzIgLTQ0IDEyNiAtODEgMjM1IC04MiAyNDMgLTIgMTMgMjE0IDE4MyAzNjAgMjgzIDM1IDI0IDYzIDQ2IDYzIDUwIDAgOCAtOTUgMTYgLTM0NCAyOCAtMTA5IDUgLTIwMSAxMSAtMjAzIDE0IC02IDYgLTEwMSAyNTkgLTE0MyAzODEgbC0zOCAxMTAgLTE2IC0zNXoiLz4KICAgIDxwYXRoIGQ9Ik0xMzk0IDQ4NjMgbC02MSAtMTY4IC0xMTkgLTggYy02NiAtNCAtMTQxIC0xMCAtMTY4IC0xNSBsLTUwIC03IDEzMiAtMTAwIGM3MyAtNTUgMTMyIC0xMDUgMTMyIC0xMTEgMCAtNiAtMjIgLTc4IC00OSAtMTYwIC0yNyAtODIgLTQ3IC0xNTAgLTQ2IC0xNTIgNSAtNiA2NiAyOSAxODIgMTA0IGwxMTUgNzUgMTMxIC04NSBjNzMgLTQ3IDE0MCAtODggMTUwIC05MiAxNCAtNSA3IDIzIC0zOCAxNTIgLTMwIDg3IC01NSAxNjEgLTU1IDE2NSAwIDQgNTkgNTIgMTMxIDEwOCA3MiA1NSAxMjcgMTAxIDEyMiAxMDIgLTQgMCAtODAgNiAtMTY3IDEyIGwtMTU5IDEyIC01NCAxNTAgYy0yOSA4MyAtNTcgMTU4IC02MSAxNjggLTUgMTIgLTI2IC0zNCAtNjggLTE1MHoiLz4KICAgIDxwYXRoIGQ9Ik0zOTgzIDUwMDAgYy02IC0xNCAtMzQgLTg4IC02MiAtMTY2IGwtNTIgLTE0MSAtMTE3IC02IGMtNjQgLTQgLTEzOCAtMTAgLTE2NCAtMTMgbC00NyAtNyAzOCAtMzEgYzIxIC0xOCA3OSAtNjMgMTMwIC0xMDEgNTAgLTM4IDkxIC03MiA5MSAtNzYgMCAtNCAtMjQgLTc3IC01NCAtMTYzIC0yOSAtODYgLTUxIC0xNTYgLTQ5IC0xNTYgMiAwIDYwIDM2IDEyOSA4MCAxNjggMTA3IDE2MiAxMDUgMTgyIDg3IDI4IC0yNCAyNjQgLTE2OSAyNjggLTE2NCAyIDIgLTE4IDcxIC00NiAxNTIgLTI3IDgyIC01MCAxNTQgLTUwIDE2MCAwIDEyIDI4IDM2IDE4MCAxNTIgbDc0IDU4IC0zOSA3IGMtMjIgMyAtOTMgMTAgLTE1NyAxNCAtNjYgNCAtMTIyIDEyIC0xMjcgMTggLTYgOCAtODEgMjA5IC0xMTQgMzA2IC0zIDExIC03IDggLTE0IC0xMHoiLz4KICAgIDxwYXRoIGQ9Ik0yMzYwIDQwMTggYy0xMjQgLTc2IC0yNTQgLTE1NiAtMjkwIC0xNzggLTMxMSAtMTg1IC0xMDAxIC02NTQgLTEzNTIgLTkxOCBsLTEyOCAtOTYgMCAtNDA4IGMwIC0yMjQgMyAtNDA4IDYgLTQwOCAzIDAgNDQgMjggOTIgNjIgNDggMzQgMzg2IDI2MiA3NTIgNTA2IDM2NiAyNDQgNjczIDQ1MiA2ODIgNDYxIDE2IDE1IDc2IDE1NSAyNTMgNTkxIDM0IDg1IDk3IDIzOCAxMzkgMzM5IDQzIDEwMSA3NiAxODUgNzQgMTg2IC0yIDIgLTEwNCAtNjAgLTIyOCAtMTM3eiIvPgogICAgPHBhdGggZD0iTTI4NTMgNDEzMyBjMTQgLTQ3IDQwMCAtMTAxNCA0MTkgLTEwNTIgMjAgLTM5IDQ3IC01OSAyMzggLTE4MyA0NzggLTMwOSAxMTI3IC03MzggMTI0MyAtODIyIDQzIC0zMSA4MCAtNTYgODMgLTU2IDIgMCAzIDE4MSAyIDQwMiBsLTMgNDAyIC0xMjAgOTMgYy0yNjQgMjA1IC04MzggNjAxIC0xMTg5IDgyMSAtNjcgNDEgLTEzMiA4MyAtMTQ2IDkzIC0xNCA5IC0xMTkgNzQgLTIzNSAxNDQgLTExNSA3MCAtMjI2IDEzOCAtMjQ2IDE1MSAtNDIgMjkgLTUzIDMwIC00NiA3eiIvPgogICAgPHBhdGggZD0iTTI5OTQgMjgyNCBjNCAtNDMgMTggLTE3OCAzMSAtMjk5IDEzIC0xMjEgMjkgLTI3MiAzNSAtMzM1IDggLTgzIDE2IC0xMjAgMjggLTEzMiAxNyAtMTYgMTExIC04OSA1MTggLTQwMCAxMjEgLTkyIDI1NyAtMTk3IDMwNCAtMjMzIDI1NCAtMTk1IDYwNCAtNDU1IDYxMyAtNDU1IDkgMCAyMjcgNTUzIDIyNyA1NzYgMCAxMCAtMTQxIDEyOSAtMjg1IDI0MCAtMjIgMTcgLTg3IDY4IC0xNDUgMTEzIC0yMjIgMTc1IC0xMjc0IDk2OCAtMTMxOSA5OTYgLTEyIDcgLTEzIC01IC03IC03MXoiLz4KICAgIDxwYXRoIGQ9Ik0yMzQ0IDI4MzIgYy05OSAtNzkgLTI4MyAtMjIzIC00MDAgLTMxMyAtNTUgLTQxIC0yMjkgLTE3NyAtMzg5IC0zMDAgLTE1OSAtMTI0IC00MDAgLTMxMCAtNTM1IC00MTQgLTI3MCAtMjA5IC0zMjAgLTI1MSAtMzIwIC0yNjkgMCAtMzEgMjA4IC01NjYgMjIxIC01NjkgMyAtMSA1MyAzNCAxMTAgNzkgMzIyIDI1MCA4OTMgNjkwIDEwODQgODM3IDEyMSA5MiAyMjggMTc1IDIzOCAxODMgMTIgOSAxNyAyOCAxNyA2MSAwIDI3IDE0IDE5OSAzMCAzODMgMzMgMzY5IDMzIDM4MCAyMyAzNzkgLTUgMCAtNDAgLTI2IC03OSAtNTd6Ii8+CiAgICA8cGF0aCBkPSJNMjU4OCAyMDMwIGMtMjkxIC0yNDQgLTE0NzggLTEzMzkgLTE0NzggLTEzNjQgMCAtMTggMzQ1IC0zOTYgMzYxIC0zOTYgNSAwIDcxIDQ4IDE0NyAxMDcgMzI1IDI1MyA1NDEgNDE5IDc1MiA1NzcgMTI0IDkzIDIzNyAxODEgMjUzIDE5NSBsMjcgMjcgLTIgNDUxIC0zIDQ1MSAtNTcgLTQ4eiIvPgogICAgPHBhdGggZD0iTTI3OTAgMTYyMyBsMSAtNDU4IDQ3NyAtMzU3IGMyNjIgLTE5NiA1MjcgLTM5NSA1ODcgLTQ0MiA2MSAtNDcgMTEzIC04NiAxMTcgLTg2IDE1IDAgMzU3IDM2OSAzNTggMzg2IDAgMTAgLTMzIDQyIC0yODAgMjc5IC0xMDEgOTcgLTQ1OCA0MjMgLTY3NyA2MTggLTczIDY0IC0xOTYgMTc3IC0yNzUgMjUwIC0xODYgMTcyIC0yOTQgMjY3IC0zMDIgMjY3IC0zIDAgLTYgLTIwNiAtNiAtNDU3eiIvPgogIDwvZz4KPC9zdmc+Cg==';

		add_menu_page(
			__( 'CTA Manager', 'cta-manager' ),
			__( 'CTA Manager', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG,
			[ CTA_Dashboard::get_instance(), 'render' ],
			$icon_svg,
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'CTA Manager Dashboard', 'cta-manager' ),
			__( 'Dashboard', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG,
			[ CTA_Dashboard::get_instance(), 'render' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Manage CTAs', 'cta-manager' ),
			__( 'Manage CTAs', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG . '-cta',
			[ CTA_Manager::get_instance(), 'render' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'CTA Manager Analytics', 'cta-manager' ),
			__( 'Analytics', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG . '-analytics',
			[ CTA_Analytics_Dashboard::get_instance(), 'render' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'CTA Manager Settings', 'cta-manager' ),
			__( 'Settings', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG . '-settings',
			[ CTA_Settings::get_instance(), 'render' ]
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'CTA Manager Tools', 'cta-manager' ),
			__( 'Tools', 'cta-manager' ),
			self::REQUIRED_CAP,
			self::MENU_SLUG . '-tools',
			[ CTA_Tools::get_instance(), 'render' ]
		);

		$pro_config = self::get_pro_menu_config();
		if ( $pro_config ) {
			$menu_label_html = '<span style="display: inline-flex; align-items: center; font-weight: 600; color: #f0b849;"><span class="dashicons dashicons-' . esc_attr( $pro_config['icon'] ) . '" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px;"></span>' . esc_html( $pro_config['label'] ) . '</span>';

			add_submenu_page(
				self::MENU_SLUG,
				$pro_config['label'],
				$menu_label_html,
				self::REQUIRED_CAP,
				self::PRO_MENU_SLUG,
				[ self::class, 'handle_pro_menu_redirect' ]
			);
		}

	}

	/**
	 * Check if current page is a plugin page
	 *
	 * @return bool
	 */
	public static function is_plugin_page(): bool {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : null;
		return $page && strpos( $page, self::MENU_SLUG ) === 0;
	}

	/**
	 * Get plugin admin URL
	 *
	 * @param string $page Page name
	 *
	 * @return string
	 */
	public static function get_admin_url( string $page = '' ): string {
		$slug = empty( $page ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $page;
		return admin_url( 'admin.php?page=' . $slug );
	}

	/**
	 * Get configuration for the Pro upgrade/add-license menu action.
	 *
	 * @return array|null
	 */
	public static function get_pro_menu_config(): ?array {
		$is_pro_enabled = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( $is_pro_enabled ) {
			return null;
		}

		$pro_plugin_file = 'cta-manager-pro/cta-manager-pro.php';

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$is_pro_active  = is_plugin_active( $pro_plugin_file );
		$has_license_key = false;
		if ( $is_pro_active && class_exists( 'CTA_Pro_Feature_Gate' ) ) {
			$has_license_key = ! empty( CTA_Pro_Feature_Gate::get_license_key() );
		}

		if ( $is_pro_active && ! $has_license_key ) {
			$label      = __( 'Add License Key', 'cta-manager' );
			$icon       = 'unlock';
			$target_url = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
			$type       = 'add_license';
		} else {
			$label      = __( 'Upgrade to Pro', 'cta-manager' );
			$icon       = 'star-filled';
			$target_url = admin_url( 'admin.php?page=cta-manager&modal=pro-upgrade' );
			$type       = 'upgrade';
		}

		$scroll_to = '';
		$focus_field = '';

		if ( 'add_license' === $type ) {
			$scroll_to  = 'cta-pro-license-key';
			$focus_field = 'cta_pro_license_key';
		}

		return [
			'type'        => $type,
			'label'       => $label,
			'icon'        => $icon,
			'target_url'  => $target_url,
			'scroll_to'   => $scroll_to,
			'focus_field' => $focus_field,
			'menu_href'   => admin_url( 'admin.php?page=' . self::PRO_MENU_SLUG ),
			'menu_slug'   => self::PRO_MENU_SLUG,
		];
	}

	/**
	 * Redirects the temporary Pro menu entry to the configured target URL.
	 *
	 * @return void
	 */
	public static function handle_pro_menu_redirect(): void {
		$pro_config = self::get_pro_menu_config();
		$target_url = $pro_config['target_url'] ?? admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
		wp_safe_redirect( $target_url );
		exit;
	}

	/**
	 * Register admin bar menu
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance
	 *
	 * @return void
	 */
	public function register_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( self::REQUIRED_CAP ) ) {
			return;
		}

		$wp_admin_bar->add_node( [
			'id'    => 'cta-manager',
			'title' => '<span class="ab-icon dashicons dashicons-megaphone" style="font-family: dashicons; font-size: 20px; line-height: 1; margin-right: 6px;"></span>' . __( 'CTA Manager', 'cta-manager' ),
			'href'  => admin_url( 'admin.php?page=' . self::MENU_SLUG ),
			'meta'  => [
				'title' => __( 'CTA Manager', 'cta-manager' ),
			],
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-dashboard',
			'parent' => 'cta-manager',
			'title'  => __( 'Dashboard', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-manage',
			'parent' => 'cta-manager',
			'title'  => __( 'Manage CTAs', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG . '-cta' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-add-new',
			'parent' => 'cta-manager',
			'title'  => __( 'Add New CTA', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG . '-cta&action=new' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-analytics',
			'parent' => 'cta-manager',
			'title'  => __( 'Analytics', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG . '-analytics' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-settings',
			'parent' => 'cta-manager',
			'title'  => __( 'Settings', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG . '-settings' ),
		] );

		$wp_admin_bar->add_node( [
			'id'     => 'cta-manager-tools',
			'parent' => 'cta-manager',
			'title'  => __( 'Tools', 'cta-manager' ),
			'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG . '-tools' ),
		] );
	}

}
