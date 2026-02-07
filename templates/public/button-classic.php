<?php
/**
 * Classic Theme Button Template
 *
 * Public-facing template for rendering the classic style cta-manager button.
 * Professional and traditional design suitable for most websites.
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
	class="cta-button cta-button-classic <?php echo esc_attr( $button_class ); ?>"
	title="<?php esc_attr_e( 'Click to call', 'cta-manager' ); ?>"
	<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo ! empty( $button_style ) ? 'style="' . esc_attr( $button_style ) . '"' : ''; ?>
>
	<?php if ( $show_icon ) : ?>
		<span class="cta-button-icon">
			<svg class="cta-icon-phone" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
				<path fill="currentColor" d="M17.9 16.1c-1.1-1.1-2.9-1.4-4.2-.8-.3.1-.6.2-.9.2-2.6 0-5.1-1-7-2.9-1.9-1.9-2.9-4.4-2.9-7 0-.3.1-.6.2-.9.6-1.3.3-3.1-.8-4.2L2.6 1c-.7-.7-2-.7-2.7 0L.1 1.6C-.6 2.3-.6 3.6.1 4.3c2.5 2.5 3.8 5.8 3.8 9.2 0 3.4-1.3 6.7-3.8 9.2-.7.7-.7 2 0 2.7l.8.8c.7.7 2 .7 2.7 0l1.4-1.4z"/>
			</svg>
		</span>
	<?php endif; ?>
	<span class="cta-button-text"><?php echo esc_html( $button_text ); ?></span>
</a>
