<?php
/**
 * Debug Logging Handler
 *
 * Handles debug logging operations for CTA Manager runtime diagnostics.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Debug {

	/**
	 * Cached debug enabled state
	 *
	 * @var bool|null
	 */
	private static ?bool $enabled = null;

	/**
	 * Log prefix
	 *
	 * @var string
	 */
	private const PREFIX = 'CTA Manager';

	/**
	 * Check if debug mode is enabled
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		if ( self::$enabled === null ) {
			$settings = CTA_Data::get_instance()->get_settings();
			self::$enabled = ! empty( $settings['debug']['enabled'] );
		}
		return self::$enabled;
	}

	/**
	 * Clear the cached enabled state (call after settings change)
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$enabled = null;
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message Message to log.
	 * @param string $context Optional context/category (e.g., 'shortcode', 'ajax', 'tracking').
	 * @param mixed  $data    Optional data to include (will be JSON encoded).
	 * @return void
	 */
	public static function log( string $message, string $context = '', $data = null ): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		$log_message = self::PREFIX;

		if ( $context ) {
			$log_message .= ' [' . strtoupper( $context ) . ']';
		}

		$log_message .= ': ' . $message;

		if ( $data !== null ) {
			if ( is_array( $data ) || is_object( $data ) ) {
				$log_message .= ' | Data: ' . wp_json_encode( $data );
			} else {
				$log_message .= ' | Data: ' . (string) $data;
			}
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $log_message );
	}

	/**
	 * Log an info-level message
	 *
	 * @param string $message Message to log.
	 * @param string $context Optional context.
	 * @param mixed  $data    Optional data.
	 * @return void
	 */
	public static function info( string $message, string $context = '', $data = null ): void {
		self::log( '[INFO] ' . $message, $context, $data );
	}

	/**
	 * Log a warning-level message
	 *
	 * @param string $message Message to log.
	 * @param string $context Optional context.
	 * @param mixed  $data    Optional data.
	 * @return void
	 */
	public static function warn( string $message, string $context = '', $data = null ): void {
		self::log( '[WARN] ' . $message, $context, $data );
	}

	/**
	 * Log an error-level message
	 *
	 * @param string $message Message to log.
	 * @param string $context Optional context.
	 * @param mixed  $data    Optional data.
	 * @return void
	 */
	public static function error( string $message, string $context = '', $data = null ): void {
		self::log( '[ERROR] ' . $message, $context, $data );
	}

	/**
	 * Log AJAX request details
	 *
	 * @param string $action AJAX action name.
	 * @param array  $params Request parameters (sensitive data will be redacted).
	 * @return void
	 */
	public static function ajax( string $action, array $params = [] ): void {
		// Redact sensitive fields
		$sensitive_keys = [ 'password', 'secret', 'api_key', 'license_key', 'token' ];
		foreach ( $sensitive_keys as $key ) {
			if ( isset( $params[ $key ] ) ) {
				$params[ $key ] = '[REDACTED]';
			}
		}

		self::log( 'AJAX request: ' . $action, 'ajax', $params );
	}

	/**
	 * Log CTA rendering details
	 *
	 * @param int    $cta_id CTA ID.
	 * @param string $status Status message.
	 * @param array  $details Additional details.
	 * @return void
	 */
	public static function render( int $cta_id, string $status, array $details = [] ): void {
		self::log( sprintf( 'CTA #%d: %s', $cta_id, $status ), 'render', $details );
	}

	/**
	 * Log tracking event
	 *
	 * @param string $event_type Event type (impression, click, conversion).
	 * @param int    $cta_id     CTA ID.
	 * @param array  $data       Event data.
	 * @return void
	 */
	public static function tracking( string $event_type, int $cta_id, array $data = [] ): void {
		self::log( sprintf( '%s event for CTA #%d', ucfirst( $event_type ), $cta_id ), 'tracking', $data );
	}

	/**
	 * Log database operation
	 *
	 * @param string $operation Operation type (insert, update, delete, query).
	 * @param string $table     Table name.
	 * @param mixed  $details   Operation details.
	 * @return void
	 */
	public static function db( string $operation, string $table, $details = null ): void {
		self::log( sprintf( 'DB %s on %s', strtoupper( $operation ), $table ), 'database', $details );
	}

	/**
	 * Log timing for performance debugging
	 *
	 * @param string $label   Operation label.
	 * @param float  $start   Microtime start (from microtime(true)).
	 * @param array  $context Additional context.
	 * @return void
	 */
	public static function timing( string $label, float $start, array $context = [] ): void {
		$elapsed = microtime( true ) - $start;
		$ms = round( $elapsed * 1000, 2 );
		self::log( sprintf( '%s completed in %sms', $label, $ms ), 'timing', $context );
	}

	/**
	 * Start a timing operation
	 *
	 * @return float Microtime for use with timing()
	 */
	public static function start_timer(): float {
		return microtime( true );
	}

	/**
	 * Dump variable contents for debugging
	 *
	 * @param string $label Variable label.
	 * @param mixed  $value Value to dump.
	 * @return void
	 */
	public static function dump( string $label, $value ): void {
		self::log( $label, 'dump', $value );
	}

	/**
	 * Log a deprecated function/feature usage
	 *
	 * @param string $feature     Deprecated feature name.
	 * @param string $replacement Suggested replacement.
	 * @param string $version     Version when deprecated.
	 * @return void
	 */
	public static function deprecated( string $feature, string $replacement = '', string $version = '' ): void {
		$message = sprintf( '"%s" is deprecated', $feature );
		if ( $version ) {
			$message .= sprintf( ' since version %s', $version );
		}
		if ( $replacement ) {
			$message .= sprintf( '. Use "%s" instead.', $replacement );
		}
		self::warn( $message, 'deprecated' );
	}
}
