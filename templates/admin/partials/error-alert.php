<?php
/**
 * Admin Partial Template - Error Alert
 *
 * Handles markup rendering for the error alert admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
