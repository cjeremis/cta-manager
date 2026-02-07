<?php
/**
 * Features Modal
 *
 * Displays all CTA Manager features and integrations in a tabbed modal interface.
 * Triggered by the awards icon in the support toolbar.
 *
 * @package CTA_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure features registry is available.
if ( ! class_exists( 'CTA_Features' ) ) {
	require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-features.php';
}

// Get features and integrations from centralized registry
$all_features = CTA_Features::get_all_features();
$integrations = CTA_Features::get_all_integrations();

// Pass data to modal body template
$modal = [
	'id'           => 'cta-features-modal',
	'title_html'   => '<span class="dashicons dashicons-awards"></span>' . esc_html__( 'CTA Manager Features', 'cta-manager' ),
	'template'     => CTA_PLUGIN_DIR . 'templates/admin/modals/features-modal.php',
	'body_context' => [
		'all_features'  => $all_features,
		'integrations'  => $integrations,
	],
	'size_class'   => 'cta-modal-xl',
	'extra_class'    => 'cta-features-modal',
	'display'        => 'none',
];

include __DIR__ . '/modal.php';
unset( $modal, $all_features, $integrations );
