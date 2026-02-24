<?php
/**
 * Shortcode Handler
 *
 * Handles shortcode registration and shortcode rendering behavior.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Shortcode {

	use CTA_Singleton;

	/**
	 * Initialize hooks
	 */
	protected function __construct() {
		// Register filter for rendering buttons (used by Pro plugin for card layouts)
		add_filter( 'cta_render_button', [ $this, 'render_button_only' ], 10, 2 );
		// Ensure shortcode renders inside block content (e.g., Custom HTML or custom blocks)
		add_filter( 'render_block', [ $this, 'render_shortcodes_in_blocks' ], 9, 2 );
	}

	/**
	 * Render CTA shortcodes inside block output when present.
	 *
	 * @param string $block_content Rendered block HTML
	 * @param array  $block         Block data
	 *
	 * @return string
	 */
	public function render_shortcodes_in_blocks( string $block_content, array $block ): string {
		if ( strpos( $block_content, '[cta-manager' ) === false ) {
			return $block_content;
		}

		return do_shortcode( $block_content );
	}

	/**
	 * Render just the button HTML (used by card layouts)
	 *
	 * @param string $html Current HTML (usually empty)
	 * @param array  $cta CTA data
	 *
	 * @return string Button HTML
	 */
	public function render_button_only( string $html, array $cta ): string {
		$type        = $cta['type'] ?? 'phone';
		$button_text = $cta['button_text'] ?: __( 'Call Now', 'cta-manager' );

		$href = '#';
		$attr = [];

		switch ( $type ) {
			case 'link':
				$href = ! empty( $cta['link_url'] ) ? $cta['link_url'] : '#';
				$link_target = $cta['link_target'] ?? '_self';
				$new_tab = $cta['link_target_new_tab'] ?? false;
				if ( '_blank' === $link_target || $new_tab ) {
					$attr['target'] = '_blank';
					$attr['rel']    = 'noopener';
				}
				break;
			case 'email':
				$href = ! empty( $cta['email_to'] ) ? 'mailto:' . $cta['email_to'] : '#';
				break;
			case 'phone':
			default:
				$phone_tel = preg_replace( '/[^0-9+]/', '', $cta['phone_number'] ?? '' );
				$href      = $phone_tel ? 'tel:' . $phone_tel : '#';
				break;
		}

		$button_classes = [ 'cta-button' ];

		// Add custom CTA classes if provided
		if ( ! empty( $cta['cta_classes'] ) ) {
			$custom_classes = preg_split( '/\s*,\s*/', trim( $cta['cta_classes'] ) );
			foreach ( $custom_classes as $custom_class ) {
				$custom_class = sanitize_html_class( $custom_class );
				if ( '' !== $custom_class ) {
					$button_classes[] = $custom_class;
				}
			}
		}

		$data_attributes = [
			'data-cta-button' => 'true',
			'data-cta-id'     => (string) ( $cta['id'] ?? '' ),
			'data-cta-type'   => $type,
		];

		// Generate inline styles for the button
		$button_styles = $this->generate_button_styles( $cta );
		$text_styles = $this->generate_text_styles( $cta );

		return $this->render_partial_template(
			'button',
			[
				'href'    => $href,
				'classes' => $button_classes,
				'id'      => ! empty( $cta['cta_html_id'] ) ? sanitize_html_class( $cta['cta_html_id'] ) : '',
				'label'   => $button_text,
				'attr'    => $attr,
				'data'    => $data_attributes,
				'extra_data_attributes' => '',
				'icon_html' => '',
				'icon_classes' => [],
				'button_styles' => $button_styles,
				'text_styles' => $text_styles,
			]
		);
	}

	/**
	 * Render shortcode
	 *
	 * @param array $atts Shortcode attributes
	 *
	 * @return string
	 */
	public function render( array $atts = [] ): string {
		$data = CTA_Data::get_instance();

		// Parse shortcode attributes
		$atts = shortcode_atts(
			[
				'id'   => 0,
				'name' => '',
			],
			$atts,
			'cta-manager'
		);

		// Get CTA by ID or name or use default
		$cta = null;

		if ( ! empty( $atts['id'] ) ) {
			$cta = $data->get_cta( intval( $atts['id'] ) );
		} elseif ( ! empty( $atts['name'] ) ) {
			$cta = $data->get_cta_by_name( sanitize_text_field( $atts['name'] ) );
		} else {
			// Fallback to default CTA
			$cta = $data->get_default_cta();
		}

		if ( ! $cta ) {
			return $this->render_admin_debug_notice( 'No CTA found for this shortcode.' );
		}

		// Check URL targeting (blacklist)
		$allowed = apply_filters( 'cta_url_targeting', true, $cta, home_url( $_SERVER['REQUEST_URI'] ?? '/' ) );
		// Backward compatibility with Pro plugin
		$allowed = apply_filters( 'cta_pro_url_targeting', $allowed, $cta, home_url( $_SERVER['REQUEST_URI'] ?? '/' ) );
		if ( ! $allowed ) {
			return $this->render_admin_debug_notice( 'CTA blocked by URL targeting.' );
		}

		// Check if CTA is enabled
		$enabled = isset( $cta['enabled'] ) ? (bool) $cta['enabled'] : true;
		$status  = $cta['status'] ?? '';
		if ( ! $enabled && in_array( $status, [ 'publish', 'published' ], true ) ) {
			$enabled = true;
		}
		if ( ! $enabled ) {
			return $this->render_admin_debug_notice( 'CTA is disabled.' );
		}

		// Check schedule (start/end dates for date_range type, or business hours for business_hours type)
		if ( ! $this->is_within_schedule( $cta ) ) {
			return $this->render_admin_debug_notice( 'CTA is outside its schedule window.' );
		}

		// Allow Pro plugin to check business hours scheduling
		$allowed = apply_filters( 'cta_pro_business_hours_check', true, $cta );
		if ( ! $allowed ) {
			return $this->render_admin_debug_notice( 'CTA blocked by business hours rules.' );
		}

		// Allow Pro plugin to check device targeting
		$allowed = apply_filters( 'cta_pro_device_targeting', true, $cta );
		if ( ! $allowed ) {
			return $this->render_admin_debug_notice( 'CTA blocked by device targeting rules.' );
		}

		return $this->render_button_from_cta( $cta );
	}

	/**
	 * Render CTA (button or card) from data
	 *
	 * @param array $cta CTA data
	 *
	 * @return string
	 */
	private function render_button_from_cta( array $cta ): string {
		$type        = $cta['type'] ?? 'phone';
		$button_text = $cta['button_text'] ?: __( 'Call Now', 'cta-manager' );
		$layout      = $cta['layout'] ?? 'button';
		$title       = $cta['title'] ?? '';
		$tagline     = $cta['tagline'] ?? '';
		$body_text   = $cta['body_text'] ?? '';
		$cta_id      = $cta['id'] ?? '';

		// Enforce Pro-only types/layouts if Pro not active
		$pro_active = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
		if ( ! $pro_active && in_array( $type, [ 'popup', 'slide-in' ], true ) ) {
			$type = 'phone';
		}
		if ( ! $pro_active && 'button' !== $layout ) {
			$layout = 'button';
		}

		// Check schedule status for data attribute
		$is_schedule_closed = false;
		if ( class_exists( 'CTA_Pro_Schedule' ) && method_exists( 'CTA_Pro_Schedule', 'get_instance' ) ) {
			$gate = class_exists( 'CTA_Pro_Feature_Gate' ) ? CTA_Pro_Feature_Gate::get_instance() : null;
			$feature_enabled = $gate ? $gate->is_feature_enabled( 'schedule_display' ) : false;
			if ( $feature_enabled ) {
				$is_schedule_closed = ! CTA_Pro_Schedule::get_instance()->is_open();
			}
		}

		$href       = '#';
		$attr       = [];
		$data_attributes = [
			'data-cta-id'       => (string) $cta_id,
			'data-cta-title'    => $title,
			'data-cta-tagline'  => $tagline,
			'data-cta-body'     => $body_text,
			'data-cta-type'     => $type,
			'data-cta-phone'    => $cta['phone_number'] ?? '',
			'data-cta-email'    => $cta['email_to'] ?? '',
			'data-cta-link'     => $cta['link_url'] ?? '',
			'data-cta-schedule' => $is_schedule_closed ? 'closed' : 'open',
			'data-cta-label'    => $button_text,
		];

		// Add advanced attributes (wrapper ID, classes, data attributes)
		$data_attributes = apply_filters( 'cta_shortcode_attributes', $data_attributes, $cta );
		// Backward compatibility with Pro plugin
		$data_attributes = apply_filters( 'cta_pro_shortcode_attributes', $data_attributes, $cta );

		// Extract CTA ID and classes from advanced attributes
		$cta_html_id = '';
		if ( isset( $data_attributes['id'] ) ) {
			$cta_html_id = $data_attributes['id'];
			unset( $data_attributes['id'] );
		}

		$cta_custom_classes = [];
		if ( isset( $data_attributes['classes'] ) && is_array( $data_attributes['classes'] ) ) {
			$cta_custom_classes = $data_attributes['classes'];
			unset( $data_attributes['classes'] );
		}

		switch ( $type ) {
			case 'link':
				$href = ! empty( $cta['link_url'] ) ? $cta['link_url'] : '#';
				// Support both link_target and link_target_new_tab fields
				$link_target = $cta['link_target'] ?? '_self';
				$new_tab = $cta['link_target_new_tab'] ?? false;
				if ( '_blank' === $link_target || $new_tab ) {
					$attr['target'] = '_blank';
					$attr['rel']    = 'noopener';
				}
				break;
			case 'email':
				$href = ! empty( $cta['email_to'] ) ? 'mailto:' . $cta['email_to'] : '#';
				break;
			case 'phone':
			default:
				$phone_tel = preg_replace( '/[^0-9+]/', '', $cta['phone_number'] );
				$href      = $phone_tel ? 'tel:' . $phone_tel : '#';
				break;
		}

		$button_classes = [
			'cta-button',
			'cta-layout-' . $layout,
		];

		// Merge custom CTA classes from Advanced tab
		if ( ! empty( $cta_custom_classes ) ) {
			$button_classes = array_merge( $button_classes, $cta_custom_classes );
		}

		// Generate inline styles for the button
		$button_styles = $this->generate_button_styles( $cta );
		$text_styles = $this->generate_text_styles( $cta );

		$button = $this->render_partial_template(
			'button',
			[
				'href'    => $href,
				'classes' => $button_classes,
				'id'      => $cta_html_id,
				'label'   => $button_text,
				'attr'    => $attr,
				'data'    => $data_attributes,
				'extra_data_attributes' => '',
				'icon_html' => '',
				'icon_classes' => [],
				'button_styles' => $button_styles,
				'text_styles' => $text_styles,
			]
		);

		// Allow Pro plugin to render special CTA types
		$button = apply_filters( 'cta_pro_render_cta_type', $button, $cta, $layout );

		// Simple button layout
		if ( 'button' === $layout ) {
			// Check if wrapper attributes are provided
			$wrapper_id = ! empty( $cta['wrapper_id'] ) ? sanitize_html_class( trim( $cta['wrapper_id'] ) ) : '';
			$wrapper_classes_raw = trim( $cta['wrapper_classes'] ?? '' );

			// If wrapper attributes exist, wrap the button in a div
			if ( '' !== $wrapper_id || '' !== $wrapper_classes_raw ) {
				$wrapper_classes = [];
				if ( '' !== $wrapper_classes_raw ) {
					$wrapper_classes_list = preg_split( '/\s*,\s*/', $wrapper_classes_raw );
					foreach ( $wrapper_classes_list as $wrapper_class ) {
						$wrapper_class = sanitize_html_class( $wrapper_class );
						if ( '' !== $wrapper_class ) {
							$wrapper_classes[] = $wrapper_class;
						}
					}
				}

				$wrapper_id_attr = $wrapper_id ? ' id="' . esc_attr( $wrapper_id ) . '"' : '';
				$wrapper_class_attr = ! empty( $wrapper_classes ) ? ' class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"' : '';

				return '<div' . $wrapper_id_attr . $wrapper_class_attr . '>' . $button . '</div>';
			}

			return $button;
		}

		// Card layouts require Pro - delegate to Pro plugin
		$card_html = apply_filters( 'cta_pro_render_layout', '', $cta, $layout );

		// If Pro plugin didn't handle it, return just the button
		if ( empty( $card_html ) ) {
			return $button;
		}

		return $card_html;
	}

	/**
	 * Render admin notice
	 *
	 * @return string
	 */
	private function render_admin_notice(): string {
		return sprintf(
			'<div class="cta-admin-notice"><strong>%s</strong> %s <a href="%s">%s</a></div>',
			esc_html__( 'CTA Manager:', 'cta-manager' ),
			esc_html__( 'No CTA configured.', 'cta-manager' ),
			esc_url( CTA_Admin_Menu::get_admin_url( 'cta' ) ),
			esc_html__( 'Create a CTA', 'cta-manager' )
		);
	}

	/**
	 * Render admin-only debug notice on the frontend.
	 *
	 * @param string $reason Reason for not rendering.
	 *
	 * @return string
	 */
	private function render_admin_debug_notice( string $reason ): string {
		// Log to debug.log if debug mode enabled
		CTA_Debug::log( $reason, 'shortcode' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		return sprintf(
			'<div class="cta-admin-notice"><strong>%s</strong> %s</div>',
			esc_html__( 'CTA Manager:', 'cta-manager' ),
			esc_html( $reason )
		);
	}

	/**
	 * Render a public partial template
	 *
	 * @param string $template Template name (without extension)
	 * @param array  $context  Data context for the template
	 *
	 * @return string
	 */
	private function render_partial_template( string $template, array $context ): string {
		$template_path = CTA_PLUGIN_DIR . 'templates/public/partials/' . $template . '.php';
		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		ob_start();
		$cta_context = $context;
		include $template_path;
		return trim( ob_get_clean() );
	}

	/**
	 * Check if CTA is within its scheduled display period
	 *
	 * @param array $cta CTA data
	 *
	 * @return bool True if CTA should be displayed, false if outside schedule
	 */
	private function is_within_schedule( array $cta ): bool {
		$today = wp_date( 'Y-m-d' );

		// Check start date - if set, CTA should not show before this date
		$schedule_start = $cta['schedule_start'] ?? '';
		if ( ! empty( $schedule_start ) && $today < $schedule_start ) {
			return false;
		}

		// Check end date - if set, CTA should not show after this date
		$schedule_end = $cta['schedule_end'] ?? '';
		if ( ! empty( $schedule_end ) && $today > $schedule_end ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate inline styles for button element
	 *
	 * @param array $cta CTA data
	 *
	 * @return string CSS style string
	 */
	private function generate_button_styles( array $cta ): string {
		$styles = [];

		// Background styling
		$background_type = $cta['background_type'] ?? 'solid';

		switch ( $background_type ) {
			case 'solid':
				$bg_color = $cta['color'] ?? '#667eea';
				$styles[] = 'background-color: ' . esc_attr( $bg_color );
				$styles[] = 'background-image: none';
				break;

			case 'gradient':
				$gradient_type = $cta['gradient_type'] ?? 'linear';
				$gradient_start = $cta['gradient_start'] ?? '#667eea';
				$gradient_end = $cta['gradient_end'] ?? '#764ba2';
				$gradient_angle = $cta['gradient_angle'] ?? '90';
				$gradient_start_position = $cta['gradient_start_position'] ?? '0';
				$gradient_end_position = $cta['gradient_end_position'] ?? '100';

				if ( 'radial' === $gradient_type ) {
					$styles[] = sprintf(
						'background: radial-gradient(circle, %s %s%%, %s %s%%)',
						esc_attr( $gradient_start ),
						esc_attr( $gradient_start_position ),
						esc_attr( $gradient_end ),
						esc_attr( $gradient_end_position )
					);
				} else {
					$styles[] = sprintf(
						'background: linear-gradient(%sdeg, %s %s%%, %s %s%%)',
						esc_attr( $gradient_angle ),
						esc_attr( $gradient_start ),
						esc_attr( $gradient_start_position ),
						esc_attr( $gradient_end ),
						esc_attr( $gradient_end_position )
					);
				}
				break;

			case 'transparent':
				$styles[] = 'background-color: transparent';
				$styles[] = 'background-image: none';
				break;
		}

		// Border styling
		$border_style = $cta['border_style'] ?? 'none';

		if ( 'none' !== $border_style ) {
			$border_color = $cta['border_color'] ?? '#667eea';
			$border_width_top = $cta['border_width_top'] ?? '2px';
			$border_width_right = $cta['border_width_right'] ?? '2px';
			$border_width_bottom = $cta['border_width_bottom'] ?? '2px';
			$border_width_left = $cta['border_width_left'] ?? '2px';

			$styles[] = 'border-style: ' . esc_attr( $border_style );
			$styles[] = 'border-color: ' . esc_attr( $border_color );
			$styles[] = sprintf(
				'border-width: %s %s %s %s',
				esc_attr( $border_width_top ),
				esc_attr( $border_width_right ),
				esc_attr( $border_width_bottom ),
				esc_attr( $border_width_left )
			);

			// Border radius
			$border_radius_top_left = $cta['border_radius_top_left'] ?? '8px';
			$border_radius_top_right = $cta['border_radius_top_right'] ?? '8px';
			$border_radius_bottom_right = $cta['border_radius_bottom_right'] ?? '8px';
			$border_radius_bottom_left = $cta['border_radius_bottom_left'] ?? '8px';

			$styles[] = sprintf(
				'border-radius: %s %s %s %s',
				esc_attr( $border_radius_top_left ),
				esc_attr( $border_radius_top_right ),
				esc_attr( $border_radius_bottom_right ),
				esc_attr( $border_radius_bottom_left )
			);
		} else {
			$styles[] = 'border: none';
			// Still apply border radius even without border
			$border_radius_top_left = $cta['border_radius_top_left'] ?? '8px';
			$border_radius_top_right = $cta['border_radius_top_right'] ?? '8px';
			$border_radius_bottom_right = $cta['border_radius_bottom_right'] ?? '8px';
			$border_radius_bottom_left = $cta['border_radius_bottom_left'] ?? '8px';

			$styles[] = sprintf(
				'border-radius: %s %s %s %s',
				esc_attr( $border_radius_top_left ),
				esc_attr( $border_radius_top_right ),
				esc_attr( $border_radius_bottom_right ),
				esc_attr( $border_radius_bottom_left )
			);
		}

		// Padding
		$padding_top = $cta['padding_top'] ?? '12px';
		$padding_right = $cta['padding_right'] ?? '24px';
		$padding_bottom = $cta['padding_bottom'] ?? '12px';
		$padding_left = $cta['padding_left'] ?? '24px';

		$styles[] = sprintf(
			'padding: %s %s %s %s',
			esc_attr( $padding_top ),
			esc_attr( $padding_right ),
			esc_attr( $padding_bottom ),
			esc_attr( $padding_left )
		);

		// Button width - auto fits content, full takes 100% width
		$button_width = $cta['button_width'] ?? 'auto';
		$styles[] = 'display: flex'; // Block-level flex for margin auto to work
		if ( 'full' === $button_width ) {
			$styles[] = 'width: 100%';
		} else {
			$styles[] = 'width: fit-content';
		}

		// Button alignment - only applies when width is auto (not full width)
		$button_alignment = $cta['button_alignment'] ?? 'center';
		if ( 'full' !== $button_width ) {
			switch ( $button_alignment ) {
				case 'left':
					$styles[] = 'margin-left: 0';
					$styles[] = 'margin-right: auto';
					break;
				case 'right':
					$styles[] = 'margin-left: auto';
					$styles[] = 'margin-right: 0';
					break;
				case 'center':
				default:
					$styles[] = 'margin-left: auto';
					$styles[] = 'margin-right: auto';
					break;
			}
		}

		// Text alignment via justify-content (button is a flex container)
		$alignment = $cta['button_text_alignment'] ?? 'center';
		$alignment_map = [
			'left'   => 'flex-start',
			'center' => 'center',
			'right'  => 'flex-end',
		];
		$justify = $alignment_map[ $alignment ] ?? 'center';
		$styles[] = 'justify-content: ' . esc_attr( $justify );

		return implode( '; ', $styles );
	}

	/**
	 * Generate inline styles for button text
	 *
	 * @param array $cta CTA data
	 *
	 * @return string CSS style string
	 */
	private function generate_text_styles( array $cta ): string {
		$styles = [];

		// Button text formatting
		$font_size = $cta['button_text_font_size'] ?? '16px';
		$font_family = $cta['button_text_font_family'] ?? 'inherit';
		$font_weight = $cta['button_text_font_weight'] ?? '600';
		$color = $cta['button_text_color'] ?? '#ffffff';

		$styles[] = 'font-size: ' . esc_attr( $font_size );

		if ( 'inherit' !== $font_family && ! empty( $font_family ) ) {
			$styles[] = 'font-family: ' . esc_attr( $font_family );
		}

		$styles[] = 'font-weight: ' . esc_attr( $font_weight );
		$styles[] = 'color: ' . esc_attr( $color );

		// Note: text-align removed - alignment is handled via justify-content on button element

		return implode( '; ', $styles );
	}
}
