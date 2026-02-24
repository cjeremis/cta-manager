<?php
/**
 * Advanced Embedding Handler
 *
 * Handles advanced embedding attributes and wrapper rendering behavior.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Advanced_Embedding {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'cta_shortcode_attributes', [ $this, 'add_advanced_attributes' ], 10, 2 );
		add_filter( 'cta_wrapper_attributes', [ $this, 'add_wrapper_attributes' ], 10, 2 );
	}

	public function add_advanced_attributes( $attributes, $cta ) {
		// Add CTA HTML ID
		$cta_html_id_raw = trim( $cta['cta_html_id'] ?? '' );
		if ( '' !== $cta_html_id_raw ) {
			$cta_html_id = sanitize_html_class( $cta_html_id_raw );
			if ( '' !== $cta_html_id ) {
				$attributes['id'] = $cta_html_id;
			}
		}

		// Add CTA classes
		$cta_classes_raw = trim( $cta['cta_classes'] ?? '' );
		if ( '' !== $cta_classes_raw ) {
			$cta_classes = [];
			$cta_classes_list = preg_split( '/\s*,\s*/', $cta_classes_raw );
			foreach ( $cta_classes_list as $cta_class ) {
				$cta_class = sanitize_html_class( $cta_class );
				if ( '' !== $cta_class ) {
					$cta_classes[] = $cta_class;
				}
			}
			if ( ! empty( $cta_classes ) ) {
				$attributes['classes'] = $cta_classes;
			}
		}

		// Add data attributes
		if ( ! empty( $cta['data_attributes'] ) && is_array( $cta['data_attributes'] ) ) {
			foreach ( $cta['data_attributes'] as $attribute ) {
				$key = trim( $attribute['key'] ?? '' );
				$value = $attribute['value'] ?? '';
				if ( '' === $key ) {
					continue;
				}
				$key = sanitize_key( $key );
				if ( '' === $key ) {
					continue;
				}
				if ( 0 !== strpos( $key, 'data-' ) ) {
					$key = 'data-' . ltrim( $key, '-' );
				}
				$attributes[ $key ] = esc_attr( $value );
			}
		}

		// Note: wrapper_id and wrapper_classes are handled separately by the shortcode
		// and card rendering classes, so they are not added to button attributes

		return $attributes;
	}

	public function add_wrapper_attributes( $wrapper_attrs, $cta ) {
		// Add wrapper ID
		$wrapper_id_raw = trim( $cta['wrapper_id'] ?? '' );
		if ( '' !== $wrapper_id_raw ) {
			$wrapper_id = sanitize_html_class( $wrapper_id_raw );
			if ( '' !== $wrapper_id ) {
				$wrapper_attrs['id'] = $wrapper_id;
			}
		}

		// Add wrapper classes
		$wrapper_classes_raw = trim( $cta['wrapper_classes'] ?? '' );
		if ( '' !== $wrapper_classes_raw ) {
			$wrapper_classes = [];
			$wrapper_classes_list = preg_split( '/\s*,\s*/', $wrapper_classes_raw );
			foreach ( $wrapper_classes_list as $wrapper_class ) {
				$wrapper_class = sanitize_html_class( $wrapper_class );
				if ( '' !== $wrapper_class ) {
					$wrapper_classes[] = $wrapper_class;
				}
			}
			if ( ! empty( $wrapper_classes ) ) {
				$wrapper_attrs['class'] = implode( ' ', $wrapper_classes );
			}
		}

		return $wrapper_attrs;
	}
}
