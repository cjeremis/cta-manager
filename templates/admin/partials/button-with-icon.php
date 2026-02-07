<?php
/**
 * Button with Icon Partial
 *
 * Displays a button with a dashicon.
 *
 * Variables:
 * - $button_text (required) - Button text content
 * - $icon (required) - Dashicon name without 'dashicons-' prefix
 * - $button_class (optional) - Button CSS class (default: 'cta-button-primary')
 * - $button_id (optional) - Button ID attribute
 * - $button_type (optional) - Button type attribute (default: 'button')
 * - $extra_attrs (optional) - Additional HTML attributes as string
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
