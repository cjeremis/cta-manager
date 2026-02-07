<?php
/**
 * Reusable Info Card Component
 *
 * Lightweight card with title and body content.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $title ) ? $title : '';
$body_html  = isset( $body_html ) ? $body_html : '';
$extra_class = isset( $extra_class ) ? $extra_class : '';
?>

<div class="cta-info-card cta-card <?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $title ) : ?>
		<div class="cta-card__header">
			<?php if ( ! empty( $icon ) ) : ?>
				<span class="cta-card__icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
			<?php endif; ?>
			<h3 class="cta-card__title"><?php echo esc_html( $title ); ?></h3>
		</div>
	<?php endif; ?>
	<div class="cta-card__body">
		<?php echo wp_kses_post( $body_html ); ?>
	</div>
</div>
