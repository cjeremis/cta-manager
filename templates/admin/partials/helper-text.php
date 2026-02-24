<?php
/**
 * Admin Partial Template - Helper Text
 *
 * Handles markup rendering for the helper text admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon        = isset( $icon ) ? $icon : '';
$text        = isset( $text ) ? $text : '';
$extra_class = isset( $extra_class ) ? $extra_class : '';
$variant     = isset( $variant ) ? $variant : 'inline'; // inline|block
?>

<div class="cta-helper-block cta-helper-block--<?php echo esc_attr( $variant ); ?> <?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $icon ) : ?>
		<span class="cta-helper-block__icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	<?php endif; ?>
	<div class="cta-helper-block__text"><?php echo wp_kses_post( $text ); ?></div>
</div>
