<?php
/**
 * Admin Partial Template - Button With Icon
 *
 * Handles markup rendering for the button with icon admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$button_class = $button_class ?? 'cta-button-primary';
$button_type  = $button_type ?? 'button';
$button_id    = $button_id ?? '';
$extra_attrs  = $extra_attrs ?? '';
?>
<button type="<?php echo esc_attr( $button_type ); ?>"
        class="<?php echo esc_attr( $button_class ); ?>"
        <?php echo $button_id ? 'id="' . esc_attr( $button_id ) . '"' : ''; ?>
        <?php echo $extra_attrs; ?>>
	<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	<?php echo esc_html( $button_text ); ?>
</button>
