<?php
/**
 * Admin Modal Template - Cta Add Icon Cta
 *
 * Handles markup rendering for the cta add icon cta admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pro_numbers = $pro_numbers ?? [];
$blacklist_urls = $blacklist_urls ?? [];
?>
<form id="cta-add-icon-form-cta">
	<div class="cta-form-group">
		<label for="cta-icon-name-cta">
			<?php esc_html_e( 'Icon Name', 'cta-manager' ); ?>
			<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/required-marker.php'; ?>
		</label>
		<input type="text" id="cta-icon-name-cta" name="icon_name" required placeholder="<?php esc_attr_e( 'e.g., Arrow Circle, Download Alt', 'cta-manager' ); ?>" />
		<?php cta_helper_text( __( 'A descriptive name for your icon that will appear in the dropdown.', 'cta-manager' ) ); ?>
	</div>

	<div class="cta-form-group">
		<label for="cta-icon-svg-cta">
			<?php esc_html_e( 'SVG Code', 'cta-manager' ); ?>
			<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/required-marker.php'; ?>
		</label>
		<textarea id="cta-icon-svg-cta" name="icon_svg" required rows="8" placeholder="<?php esc_attr_e( '<svg viewBox="0 0 24 24">...</svg>', 'cta-manager' ); ?>"></textarea>
		<?php cta_helper_text( __( 'Paste your SVG icon code. Width and height attributes will be automatically removed.', 'cta-manager' ) ); ?>
	</div>

	<?php
	$preview_id = 'cta-icon-preview-cta';
	$group_id   = 'cta-icon-preview-group-cta';
	include CTA_PLUGIN_DIR . 'templates/admin/partials/icon-preview-box.php';
	unset( $preview_id, $group_id );
	?>

	<div id="cta-add-icon-error-cta" class="cta-error-message" style="display: none;"></div>
</form>

<!-- MODAL_FOOTER -->
<button type="button" class="cta-button-secondary" data-close-modal><?php esc_html_e( 'Cancel', 'cta-manager' ); ?></button>
<?php
$button_text  = __( 'Add Icon', 'cta-manager' );
$button_class = 'cta-button-primary';
$button_id    = 'cta-save-icon-btn-cta';
$button_type  = 'submit';
$extra_attrs  = 'form="cta-add-icon-form-cta"';
include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-spinner.php';
unset( $button_text, $button_class, $button_id, $button_type, $extra_attrs );
