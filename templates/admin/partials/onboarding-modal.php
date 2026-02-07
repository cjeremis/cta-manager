<?php
/**
 * Get Started / Onboarding Modal
 *
 * Global onboarding wizard modal available on all plugin admin pages.
 * Uses the shared modal.php wrapper for consistent styling and behavior.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get onboarding state from CTA_Onboarding class
$onboarding_state = CTA_Onboarding::get_state();
$completed_steps = $onboarding_state['completed_steps'];

$is_pro_active = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$welcome_title = $is_pro_active
	? __( 'Welcome to CTA Manager Pro!', 'cta-manager' )
	: __( 'Welcome to CTA Manager!', 'cta-manager' );

// Configure and render the modal using the shared wrapper
$modal = [
	'id'           => 'cta-dashboard-help-modal',
	'extra_class'  => 'cta-onboarding-modal',
	'title_html'   => '<span class="dashicons dashicons-star-filled"></span>' . esc_html( $welcome_title ),
	'template'     => CTA_PLUGIN_DIR . 'templates/admin/modals/onboarding.php',
	'body_context' => [
		'completed_steps' => $completed_steps,
	],
	'size_class'   => 'cta-pro-modal-card',
	'display'      => 'none',
];
include __DIR__ . '/modal.php';
unset( $modal );
