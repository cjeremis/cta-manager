<?php
/**
 * CTA Management Dashboard
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Manager {

	use CTA_Singleton;

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
		$link_target = '_self';
		if ( isset( $_POST['link_target_new_tab'] ) ) {
			$link_target = '_blank';
		} elseif ( isset( $_POST['link_target'] ) ) {
			$link_target = sanitize_text_field( wp_unslash( $_POST['link_target'] ) );
		}
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
			'title'          => isset( $_POST['cta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_title'] ) ) : '',
			'tagline'      => isset( $_POST['cta_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_tagline'] ) ) : '',
			'body_text'    => isset( $_POST['cta_body_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cta_body_text'] ) ) : '',
			'type'         => isset( $_POST['cta_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_type'] ) ) : 'phone',
			'layout'       => isset( $_POST['cta_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_layout'] ) ) : 'button',
			'phone_number' => isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '',
			'email_to'     => isset( $_POST['email_to'] ) ? sanitize_email( wp_unslash( $_POST['email_to'] ) ) : '',
			'link_url'     => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
			'link_target'  => $link_target,
			'link_target_new_tab' => isset( $_POST['link_target_new_tab'] ),
			'link_target_new_tab' => isset( $_POST['link_target_new_tab'] ),
			'button_text'  => isset( $_POST['button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['button_text'] ) ) : 'Call Now',
			'icon'         => $icon,
			'button_animation' => isset( $_POST['button_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['button_animation'] ) ) : 'none',
			'icon_animation' => isset( $_POST['icon_animation'] ) ? sanitize_text_field( wp_unslash( $_POST['icon_animation'] ) ) : 'none',
			'wrapper_id'   => isset( $_POST['wrapper_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_id'] ) ) : '',
			'wrapper_classes' => isset( $_POST['wrapper_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['wrapper_classes'] ) ) : '',
			'cta_html_id'  => isset( $_POST['cta_html_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_html_id'] ) ) : '',
			'cta_classes'  => isset( $_POST['cta_classes'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_classes'] ) ) : '',
			'data_attributes' => $data_attributes,
			'blacklist_urls'  => $blacklist_urls,
			'visibility'   => isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : 'all_devices',
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
			'border_width_top'       => isset( $_POST['cta_border_width_top'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_top'] ) ) : '2px',
			'border_width_right'     => isset( $_POST['cta_border_width_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_right'] ) ) : '2px',
			'border_width_bottom'    => isset( $_POST['cta_border_width_bottom'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_bottom'] ) ) : '2px',
			'border_width_left'      => isset( $_POST['cta_border_width_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_left'] ) ) : '2px',
			'border_width_linked'    => isset( $_POST['cta_border_width_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_linked'] ) ) : '1',
			// Button border radius
			'border_radius_top_left'     => isset( $_POST['cta_border_radius_top_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_left'] ) ) : '8px',
			'border_radius_top_right'    => isset( $_POST['cta_border_radius_top_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_right'] ) ) : '8px',
			'border_radius_bottom_right' => isset( $_POST['cta_border_radius_bottom_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_right'] ) ) : '8px',
			'border_radius_bottom_left'  => isset( $_POST['cta_border_radius_bottom_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_left'] ) ) : '8px',
			'border_radius_linked'       => isset( $_POST['cta_border_radius_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_linked'] ) ) : '1',
			// Button padding
			'padding_top'            => isset( $_POST['cta_padding_top'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_top'] ) ) : '12px',
			'padding_right'          => isset( $_POST['cta_padding_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_right'] ) ) : '24px',
			'padding_bottom'         => isset( $_POST['cta_padding_bottom'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_bottom'] ) ) : '12px',
			'padding_left'           => isset( $_POST['cta_padding_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_left'] ) ) : '24px',
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

		// Validate schedule requirements when status is 'schedule'
		$status = $cta_data['status'] ?? 'publish';
		$schedule_start = $cta_data['schedule_start'] ?? '';
		$schedule_end = $cta_data['schedule_end'] ?? '';

		if ( 'schedule' === $status ) {
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
			wp_safe_redirect( add_query_arg( [ 'message' => 'created' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
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
		$link_target = '_self';
		if ( isset( $_POST['link_target_new_tab'] ) ) {
			$link_target = '_blank';
		} elseif ( isset( $_POST['link_target'] ) ) {
			$link_target = sanitize_text_field( wp_unslash( $_POST['link_target'] ) );
		}
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
			'title'          => isset( $_POST['cta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_title'] ) ) : '',
			'tagline'      => isset( $_POST['cta_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_tagline'] ) ) : '',
			'body_text'    => isset( $_POST['cta_body_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cta_body_text'] ) ) : '',
			'type'         => isset( $_POST['cta_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_type'] ) ) : 'phone',
			'layout'       => isset( $_POST['cta_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_layout'] ) ) : 'button',
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
			'data_attributes' => $data_attributes,
			'blacklist_urls'  => $blacklist_urls,
			'visibility'   => isset( $_POST['visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['visibility'] ) ) : 'all_devices',
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
			'border_width_top'       => isset( $_POST['cta_border_width_top'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_top'] ) ) : '2px',
			'border_width_right'     => isset( $_POST['cta_border_width_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_right'] ) ) : '2px',
			'border_width_bottom'    => isset( $_POST['cta_border_width_bottom'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_bottom'] ) ) : '2px',
			'border_width_left'      => isset( $_POST['cta_border_width_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_left'] ) ) : '2px',
			'border_width_linked'    => isset( $_POST['cta_border_width_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_width_linked'] ) ) : '1',
			// Button border radius
			'border_radius_top_left'     => isset( $_POST['cta_border_radius_top_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_left'] ) ) : '8px',
			'border_radius_top_right'    => isset( $_POST['cta_border_radius_top_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_top_right'] ) ) : '8px',
			'border_radius_bottom_right' => isset( $_POST['cta_border_radius_bottom_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_right'] ) ) : '8px',
			'border_radius_bottom_left'  => isset( $_POST['cta_border_radius_bottom_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_bottom_left'] ) ) : '8px',
			'border_radius_linked'       => isset( $_POST['cta_border_radius_linked'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_border_radius_linked'] ) ) : '1',
			// Button padding
			'padding_top'            => isset( $_POST['cta_padding_top'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_top'] ) ) : '12px',
			'padding_right'          => isset( $_POST['cta_padding_right'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_right'] ) ) : '24px',
			'padding_bottom'         => isset( $_POST['cta_padding_bottom'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_bottom'] ) ) : '12px',
			'padding_left'           => isset( $_POST['cta_padding_left'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_padding_left'] ) ) : '24px',
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
			wp_safe_redirect( add_query_arg( [ 'message' => 'updated' ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) );
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
		$ctas = $data->get_ctas();
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
				return ! empty( $cta_data['phone_number'] );
		}
	}

	/**
	 * Enforce Pro-only fields and whitelist values
	 *
	 * @param array $cta_data
	 *
	 * @return array
	 */
	private function enforce_pro_restrictions( array $cta_data ): array {
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
		}
		$cta_data['visibility'] = $visibility;

		// Icon
		$icon = $cta_data['icon'] ?? 'none';
		if ( ! in_array( $icon, $icon_allowed, true ) ) {
			$icon = 'none';
		}
		if ( ! $is_pro && ! in_array( $icon, $icon_free, true ) ) {
			$icon = 'none';
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
		}
		$cta_data['button_animation'] = $button_animation;

		// Icon animation
		$icon_animation = $cta_data['icon_animation'] ?? 'none';
		if ( ! in_array( $icon_animation, $icon_animation_allowed, true ) ) {
			$icon_animation = 'none';
		}
		if ( ! $is_pro && ! in_array( $icon_animation, $icon_animation_free, true ) ) {
			$icon_animation = 'none';
		}
		$cta_data['icon_animation'] = $icon_animation;

		// Blacklist URLs (Pro-only feature)
		if ( ! $is_pro ) {
			$cta_data['blacklist_urls'] = [];
		}

		return $cta_data;
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
}
