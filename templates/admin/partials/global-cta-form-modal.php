<?php
/**
 * Global CTA form modal
 *
 * Reuses the CTA Manager form so it can be launched from any admin page.
 *
 * @package CTA_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_data_instance = CTA_Data::get_instance();
$is_pro_modal      = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

$pro_numbers_modal = ( $is_pro_modal && class_exists( 'CTA_Pro_Phone_Numbers' ) ) ? CTA_Pro_Phone_Numbers::get_instance()->get_all() : [];

$cta_form_context = [
	'editing_cta' => null,
	'show_form'   => true,
	'force_render' => true,
	'form_action' => CTA_Admin_Menu::get_admin_url( 'cta' ),
	'is_pro'      => $is_pro_modal,
	'pro_numbers' => $pro_numbers_modal,
];

ob_start();
include __DIR__ . '/cta-manager-form.php';
$cta_global_form_markup = ob_get_clean();
?>

<?php
// Build header actions HTML: save button
ob_start();
?>
<button type="button" id="cta-modal-save-btn" class="cta-button-primary">
	<?php esc_html_e( 'Save CTA', 'cta-manager' ); ?>
</button>
<?php
$header_actions_html = ob_get_clean();

$modal = [
	'id'             => 'cta-global-form-modal',
	'title_html'     => '<span class="dashicons dashicons-welcome-add-page"></span>' . esc_html__( 'New CTA', 'cta-manager' ),
	'header_actions' => $header_actions_html,
	'template'       => CTA_PLUGIN_DIR . 'templates/admin/modals/cta-global-form.php',
	'body_context'   => [ 'form_markup' => $cta_global_form_markup ],
	'size_class'     => 'cta-modal-xl',
	'extra_class'    => 'cta-global-form-modal',
	'display'        => 'none',
];
include __DIR__ . '/modal.php';
unset( $modal, $cta_global_form_markup, $header_actions_html );
