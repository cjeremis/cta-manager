<?php
/**
 * Admin Partial Template - Upgrade Link
 *
 * Handles markup rendering for the upgrade link admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon = $icon ?? 'star-filled';
$icon_class = 'star-filled' === $icon ? 'cta-animate-fast' : '';
?>
<a href="<?php echo esc_url( $url ); ?>" class="cta-upgrade-link">
	<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?><?php echo $icon_class ? ' ' . esc_attr( $icon_class ) : ''; ?>"></span>
	<?php echo esc_html( $text ); ?>
</a>
