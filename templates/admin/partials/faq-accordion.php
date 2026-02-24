<?php
/**
 * Admin Partial Template - Faq Accordion
 *
 * Handles markup rendering for the faq accordion admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faq_items = $faq_items ?? [];
?>
<div class="cta-faq-accordion" data-cta-faq-accordion>
	<?php foreach ( $faq_items as $index => $faq_item ) : ?>
		<?php
		$question = $faq_item['question'] ?? '';
		$answer   = $faq_item['answer'] ?? '';
		$is_open  = $index === 0; // open first by default
		include __DIR__ . '/faq-accordion-item.php';
		unset( $question, $answer, $is_open );
		?>
	<?php endforeach; ?>
</div>
