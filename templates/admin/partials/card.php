<?php
/**
 * Admin Partial Template - Card
 *
 * Handles markup rendering for the card admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon        = isset( $icon ) ? $icon : '';
$title       = isset( $title ) ? $title : '';
$body_html   = isset( $body_html ) ? $body_html : '';
$extra_class = isset( $extra_class ) ? $extra_class : '';
?>

<div class="cta-card <?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $title || $icon ) : ?>
		<div class="cta-card__header">
			<?php if ( $icon ) : ?>
				<span class="cta-card__icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h3 class="cta-card__title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="cta-card__body">
		<?php echo wp_kses_post( $body_html ); ?>
	</div>
</div>
