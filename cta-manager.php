<?php
/**
 * CTA Manager
 *
 * Add cta-manager buttons to your WordPress site with beautiful themes and analytics.
 *
 * @package CTAManager
 * @since 1.0.0
 *
 * Plugin Name: CTA Manager
 * Plugin URI: https://topdevamerica.com/plugins/cta-manager
 * Description: Add cta-manager buttons to your WordPress site with beautiful themes and analytics.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: TopDevAmerica
 * Author URI: https://topdevamerica.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cta-manager
 * Domain Path: /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'CTA_VERSION', '1.0.0' );
define( 'CTA_DB_VERSION', '1.2.0' ); // Database schema version for migrations
define( 'CTA_PLUGIN_FILE', __FILE__ );
define( 'CTA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CTA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CTA_MIN_WP_VERSION', '6.0' );
define( 'CTA_MIN_PHP_VERSION', '8.0' );

/**
 * Autoloader for CTA classes and traits
 */
spl_autoload_register( function( $class ) {
	// Support CTA_ prefix
	$prefix = 'CTA_';

	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}

	$class_name = str_replace( $prefix, '', $class );
	$class_name = strtolower( str_replace( '_', '-', $class_name ) );
	$prefix_lower = 'cta';

	$directories = [
		CTA_PLUGIN_DIR . 'includes/',
		CTA_PLUGIN_DIR . 'includes/admin/',
		CTA_PLUGIN_DIR . 'includes/public/',
		CTA_PLUGIN_DIR . 'includes/core/',
		CTA_PLUGIN_DIR . 'includes/traits/',
	];

	foreach ( $directories as $directory ) {
		// Check for class file with prefix
		$file = $directory . 'class-' . $prefix_lower . '-' . $class_name . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}

		// Check for trait file with prefix
		$trait_file = $directory . 'trait-' . $prefix_lower . '-' . $class_name . '.php';
		if ( file_exists( $trait_file ) ) {
			require_once $trait_file;
			return;
		}
	}
} );

// Activation hook
register_activation_hook( __FILE__, [ 'CTA_MANAGER_ACTIVATOR', 'activate' ] );

// Deactivation hook
register_deactivation_hook( __FILE__, [ 'CTA_Deactivator', 'deactivate' ] );

// Initialize plugin
function cta_init() {
	// Version check
	if ( version_compare( PHP_VERSION, CTA_MIN_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'cta_php_version_notice' );
		return;
	}

	if ( version_compare( get_bloginfo( 'version' ), CTA_MIN_WP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'cta_wp_version_notice' );
		return;
	}

	// Load text domain
	load_plugin_textdomain(
		'cta-manager',
		false,
		dirname( CTA_PLUGIN_BASENAME ) . '/languages'
	);

	// Load template helpers
	require_once CTA_PLUGIN_DIR . 'includes/template-helpers.php';

	// Initialize loader
	$loader = CTA_Loader::get_instance();
	$loader->run();
}
add_action( 'plugins_loaded', 'cta_init' );



/**
 * Display PHP version notice
 *
 * @return void
 */
function cta_php_version_notice() {
	$message = sprintf(
		/* translators: 1: Required PHP version, 2: Current PHP version */
		esc_html__( 'CTA Manager requires PHP %1$s or higher. You are running PHP %2$s.', 'cta-manager' ),
		CTA_MIN_PHP_VERSION,
		PHP_VERSION
	);
	echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
}

/**
 * Display WordPress version notice
 *
 * @return void
 */
function cta_wp_version_notice() {
	$message = sprintf(
		/* translators: 1: Required WP version, 2: Current WP version */
		esc_html__( 'CTA Manager requires WordPress %1$s or higher. You are running WordPress %2$s.', 'cta-manager' ),
		CTA_MIN_WP_VERSION,
		get_bloginfo( 'version' )
	);
	if ( function_exists( 'wp_admin_notice' ) ) {
		wp_admin_notice( $message, array( 'type' => 'error' ) );
	} else {
		echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
	}
}
