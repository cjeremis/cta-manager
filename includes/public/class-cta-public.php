<?php
/**
 * Public Controller Handler
 *
 * Handles public asset loading and frontend runtime setup.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Public {

	use CTA_Singleton;

	/**
	 * Enqueue public styles
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$data = CTA_Data::get_instance();

		$has_cta = false;
		foreach ( $data->get_ctas() as $cta ) {
			if ( ! empty( $cta['enabled'] ) ) {
				$has_cta = true;
				break;
			}
		}

		if ( ! $has_cta ) {
			return;
		}

		// Public styles (minimized)
		wp_enqueue_style(
			'cta-public',
			CTA_PLUGIN_URL . 'assets/css/minimized/public/public.min.css',
			[],
			CTA_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue public scripts
	 *
	 * Uses webpack-compiled minimized bundles.
	 * - public.js: Main public bundle (tracker)
	 *
	 * Respects the "Load CTA Scripts in Footer" performance setting:
	 * - When enabled: Scripts load in footer for better page load performance
	 * - When disabled: Scripts load in head alongside other scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$data = CTA_Data::get_instance();

		$has_cta = false;
		foreach ( $data->get_ctas() as $cta ) {
			if ( ! empty( $cta['enabled'] ) ) {
				$has_cta = true;
				break;
			}
		}

		if ( ! $has_cta ) {
			return;
		}

		// Check performance setting for footer loading (Pro feature)
		$settings = $data->get_settings();
		$load_in_footer = ! empty( $settings['performance']['load_scripts_footer'] );
		$debug_enabled = ! empty( $settings['debug']['enabled'] );

		// Main public bundle
		wp_enqueue_script(
			'cta-public',
			CTA_PLUGIN_URL . 'assets/js/minimized/public/public.min.js',
			[ 'jquery' ],
			CTA_VERSION,
			$load_in_footer
		);

		/**
		 * Filter whether automatic impression tracking is enabled.
		 *
		 * When enabled (default), impressions are tracked automatically on page load
		 * for all CTAs visible on the page. When disabled, impressions must be
		 * triggered manually using CTATracker.triggerImpression().
		 *
		 * @since 1.0.0
		 *
		 * @param bool $enabled Whether auto impressions are enabled. Default true.
		 */
		$auto_impressions_enabled = apply_filters( 'cta_auto_impressions_enabled', true );

		wp_localize_script(
			'cta-public',
			'ctaTrackerVars',
			[
				'nonce'                  => wp_create_nonce( 'cta_public_nonce' ),
				'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
				'debug'                  => $debug_enabled,
				'autoImpressionsEnabled' => $auto_impressions_enabled,
			]
		);
	}
}
