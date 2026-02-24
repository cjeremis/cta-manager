<?php
/**
 * Validator Handler
 *
 * Handles validation of CTA Manager input and import data.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Validator {

	/**
	 * Validate import data
	 *
	 * Supports flexible partial imports:
	 * - Only ctas, only settings, only analytics, or any combination
	 * - Missing keys within parent nodes are allowed
	 * - Plugin identifier is validated if present
	 * - Version compatibility is checked if version is present
	 *
	 * Valid data keys:
	 * - settings, ctas, analytics, custom_icons, onboarding
	 * - notifications, settings_table
	 * - ab_tests, ab_events (Pro)
	 *
	 * @param array $data Import data
	 *
	 * @return array Array of error messages (empty if valid)
	 */
	public static function validate_import_data( array $data ): array {
		$errors = [];

		// If plugin identifier is present, it must match
		if ( isset( $data['plugin'] ) && $data['plugin'] !== 'cta-manager' ) {
			$errors[] = __( 'Invalid export file: plugin identifier mismatch.', 'cta-manager' );
		}

		// If version is present, check compatibility
		if ( isset( $data['version'] ) && version_compare( $data['version'], CTA_VERSION, '>' ) ) {
			$errors[] = sprintf(
				/* translators: %1$s: file version, %2$s: installed version */
				__( 'Import file is from a newer version (%1$s) than installed (%2$s).', 'cta-manager' ),
				esc_html( $data['version'] ),
				CTA_VERSION
			);
		}

		// Valid data keys that can be imported
		$valid_keys = [
			'settings',
			'ctas',
			'analytics',
			'custom_icons',
			'onboarding',
			'notifications',
			'settings_table',
			'ab_tests',
			'ab_events',
		];

		// Check that at least one valid data type is present
		$has_data = false;
		foreach ( $valid_keys as $key ) {
			if ( isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
				$has_data = true;
				break;
			}
		}

		if ( ! $has_data ) {
			$errors[] = __( 'Invalid import file: no valid data found.', 'cta-manager' );
		}

		// Validate CTAs structure if present
		if ( isset( $data['ctas'] ) && is_array( $data['ctas'] ) ) {
			foreach ( $data['ctas'] as $index => $cta ) {
				if ( ! is_array( $cta ) ) {
					$errors[] = sprintf(
						/* translators: %d: CTA index */
						__( 'Invalid CTA at index %d: must be an array.', 'cta-manager' ),
						$index
					);
				}
			}
		}

		// Validate analytics events structure if present
		if ( isset( $data['analytics']['events'] ) && ! is_array( $data['analytics']['events'] ) ) {
			$errors[] = __( 'Invalid analytics data: events must be an array.', 'cta-manager' );
		}

		return $errors;
	}

	/**
	 * Validate nested settings structure
	 *
	 * @param array $settings Nested settings to validate
	 *
	 * @return array Validation errors (empty if valid) with dot-notation keys
	 */
	public static function validate_settings_nested( array $settings ): array {
		$errors = [];

		// Validate analytics section
		if ( isset( $settings['analytics'] ) && is_array( $settings['analytics'] ) ) {
			// Validate retention value
			if ( isset( $settings['analytics']['retention'] ) ) {
				if ( ! in_array( $settings['analytics']['retention'], CTA_Sanitizer::VALID_RETENTION, true ) ) {
					$errors['analytics.retention'] = __( 'Invalid retention period.', 'cta-manager' );
				}
			}

			// Validate custom retention days
			if ( isset( $settings['analytics']['retention_custom_days'] ) ) {
				$days = (int) $settings['analytics']['retention_custom_days'];
				if ( $days < 1 || $days > 3650 ) {
					$errors['analytics.retention_custom_days'] = __(
						'Custom retention must be between 1 and 3650 days.',
						'cta-manager'
					);
				}
			}
		}

		// Validate custom CSS section
		if ( isset( $settings['custom_css']['css'] ) ) {
			$css = $settings['custom_css']['css'];
			if ( strlen( $css ) > 5000 ) {
				$errors['custom_css.css'] = __(
					'Custom CSS must be 5000 characters or less.',
					'cta-manager'
				);
			}
		}

		return $errors;
	}
}
