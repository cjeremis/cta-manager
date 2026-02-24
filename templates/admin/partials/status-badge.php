<?php
/**
 * Admin Partial Template - Status Badge
 *
 * Handles markup rendering for the status badge admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pulse_class  = $pulse_class ?? '';
$extra_styles = $extra_styles ?? '';
$extra_attrs  = $extra_attrs ?? '';
?>
<span class="cta-badge cta-badge-<?php echo esc_attr( $variant ); ?><?php echo $pulse_class ? ' ' . esc_attr( $pulse_class ) : ''; ?>" <?php echo $extra_styles ? 'style="' . esc_attr( $extra_styles ) . '"' : ''; ?> <?php echo $extra_attrs ? $extra_attrs : ''; ?>><?php echo esc_html( $text ); ?></span>
