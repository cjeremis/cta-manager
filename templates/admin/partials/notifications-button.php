<?php
/**
 * Notifications Button
 *
 * Renders the megaphone icon button for the topbar that opens the notifications panel.
 * Styled to match other support toolbar icons.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = isset( $count ) ? (int) $count : 0;
?>

<button class="cta-support-link cta-notification-button" id="cta-notification-button" type="button" title="<?php esc_attr_e( 'View notifications', 'cta-manager' ); ?>">
	<span class="dashicons dashicons-megaphone"></span>
	<?php if ( $count > 0 ) : ?>
		<span class="cta-notification-badge"></span>
	<?php endif; ?>
</button>
