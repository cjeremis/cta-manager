<?php
/**
 * CTA Manager Core Handler
 *
 * Handles core admin-side CTA management and form processing logic.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Manager {

	use CTA_Singleton;

	/** @var array Fields downgraded from Pro to free defaults on the last save. */
	private array $pro_downgrade_fields = [];

	/**
	 * Validate and clamp a schedule hour value (1–12).
	 */
	private static function sanitize_time_hour( string $value, string $default ): string {
		$int = (int) $value;
		return ( '' !== $value && $int >= 1 && $int <= 12 ) ? (string) $int : $default;
	}

	/**
	 * Validate and clamp a schedule minute value (0–59), zero-padded.
	 */
	private static function sanitize_time_minute( string $value, string $default ): string {
		if ( '' === $value ) {
			return $default;
		}
		$int = (int) $value;
		return ( $int >= 0 && $int <= 59 ) ? str_pad( (string) $int, 2, '0', STR_PAD_LEFT ) : $default;
	}

	/**
	 * Validate a schedule period value against AM/PM whitelist.
	 */
	private static function sanitize_time_period( string $value, string $default ): string {
		return in_array( $value, [ 'AM', 'PM' ], true ) ? $value : $default;
	}

	/**
	 * Validate a CSS dimension value (e.g. 12px, 1.5rem, 50%).
	 * Returns $default when the value does not match the expected format.
	 */
	private static function sanitize_css_dimension( string $value, string $default ): string {
		return preg_match( '/^[0-9]+(\.[0-9]+)?(px|rem|em|%)$/', $value ) ? $value : $default;
	}

	/**
	 * Handle form submissions
	 *
	 * @return void
	 */
	public function handle_form_submission(): void {
		if ( ! isset( $_POST['cta_cta_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cta_cta_nonce'] ) ), 'cta_cta_action' ) ) {
			wp_die( esc_html__( 'Invalid nonce', 'cta-manager' ) );
		}

		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$data = CTA_Data::get_instance();
		$action = isset( $_POST['cta_action'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_action'] ) ) : '';

		switch ( $action ) {
			case 'create':
				$this->handle_create_cta( $data );
				break;
			case 'update':
				$this->handle_update_cta( $data );
				break;
			case 'delete':
				$this->handle_delete_cta( $data );
				break;
		}
	}

	/**
	 * Handle CTA creation
	 *
	 * @param CTA_Data $data Data instance
	 *
	 * @return void
	 */
	private function handle_create_cta( CTA_Data $data ): void {
		$icon = isset( $_POST['cta_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_icon'] ) ) : 'none';
		$link_target = isset( $_POST['link_target_new_tab'] ) ? '_blank' : '_self';
		$data_attributes = [];
		if ( isset( $_POST['cta_data_attribute_key'], $_POST['cta_data_attribute_value'] ) && is_array( $_POST['cta_data_attribute_key'] ) && is_array( $_POST['cta_data_attribute_value'] ) ) {
			$keys = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_data_attribute_key'] ) );
			$values = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_data_attribute_value'] ) );
			foreach ( $keys as $index => $key ) {
				$value = $values[ $index ] ?? '';
				if ( '' === $key && '' === $value ) {
					continue;
				}
				$data_attributes[] = [
					'key'   => $key,
					'value' => $value,
				];
			}
		}
		$blacklist_urls = [];
		if ( isset( $_POST['cta_blacklist_urls'] ) && is_array( $_POST['cta_blacklist_urls'] ) ) {
			$urls = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_blacklist_urls'] ) );
			foreach ( $urls as $pattern ) {
				$pattern = trim( $pattern );
				if ( '' === $pattern ) {
					continue;
				}
				$blacklist_urls[] = $pattern;
			}
		}

		$enabled = $this->resolve_enabled_from_post( true );

		$cta_data = [
			'name'           => isset( $_POST['cta_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_name'] ) ) : '',
			'status'         => isset( $_POST['cta_status'] ) ? CTA_Sanitizer::sanitize_status( wp_unslash( $_POST['cta_status'] ) ) : 'draft',
			'schedule_start' => CTA_Sanitizer::sanitize_schedule_date( $_POST['schedule_start'] ?? '' ),
			'schedule_end'   => CTA_Sanitizer::sanitize_schedule_date( $_POST['schedule_end'] ?? '' ),
			'schedule_type'  => isset( $_POST['schedule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_type'] ) ) : 'date_range',
			'include_times'          => ! empty( $_POST['include_times'] ),
			'schedule_start_hour'    => self::sanitize_time_hour( sanitize_text_field( wp_unslash( $_POST['schedule_start_hour'] ?? '' ) ), '12' ),
			'schedule_start_minute'  => self::sanitize_time_minute( sanitize_text_field( wp_unslash( $_POST['schedule_start_minute'] ?? '' ) ), '00' ),
			'schedule_start_period'  => self::sanitize_time_period( sanitize_text_field( wp_unslash( $_POST['schedule_start_period'] ?? '' ) ), 'AM' ),
			'schedule_end_hour'      => self::sanitize_time_hour( sanitize_text_field( wp_unslash( $_POST['schedule_end_hour'] ?? '' ) ), '11' ),
			'schedule_end_minute'    => self::sanitize_time_minute( sanitize_text_field( wp_unslash( $_POST['schedule_end_minute'] ?? '' ) ), '59' ),
			'schedule_end_period'    => self::sanitize_time_period( sanitize_text_field( wp_unslash( $_POST['schedule_end_period'] ?? '' ) ), 'PM' ),
			'title'          => isset( $_POST['cta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_title'] ) ) : '',
			'tagline'      => isset( $_POST['cta_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_tagline'] ) ) : '',
			'body_text'    => isset( $_POST['cta_body_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cta_body_text'] ) ) : '',
			'type'         => isset( $_POST['cta_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_type'] ) ) : 'phone',
			'layout'       => isset( $_POST['cta_layout'] ) ? CTA_Sanitizer::sanitize_layout( wp_unslash( $_POST['cta_layout'] ) ) : 'button',
			'phone_number' => isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '',
			'email_to'     => isset( $_POST['email_to'] ) ? sanitize_email( wp_unslash( $_POST['email_to'] ) ) : '',
			'link_url'     => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
			'link_target'  => $link_target,
			'button_text'  => isset( $_POST['button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) : 'Call Now',
			'icon'         => $icon,
			'button_animation' => isset( $_POST['button_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['button_animation'] ) ) : 'none',
			'icon_animation' => isset( $_POST['icon_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_animation'] ) ) : 'none',
			'wrapper_id'   => isset( $_POST['wrapper_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_id'] ) ) : '',
			'wrapper_classes' => isset( $_POST['wrapper_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_classes'] ) ) : '',
			'cta_html_id'  => isset( $_POST['cta_html_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_html_id'] ) ) : '',
			'cta_classes'  => isset( $_POST['cta_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_classes'] ) ) : '',
			'aria_label'   => isset( $_POST['cta_aria_label'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_label'] ) ) : '',
			'aria_description' => isset( $_POST['cta_aria_description'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_description'] ) ) : '',
			'aria_controls' => isset( $_POST['cta_aria_controls'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_controls'] ) ) : '',
			'aria_expanded' => isset( $_POST['cta_aria_expanded'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_expanded'] ) ) : '',
			'aria_role' => isset( $_POST['cta_aria_role'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_role'] ) ) : '',
			'custom_css'   => CTA_Sanitizer::sanitize_css( is_string( $_POST['custom_css'] ?? null ) ? wp_unslash( $_POST['custom_css'] ) : '' ),
			'custom_js'    => $this->sanitize_custom_code( $_POST['custom_js'] ?? '' ),
			'data_attributes' => $data_attributes,
			'blacklist_urls'  => $blacklist_urls,
			'visibility'   => isset( $_POST['visibility'] ) ? CTA_Sanitizer::sanitize_visibility( wp_unslash( $_POST['visibility'] ) ) : 'all_devices',
			'show_icon'    => 'none' !== $icon,
			'enabled'      => $enabled,
			// Formatting fields
			'title_font_size'     => isset( $_POST['title_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_size'] ) ) : '24px',
			'title_font_family'   => isset( $_POST['title_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_family'] ) ) : 'inherit',
			'title_font_weight'   => isset( $_POST['title_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_weight'] ) ) : '700',
			'title_color'         => isset( $_POST['title_color'] ) ? sanitize_text_field( wp_unslash( $_POST['title_color'] ) ) : '#1e1e1e',
			'title_alignment'     => isset( $_POST['title_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['title_alignment'] ) ) : 'left',
			'tagline_font_size'   => isset( $_POST['tagline_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_size'] ) ) : '14px',
			'tagline_font_family' => isset( $_POST['tagline_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_family'] ) ) : 'inherit',
			'tagline_font_weight' => isset( $_POST['tagline_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_weight'] ) ) : '400',
			'tagline_color'       => isset( $_POST['tagline_color'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_color'] ) ) : '#757575',
			'tagline_alignment'   => isset( $_POST['tagline_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_alignment'] ) ) : 'left',
			'body_font_size'      => isset( $_POST['body_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_size'] ) ) : '14px',
			'body_font_family'    => isset( $_POST['body_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_family'] ) ) : 'inherit',
			'body_font_weight'    => isset( $_POST['body_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_weight'] ) ) : '400',
			'body_color'          => isset( $_POST['body_color'] ) ? sanitize_text_field( wp_unslash( $_POST['body_color'] ) ) : '#1e1e1e',
			'body_alignment'      => isset( $_POST['body_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['body_alignment'] ) ) : 'left',
			// Button text formatting
			'button_text_font_size'   => isset( $_POST['button_text_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_size'] ) ) : '16px',
			'button_text_font_family' => isset( $_POST['button_text_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_family'] ) ) : 'inherit',
			'button_text_font_weight' => isset( $_POST['button_text_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_weight'] ) ) : '600',
			'button_text_color'       => isset( $_POST['button_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_color'] ) ) : '#ffffff',
			'button_text_alignment'   => isset( $_POST['button_text_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_alignment'] ) ) : 'center',
			'button_alignment'        => isset( $_POST['button_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['button_alignment'] ) ) : 'center',
			'button_width'            => isset( $_POST['button_width'] ) ? sanitize_text_field( wp_unslash( $_POST['button_width'] ) ) : 'auto',
			// Button background styling
			'background_type'        => isset( $_POST['cta_background_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_background_type'] ) ) : 'solid',
			'gradient_type'          => isset( $_POST['cta_gradient_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_type'] ) ) : 'linear',
			'gradient_start'         => isset( $_POST['cta_gradient_start'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_start'] ) ) : '#667eea',
			'gradient_end'           => isset( $_POST['cta_gradient_end'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_end'] ) ) : '#764ba2',
			'gradient_angle'         => isset( $_POST['cta_gradient_angle'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_angle'] ) ) : '90',
			'gradient_start_position' => isset( $_POST['cta_gradient_start_position'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_start_position'] ) ) : '0',
			'gradient_end_position'  => isset( $_POST['cta_gradient_end_position'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_end_position'] ) ) : '100',
			// Button border styling
			'border_style'           => isset( $_POST['cta_border_style'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_style'] ) ) : 'none',
			'border_color'           => isset( $_POST['cta_border_color'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_color'] ) ) : '#667eea',
			'border_width_top'       => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_top'] ?? '' ) ), '2px' ),
			'border_width_right'     => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_right'] ?? '' ) ), '2px' ),
			'border_width_bottom'    => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_bottom'] ?? '' ) ), '2px' ),
			'border_width_left'      => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_left'] ?? '' ) ), '2px' ),
			'border_width_linked'    => isset( $_POST['cta_border_width_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_linked'] ) ) : '1',
			// Button border radius
			'border_radius_top_left'     => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_left'] ?? '' ) ), '8px' ),
			'border_radius_top_right'    => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_right'] ?? '' ) ), '8px' ),
			'border_radius_bottom_right' => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_right'] ?? '' ) ), '8px' ),
			'border_radius_bottom_left'  => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_left'] ?? '' ) ), '8px' ),
			'border_radius_linked'       => isset( $_POST['cta_border_radius_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_linked'] ) ) : '1',
			// Button padding
			'padding_top'            => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_top'] ?? '' ) ), '12px' ),
			'padding_right'          => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_right'] ?? '' ) ), '24px' ),
			'padding_bottom'         => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_bottom'] ?? '' ) ), '12px' ),
			'padding_left'           => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_left'] ?? '' ) ), '24px' ),
			'padding_linked'         => isset( $_POST['cta_padding_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_linked'] ) ) : '0',
		];

		$cta_data = $this->enforce_pro_restrictions( $cta_data );

		// Handle slide-in settings if type is slide-in
		if ( 'slide-in' === ( $cta_data['type'] ?? 'phone' ) ) {
			$slide_in_settings = [
				'trigger_type'       => isset( $_POST['slide_in_trigger_type'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_trigger_type'] ) ) : 'time',
				'trigger_delay'      => isset( $_POST['slide_in_trigger_delay'] ) ? intval( wp_unslash( $_POST['slide_in_trigger_delay'] ) ) : 3,
				'trigger_scroll'     => isset( $_POST['slide_in_trigger_scroll'] ) ? intval( wp_unslash( $_POST['slide_in_trigger_scroll'] ) ) : 50,
				'position'           => isset( $_POST['slide_in_position'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_position'] ) ) : 'bottom-right',
				'show_animation'     => isset( $_POST['slide_in_show_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_show_animation'] ) ) : 'slide-in',
				'hide_animation'     => isset( $_POST['slide_in_hide_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_hide_animation'] ) ) : 'slide-out',
				'auto_dismiss'       => isset( $_POST['slide_in_auto_dismiss'] ),
				'auto_dismiss_delay' => isset( $_POST['slide_in_auto_dismiss_delay'] ) ? intval( wp_unslash( $_POST['slide_in_auto_dismiss_delay'] ) ) : 10,
				'dismiss_behavior'   => isset( $_POST['slide_in_dismiss_behavior'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_dismiss_behavior'] ) ) : 'session',
			];

			// Sanitize slide-in settings using the sanitizer
			$cta_data['slide_in_settings'] = CTA_Sanitizer::sanitize_slide_in_settings( $slide_in_settings );
		}

		// Per-CTA integration tracking
		$cta_data['gtm_tracking'] = $this->collect_tracking_settings(
			'gtm_tracking',
			[ 'impression_event', 'click_event', 'conversion_event', 'hover_event', 'close_event', 'event_category', 'event_label' ],
			[ 'enabled', 'track_hover' ],
			[ [ 'key_field' => 'gtm_tracking_custom_var_key', 'value_field' => 'gtm_tracking_custom_var_value', 'field_name' => 'custom_variables' ] ]
		);

		$cta_data['ga4_tracking'] = $this->collect_tracking_settings(
			'ga4_tracking',
			[ 'event_name', 'event_category', 'event_label', 'content_group' ],
			[ 'enabled', 'mark_as_conversion', 'track_impressions', 'track_hover' ],
			[ [ 'key_field' => 'ga4_tracking_custom_param_key', 'value_field' => 'ga4_tracking_custom_param_value', 'field_name' => 'custom_params' ] ]
		);

		$cta_data['posthog_tracking'] = $this->collect_tracking_settings(
			'posthog_tracking',
			[ 'event_name', 'feature_flag', 'group_type', 'group_key' ],
			[ 'enabled', 'enable_recording', 'mark_as_conversion' ],
			[
				[ 'key_field' => 'posthog_tracking_person_prop_key', 'value_field' => 'posthog_tracking_person_prop_value', 'field_name' => 'person_properties' ],
				[ 'key_field' => 'posthog_tracking_custom_prop_key', 'value_field' => 'posthog_tracking_custom_prop_value', 'field_name' => 'custom_properties' ],
			]
		);

		// Validate schedule requirements when status is 'scheduled'
		$status = $cta_data['status'] ?? 'publish';
		$schedule_start = $cta_data['schedule_start'] ?? '';
		$schedule_end = $cta_data['schedule_end'] ?? '';

		if ( 'scheduled' === $status ) {
			if ( empty( $schedule_start ) || empty( $schedule_end ) ) {
				wp_safe_redirect(
					add_query_arg(
						[ 'message' => 'missing_schedule_dates' ],
						CTA_Admin_Menu::get_admin_url( 'manage-ctas' )
					)
				);
				exit;
			}
		}

		if ( ! empty( $schedule_start ) && ! empty( $schedule_end ) && $schedule_start > $schedule_end ) {
			wp_safe_redirect(
				add_query_arg(
					[ 'message' => 'invalid_schedule_range' ],
					CTA_Admin_Menu::get_admin_url( 'manage-ctas' )
				)
			);
			exit;
		}

		if ( ! $this->validate_type_requirements( $cta_data ) ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'invalid_fields' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}

		if ( ! ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() ) ) {
			// Email is free, only popup and slide-in are Pro-only
			if ( in_array( $cta_data['type'], [ 'popup', 'slide-in' ], true ) ) {
				$cta_data['type'] = 'phone';
			}
			if ( 'button' !== $cta_data['layout'] ) {
				$cta_data['layout'] = 'button';
			}
		}

		$cta_id = $data->create_cta( $cta_data );

		if ( $cta_id ) {
			$args = [ 'message' => 'created', 'new_cta_id' => $cta_id ];
			if ( ! empty( $this->pro_downgrade_fields ) ) {
				$args['pro_downgrade'] = '1';
			}
			wp_safe_redirect( add_query_arg( $args, CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		} else {
			wp_safe_redirect( add_query_arg( [ 'message' => 'error' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}
	}

	/**
	 * Handle CTA update
	 *
	 * @param CTA_Data $data Data instance
	 *
	 * @return void
	 */
	private function handle_update_cta( CTA_Data $data ): void {
		$cta_id = isset( $_POST['cta_id'] ) ? intval( $_POST['cta_id'] ) : 0;

		if ( ! $cta_id ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'invalid_id' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}

		$icon = isset( $_POST['cta_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_icon'] ) ) : 'none';
		$link_target = isset( $_POST['link_target_new_tab'] ) ? '_blank' : '_self';
		$data_attributes = [];
		if ( isset( $_POST['cta_data_attribute_key'], $_POST['cta_data_attribute_value'] ) && is_array( $_POST['cta_data_attribute_key'] ) && is_array( $_POST['cta_data_attribute_value'] ) ) {
			$keys = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_data_attribute_key'] ) );
			$values = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_data_attribute_value'] ) );
			foreach ( $keys as $index => $key ) {
				$value = $values[ $index ] ?? '';
				if ( '' === $key && '' === $value ) {
					continue;
				}
				$data_attributes[] = [
					'key'   => $key,
					'value' => $value,
				];
			}
		}
		$blacklist_urls = [];
		if ( isset( $_POST['cta_blacklist_urls'] ) && is_array( $_POST['cta_blacklist_urls'] ) ) {
			$urls = array_map( 'sanitize_text_field', wp_unslash( $_POST['cta_blacklist_urls'] ) );
			foreach ( $urls as $pattern ) {
				$pattern = trim( $pattern );
				if ( '' === $pattern ) {
					continue;
				}
				$blacklist_urls[] = $pattern;
			}
		}

		$enabled = $this->resolve_enabled_from_post( true );

		$cta_data = [
			'name'           => isset( $_POST['cta_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_name'] ) ) : '',
			'status'         => isset( $_POST['cta_status'] ) ? CTA_Sanitizer::sanitize_status( wp_unslash( $_POST['cta_status'] ) ) : 'draft',
			'schedule_start' => CTA_Sanitizer::sanitize_schedule_date( $_POST['schedule_start'] ?? '' ),
			'schedule_end'   => CTA_Sanitizer::sanitize_schedule_date( $_POST['schedule_end'] ?? '' ),
			'schedule_type'  => isset( $_POST['schedule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_type'] ) ) : 'date_range',
			'include_times'          => ! empty( $_POST['include_times'] ),
			'schedule_start_hour'    => self::sanitize_time_hour( sanitize_text_field( wp_unslash( $_POST['schedule_start_hour'] ?? '' ) ), '12' ),
			'schedule_start_minute'  => self::sanitize_time_minute( sanitize_text_field( wp_unslash( $_POST['schedule_start_minute'] ?? '' ) ), '00' ),
			'schedule_start_period'  => self::sanitize_time_period( sanitize_text_field( wp_unslash( $_POST['schedule_start_period'] ?? '' ) ), 'AM' ),
			'schedule_end_hour'      => self::sanitize_time_hour( sanitize_text_field( wp_unslash( $_POST['schedule_end_hour'] ?? '' ) ), '11' ),
			'schedule_end_minute'    => self::sanitize_time_minute( sanitize_text_field( wp_unslash( $_POST['schedule_end_minute'] ?? '' ) ), '59' ),
			'schedule_end_period'    => self::sanitize_time_period( sanitize_text_field( wp_unslash( $_POST['schedule_end_period'] ?? '' ) ), 'PM' ),
			'title'          => isset( $_POST['cta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_title'] ) ) : '',
			'tagline'      => isset( $_POST['cta_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_tagline'] ) ) : '',
			'body_text'    => isset( $_POST['cta_body_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cta_body_text'] ) ) : '',
			'type'         => isset( $_POST['cta_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_type'] ) ) : 'phone',
			'layout'       => isset( $_POST['cta_layout'] ) ? CTA_Sanitizer::sanitize_layout( wp_unslash( $_POST['cta_layout'] ) ) : 'button',
			'phone_number' => isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '',
			'email_to'     => isset( $_POST['email_to'] ) ? sanitize_email( wp_unslash( $_POST['email_to'] ) ) : '',
			'link_url'     => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
			'link_target'  => $link_target,
			'button_text'  => isset( $_POST['button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) : 'Call Now',
			'icon'         => $icon,
			'button_animation' => isset( $_POST['button_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['button_animation'] ) ) : 'none',
			'icon_animation' => isset( $_POST['icon_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_animation'] ) ) : 'none',
			'wrapper_id'   => isset( $_POST['wrapper_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_id'] ) ) : '',
			'wrapper_classes' => isset( $_POST['wrapper_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_classes'] ) ) : '',
			'cta_html_id'  => isset( $_POST['cta_html_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_html_id'] ) ) : '',
			'cta_classes'  => isset( $_POST['cta_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_classes'] ) ) : '',
			'aria_label'   => isset( $_POST['cta_aria_label'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_label'] ) ) : '',
			'aria_description' => isset( $_POST['cta_aria_description'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_description'] ) ) : '',
			'aria_controls' => isset( $_POST['cta_aria_controls'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_controls'] ) ) : '',
			'aria_expanded' => isset( $_POST['cta_aria_expanded'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_expanded'] ) ) : '',
			'aria_role' => isset( $_POST['cta_aria_role'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_aria_role'] ) ) : '',
			'custom_css'   => CTA_Sanitizer::sanitize_css( is_string( $_POST['custom_css'] ?? null ) ? wp_unslash( $_POST['custom_css'] ) : '' ),
			'custom_js'    => $this->sanitize_custom_code( $_POST['custom_js'] ?? '' ),
			'data_attributes' => $data_attributes,
			'blacklist_urls'  => $blacklist_urls,
			'visibility'   => isset( $_POST['visibility'] ) ? CTA_Sanitizer::sanitize_visibility( wp_unslash( $_POST['visibility'] ) ) : 'all_devices',
			'show_icon'    => 'none' !== $icon,
			'enabled'      => $enabled,
			// Formatting fields
			'title_font_size'     => isset( $_POST['title_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_size'] ) ) : '24px',
			'title_font_family'   => isset( $_POST['title_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_family'] ) ) : 'inherit',
			'title_font_weight'   => isset( $_POST['title_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['title_font_weight'] ) ) : '700',
			'title_color'         => isset( $_POST['title_color'] ) ? sanitize_text_field( wp_unslash( $_POST['title_color'] ) ) : '#1e1e1e',
			'title_alignment'     => isset( $_POST['title_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['title_alignment'] ) ) : 'left',
			'tagline_font_size'   => isset( $_POST['tagline_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_size'] ) ) : '14px',
			'tagline_font_family' => isset( $_POST['tagline_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_family'] ) ) : 'inherit',
			'tagline_font_weight' => isset( $_POST['tagline_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_font_weight'] ) ) : '400',
			'tagline_color'       => isset( $_POST['tagline_color'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_color'] ) ) : '#757575',
			'tagline_alignment'   => isset( $_POST['tagline_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['tagline_alignment'] ) ) : 'left',
			'body_font_size'      => isset( $_POST['body_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_size'] ) ) : '14px',
			'body_font_family'    => isset( $_POST['body_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_family'] ) ) : 'inherit',
			'body_font_weight'    => isset( $_POST['body_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['body_font_weight'] ) ) : '400',
			'body_color'          => isset( $_POST['body_color'] ) ? sanitize_text_field( wp_unslash( $_POST['body_color'] ) ) : '#1e1e1e',
			'body_alignment'      => isset( $_POST['body_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['body_alignment'] ) ) : 'left',
			// Button text formatting
			'button_text_font_size'   => isset( $_POST['button_text_font_size'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_size'] ) ) : '16px',
			'button_text_font_family' => isset( $_POST['button_text_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_family'] ) ) : 'inherit',
			'button_text_font_weight' => isset( $_POST['button_text_font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_font_weight'] ) ) : '600',
			'button_text_color'       => isset( $_POST['button_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_color'] ) ) : '#ffffff',
			'button_text_alignment'   => isset( $_POST['button_text_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text_alignment'] ) ) : 'center',
			'button_alignment'        => isset( $_POST['button_alignment'] ) ? sanitize_text_field( wp_unslash( $_POST['button_alignment'] ) ) : 'center',
			'button_width'            => isset( $_POST['button_width'] ) ? sanitize_text_field( wp_unslash( $_POST['button_width'] ) ) : 'auto',
			// Button background styling
			'background_type'        => isset( $_POST['cta_background_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_background_type'] ) ) : 'solid',
			'gradient_type'          => isset( $_POST['cta_gradient_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_type'] ) ) : 'linear',
			'gradient_start'         => isset( $_POST['cta_gradient_start'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_start'] ) ) : '#667eea',
			'gradient_end'           => isset( $_POST['cta_gradient_end'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_end'] ) ) : '#764ba2',
			'gradient_angle'         => isset( $_POST['cta_gradient_angle'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_angle'] ) ) : '90',
			'gradient_start_position' => isset( $_POST['cta_gradient_start_position'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_start_position'] ) ) : '0',
			'gradient_end_position'  => isset( $_POST['cta_gradient_end_position'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_gradient_end_position'] ) ) : '100',
			// Button border styling
			'border_style'           => isset( $_POST['cta_border_style'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_style'] ) ) : 'none',
			'border_color'           => isset( $_POST['cta_border_color'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_color'] ) ) : '#667eea',
			'border_width_top'       => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_top'] ?? '' ) ), '2px' ),
			'border_width_right'     => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_right'] ?? '' ) ), '2px' ),
			'border_width_bottom'    => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_bottom'] ?? '' ) ), '2px' ),
			'border_width_left'      => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_width_left'] ?? '' ) ), '2px' ),
			'border_width_linked'    => isset( $_POST['cta_border_width_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_linked'] ) ) : '1',
			// Button border radius
			'border_radius_top_left'     => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_left'] ?? '' ) ), '8px' ),
			'border_radius_top_right'    => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_right'] ?? '' ) ), '8px' ),
			'border_radius_bottom_right' => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_right'] ?? '' ) ), '8px' ),
			'border_radius_bottom_left'  => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_left'] ?? '' ) ), '8px' ),
			'border_radius_linked'       => isset( $_POST['cta_border_radius_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_linked'] ) ) : '1',
			// Button padding
			'padding_top'            => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_top'] ?? '' ) ), '12px' ),
			'padding_right'          => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_right'] ?? '' ) ), '24px' ),
			'padding_bottom'         => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_bottom'] ?? '' ) ), '12px' ),
			'padding_left'           => self::sanitize_css_dimension( sanitize_text_field( wp_unslash( $_POST['cta_padding_left'] ?? '' ) ), '24px' ),
			'padding_linked'         => isset( $_POST['cta_padding_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_linked'] ) ) : '0',
		];

		$cta_data = $this->enforce_pro_restrictions( $cta_data );

		// Handle slide-in settings if type is slide-in
		if ( 'slide-in' === ( $cta_data['type'] ?? 'phone' ) ) {
			$slide_in_settings = [
				'trigger_type'       => isset( $_POST['slide_in_trigger_type'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_trigger_type'] ) ) : 'time',
				'trigger_delay'      => isset( $_POST['slide_in_trigger_delay'] ) ? intval( wp_unslash( $_POST['slide_in_trigger_delay'] ) ) : 3,
				'trigger_scroll'     => isset( $_POST['slide_in_trigger_scroll'] ) ? intval( wp_unslash( $_POST['slide_in_trigger_scroll'] ) ) : 50,
				'position'           => isset( $_POST['slide_in_position'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_position'] ) ) : 'bottom-right',
				'show_animation'     => isset( $_POST['slide_in_show_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_show_animation'] ) ) : 'slide-in',
				'hide_animation'     => isset( $_POST['slide_in_hide_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_hide_animation'] ) ) : 'slide-out',
				'auto_dismiss'       => isset( $_POST['slide_in_auto_dismiss'] ),
				'auto_dismiss_delay' => isset( $_POST['slide_in_auto_dismiss_delay'] ) ? intval( wp_unslash( $_POST['slide_in_auto_dismiss_delay'] ) ) : 10,
				'dismiss_behavior'   => isset( $_POST['slide_in_dismiss_behavior'] ) ? sanitize_text_field( wp_unslash( $_POST['slide_in_dismiss_behavior'] ) ) : 'session',
			];

			// Sanitize slide-in settings using the sanitizer
			$cta_data['slide_in_settings'] = CTA_Sanitizer::sanitize_slide_in_settings( $slide_in_settings );
		}

		// Per-CTA integration tracking
		$cta_data['gtm_tracking'] = $this->collect_tracking_settings(
			'gtm_tracking',
			[ 'impression_event', 'click_event', 'conversion_event', 'hover_event', 'close_event', 'event_category', 'event_label' ],
			[ 'enabled', 'track_hover' ],
			[ [ 'key_field' => 'gtm_tracking_custom_var_key', 'value_field' => 'gtm_tracking_custom_var_value', 'field_name' => 'custom_variables' ] ]
		);

		$cta_data['ga4_tracking'] = $this->collect_tracking_settings(
			'ga4_tracking',
			[ 'event_name', 'event_category', 'event_label', 'content_group' ],
			[ 'enabled', 'mark_as_conversion', 'track_impressions', 'track_hover' ],
			[ [ 'key_field' => 'ga4_tracking_custom_param_key', 'value_field' => 'ga4_tracking_custom_param_value', 'field_name' => 'custom_params' ] ]
		);

		$cta_data['posthog_tracking'] = $this->collect_tracking_settings(
			'posthog_tracking',
			[ 'event_name', 'feature_flag', 'group_type', 'group_key' ],
			[ 'enabled', 'enable_recording', 'mark_as_conversion' ],
			[
				[ 'key_field' => 'posthog_tracking_person_prop_key', 'value_field' => 'posthog_tracking_person_prop_value', 'field_name' => 'person_properties' ],
				[ 'key_field' => 'posthog_tracking_custom_prop_key', 'value_field' => 'posthog_tracking_custom_prop_value', 'field_name' => 'custom_properties' ],
			]
		);

		// Get existing CTA to check for schedule type changes
		$existing_cta = $data->get_cta( $cta_id );
		$old_schedule_type = $existing_cta['schedule_type'] ?? 'date_range';
		$new_schedule_type = $cta_data['schedule_type'] ?? 'date_range';
		$status = $cta_data['status'] ?? 'publish';

		// Clean up deprecated schedule fields when schedule type changes or status changes
		if ( $new_schedule_type !== 'date_range' ) {
			// Clear date-based schedule fields when using business_hours or always_on
			$cta_data['schedule_start'] = '';
			$cta_data['schedule_end'] = '';
		}

		// Clear schedule fields when status changes to non-scheduled states
		if ( in_array( $status, [ 'draft', 'archived', 'trash' ], true ) ) {
			$cta_data['schedule_start'] = '';
			$cta_data['schedule_end'] = '';
		}

		// Validate schedule requirements when status is 'scheduled'
		$schedule_start = $cta_data['schedule_start'] ?? '';
		$schedule_end = $cta_data['schedule_end'] ?? '';

		if ( 'scheduled' === $status ) {
			// For date_range schedule type, require dates
			if ( 'date_range' === $new_schedule_type ) {
				if ( empty( $schedule_start ) || empty( $schedule_end ) ) {
					wp_safe_redirect(
						add_query_arg(
							[ 'message' => 'missing_schedule_dates' ],
							CTA_Admin_Menu::get_admin_url( 'manage-ctas' )
						)
					);
					exit;
				}
			}
		}

		if ( ! empty( $schedule_start ) && ! empty( $schedule_end ) && $schedule_start > $schedule_end ) {
			wp_safe_redirect(
				add_query_arg(
					[ 'message' => 'invalid_schedule_range' ],
					CTA_Admin_Menu::get_admin_url( 'manage-ctas' )
				)
			);
			exit;
		}

		if ( ! $this->validate_type_requirements( $cta_data ) ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'invalid_fields' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}

		if ( ! ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() ) ) {
			// Email is free, only popup and slide-in are Pro-only
			if ( in_array( $cta_data['type'], [ 'popup', 'slide-in' ], true ) ) {
				$cta_data['type'] = 'phone';
			}
			if ( 'button' !== $cta_data['layout'] ) {
				$cta_data['layout'] = 'button';
			}
		}

		$success = $data->update_cta( $cta_id, $cta_data );

		if ( $success ) {
			$args = [ 'message' => 'updated' ];
			if ( ! empty( $this->pro_downgrade_fields ) ) {
				$args['pro_downgrade'] = '1';
			}
			wp_safe_redirect( add_query_arg( $args, CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		} else {
			wp_safe_redirect( add_query_arg( [ 'message' => 'error' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}
	}

	/**
	 * Handle CTA deletion
	 *
	 * @param CTA_Data $data Data instance
	 *
	 * @return void
	 */
	private function handle_delete_cta( CTA_Data $data ): void {
		$cta_id = isset( $_POST['cta_id'] ) ? intval( $_POST['cta_id'] ) : 0;

		if ( ! $cta_id ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'invalid_id' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}

		$success = $data->delete_cta( $cta_id );

		if ( $success ) {
			wp_safe_redirect( add_query_arg( [ 'message' => 'deleted' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		} else {
			wp_safe_redirect( add_query_arg( [ 'message' => 'error' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
			exit;
		}
	}

	/**
	 * Render CTA manager page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		$this->handle_form_submission();

		$data = CTA_Data::get_instance();
		$repository = CTA_Repository::get_instance();
		$ctas = $repository->get_all( [ 'include_trashed' => true ] );
		$cta_count = $data->get_cta_count();
		$demo_count = $data->get_total_cta_count() - $cta_count;
		$can_add = $data->can_add_cta();
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		// Check for edit mode
		$edit_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
		$editing_cta = $edit_id ? $data->get_cta( $edit_id ) : null;

		include CTA_PLUGIN_DIR . 'templates/admin/manage-ctas.php';
	}

	/**
	 * Validate required fields per CTA type
	 */
	private function validate_type_requirements( array $cta_data ): bool {
		$type = $cta_data['type'] ?? 'phone';
		switch ( $type ) {
			case 'link':
				return ! empty( $cta_data['link_url'] ) && filter_var( $cta_data['link_url'], FILTER_VALIDATE_URL );
			case 'email':
				return ! empty( $cta_data['email_to'] ) && is_email( $cta_data['email_to'] );
			case 'popup':
				return true;
			case 'slide-in':
				return true;
			case 'phone':
			default:
				if ( empty( $cta_data['phone_number'] ) ) {
					return false;
				}
				// Must contain at least 7 digits (shortest valid phone number)
				$digits = preg_replace( '/\D/', '', $cta_data['phone_number'] );
				return strlen( $digits ) >= 7;
		}
	}

	/**
	 * Sanitize an analytics event name to a safe, portable identifier.
	 *
	 * Enforces lowercase alphanumeric + underscores (GA4 convention).
	 * Max 40 characters. Non-conforming characters are replaced with underscores
	 * and leading/trailing underscores are stripped.
	 *
	 * @param string $name Raw event name.
	 * @return string
	 */
	private function sanitize_event_name( string $name ): string {
		$name = strtolower( sanitize_text_field( wp_unslash( $name ) ) );
		$name = preg_replace( '/[^a-z0-9_]/', '_', $name );
		$name = trim( $name, '_' );
		return substr( $name, 0, 40 );
	}

	/**
	 * Collect per-CTA tracking settings from POST data.
	 *
	 * @param string $prefix    POST field prefix (e.g. 'gtm_tracking').
	 * @param array  $text_keys Keys whose values are text strings.
	 * @param array  $bool_keys Keys whose values are booleans/checkboxes.
	 * @param array  $kv_pairs  KV pair config arrays with key_field, value_field, field_name.
	 *
	 * @return array
	 */
	private function collect_tracking_settings( string $prefix, array $text_keys, array $bool_keys, array $kv_pairs ): array {
		$settings = [];

		// Keys that represent analytics event names and must conform to GA4/PostHog naming rules.
		$event_name_keys = [ 'event_name', 'impression_event', 'click_event', 'conversion_event', 'hover_event', 'close_event' ];

		foreach ( $text_keys as $key ) {
			$value = $_POST[ $prefix ][ $key ] ?? '';
			$settings[ $key ] = in_array( $key, $event_name_keys, true )
				? $this->sanitize_event_name( $value )
				: sanitize_text_field( wp_unslash( $value ) );
		}

		foreach ( $bool_keys as $key ) {
			$settings[ $key ] = ! empty( $_POST[ $prefix ][ $key ] );
		}

		foreach ( $kv_pairs as $kv ) {
			$keys_post = $_POST[ $kv['key_field'] ] ?? [];
			$vals_post = $_POST[ $kv['value_field'] ] ?? [];
			$pairs     = [];
			if ( is_array( $keys_post ) && is_array( $vals_post ) ) {
				// All custom variable values are stored as strings.
				// Integrations expecting numbers/booleans (e.g. GA4 params) must
				// coerce types on the frontend before sending to the analytics API.
				$keys_post = array_map( 'sanitize_text_field', wp_unslash( $keys_post ) );
				$vals_post = array_map( 'sanitize_text_field', wp_unslash( $vals_post ) );
				foreach ( $keys_post as $i => $k ) {
					$v = $vals_post[ $i ] ?? '';
					if ( '' === $k && '' === $v ) {
						continue;
					}
					$pairs[] = [ 'key' => $k, 'value' => $v ];
				}
			}
			$settings[ $kv['field_name'] ] = $pairs;
		}

		return $settings;
	}

	/**
	 * Enforce Pro-only fields and whitelist values
	 *
	 * @param array $cta_data
	 *
	 * @return array
	 */
	private function enforce_pro_restrictions( array $cta_data ): array {
		$this->pro_downgrade_fields = [];
		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		$visibility_free    = [ 'all_devices' ];
		$visibility_allowed = array_merge( $visibility_free, [ 'mobile_only', 'desktop_only', 'tablet_only' ] );

		$icon_free    = [ 'none', 'phone' ];
		$icon_allowed = array_merge( $icon_free, [ 'phone-alt', 'star', 'star-filled', 'chat', 'email', 'arrow-right', 'calendar', 'cart', 'download', 'heart', 'bolt' ] );

		$button_animation_free    = [ 'none' ];
		$button_animation_allowed = array_merge( $button_animation_free, [ 'pulse', 'bounce', 'shake', 'glow', 'heartbeat' ] );

		$icon_animation_free    = [ 'none' ];
		$icon_animation_allowed = array_merge( $icon_animation_free, [ 'spin', 'ring', 'tada', 'flash', 'swing' ] );

		// Visibility
		$visibility = $cta_data['visibility'] ?? 'all_devices';
		if ( ! in_array( $visibility, $visibility_allowed, true ) ) {
			$visibility = 'all_devices';
		}
		if ( ! $is_pro && ! in_array( $visibility, $visibility_free, true ) ) {
			$visibility = 'all_devices';
			$this->pro_downgrade_fields[] = 'visibility';
		}
		$cta_data['visibility'] = $visibility;

		// Icon
		$icon = $cta_data['icon'] ?? 'none';
		if ( ! in_array( $icon, $icon_allowed, true ) ) {
			$icon = 'none';
		}
		if ( ! $is_pro && ! in_array( $icon, $icon_free, true ) ) {
			$icon = 'none';
			$this->pro_downgrade_fields[] = 'icon';
		}
		$cta_data['icon'] = $icon;
		$cta_data['show_icon'] = ( 'none' !== $icon );

		// Button animation
		$button_animation = $cta_data['button_animation'] ?? 'none';
		if ( ! in_array( $button_animation, $button_animation_allowed, true ) ) {
			$button_animation = 'none';
		}
		if ( ! $is_pro && ! in_array( $button_animation, $button_animation_free, true ) ) {
			$button_animation = 'none';
			$this->pro_downgrade_fields[] = 'button_animation';
		}
		$cta_data['button_animation'] = $button_animation;

		// Icon animation
		$icon_animation = $cta_data['icon_animation'] ?? 'none';
		if ( ! in_array( $icon_animation, $icon_animation_allowed, true ) ) {
			$icon_animation = 'none';
		}
		if ( ! $is_pro && ! in_array( $icon_animation, $icon_animation_free, true ) ) {
			$icon_animation = 'none';
			$this->pro_downgrade_fields[] = 'icon_animation';
		}
		$cta_data['icon_animation'] = $icon_animation;

		// Pro-only features
		if ( ! $is_pro ) {
			$pro_fields = [ 'wrapper_id', 'wrapper_classes', 'cta_html_id', 'cta_classes', 'aria_label', 'aria_description', 'aria_controls', 'aria_expanded', 'aria_role', 'custom_css', 'custom_js' ];
			foreach ( $pro_fields as $field ) {
				if ( ! empty( $cta_data[ $field ] ) ) {
					$this->pro_downgrade_fields[] = $field;
				}
			}
			$pro_array_fields = [ 'data_attributes', 'gtm_tracking', 'ga4_tracking', 'posthog_tracking', 'blacklist_urls' ];
			foreach ( $pro_array_fields as $field ) {
				if ( ! empty( $cta_data[ $field ] ) ) {
					$this->pro_downgrade_fields[] = $field;
				}
			}
			$cta_data['wrapper_id'] = '';
			$cta_data['wrapper_classes'] = '';
			$cta_data['cta_html_id'] = '';
			$cta_data['cta_classes'] = '';
			$cta_data['data_attributes'] = [];
			$cta_data['aria_label'] = '';
			$cta_data['aria_description'] = '';
			$cta_data['aria_controls'] = '';
			$cta_data['aria_expanded'] = '';
			$cta_data['aria_role'] = '';
			$cta_data['gtm_tracking'] = [];
			$cta_data['ga4_tracking'] = [];
			$cta_data['posthog_tracking'] = [];
			$cta_data['blacklist_urls'] = [];
			$cta_data['custom_css'] = '';
			$cta_data['custom_js']  = '';
		}

		return $cta_data;
	}

	/**
	 * Sanitize custom CSS/JS input while preserving code syntax.
	 *
	 * @param mixed $raw Raw field input.
	 *
	 * @return string
	 */
	private function sanitize_custom_code( $raw ): string {
		$code = is_string( $raw ) ? wp_unslash( $raw ) : '';
		$code = wp_check_invalid_utf8( $code );

		// Strip wrapped script tags if pasted in.
		$code = preg_replace( '/<\/?script\b[^>]*>/i', '', $code );

		return trim( $code );
	}

	/**
	 * Resolve enabled flag from POST data.
	 *
	 * @param bool|null $fallback Fallback when no enabled field is provided.
	 *
	 * @return bool
	 */
	private function resolve_enabled_from_post( ?bool $fallback = null ): bool {
		$enabled_keys = [ 'enabled', 'is_enabled', 'cta_enabled' ];
		foreach ( $enabled_keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			$value = wp_unslash( $_POST[ $key ] );
			if ( is_array( $value ) ) {
				$value = reset( $value );
			}

			return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true;
		}

		return $fallback ?? true;
	}

	/**
	 * AJAX handler to toggle CTA status between publish and draft
	 */
	public function ajax_toggle_status(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cta_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'cta-manager' ) ] );
		}

		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'cta-manager' ) ] );
		}

		$cta_id = isset( $_POST['cta_id'] ) ? intval( $_POST['cta_id'] ) : 0;
		if ( ! $cta_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid CTA ID', 'cta-manager' ) ] );
		}

		$data = CTA_Data::get_instance();
		$cta = $data->get_cta( $cta_id );
		if ( ! $cta ) {
			wp_send_json_error( [ 'message' => __( 'CTA not found', 'cta-manager' ) ] );
		}

		$current_status = $cta['status'] ?? 'draft';
		$new_status = ( 'publish' === $current_status ) ? 'draft' : 'publish';

		$success = $data->update_cta( $cta_id, [ 'status' => $new_status ] );

		if ( $success ) {
			wp_send_json_success( [
				'status'  => $new_status,
				'cta_id'  => $cta_id,
				'message' => ( 'publish' === $new_status )
					? __( 'CTA published', 'cta-manager' )
					: __( 'CTA set to draft', 'cta-manager' ),
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to update CTA status', 'cta-manager' ) ] );
		}
	}

	/**
	 * AJAX handler to permanently delete all trashed CTAs
	 *
	 * @return void
	 */
	public function ajax_empty_trash(): void {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cta_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'cta-manager' ) ] );
		}

		// Check permissions
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'cta-manager' ) ] );
		}

		$repository = CTA_Repository::get_instance();

		// Get all trashed CTAs
		$trashed_ctas = $repository->get_all( [ 'include_deleted' => true ] );
		$trashed_ctas = array_filter( $trashed_ctas, function( $cta ) {
			return ! empty( $cta['deleted_at'] );
		} );

		if ( empty( $trashed_ctas ) ) {
			wp_send_json_success( [ 'message' => __( 'Trash is already empty', 'cta-manager' ) ] );
		}

		// Permanently delete each trashed CTA
		$deleted_count = 0;
		foreach ( $trashed_ctas as $cta ) {
			if ( $repository->delete( $cta['id'], true ) ) {
				$deleted_count++;
			}
		}

		if ( $deleted_count > 0 ) {
			wp_send_json_success( [
				'message' => sprintf(
					// translators: %d is the number of CTAs deleted
					_n( '%d CTA permanently deleted', '%d CTAs permanently deleted', $deleted_count, 'cta-manager' ),
					$deleted_count
				),
				'count'   => $deleted_count,
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to empty trash', 'cta-manager' ) ] );
		}
	}

	/**
	 * AJAX handler to change CTA status to any valid status
	 *
	 * Uses repository-specific methods for trash/archive to handle
	 * timestamps and schedule cleanup properly.
	 *
	 * @return void
	 */
	public function ajax_change_status(): void {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cta_admin_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid nonce', 'cta-manager' ) ] );
		}

		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'cta-manager' ) ] );
		}

		$cta_id = isset( $_POST['cta_id'] ) ? intval( $_POST['cta_id'] ) : 0;
		if ( ! $cta_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid CTA ID', 'cta-manager' ) ] );
		}

		$new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( wp_unslash( $_POST['new_status'] ) ) : '';
		$valid_statuses = [ 'publish', 'draft', 'scheduled', 'archived', 'trash' ];
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid status', 'cta-manager' ) ] );
		}

		$repository = CTA_Repository::get_instance();
		$cta = $repository->get( $cta_id );
		if ( ! $cta ) {
			wp_send_json_error( [ 'message' => __( 'CTA not found', 'cta-manager' ) ] );
		}

		$current_status = $cta['status'] ?? 'draft';
		$is_demo        = ! empty( $cta['_demo'] );
		$success        = false;
		$status_labels  = [
			'publish'   => __( 'Published', 'cta-manager' ),
			'draft'     => __( 'Draft', 'cta-manager' ),
			'scheduled' => __( 'Scheduled', 'cta-manager' ),
			'archived'  => __( 'Archived', 'cta-manager' ),
			'trash'     => __( 'Trashed', 'cta-manager' ),
		];

		// Clear demo flag before status change — demo CTAs become normal CTAs
		if ( $is_demo ) {
			$data = CTA_Data::get_instance();
			$data->update_cta( $cta_id, [ '_demo' => false ] );
		}

		// Handle scheduled status - requires date fields
		if ( 'scheduled' === $new_status ) {
			$schedule_start = isset( $_POST['schedule_start'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_start'] ) ) : '';
			$schedule_end   = isset( $_POST['schedule_end'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_end'] ) ) : '';

			if ( empty( $schedule_start ) || empty( $schedule_end ) ) {
				wp_send_json_error( [ 'message' => __( 'Schedule start and end dates are required', 'cta-manager' ) ] );
			}

			if ( $schedule_start > $schedule_end ) {
				wp_send_json_error( [ 'message' => __( 'Start date must be before end date', 'cta-manager' ) ] );
			}

			$include_times         = ! empty( $_POST['include_times'] );
			$schedule_start_hour   = isset( $_POST['schedule_start_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_start_hour'] ) ) : '12';
			$schedule_start_minute = isset( $_POST['schedule_start_minute'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_start_minute'] ) ) : '00';
			$schedule_start_period = isset( $_POST['schedule_start_period'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_start_period'] ) ) : 'AM';
			$schedule_end_hour     = isset( $_POST['schedule_end_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_end_hour'] ) ) : '11';
			$schedule_end_minute   = isset( $_POST['schedule_end_minute'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_end_minute'] ) ) : '59';
			$schedule_end_period   = isset( $_POST['schedule_end_period'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_end_period'] ) ) : 'PM';

			$data = CTA_Data::get_instance();
			$success = $data->update_cta( $cta_id, [
				'status'                 => $new_status,
				'schedule_start'         => $schedule_start,
				'schedule_end'           => $schedule_end,
				'include_times'          => $include_times,
				'schedule_start_hour'    => $schedule_start_hour,
				'schedule_start_minute'  => $schedule_start_minute,
				'schedule_start_period'  => $schedule_start_period,
				'schedule_end_hour'      => $schedule_end_hour,
				'schedule_end_minute'    => $schedule_end_minute,
				'schedule_end_period'    => $schedule_end_period,
			] );

			if ( $success ) {
				wp_send_json_success( [
					'status'  => $new_status,
					'cta_id'  => $cta_id,
					'message' => sprintf(
						/* translators: %s is the new status label */
						__( 'CTA status changed to %s', 'cta-manager' ),
						$status_labels[ $new_status ]
					),
				] );
			} else {
				wp_send_json_error( [ 'message' => __( 'Failed to update CTA status', 'cta-manager' ) ] );
			}
		}

		// Use repository-specific methods for proper handling
		if ( 'trash' === $new_status ) {
			$success = (bool) $repository->trash( $cta_id );
		} elseif ( 'archived' === $new_status ) {
			$success = (bool) $repository->archive( $cta_id );
		} elseif ( 'trash' === $current_status ) {
			// Restoring from trash - use restore method to clear trashed_at
			$success = $repository->restore_from_trash( $cta_id, $new_status );
		} else {
			// Standard status change (publish <-> draft, archived -> publish/draft)
			$data = CTA_Data::get_instance();
			$success = $data->update_cta( $cta_id, [ 'status' => $new_status ] );
		}

		if ( $success ) {
			wp_send_json_success( [
				'status'  => $new_status,
				'cta_id'  => $cta_id,
				'message' => sprintf(
					/* translators: %s is the new status label */
					__( 'CTA status changed to %s', 'cta-manager' ),
					$status_labels[ $new_status ] ?? $new_status
				),
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to update CTA status', 'cta-manager' ) ] );
		}
	}
}
