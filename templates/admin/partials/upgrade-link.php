<?php
/**
 * Upgrade Link Partial
 *
 * Displays a link with an icon for upgrading to Pro.
 *
 * Variables:
 * - $text (required) - Link text
 * - $url (required) - Link URL
 * - $icon (optional) - Dashicon name without 'dashicons-' prefix (default: 'star-filled')
 *
 * @package CTA_Manager
 * @since 1.0.0
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
