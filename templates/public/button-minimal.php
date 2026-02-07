<?php
/**
 * Minimal Theme Button Template
 *
 * Public-facing template for rendering the minimal style cta-manager button.
 * Simple and elegant design with subtle styling.
 *
 * @package CTA_Manager
 * @subpackage Templates/Public
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables available in this template:
 *
 * @var string $phone_number The phone number to call
 * @var string $button_text Button display text
 * @var bool $show_icon Show phone icon in button
 * @var array $custom_colors Custom color settings (if Pro)
 * @var string $button_class Additional CSS classes
 * @var array $data_attributes Data attributes for JS tracking
 */

$phone_number = isset( $phone_number ) ? $phone_number : '';
$button_text = isset( $button_text ) ? $button_text : __( 'Call Us', 'cta-manager' );
$show_icon = isset( $show_icon ) ? (bool) $show_icon : true;
$custom_colors = isset( $custom_colors ) ? (array) $custom_colors : [];
$button_class = isset( $button_class ) ? $button_class : '';
$data_attributes = isset( $data_attributes ) ? (array) $data_attributes : [];

// Build inline styles from custom colors
$button_style = '';
if ( ! empty( $custom_colors['primary'] ) ) {
	$button_style .= 'border-color: ' . esc_attr( $custom_colors['primary'] ) . '; color: ' . esc_attr( $custom_colors['primary'] ) . ';';
}

// Build data attributes string
$data_attrs = '';
foreach ( $data_attributes as $key => $value ) {
	$data_attrs .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
}
?>

<a
	href="<?php echo esc_url( 'tel:' . $phone_number ); ?>"
	class="cta-button cta-button-minimal <?php echo esc_attr( $button_class ); ?>"
	title="<?php esc_attr_e( 'Click to call', 'cta-manager' ); ?>"
	<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo ! empty( $button_style ) ? 'style="' . esc_attr( $button_style ) . '"' : ''; ?>
>
	<?php if ( $show_icon ) : ?>
		<span class="cta-button-icon cta-button-icon-minimal">
			<svg class="cta-icon-phone-minimal" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" stroke="currentColor" stroke-width="2" fill="none">
				<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
			</svg>
		</span>
	<?php endif; ?>
	<span class="cta-button-text cta-button-text-minimal">
		<?php echo esc_html( $button_text ); ?>
	</span>
</a>
