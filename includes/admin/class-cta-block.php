<?php
/**
 * Gutenberg Block Registration
 *
 * Handles block registration and editor assets for CTA Manager.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Block {

	use CTA_Singleton;

	/**
	 * Register block assets and block type.
	 *
	 * @return void
	 */
	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$handle = 'cta-manager-block-editor';
		$block_script_path = CTA_PLUGIN_DIR . 'assets/minimized/js/admin/cta-block.min.js';
		$block_script_ver  = file_exists( $block_script_path ) ? (string) filemtime( $block_script_path ) : CTA_VERSION;
		wp_register_script(
			$handle,
			CTA_PLUGIN_URL . 'assets/minimized/js/admin/cta-block.min.js',
			[
				'wp-blocks',
				'wp-element',
				'wp-i18n',
				'wp-components',
				'wp-editor',
				'wp-block-editor',
				'wp-api-fetch',
			],
			$block_script_ver,
			true
		);

		register_block_type(
			'cta-manager/cta',
			[
				'editor_script'   => $handle,
				'render_callback' => [ $this, 'render_block' ],
				'attributes'      => [
					'ctaId' => [
						'type'    => 'number',
						'default' => 0,
					],
				],
			]
		);
	}

	/**
	 * Register REST routes for block data.
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'cta-manager/v1',
			'/ctas',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_active_ctas' ],
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Get published + enabled CTAs for the block selector.
	 *
	 * @return WP_REST_Response
	 */
	public function get_active_ctas(): WP_REST_Response {
		$repo = CTA_Repository::get_instance();
		$ctas = $repo->get_all(
			[
				'status'       => CTA_Repository::STATUS_PUBLISHED,
				'is_enabled'   => true,
				'exclude_demo' => true,
				'orderby'      => 'id',
				'order'        => 'ASC',
			]
		);

		$items = [];
		foreach ( $ctas as $cta ) {
			$id = isset( $cta['id'] ) ? (int) $cta['id'] : 0;
			if ( ! $id ) {
				continue;
			}
			$name = $cta['name'] ?? '';
			$title = $cta['title'] ?? '';
			$label = $name ?: $title;
			if ( '' === $label ) {
				$label = sprintf( __( 'CTA #%d', 'cta-manager' ), $id );
			}

			$items[] = [
				'id'    => $id,
				'label' => $label,
			];
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Render block output (frontend).
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_block( array $attributes ): string {
		$cta_id = isset( $attributes['ctaId'] ) ? absint( $attributes['ctaId'] ) : 0;
		if ( ! $cta_id ) {
			return '';
		}

		return do_shortcode( sprintf( '[cta-manager id="%d"]', $cta_id ) );
	}
}
