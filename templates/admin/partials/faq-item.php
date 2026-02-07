<?php
/**
 * FAQ Item Partial
 *
 * Displays a single FAQ question and answer.
 *
 * Variables:
 * - $question (required) - The FAQ question
 * - $answer (required) - The FAQ answer
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-faq-item">
	<h3><?php echo esc_html( $question ); ?></h3>
	<p><?php echo esc_html( $answer ); ?></p>
</div>
