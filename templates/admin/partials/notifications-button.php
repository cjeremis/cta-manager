<?php
/**
 * Admin Partial Template - Notifications Button
 *
 * Handles markup rendering for the notifications button admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
