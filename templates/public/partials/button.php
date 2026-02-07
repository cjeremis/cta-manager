<?php
/**
 * CTA button partial
 *
 * Renders a CTA anchor with data attributes for front-end behaviors.
 *
 * @var array $cta_context Contextual data for rendering
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ctx          = $cta_context ?? [];
$href         = $ctx['href'] ?? '#';
$classes      = isset( $ctx['classes'] ) && is_array( $ctx['classes'] ) ? $ctx['classes'] : [];
$id           = $ctx['id'] ?? '';
$label        = $ctx['label'] ?? '';
$attr         = isset( $ctx['attr'] ) && is_array( $ctx['attr'] ) ? $ctx['attr'] : [];
$data         = isset( $ctx['data'] ) && is_array( $ctx['data'] ) ? $ctx['data'] : [];
$extra_data   = trim( $ctx['extra_data_attributes'] ?? '' );
$icon_html    = $ctx['icon_html'] ?? '';
$icon_classes = isset( $ctx['icon_classes'] ) && is_array( $ctx['icon_classes'] ) ? $ctx['icon_classes'] : [];
$button_styles = $ctx['button_styles'] ?? '';
$text_styles  = $ctx['text_styles'] ?? '';

// Ensure required data attributes exist
$data['data-cta-button'] = $data['data-cta-button'] ?? 'true';
?>
<a
	href="<?php echo esc_url( $href ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	<?php echo $id ? 'id="' . esc_attr( $id ) . '"' : ''; ?>
	<?php echo $button_styles ? 'style="' . esc_attr( $button_styles ) . '"' : ''; ?>
	aria-label="<?php echo esc_attr( $label ); ?>"
	<?php foreach ( $attr as $name => $value ) : ?>
		<?php if ( '' !== $value ) : ?>
			<?php echo $name ? esc_attr( $name ) : ''; ?>="<?php echo esc_attr( $value ); ?>"
		<?php endif; ?>
	<?php endforeach; ?>
	<?php foreach ( $data as $name => $value ) : ?>
		<?php if ( '' !== $value ) : ?>
			<?php echo esc_attr( $name ); ?>="<?php echo esc_attr( $value ); ?>"
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $extra_data; ?>
>
	<?php if ( $icon_html && ! empty( $icon_classes ) ) : ?>
		<span class="<?php echo esc_attr( implode( ' ', $icon_classes ) ); ?>"><?php echo wp_kses_post( $icon_html ); ?></span>
	<?php endif; ?>
	<span class="cta-button__text"<?php echo $text_styles ? ' style="' . esc_attr( $text_styles ) . '"' : ''; ?>><?php echo esc_html( $label ); ?></span>
</a>
