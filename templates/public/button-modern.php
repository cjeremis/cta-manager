<?php
/**
 * Modern Theme Button Template
 *
 * Public-facing template for rendering the modern style cta-manager button.
 * Contemporary design with gradient effects and smooth animations.
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
 * @var bool $show_label Show text label in button
 * @var array $custom_colors Custom color settings (if Pro)
 * @var string $button_class Additional CSS classes
 * @var array $data_attributes Data attributes for JS tracking
 */

$phone_number = isset( $phone_number ) ? $phone_number : '';
$button_text = isset( $button_text ) ? $button_text : __( 'Call Us', 'cta-manager' );
$show_icon = isset( $show_icon ) ? (bool) $show_icon : true;
$show_label = isset( $show_label ) ? (bool) $show_label : true;
$custom_colors = isset( $custom_colors ) ? (array) $custom_colors : [];
$button_class = isset( $button_class ) ? $button_class : '';
$data_attributes = isset( $data_attributes ) ? (array) $data_attributes : [];

// Build inline styles from custom colors
$button_style = '';
if ( ! empty( $custom_colors['primary'] ) ) {
	$button_style .= 'background-color: ' . esc_attr( $custom_colors['primary'] ) . ';';
}
if ( ! empty( $custom_colors['text'] ) ) {
	$button_style .= 'color: ' . esc_attr( $custom_colors['text'] ) . ';';
}

// Build data attributes string
$data_attrs = '';
foreach ( $data_attributes as $key => $value ) {
	$data_attrs .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
}
?>

<a
	href="<?php echo esc_url( 'tel:' . $phone_number ); ?>"
	class="cta-button cta-button-modern <?php echo esc_attr( $button_class ); ?>"
	title="<?php esc_attr_e( 'Click to call', 'cta-manager' ); ?>"
	<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo ! empty( $button_style ) ? 'style="' . esc_attr( $button_style ) . '"' : ''; ?>
>
	<span class="cta-button-content">
		<?php if ( $show_icon ) : ?>
			<span class="cta-button-icon cta-button-icon-modern">
				<svg class="cta-icon-phone-modern" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
					<path fill="currentColor" d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
				</svg>
			</span>
		<?php endif; ?>

		<?php if ( $show_label ) : ?>
			<span class="cta-button-text cta-button-text-modern">
				<?php echo esc_html( $button_text ); ?>
			</span>
		<?php endif; ?>
	</span>

	<!-- Ripple effect element -->
	<span class="cta-button-ripple" aria-hidden="true"></span>
</a>
