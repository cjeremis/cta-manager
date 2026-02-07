<?php
/**
 * Custom Icon Item Partial
 *
 * Renders a single custom icon grid item with preview and delete button.
 *
 * Variables:
 * - $icon_id (required) - Icon ID
 * - $icon_name (required) - Icon name/label
 * - $svg_content (required) - SVG content to display
 * - $extra_class (optional) - Additional CSS classes
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$extra_class = $extra_class ?? '';
?>

<div class="cta-custom-icon-item <?php echo esc_attr( $extra_class ); ?>" data-icon-id="<?php echo esc_attr( $icon_id ); ?>">
	<div class="cta-custom-icon-preview">
		<?php echo wp_kses_post( $svg_content ); ?>
	</div>
	<div class="cta-custom-icon-name"><?php echo esc_html( $icon_name ); ?></div>
	<button type="button"
		class="cta-custom-icon-delete"
		data-icon-id="<?php echo esc_attr( $icon_id ); ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Delete %s', 'cta-manager' ), $icon_name ) ); ?>">
		<span class="dashicons dashicons-trash"></span>
	</button>
</div>
