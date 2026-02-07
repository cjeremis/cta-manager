<?php
/**
 * Global footer partial for CTA Manager admin pages.
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$year        = gmdate( 'Y' );
$version     = defined( 'CTA_VERSION' ) ? CTA_VERSION : '';
$pro_version = defined( 'CTA_PRO_VERSION' ) ? CTA_PRO_VERSION : '';
$is_pro      = apply_filters( 'cta_manager_pro_enabled', false );
?>
<!-- Pro Upsell Footer -->
<?php include __DIR__ . '/pro-upsell-footer.php'; ?>

<div class="cta-footer">
	<span>
		<?php
		if ( $is_pro && $pro_version ) {
			echo esc_html(
				sprintf(
					__( 'CTA Manager v%s | CTA Manager Pro v%s', 'cta-manager' ),
					$version,
					$pro_version
				)
			);
		} else {
			echo esc_html(
				sprintf(
					__( 'CTA Manager v%s', 'cta-manager' ),
					$version
				)
			);
		}
		?>
	</span>
</div>

<?php
// Global CTA form modal (available on all plugin admin pages).
include __DIR__ . '/global-cta-form-modal.php';

// Global Get Started modal (available on all plugin admin pages).
// Only load if there are no active non-demo CTAs
if ( CTA_Onboarding::should_show() ) {
	include __DIR__ . '/onboarding-modal.php';
}

// Features modal (available on all plugin admin pages).
include __DIR__ . '/features-modal.php';

// Global form helper modals (Blacklist and Format) - available for all form contexts.
?>

<!-- Blacklist URLs Modal -->
<?php
$modal = [
	'id'         => 'cta-blacklist-modal',
	'title_html' => '<span class="dashicons dashicons-dismiss"></span>' . esc_html__( 'Blacklist URLs', 'cta-manager' ),
	'template'   => CTA_PLUGIN_DIR . 'templates/admin/modals/cta-blacklist.php',
	'display'    => 'none',
];
include __DIR__ . '/modal.php';
unset( $modal );
?>

<!-- Text Formatting Modal -->
<?php
$modal = [
	'id'             => 'cta-format-modal',
	'title_html'     => '<span class="dashicons dashicons-editor-textcolor"></span>' . esc_html__( 'Format Text', 'cta-manager' ),
	'template'       => CTA_PLUGIN_DIR . 'templates/admin/modals/cta-format.php',
	'size_class'     => 'cta-modal-md',
	'extra_class'    => 'cta-format-modal',
	'display'        => 'none',
	'header_actions' => '<button type="button" class="cta-button-primary" data-action="apply">' . esc_html__( 'Apply', 'cta-manager' ) . '</button>',
];
include __DIR__ . '/modal.php';
unset( $modal );
?>
