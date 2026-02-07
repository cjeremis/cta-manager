<?php
/**
 * Error Alert Partial
 *
 * Displays an error message with an icon.
 *
 * Variables:
 * - $error_id (required) - Unique ID for the error element
 * - $error_message (required) - The error message to display
 * - $show (optional) - Whether to show the error initially (default: false)
 * - $icon (optional) - Dashicon name without 'dashicons-' prefix (default: 'warning')
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show = $show ?? false;
$icon = $icon ?? 'warning';
?>
<p
	class="cta-error-text"
	id="<?php echo esc_attr( $error_id ); ?>"
	style="display:<?php echo $show ? 'flex' : 'none'; ?>; color:#dc3232; margin-top:8px; font-weight:500; align-items:center; gap:6px;"
>
	<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" style="font-size:16px; vertical-align:middle;"></span>
	<?php echo esc_html( $error_message ); ?>
	</p>
