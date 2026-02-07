<?php
/**
 * File Input Wrapper Partial
 *
 * Custom styled file input with selected file display.
 *
 * Variables:
 * - $input_id (required) - ID for file input element
 * - $input_name (optional) - Name attribute for file input (default: 'file')
 * - $accept_types (optional) - Accepted file types (default: '.json')
 * - $label_text (optional) - Label text for file chooser (default: 'Choose file...')
 * - $wrapper_id (optional) - ID for wrapper element
 * - $selected_id (optional) - ID for selected file container
 * - $file_name_id (optional) - ID for file name span
 * - $file_size_id (optional) - ID for file size span
 * - $remove_button_id (optional) - ID for remove button
 * - $show_file_icon (optional) - Show file icon (default: true)
 * - $file_icon_class (optional) - Icon class for file (default: 'dashicons-media-code')
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$input_name       = $input_name ?? 'file';
$accept_types     = $accept_types ?? '.json';
$label_text       = $label_text ?? __( 'Choose file...', 'cta-manager' );
$wrapper_id       = $wrapper_id ?? $input_id . '-wrapper';
$selected_id      = $selected_id ?? $input_id . '-selected';
$file_name_id     = $file_name_id ?? $input_id . '-name';
$file_size_id     = $file_size_id ?? $input_id . '-size';
$remove_button_id = $remove_button_id ?? $input_id . '-remove';
$show_file_icon   = $show_file_icon ?? true;
$file_icon_class  = $file_icon_class ?? 'dashicons-media-code';
?>
<div class="cta-file-input-wrapper" id="<?php echo esc_attr( $wrapper_id ); ?>">
	<input
		type="file"
		id="<?php echo esc_attr( $input_id ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		accept="<?php echo esc_attr( $accept_types ); ?>"
		class="cta-file-input"
	/>
	<span class="cta-file-input-label">
		<?php echo esc_html( $label_text ); ?>
	</span>
</div>
<div class="cta-file-selected" id="<?php echo esc_attr( $selected_id ); ?>" style="display: none;">
	<?php if ( $show_file_icon ) : ?>
		<span class="cta-file-icon dashicons <?php echo esc_attr( $file_icon_class ); ?>"></span>
	<?php endif; ?>
	<span class="cta-file-name" id="<?php echo esc_attr( $file_name_id ); ?>"></span>
	<span class="cta-file-size" id="<?php echo esc_attr( $file_size_id ); ?>"></span>
	<button type="button" class="cta-file-remove" id="<?php echo esc_attr( $remove_button_id ); ?>" aria-label="<?php esc_attr_e( 'Remove file', 'cta-manager' ); ?>">
		<span class="dashicons dashicons-trash"></span>
	</button>
</div>
