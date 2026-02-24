<?php
/**
 * Admin Partial Template - Button With Spinner
 *
 * Handles markup rendering for the button with spinner admin partial template.
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
$button_icon  = $button_icon ?? '';
?>
<button type="<?php echo esc_attr( $button_type ); ?>"
        class="<?php echo esc_attr( $button_class ); ?>"
        <?php echo $button_id ? 'id="' . esc_attr( $button_id ) . '"' : ''; ?>
        <?php echo $extra_attrs; ?>>
	<?php if ( ! empty( $button_icon ) ) : ?>
		<span class="<?php echo esc_attr( $button_icon ); ?>"></span>
	<?php endif; ?>
	<span class="cta-button-text"><?php echo esc_html( $button_text ); ?></span>
	<span class="cta-button-spinner" style="display: none;">
		<span class="dashicons dashicons-update dashicons-spin"></span>
	</span>
</button>
