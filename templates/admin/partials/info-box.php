<?php
/**
 * Admin Partial Template - Info Box
 *
 * Handles markup rendering for the info box admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant   = isset( $variant ) ? sanitize_html_class( $variant ) : 'info'; // info|warning|success|tip
$title     = isset( $title ) ? $title : '';
$message   = isset( $message ) ? $message : '';
$icon      = isset( $icon ) ? $icon : 'info-outline';
$extra_cls = isset( $extra_class ) ? $extra_class : '';
?>

<div class="cta-info-box cta-info-box--<?php echo esc_attr( $variant ); ?> <?php echo esc_attr( $extra_cls ); ?>">
	<div class="cta-info-box__icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	</div>
	<div class="cta-info-box__content">
		<?php if ( $title ) : ?>
			<h4 class="cta-info-box__title"><?php echo esc_html( $title ); ?></h4>
		<?php endif; ?>
		<?php if ( $message ) : ?>
			<p class="cta-info-box__message"><?php echo wp_kses_post( $message ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $content ) ) : ?>
			<div class="cta-info-box__body"><?php echo wp_kses_post( $content ); ?></div>
		<?php endif; ?>
	</div>
</div>
