<?php
/**
 * Toggle Switch Partial
 *
 * Displays a toggle switch for boolean settings.
 *
 * Variables:
 * - $input_name (required) - Input name attribute
 * - $label (required) - Toggle label text
 * - $checked (optional) - Whether toggle is checked (default: false)
 * - $input_id (optional) - Input ID attribute
 * - $input_value (optional) - Input value (default: '1')
 * - $extra_class (optional) - Additional CSS classes
 * - $on_text (optional) - Text for "on" state (default: 'On')
 * - $off_text (optional) - Text for "off" state (default: 'Off')
 * - $input_attrs (optional) - Additional attributes for the input element
 * - $wrapper_attrs (optional) - Additional attributes for the label element
 * - $size (optional) - 'regular' or 'small' (default: 'regular')
 * - $show_status (optional) - Whether to render status text (default: true)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checked       = $checked ?? false;
$input_id      = $input_id ?? '';
$input_value   = $input_value ?? '1';
$extra_class   = $extra_class ?? '';
$on_text       = $on_text ?? __( 'On', 'cta-manager' );
$off_text      = $off_text ?? __( 'Off', 'cta-manager' );
$input_attrs   = $input_attrs ?? '';
$wrapper_attrs = $wrapper_attrs ?? '';
$size          = $size ?? 'regular';
$show_status   = isset( $show_status ) ? (bool) $show_status : true;

$is_small      = 'small' === $size;
$track_class   = $is_small ? 'cta-toggle-track-small' : 'cta-toggle-track';
$thumb_class   = $is_small ? 'cta-toggle-thumb-small' : 'cta-toggle-thumb';
$label_class   = $is_small ? 'cta-toggle-label-small' : 'cta-toggle-label';
?>
<label class="cta-toggle <?php echo esc_attr( $extra_class ); ?>" <?php echo $wrapper_attrs ? $wrapper_attrs : ''; ?>>
	<input type="checkbox"
	       name="<?php echo esc_attr( $input_name ); ?>"
	       <?php echo $input_id ? 'id="' . esc_attr( $input_id ) . '"' : ''; ?>
	       value="<?php echo esc_attr( $input_value ); ?>"
	       <?php checked( $checked ); ?>
	       <?php echo $input_attrs; ?> />
	<span class="<?php echo esc_attr( $track_class ); ?>" aria-hidden="true">
		<span class="<?php echo esc_attr( $thumb_class ); ?>"></span>
	</span>
	<?php if ( $show_status ) : ?>
		<span class="cta-toggle-status" data-on="<?php echo esc_attr( $on_text ); ?>" data-off="<?php echo esc_attr( $off_text ); ?>"></span>
	<?php endif; ?>
	<span class="<?php echo esc_attr( $label_class ); ?>"><?php echo esc_html( $label ); ?></span>
</label>
