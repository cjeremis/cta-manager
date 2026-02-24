<?php
/**
 * Admin Partial Template - Messages
 *
 * Handles markup rendering for the messages admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['message'] ) ) {
	return;
}

$message = sanitize_text_field( wp_unslash( $_GET['message'] ) );

$type = ( 'error' === $message || 'invalid_id' === $message ) ? 'error' : 'success';
?>
<div class="cta-notice <?php echo esc_attr( 'cta-notice-' . $type ); ?>">
	<?php
	switch ( $message ) {
		case 'created':
			esc_html_e( 'CTA created successfully!', 'cta-manager' );
			break;
		case 'updated':
			esc_html_e( 'CTA updated successfully!', 'cta-manager' );
			break;
		case 'deleted':
			esc_html_e( 'CTA deleted successfully!', 'cta-manager' );
			break;
		case 'error':
			esc_html_e( 'An error occurred. Please try again.', 'cta-manager' );
			break;
		case 'invalid_id':
			esc_html_e( 'Invalid CTA ID.', 'cta-manager' );
			break;
		case 'invalid_fields':
			esc_html_e( 'Please complete all required fields for the selected CTA type.', 'cta-manager' );
			break;
	}
	?>
</div>
