<?php
/**
 * Admin Partial Template - Custom Icon Item
 *
 * Handles markup rendering for the custom icon item admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
