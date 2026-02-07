<?php
/**
 * Button with Spinner Partial
 *
 * Displays a button with loading spinner for async operations.
 *
 * Variables:
 * - $button_text (required) - Button text content
 * - $button_class (optional) - Button CSS class (default: 'cta-button-primary')
 * - $button_id (optional) - Button ID attribute
 * - $button_type (optional) - Button type attribute (default: 'button')
 * - $extra_attrs (optional) - Additional HTML attributes as string
 * - $button_icon (optional) - Additional icon class list (e.g., 'dashicons dashicons-yes-alt')
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
