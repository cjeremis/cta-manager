<?php
/**
 * Singleton Trait
 *
 * Handles shared singleton instance behavior for CTA Manager classes.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait CTA_Singleton {

	/**
	 * Stores the singleton instance
	 *
	 * @var static|null
	 */
	private static ?self $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return static
	 */
	public static function get_instance(): static {
		if ( null === self::$instance ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning
	 *
	 * @throws Exception
	 */
	private function __clone() {
		throw new Exception( 'Cannot clone singleton instance' );
	}

	/**
	 * Prevent unserialization
	 *
	 * @throws Exception
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton' );
	}
}
