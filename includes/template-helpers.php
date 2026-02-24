<?php
/**
 * Template Helpers
 *
 * Handles reusable template helper functions for CTA Manager rendering.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cta_load_partial' ) ) {
	/**
	 * Load a partial template with variables.
	 *
	 * @param string $partial_name Partial name (e.g., 'pro-badge', 'upgrade-notice').
	 * @param array  $vars         Variables to extract into template scope.
	 * @param bool   $echo         Whether to echo or return output.
	 * @return string|void
	 */
	function cta_load_partial( string $partial_name, array $vars = [], bool $echo = true ) {
		$partial_path = CTA_PLUGIN_DIR . 'templates/admin/partials/' . $partial_name . '.php';

		if ( ! file_exists( $partial_path ) ) {
			return $echo ? '' : '';
		}

		extract( $vars, EXTR_SKIP );

		if ( ! $echo ) {
			ob_start();
		}

		include $partial_path;

		if ( ! $echo ) {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'cta_pro_badge' ) ) {
	/**
	 * Render a Pro badge.
	 *
	 * @param string $style       Badge style: 'inline', 'large', 'minimal'.
	 * @param string $text        Badge text (default: 'Pro').
	 * @param bool   $echo        Whether to echo or return.
	 * @return string|void
	 */
	function cta_pro_badge( string $style = 'inline', string $text = '', bool $echo = true ) {
		if ( empty( $text ) ) {
			$text = __( 'Pro', 'cta-manager' );
		}

		return cta_load_partial( 'pro-badge', [
			'badge_style' => $style,
			'custom_text' => $text,
		], $echo );
	}
}

if ( ! function_exists( 'cta_upgrade_notice' ) ) {
	/**
	 * Render an upgrade notice.
	 *
	 * @param array $args {
	 *     Optional. Upgrade notice arguments.
	 *
	 *     @type string $icon        Dashicon name (default: 'star-filled').
	 *     @type string $title       Notice title.
	 *     @type string $message     Notice message.
	 *     @type string $button_url  Upgrade URL.
	 *     @type string $button_text Button text.
	 *     @type string $extra_class Additional CSS classes.
	 * }
	 * @param bool  $echo Whether to echo or return.
	 * @return string|void
	 */
	function cta_upgrade_notice( array $args = [], bool $echo = true ) {
		$defaults = [
			'icon'        => 'star-filled',
			'title'       => __( 'Upgrade to Pro', 'cta-manager' ),
			'message'     => '',
			'button_url'  => admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ),
			'button_text' => __( 'Upgrade Now', 'cta-manager' ),
			'extra_class' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		return cta_load_partial( 'upgrade-notice', $args, $echo );
	}
}

if ( ! function_exists( 'cta_promo_banner' ) ) {
	/**
	 * Render a promo banner.
	 *
	 * @param array $config {
	 *     Promo banner configuration.
	 *
	 *     @type string       $icon_html     Icon HTML/SVG.
	 *     @type string       $title         Banner title.
	 *     @type string       $description   Banner description.
	 *     @type string       $button_text   Button text.
	 *     @type string       $button_url    Button URL.
	 *     @type string       $button_target Button target (_self|_blank).
	 *     @type string       $button_icon   Dashicon class.
	 *     @type string       $badge         Optional badge text.
	 *     @type array|string $classes       Extra CSS classes.
	 * }
	 * @param bool  $echo Whether to echo or return.
	 * @return string|void
	 */
	function cta_promo_banner( array $config, bool $echo = true ) {
		return cta_load_partial( 'promo-banner', [ 'config' => $config ], $echo );
	}
}

if ( ! function_exists( 'cta_pro_cta' ) ) {
	/**
	 * Render a Pro call-to-action.
	 *
	 * @param array $args {
	 *     Optional. Pro CTA arguments.
	 *
	 *     @type string $cta_style    CTA style: 'standard', 'card', 'inline', 'banner'.
	 *     @type string $feature_name Feature name being promoted.
	 *     @type string $upgrade_url  Upgrade URL.
	 *     @type bool   $show_details Show detailed feature explanation.
	 * }
	 * @param bool  $echo Whether to echo or return.
	 * @return string|void
	 */
	function cta_pro_cta( array $args = [], bool $echo = true ) {
		$defaults = [
			'cta_style'    => 'standard',
			'feature_name' => __( 'This Feature', 'cta-manager' ),
			'upgrade_url'  => admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' ),
			'show_details' => true,
		];

		$args = wp_parse_args( $args, $defaults );

		return cta_load_partial( 'pro-cta', $args, $echo );
	}
}

if ( ! function_exists( 'cta_pro_badge_inline' ) ) {
	/**
	 * Output an inline Pro badge
	 *
	 * @param string|null $custom_text Optional custom text for badge
	 * @return void
	 */
	function cta_pro_badge_inline( $custom_text = null ) {
		$badge_style = 'inline';
		$custom_text = $custom_text ?? __( 'PRO', 'cta-manager' );
		include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-badge.php';
	}
}

if ( ! function_exists( 'cta_error_alert' ) ) {
	/**
	 * Output an error alert with icon
	 *
	 * @param string $id      Unique ID for the error element
	 * @param string $message The error message to display
	 * @param bool   $show    Whether to show the error initially (default: false)
	 * @param string $icon    Dashicon name without 'dashicons-' prefix (default: 'warning')
	 * @return void
	 */
	function cta_error_alert( $id, $message, $show = false, $icon = 'warning' ) {
		$error_id      = $id;
		$error_message = $message;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/error-alert.php';
	}
}

if ( ! function_exists( 'cta_form_section_header' ) ) {
	/**
	 * Output a form section header with icon
	 *
	 * @param string $icon            Dashicon name without 'dashicons-' prefix
	 * @param string $title           Section title text
	 * @param bool   $show_pro_badge  Whether to show Pro badge (default: false)
	 * @return void
	 */
	function cta_form_section_header( $icon, $title, $show_pro_badge = false ) {
		include CTA_PLUGIN_DIR . 'templates/admin/partials/form-section-header.php';
	}
}

if ( ! function_exists( 'cta_pro_gated_select' ) ) {
	/**
	 * Render a Pro-gated select field with badge and error messaging.
	 *
	 * @param array $args {
	 *     Arguments for rendering the select.
	 *
	 *     @type string $select_id       Required. Select element ID.
	 *     @type string $select_name     Optional. Select name attribute (defaults to $select_id).
	 *     @type array  $options         Required. Array of option/optgroup configs (see partial for shape).
	 *     @type string $selected_value  Optional. Selected value.
	 *     @type string $error_message   Optional. Error message text.
	 *     @type string $error_id        Optional. Error element ID.
	 *     @type string $badge_id        Optional. Badge element ID.
	 *     @type bool   $is_pro          Optional. Whether current user has Pro.
	 *     @type bool   $show_badge      Optional. Whether to render the badge.
	 *     @type string $select_class    Optional. CSS classes for select.
	 *     @type string $select_attrs    Optional. Raw attributes to append to select.
	 *     @type string $wrapper_class   Optional. Extra wrapper classes.
	 * }
	 * @return void
	 */
	function cta_pro_gated_select( array $args ) {
		$defaults = [
			'select_id'      => '',
			'select_name'    => '',
			'options'        => [],
			'selected_value' => '',
			'error_message'  => '',
			'error_id'       => '',
			'badge_id'       => '',
			'is_pro'         => false,
			'show_badge'     => true,
			'select_class'   => 'cta-select',
			'select_attrs'   => '',
			'wrapper_class'  => '',
		];

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-gated-select.php';
	}
}

if ( ! function_exists( 'cta_button_with_icon' ) ) {
	/**
	 * Output a button with dashicon
	 *
	 * @param string $button_text Button text content
	 * @param string $icon Dashicon name without 'dashicons-' prefix
	 * @param string $button_class Button CSS class (default: 'cta-button-primary')
	 * @param string $button_id Button ID attribute
	 * @param string $button_type Button type attribute (default: 'button')
	 * @return void
	 */
	function cta_button_with_icon( $button_text, $icon, $button_class = 'cta-button-primary', $button_id = '', $button_type = 'button' ) {
		include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-icon.php';
	}
}

if ( ! function_exists( 'cta_toggle_switch' ) ) {
	/**
	 * Output a toggle switch
	 *
	 * @param string $input_name Input name attribute.
	 * @param string $label Toggle label text.
	 * @param bool   $checked Whether toggle is checked (default: false).
	 * @param string $input_id Input ID attribute.
	 * @param array  $args {
	 *     Optional. Extra configuration.
	 *
	 *     @type string $input_value  Checkbox value (default: '1').
	 *     @type string $extra_class  Additional wrapper classes.
	 *     @type string $on_text      Text for "on" state.
	 *     @type string $off_text     Text for "off" state.
	 *     @type string $input_attrs  Additional attributes for the input tag.
	 *     @type string $wrapper_attrs Additional attributes for the label wrapper.
	 *     @type string $size         Toggle size: 'regular' or 'small'. Default 'regular'.
	 *     @type bool   $show_status  Whether to render the on/off status text. Default true.
	 * }
	 * @return void
	 */
	function cta_toggle_switch( $input_name, $label, $checked = false, $input_id = '', $args = [] ) {
		$defaults = [
			'input_value'   => '1',
			'extra_class'   => '',
			'on_text'       => __( 'On', 'cta-manager' ),
			'off_text'      => __( 'Off', 'cta-manager' ),
			'input_attrs'   => '',
			'wrapper_attrs' => '',
			'size'          => 'regular',
			'show_status'   => true,
		];

		$args = wp_parse_args( $args, $defaults );

		$input_value   = $args['input_value'];
		$extra_class   = $args['extra_class'];
		$on_text       = $args['on_text'];
		$off_text      = $args['off_text'];
		$input_attrs   = $args['input_attrs'];
		$wrapper_attrs = $args['wrapper_attrs'];
		$size          = $args['size'];
		$show_status   = $args['show_status'];

		include CTA_PLUGIN_DIR . 'templates/admin/partials/toggle-switch.php';
	}
}

if ( ! function_exists( 'cta_helper_text' ) ) {
	/**
	 * Output helper text with icon
	 *
	 * @param string $text The helper text to display
	 * @param string $icon Dashicon name without 'dashicons-' prefix (default: 'info')
	 * @param string $variant Display variant: 'inline' or 'block' (default: 'inline')
	 * @param string $extra_class Additional CSS classes (default: '')
	 * @return void
	 */
	function cta_helper_text( $text, $icon = 'info', $variant = 'inline', $extra_class = '' ) {
		include CTA_PLUGIN_DIR . 'templates/admin/partials/helper-text.php';
	}
}
