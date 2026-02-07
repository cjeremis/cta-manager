<?php
/**
 * CTA Manager Header Partial
 *
 * Reusable header section that appears at the top of all CTA Manager admin pages.
 *
 * Expected variables:
 * - string $header_title       Page title for the h1
 * - string $header_description Page description for the paragraph
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_title       = isset( $header_title ) ? $header_title : '';
$header_description = isset( $header_description ) ? $header_description : '';
$current_page       = isset( $current_page ) ? $current_page : '';

// Apply filters to allow Pro customization
$header_config = apply_filters(
	'cta_header_config',
	[
		'title'       => $header_title,
		'description' => $header_description,
	],
	$current_page
);

$header_title       = isset( $header_config['title'] ) ? $header_config['title'] : $header_title;
$header_description = isset( $header_config['description'] ) ? $header_config['description'] : $header_description;
?>

<div class="cta-header">
	<h1><?php echo wp_kses_post( $header_title ); ?></h1>
	<p><?php echo esc_html( $header_description ); ?></p>
</div>
