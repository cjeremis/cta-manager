<?php
/**
 * Admin Partial Template - Faq Item
 *
 * Handles markup rendering for the faq item admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-faq-item">
	<h3><?php echo esc_html( $question ); ?></h3>
	<p><?php echo esc_html( $answer ); ?></p>
</div>
