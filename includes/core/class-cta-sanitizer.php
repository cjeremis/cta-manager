<?php
/**
 * Sanitizer Handler
 *
 * Handles sanitization of CTA Manager input and payload data.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Sanitizer {

	public const VALID_VISIBILITY = [ 'all_devices', 'mobile_only', 'desktop_only', 'tablet_only' ];
	public const VALID_TYPES      = [ 'phone', 'link', 'email', 'popup', 'slide-in' ];
	public const VALID_LAYOUTS    = [ 'button', 'card-top', 'card-left', 'card-right', 'card-bottom' ];
	public const VALID_POSITIONS  = [ 'top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right' ];
	public const VALID_ANIMATIONS = [ 'none', 'slide-in', 'fade-in', 'slide-out', 'fade-out' ];
	public const VALID_DISMISS_BEHAVIORS = [ 'session', 'page', 'always' ];
	public const VALID_TRIGGER_TYPES = [ 'time', 'scroll' ];
	public const VALID_STATUSES   = [ 'publish', 'schedule', 'draft', 'trashed', 'archived' ];

	/**
	 * Sanitize all settings
	 *
	 * @param array $settings Settings to sanitize
	 *
	 * @return array
	 */
	public static function sanitize_settings( array $settings ): array {
		$sanitized = [];

		if ( isset( $settings['phone_number'] ) ) {
			$sanitized['phone_number'] = self::sanitize_phone_number( $settings['phone_number'] );
		}

		if ( isset( $settings['button_text'] ) ) {
			$sanitized['button_text'] = sanitize_text_field( $settings['button_text'] );
		}

		if ( isset( $settings['title'] ) ) {
			$sanitized['title'] = sanitize_text_field( $settings['title'] );
		}

		if ( isset( $settings['tagline'] ) ) {
			$sanitized['tagline'] = sanitize_text_field( $settings['tagline'] );
		}

		if ( isset( $settings['body_text'] ) ) {
			$sanitized['body_text'] = sanitize_textarea_field( $settings['body_text'] );
		}

		if ( isset( $settings['type'] ) ) {
			$sanitized['type'] = self::sanitize_type( $settings['type'] );
		}

		if ( isset( $settings['layout'] ) ) {
			$sanitized['layout'] = self::sanitize_layout( $settings['layout'] );
		}

		if ( isset( $settings['visibility'] ) ) {
			$sanitized['visibility'] = self::sanitize_visibility( $settings['visibility'] );
		}

		if ( isset( $settings['email_to'] ) ) {
			$sanitized['email_to'] = sanitize_email( $settings['email_to'] );
		}

		if ( isset( $settings['link_url'] ) ) {
			$sanitized['link_url'] = esc_url_raw( $settings['link_url'] );
		}

		$boolean_fields = [ 'show_icon', 'open_in_new_tab', 'enabled' ];
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = self::sanitize_boolean( $settings[ $field ] );
			}
		}

		// Additional boolean settings used in settings page
		$extra_boolean_fields = [ 'enable_analytics', 'load_scripts_footer', 'disable_animations', 'delete_data_on_uninstall' ];
		foreach ( $extra_boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = self::sanitize_boolean( $settings[ $field ] );
			}
		}

		if ( isset( $settings['custom_css'] ) ) {
			$sanitized['custom_css'] = self::sanitize_css( $settings['custom_css'] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize phone number
	 *
	 * @param string $phone Phone number
	 *
	 * @return string
	 */
	public static function sanitize_phone_number( string $phone ): string {
		$clean = preg_replace( '/[^0-9+\-().\s]/', '', $phone );
		return substr( $clean, 0, 30 );
	}

	/**
	 * Sanitize visibility
	 *
	 * @param string $visibility Visibility setting
	 *
	 * @return string
	 */
	public static function sanitize_visibility( string $visibility ): string {
		return in_array( $visibility, self::VALID_VISIBILITY, true ) ? $visibility : 'all_devices';
	}

	/**
	 * Sanitize CTA type
	 */
	public static function sanitize_type( string $type ): string {
		return in_array( $type, self::VALID_TYPES, true ) ? $type : 'phone';
	}

	/**
	 * Sanitize CTA layout
	 */
	public static function sanitize_layout( string $layout ): string {
		return in_array( $layout, self::VALID_LAYOUTS, true ) ? $layout : 'button';
	}

	/**
	 * Sanitize CTA status
	 */
	public static function sanitize_status( string $status ): string {
		return in_array( $status, self::VALID_STATUSES, true ) ? $status : 'draft';
	}

	/**
	 * Sanitize boolean value
	 *
	 * @param mixed $value Value to sanitize
	 *
	 * @return bool
	 */
	public static function sanitize_boolean( mixed $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			return in_array( strtolower( $value ), [ 'true', '1', 'yes', 'on' ], true );
		}
		return (bool) $value;
	}

	/**
	 * Sanitize schedule date (YYYY-MM-DD format)
	 *
	 * @param mixed $date Date value to sanitize
	 * @return string Empty string or valid YYYY-MM-DD date
	 */
	public static function sanitize_schedule_date( $date ): string {
		// Handle null/empty
		if ( empty( $date ) ) {
			return '';
		}

		// Clean the input
		$date = sanitize_text_field( trim( $date ) );

		// Extract date part if datetime format (handles "2024-01-15 00:00:00" -> "2024-01-15")
		if ( strlen( $date ) > 10 && strpos( $date, ' ' ) !== false ) {
			$date = substr( $date, 0, 10 );
		}

		// Validate YYYY-MM-DD format
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return '';
		}

		// Validate it's a real date
		$parts = explode( '-', $date );
		if ( ! checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
			return '';
		}

		return $date;
	}

	/**
	 * Sanitize CSS
	 *
	 * @param string $css CSS string
	 *
	 * @return string
	 */
	public static function sanitize_css( string $css ): string {
		// Remove script tags
		$css = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $css );

		// Remove javascript: protocol
		$css = preg_replace( '/javascript\s*:/i', '', $css );

		// Remove CSS expressions (IE)
		$css = preg_replace( '/expression\s*\(/i', '', $css );

		// Remove data: URIs (potential XSS vector)
		$css = preg_replace( '/data\s*:/i', '', $css );

		// Remove vbscript: protocol
		$css = preg_replace( '/vbscript\s*:/i', '', $css );

		// Remove behavior: property (IE)
		$css = preg_replace( '/behavior\s*:/i', '', $css );

		// Remove -moz-binding (XBL)
		$css = preg_replace( '/-moz-binding\s*:/i', '', $css );

		// Remove import statements
		$css = preg_replace( '/@import/i', '', $css );

		// Strip all HTML tags
		$css = wp_strip_all_tags( $css );

		// Limit length to prevent DoS
		return substr( $css, 0, 5000 );
	}

	/**
	 * Valid analytics retention values
	 */
	public const VALID_RETENTION = [ '1', '7', '30', '90', '180', '365', 'custom', 'unlimited' ];

	/**
	 * Sanitize nested settings structure
	 *
	 * @param array $settings Nested settings to sanitize
	 *
	 * @return array
	 */
	public static function sanitize_settings_nested( array $settings ): array {
		$sanitized = [];

		// Analytics section
		if ( isset( $settings['analytics'] ) && is_array( $settings['analytics'] ) ) {
			$sanitized['analytics'] = [];

			if ( isset( $settings['analytics']['enabled'] ) ) {
				$sanitized['analytics']['enabled'] = self::sanitize_boolean( $settings['analytics']['enabled'] );
			}

			if ( isset( $settings['analytics']['retention'] ) ) {
				$sanitized['analytics']['retention'] = self::sanitize_retention( $settings['analytics']['retention'] );
			}

			if ( isset( $settings['analytics']['retention_custom_days'] ) ) {
				$sanitized['analytics']['retention_custom_days'] = self::sanitize_retention_days(
					$settings['analytics']['retention_custom_days']
				);
			}
		}

		// Custom CSS section
		if ( isset( $settings['custom_css'] ) && is_array( $settings['custom_css'] ) ) {
			$sanitized['custom_css'] = [];

			if ( isset( $settings['custom_css']['css'] ) ) {
				$sanitized['custom_css']['css'] = self::sanitize_css( $settings['custom_css']['css'] );
			}
		}

		// Custom Icons section (icons are sanitized individually when added)
		if ( isset( $settings['custom_icons'] ) && is_array( $settings['custom_icons'] ) ) {
			$sanitized['custom_icons'] = [];

			if ( isset( $settings['custom_icons']['icons'] ) && is_array( $settings['custom_icons']['icons'] ) ) {
				$sanitized['custom_icons']['icons'] = array_values( $settings['custom_icons']['icons'] );
			}
		}

		// Performance section
		if ( isset( $settings['performance'] ) && is_array( $settings['performance'] ) ) {
			$sanitized['performance'] = [];

			if ( isset( $settings['performance']['load_scripts_footer'] ) ) {
				$sanitized['performance']['load_scripts_footer'] = self::sanitize_boolean(
					$settings['performance']['load_scripts_footer']
				);
			}
		}

		// Data Management section
		if ( isset( $settings['data_management'] ) && is_array( $settings['data_management'] ) ) {
			$sanitized['data_management'] = [];

			if ( isset( $settings['data_management']['delete_on_uninstall'] ) ) {
				$sanitized['data_management']['delete_on_uninstall'] = self::sanitize_boolean(
					$settings['data_management']['delete_on_uninstall']
				);
			}
		}

		// Preserve version marker
		if ( isset( $settings['_settings_version'] ) ) {
			$sanitized['_settings_version'] = absint( $settings['_settings_version'] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize retention value
	 *
	 * @param string $value Retention value
	 *
	 * @return string
	 */
	public static function sanitize_retention( string $value ): string {
		return in_array( $value, self::VALID_RETENTION, true ) ? $value : '1';
	}

	/**
	 * Sanitize custom retention days
	 *
	 * @param mixed $value Days value
	 *
	 * @return int
	 */
	public static function sanitize_retention_days( mixed $value ): int {
		$days = absint( $value );
		return max( 1, min( $days, 3650 ) );
	}

	/**
	 * Sanitize slide-in settings
	 *
	 * @param array $settings Slide-in settings to sanitize
	 *
	 * @return array
	 */
	public static function sanitize_slide_in_settings( array $settings ): array {
		$sanitized = [
			'trigger_type'       => 'time',
			'trigger_delay'      => 3,
			'trigger_scroll'     => 50,
			'position'           => 'bottom-right',
			'show_animation'     => 'slide-in',
			'hide_animation'     => 'slide-out',
			'auto_dismiss'       => true,
			'auto_dismiss_delay' => 10,
			'dismiss_behavior'   => 'session',
		];

		// Validate trigger_type
		if ( isset( $settings['trigger_type'] ) ) {
			$trigger_type = sanitize_text_field( $settings['trigger_type'] );
			if ( in_array( $trigger_type, self::VALID_TRIGGER_TYPES, true ) ) {
				$sanitized['trigger_type'] = $trigger_type;
			}
		}

		// Validate trigger_delay (seconds, must be >= 0)
		if ( isset( $settings['trigger_delay'] ) ) {
			$delay = (int) $settings['trigger_delay'];
			$sanitized['trigger_delay'] = max( 0, min( $delay, 3600 ) ); // 0-3600 seconds (1 hour)
		}

		// Validate trigger_scroll (percentage, must be 0-100)
		if ( isset( $settings['trigger_scroll'] ) ) {
			$scroll = (int) $settings['trigger_scroll'];
			$sanitized['trigger_scroll'] = max( 0, min( $scroll, 100 ) );
		}

		// Validate position
		if ( isset( $settings['position'] ) ) {
			$position = sanitize_text_field( $settings['position'] );
			if ( in_array( $position, self::VALID_POSITIONS, true ) ) {
				$sanitized['position'] = $position;
			}
		}

		// Validate show_animation
		if ( isset( $settings['show_animation'] ) ) {
			$animation = sanitize_text_field( $settings['show_animation'] );
			if ( in_array( $animation, [ 'none', 'slide-in', 'fade-in' ], true ) ) {
				$sanitized['show_animation'] = $animation;
			}
		}

		// Validate hide_animation
		if ( isset( $settings['hide_animation'] ) ) {
			$animation = sanitize_text_field( $settings['hide_animation'] );
			if ( in_array( $animation, [ 'none', 'slide-out', 'fade-out' ], true ) ) {
				$sanitized['hide_animation'] = $animation;
			}
		}

		// Validate auto_dismiss
		if ( isset( $settings['auto_dismiss'] ) ) {
			$sanitized['auto_dismiss'] = self::sanitize_boolean( $settings['auto_dismiss'] );
		}

		// Validate auto_dismiss_delay (seconds, must be > 0)
		if ( isset( $settings['auto_dismiss_delay'] ) ) {
			$delay = (int) $settings['auto_dismiss_delay'];
			$sanitized['auto_dismiss_delay'] = max( 1, min( $delay, 3600 ) ); // 1-3600 seconds (1 hour)
		}

		// Validate dismiss_behavior
		if ( isset( $settings['dismiss_behavior'] ) ) {
			$behavior = sanitize_text_field( $settings['dismiss_behavior'] );
			if ( in_array( $behavior, self::VALID_DISMISS_BEHAVIORS, true ) ) {
				$sanitized['dismiss_behavior'] = $behavior;
			}
		}

		return $sanitized;
	}

}
